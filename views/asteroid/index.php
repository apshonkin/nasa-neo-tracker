<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\search\AsteroidApproachSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use app\models\AsteroidApproach;
use yii\bootstrap5\LinkPager;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Астероиды';

// имя поля берём у модели, чтобы не дублировать его строкой в двух местах
$hazardousName = Html::getInputName($searchModel, 'hazardousOnly');
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>Сближения с Землёй по данным NASA. Ни одно из них не является столкновением:
    даже самый близкий пролёт проходит в нескольких расстояниях до Луны.</p>

<p>
    <?= Html::checkbox($hazardousName, (bool) $searchModel->hazardousOnly, [
        'id' => 'hazardous-only',
        'label' => 'Показать только опасные',
        'class' => 'form-check-input',
        'labelOptions' => ['class' => 'form-check-label ms-1'],
        // без uncheck снятая галочка не отправляется
        'uncheck' => '0',
    ]) ?>
</p>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    // строка фильтра в шапке таблицы: поиск по названию
    'filterModel' => $searchModel,
    // флажок лежит вне таблицы, но участвует в тех же фильтрах
    'filterSelector' => 'input[name="' . $hazardousName . '"]',
    // пагинатор из bootstrap5
    'pager' => ['class' => LinkPager::class],
    'emptyText' => ($searchModel->name ?? '') !== ''
        ? 'По этому названию ничего не нашлось.'
        : 'Предстоящих сближений в базе нет. Забрать: php yii nasa/asteroids',
    'columns' => [
        [
            'attribute' => 'name',
            'format' => 'raw',
            'value' => static fn (AsteroidApproach $model): string => Html::encode($model->name)
                . ($model->is_hazardous
                    ? ' ' . Html::tag('span', 'опасный', ['class' => 'badge text-bg-danger'])
                    : ''),
        ],
        'approach_at:datetime',
        [
            'label' => 'Осталось',
            'value' => static fn (AsteroidApproach $model): string => Yii::$app->formatter
                ->asRelativeTime($model->approach_at),
        ],
        [
            'attribute' => 'miss_distance_lunar',
            'format' => ['decimal', 1],
        ],
        [
            'attribute' => 'velocity_kmh',
            'format' => 'integer',
        ],
        [
            'attribute' => 'diameter_m',
            'format' => 'integer',
        ],
    ],
]) ?>
