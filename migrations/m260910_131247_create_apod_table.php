<?php

declare(strict_types=1);

use yii\db\Migration;

// Картинка дня (APOD). Храним описание и ссылки, сам файл остаётся у NASA.
class m260910_131247_create_apod_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%apod}}', [
            'id' => $this->primaryKey(),
            'date' => $this->date()->notNull()->comment('Дата публикации'),
            'title' => $this->string(255)->notNull()->comment('Название'),
            'explanation' => $this->text()->comment('Описание снимка'),
            'media_type' => $this->string(32)->notNull()->defaultValue('image')
                ->comment('Тип медиа'),
            'url' => $this->text()->comment('Ссылка на медиа'),
            'hdurl' => $this->text()->comment('Ссылка в HD'),
            'thumbnail_url' => $this->text()->comment('Превью для видео'),
            'copyright' => $this->text()->comment('Автор снимка'),
            'created_at' => 'timestamptz NOT NULL DEFAULT now()',
        ]);

        $this->addCommentOnColumn('{{%apod}}', 'created_at', 'Загружено');
        $this->createIndex('idx_apod_date', '{{%apod}}', 'date', true);
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%apod}}');
    }
}
