<?php

declare(strict_types=1);

namespace app\models\search;

use app\models\AsteroidApproach;
use yii\data\ActiveDataProvider;
use yii\db\Expression;


class AsteroidApproachSearch extends AsteroidApproach
{
    // не колонка таблицы, а флажок в интерфейсе
    public bool|string|null $hazardousOnly = null;

    // добавляем руками , т.к нет в бд
    public function attributes(): array
    {
        return array_merge(parent::attributes(), ['hazardousOnly']);
    }

    public function rules(): array
    {
        // фильтруем по названию и флажку, остальные поля из запроса игнорируются
        return [
            [['name'], 'string', 'max' => 255],
            [['hazardousOnly'], 'boolean'],
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    public function search(array $params): ActiveDataProvider
    {
        // прошедшие сближения в списке не нужны: обратный отсчёт по ним бессмысленен
        $query = AsteroidApproach::find()->where(['>=', 'approach_at', new Expression('now()')]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['approach_at' => SORT_ASC],
                'attributes' => [
                    'name',
                    'approach_at',
                    'miss_distance_lunar',
                    'velocity_kmh',
                    'diameter_m',
                ],
            ],
            'pagination' => ['pageSize' => 10],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // с кривым фильтром ничего не показываем
            $query->where('0=1');

            return $dataProvider;
        }

        // ilike, like регистрозависим
        $query->andFilterWhere(['ilike', 'name', $this->name]);

        if ($this->hazardousOnly) {
            $query->andWhere(['is_hazardous' => true]);
        }

        return $dataProvider;
    }
}
