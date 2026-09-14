<?php

declare(strict_types=1);

use yii\db\Migration;

class m260910_154305_create_asteroid_approach_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('{{%asteroid_approach}}', [
            'id' => $this->primaryKey(),
            // по нему обновляем запись
            'neo_reference_id' => $this->string(32)->notNull()->comment('ID в каталоге NASA'),
            'name' => $this->string(255)->notNull()->comment('Название'),
            // NASA даёт вилку диаметра, берем середину
            'diameter_m' => $this->decimal(12, 1)->comment('Средний диаметр, м'),
            'velocity_kmh' => $this->decimal(14, 2)->comment('Скорость, км/ч'),
            'approach_at' => 'timestamptz NOT NULL',
            'miss_distance_lunar' => $this->decimal(10, 4)->notNull()->comment('Промах, лунных дистанций'),
            'is_hazardous' => $this->boolean()->notNull()->defaultValue(false)->comment('Потенциально опасен'),
            'created_at' => 'timestamptz NOT NULL DEFAULT now()',
        ]);
        $this->addCommentOnColumn('{{%asteroid_approach}}', 'approach_at', 'Момент сближения');
        $this->addCommentOnColumn('{{%asteroid_approach}}', 'created_at', 'Загружено');

        // защита от дублей при повторной выгрузке и ключ для обновления
        $this->createIndex(
            'idx_approach_unique',
            '{{%asteroid_approach}}',
            ['neo_reference_id', 'approach_at'],
            true,
        );
        // главный запрос страницы: предстоящие сближения по времени
        $this->createIndex('idx_approach_at', '{{%asteroid_approach}}', 'approach_at');
    }

    public function safeDown(): void
    {
        $this->dropTable('{{%asteroid_approach}}');
    }
}
