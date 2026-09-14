<?php

declare(strict_types=1);

use yii\db\Migration;

// Донаты в поддержку проекта. Платёж имитируется: запись создаётся в статусе
// «ожидает оплаты» и переходит в «оплачен» после возврата со страницы шлюза.
class m260911_140459_create_donation_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%donation}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->comment('Кто поддержал'),
            // одна валюта на проект, поэтому отдельной колонки под неё нет
            'amount' => $this->decimal(10, 2)->notNull()->comment('Сумма, ₽'),
            'comment' => $this->string(255)->comment('Комментарий'),
            'status' => $this->string(16)->notNull()->defaultValue('pending')->comment('Статус'),
            // внешний идентификатор платежа: у фейкового шлюза выдуманный,
            // у настоящего сюда ляжет его id - по нему потом сверяют платежи
            'payment_id' => $this->string(64)->comment('ID платежа в шлюзе'),
            'created_at' => 'timestamptz NOT NULL DEFAULT now()',
            'paid_at' => 'timestamptz NULL',
        ]);
        $this->addCommentOnColumn('{{%donation}}', 'created_at', 'Создан');
        $this->addCommentOnColumn('{{%donation}}', 'paid_at', 'Оплачен');

        $this->addForeignKey('fk_donation_user', '{{%donation}}', 'user_id', '{{%user}}', 'id', 'CASCADE');
        // список последних: только оплаченные, свежие сверху
        $this->createIndex('idx_donation_paid_at', '{{%donation}}', 'paid_at');
        // топ поддержавших: группировка по пользователю
        $this->createIndex('idx_donation_user', '{{%donation}}', 'user_id');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%donation}}');
    }
}
