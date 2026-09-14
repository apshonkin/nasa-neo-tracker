<?php

declare(strict_types=1);

use yii\db\Migration;
use yii\db\Query;

class m260911_141436_seed_demo_donations extends Migration
{
    private const PASSWORD = 'demo';

    private const NAMES = [
        'Adam',
        'Artem',
        'Boris',
        'Vadim',
        'Valera',
        'Vera',
        'Viktor',
        'Vlad',
        'Gleb',
        'Grigoriy',
        'Daniil',
        'Darya',
        'Denis',
        'Dmitriy',
        'Egor',
        'Ekaterina',
        'Elena',
        'Zhanna',
        'Zoya',
        'Igor',
        'Ilya',
        'Irina',
        'Kirill',
        'Konstantin',
        'Ksenia',
        'Larisa',
        'Leonid',
        'Lidia',
        'Lyubov',
        'Maksim',
        'Marina',
        'Mark',
        'Matvey',
        'Mihail',
        'Nadezhda',
        'Nataliya',
        'Nikita',
        'Nikolay',
        'Nina',
        'Oleg',
        'Olga',
        'Pavel',
        'Polina',
        'Raisa',
        'Roman',
        'Ruslan',
        'Svetlana',
        'Semen',
        'Sergey',
        'Sofia',
        'Stepan',
        'Taisiya',
        'Tamara',
        'Timur',
        'Ulyana',
        'Fedor',
        'Filipp',
        'Eduard',
        'Yuliya',
        'Yakov',
    ];

    private const COMMENTS = [
        'Отличный проект',
        'Спасибо за работу',
        'Держите на кофе',
        'Полезная штука',
        'Успехов!',
        'Астероиды это интересно',
        null,
        null,
        null,
    ];

    public function safeUp(): void
    {
        // одинаковые суммы и даты при каждом накатывании
        mt_srand(20260911);

        $security = Yii::$app->security;
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('user');
        $now = time();

        // один хеш на всех: bcrypt считается сотни миллисекунд, а тут 60 записей.
        // Пароль у всех демо-пользователей одинаковый.
        $hash = $security->generatePasswordHash(self::PASSWORD);

        $users = [];
        foreach (self::NAMES as $name) {
            $users[] = [
                $name,
                $security->generateRandomString(),
                $hash,
                strtolower($name) . '@example.com',
                10,
                $now,
                $now,
            ];
        }

        $this->batchInsert(
            '{{%user}}',
            ['username', 'auth_key', 'password_hash', 'email', 'status', 'created_at', 'updated_at'],
            $users,
        );

        $ids = (new Query())
            ->select(['id'])
            ->from('{{%user}}')
            ->where(['username' => self::NAMES])
            ->column($this->db);

        foreach ($ids as $id) {
            $auth->assign($role, (int) $id);
        }

        $donations = [];
        for ($i = 0; $i < 100; $i++) {
            // за последние 60 дней, в случайный момент суток
            $paidAt = date('Y-m-d H:i:sP', $now - mt_rand(0, 60 * 86400));
            $donations[] = [
                (int) $ids[array_rand($ids)],
                // суммы кратны 50, чтобы выглядели как настоящие
                mt_rand(2, 60) * 50,
                self::COMMENTS[array_rand(self::COMMENTS)],
                'paid',
                'fake_seed_' . $i,
                $paidAt,
                $paidAt,
            ];
        }

        $this->batchInsert(
            '{{%donation}}',
            ['user_id', 'amount', 'comment', 'status', 'payment_id', 'created_at', 'paid_at'],
            $donations,
        );
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;

        $ids = (new Query())
            ->select(['id'])
            ->from('{{%user}}')
            ->where(['username' => self::NAMES])
            ->column($this->db);

        foreach ($ids as $id) {
            $auth->revokeAll((int) $id);
        }

        // донаты уйдут сами: внешний ключ стоит с ON DELETE CASCADE
        $this->delete('{{%user}}', ['username' => self::NAMES]);
    }
}
