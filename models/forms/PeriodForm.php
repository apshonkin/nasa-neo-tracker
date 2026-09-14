<?php

declare(strict_types=1);

namespace app\models\forms;

use DateTimeImmutable;
use yii\base\Model;

// Период для отчётов. По умолчанию - последние 30 дней.
class PeriodForm extends Model
{
    public const FORMAT = 'Y-m-d';

    public ?string $from = null;
    public ?string $to = null;

    public function init(): void
    {
        parent::init();

        $this->to ??= (new DateTimeImmutable('today'))->format(self::FORMAT);
        $this->from ??= (new DateTimeImmutable('today -29 days'))->format(self::FORMAT);
    }

    public function rules(): array
    {
        return [
            [['from', 'to'], 'required'],
            [['from', 'to'], 'date', 'format' => 'php:' . self::FORMAT],
            [['to'], 'compare', 'compareAttribute' => 'from', 'operator' => '>=',
                'message' => 'Конец периода не может быть раньше начала.'],
        ];
    }

    public function attributeLabels(): array
    {
        return ['from' => 'С', 'to' => 'По'];
    }

    public function fromDate(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->from . ' 00:00:00');
    }

    // конец дня, иначе донаты за последнюю дату не попадут в выборку
    public function toDate(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->to . ' 23:59:59');
    }

    public function days(): int
    {
        return (int) $this->fromDate()->diff($this->toDate())->days + 1;
    }
}
