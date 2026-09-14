<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "asteroid_approach".
 *
 * @property int $id
 * @property string $neo_reference_id ID в каталоге NASA
 * @property string $name Название
 * @property float|null $diameter_m Средний диаметр, м
 * @property float|null $velocity_kmh Скорость, км/ч
 * @property string $approach_at Момент сближения
 * @property float $miss_distance_lunar Промах, лунных дистанций
 * @property bool $is_hazardous Потенциально опасен
 * @property string $created_at Загружено
 */
class AsteroidApproach extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'asteroid_approach';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['diameter_m', 'velocity_kmh'], 'default', 'value' => null],
            [['is_hazardous'], 'default', 'value' => 0],
            [['neo_reference_id', 'name', 'approach_at', 'miss_distance_lunar'], 'required'],
            [['diameter_m', 'velocity_kmh', 'miss_distance_lunar'], 'number'],
            [['approach_at', 'created_at'], 'safe'],
            [['is_hazardous'], 'boolean'],
            [['neo_reference_id'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 255],
            [['neo_reference_id', 'approach_at'], 'unique', 'targetAttribute' => ['neo_reference_id', 'approach_at']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'neo_reference_id' => 'ID в каталоге NASA',
            'name' => 'Название',
            'diameter_m' => 'Средний диаметр, м',
            'velocity_kmh' => 'Скорость, км/ч',
            'approach_at' => 'Момент сближения',
            'miss_distance_lunar' => 'Промах, лунных дистанций',
            'is_hazardous' => 'Потенциально опасен',
            'created_at' => 'Загружено',
        ];
    }

}
