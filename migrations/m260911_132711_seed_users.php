<?php

declare(strict_types=1);

use yii\db\Migration;

// Два пользователя для демонстрации: админ и обычный.
// Пароли совпадают с логинами - это демо-данные, а не боевые.
class m260911_132711_seed_users extends Migration
{
    private const USERS = [
        ['username' => 'admin', 'email' => 'admin@example.com', 'password' => 'admin', 'role' => 'admin'],
        ['username' => 'user', 'email' => 'user@example.com', 'password' => 'user', 'role' => 'user'],
    ];

    public function safeUp(): void
    {
        $security = Yii::$app->security;
        $auth = Yii::$app->authManager;
        $now = time();

        foreach (self::USERS as $data) {
            $this->insert('{{%user}}', [
                'username' => $data['username'],
                'email' => $data['email'],
                // хеш считается при накатывании, в миграции пароля в открытом виде нет
                'password_hash' => $security->generatePasswordHash($data['password']),
                'auth_key' => $security->generateRandomString(),
                'status' => 10, // активен
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $id = (int) $this->db
                ->createCommand('select id from {{%user}} where username = :username', [':username' => $data['username']])
                ->queryScalar();

            $auth->assign($auth->getRole($data['role']), $id);
        }
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->authManager;

        foreach (self::USERS as $data) {
            $id = $this->db
                ->createCommand('select id from {{%user}} where username = :username', [':username' => $data['username']])
                ->queryScalar();

            if ($id !== false && $id !== null) {
                $auth->revokeAll((int) $id);
            }

            $this->delete('{{%user}}', ['username' => $data['username']]);
        }
    }
}
