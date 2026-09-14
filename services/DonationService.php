<?php

declare(strict_types=1);

namespace app\services;

use app\components\payment\Payment;
use app\components\payment\PaymentGatewayInterface;
use app\models\Donation;
use app\models\User;
use DateTimeImmutable;
use Yii;
use yii\db\Query;
use yii\helpers\Url;

// Сценарий поддержки проекта: создать платёж, подтвердить оплату, отдать списки.
class DonationService
{
    public function __construct(private readonly PaymentGatewayInterface $gateway)
    {
    }

    /**
     * Завести донат и получить адрес оплаты. Запись создаётся до похода в шлюз,
     * чтобы платежу было куда вернуться и было что подтверждать.
     */
    public function start(int $userId, float $amount, ?string $comment): ?Payment
    {
        $donation = new Donation([
            'user_id' => $userId,
            'amount' => $amount,
            'comment' => $comment !== '' ? $comment : null,
        ]);

        if (!$donation->save()) {
            Yii::error('Донат не сохранился: ' . json_encode($donation->getFirstErrors(), JSON_UNESCAPED_UNICODE), 'donation');

            return null;
        }

        $payment = $this->gateway->create(
            $amount,
            'Поддержка проекта NEO Tracker',
            Url::to(['/donation/back', 'id' => $donation->id], true),
        );

        $donation->payment_id = $payment->id;
        $donation->save(false);

        return $payment;
    }

    /**
     * Подтвердить оплату. Верим не возврату пользователя, а ответу шлюза:
     * адрес возврата можно открыть руками, не заплатив.
     */
    public function confirm(Donation $donation): bool
    {
        if ($donation->isPaid()) {
            return true;
        }

        if ($donation->payment_id === null || !$this->gateway->isPaid($donation->payment_id)) {
            return false;
        }

        return $donation->markPaid();
    }

    /**
     * Последние поддержавшие.
     *
     * @return Donation[]
     */
    public function latest(int $limit = 10): array
    {
        return Donation::find()
            ->with('user')
            ->where(['status' => Donation::STATUS_PAID])
            ->orderBy(['paid_at' => SORT_DESC])
            ->limit($limit)
            ->all();
    }

    /**
     * Топ поддержавших по сумме всех оплаченных донатов.
     *
     * @return list<array{username: string, total: float, donations: int}>
     */
    public function top(int $limit = 10): array
    {
        $rows = (new Query())
            ->select([
                'username' => 'u.username',
                'total' => 'sum(d.amount)',
                'donations' => 'count(*)',
            ])
            ->from(['d' => Donation::tableName()])
            ->innerJoin(['u' => User::tableName()], 'u.id = d.user_id')
            ->where(['d.status' => Donation::STATUS_PAID])
            ->groupBy('u.username')
            ->orderBy(['total' => SORT_DESC])
            ->limit($limit)
            ->all();

        return array_map(static fn (array $row): array => [
            'username' => (string) $row['username'],
            'total' => (float) $row['total'],
            'donations' => (int) $row['donations'],
        ], $rows);
    }

    /**
     * Итоги за период.
     *
     * @return array{donations: int, supporters: int, total: float, average: float, perDay: float}
     */
    public function summary(DateTimeImmutable $from, DateTimeImmutable $to, int $days): array
    {
        $row = $this->paidBetween($from, $to)
            ->select([
                'donations' => 'count(*)',
                'supporters' => 'count(distinct user_id)',
                'total' => 'coalesce(sum(amount), 0)',
            ])
            ->one() ?: ['donations' => 0, 'supporters' => 0, 'total' => 0];

        $donations = (int) $row['donations'];
        $total = (float) $row['total'];

        return [
            'donations' => $donations,
            'supporters' => (int) $row['supporters'],
            'total' => $total,
            // средний чек
            'average' => $donations > 0 ? $total / $donations : 0.0,
            // среднее в день считаем по всем дням периода, включая пустые:
            // иначе цифра завышена и отвечает на другой вопрос
            'perDay' => $days > 0 ? $total / $days : 0.0,
        ];
    }

    /**
     * Суммы по дням — сплошным рядом, без пропусков, с нарастающим итогом.
     *
     * Здесь чистый SQL, а не конструктор запросов, и по делу:
     *  - generate_series достраивает дни, в которые донатов не было, иначе
     *    в таблице дыры и видно только «удачные» дни;
     *  - оконная функция даёт накопленную сумму одним проходом, без второго
     *    запроса и без досчёта в PHP.
     *
     * @return list<array{day: string, donations: int, total: float, running_total: float}>
     */
    public function byDays(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $sql = <<<'SQL'
            select
                series.day::date as day,
                count(d.id) as donations,
                coalesce(sum(d.amount), 0) as total,
                sum(coalesce(sum(d.amount), 0)) over (order by series.day) as running_total
            from generate_series(:from::date, :to::date, interval '1 day') as series(day)
            left join {{%donation}} d
                on (d.paid_at at time zone :tz)::date = series.day::date
               and d.status = :status
            group by series.day
            order by series.day desc
            SQL;

        $rows = Yii::$app->db->createCommand($sql, [
            ':from' => $from->format('Y-m-d'),
            ':to' => $to->format('Y-m-d'),
            ':tz' => Yii::$app->timeZone,
            ':status' => Donation::STATUS_PAID,
        ])->queryAll();

        return array_map(static fn (array $r): array => [
            'day' => (string) $r['day'],
            'donations' => (int) $r['donations'],
            'total' => (float) $r['total'],
            'running_total' => (float) $r['running_total'],
        ], $rows);
    }

    /**
     * Суммы по пользователям.
     *
     * @return list<array{username: string, donations: int, total: float}>
     */
    public function byUsers(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $rows = $this->paidBetween($from, $to)
            ->select([
                'username' => 'u.username',
                'donations' => 'count(*)',
                'total' => 'sum(d.amount)',
            ])
            ->innerJoin(['u' => User::tableName()], 'u.id = d.user_id')
            ->groupBy('u.username')
            ->orderBy(['total' => SORT_DESC])
            ->all();

        return array_map(static fn (array $r): array => [
            'username' => (string) $r['username'],
            'donations' => (int) $r['donations'],
            'total' => (float) $r['total'],
        ], $rows);
    }

    // общая часть всех отчётов: только оплаченные и только за период
    private function paidBetween(DateTimeImmutable $from, DateTimeImmutable $to): Query
    {
        return (new Query())
            ->from(['d' => Donation::tableName()])
            ->where(['d.status' => Donation::STATUS_PAID])
            ->andWhere(['between', 'd.paid_at', $from->format('Y-m-d H:i:sP'), $to->format('Y-m-d H:i:sP')]);
    }
}
