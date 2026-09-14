<?php

declare(strict_types=1);

use yii\db\Migration;

class m260911_131430_create_user_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(255)->notNull()->unique()->comment('Логин'),
            'auth_key' => $this->string(32)->notNull()->comment('Ключ автологина'),
            'password_hash' => $this->string(255)->notNull()->comment('Хеш пароля'),
            'password_reset_token' => $this->string(255)->unique()->comment('Токен сброса пароля'),
            'email' => $this->string(255)->notNull()->unique()->comment('E-mail'),
            // 10 - активен, 9 - не активирован, 0 - удалён (константы модели)
            'status' => $this->smallInteger()->notNull()->defaultValue(10)->comment('Статус'),
            'verification_token' => $this->string(255)->defaultValue(null)->comment('Токен подтверждения почты'),
            'created_at' => $this->integer()->notNull()->comment('Зарегистрирован'),
            'updated_at' => $this->integer()->notNull()->comment('Изменён'),
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%user}}');
    }
}
