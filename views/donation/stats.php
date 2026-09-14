<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\forms\PeriodForm $period */
/** @var array|null $summary */
/** @var array $byDays */
/** @var array $byUsers */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Статистика поддержки';
$formatter = Yii::$app->formatter;
?>
<h1><?= Html::encode($this->title) ?></h1>

<div class="card mb-4">
    <div class="card-body pb-0">
        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => ['stats'],
            'options' => ['class' => 'row g-2 align-items-end'],
            'fieldConfig' => ['options' => ['class' => 'col-auto']],
            // без зелёных галочек на успешно провалидированных полях
            'successCssClass' => '',
        ]) ?>
            <?= $form->field($period, 'from')->input('date') ?>
            <?= $form->field($period, 'to')->input('date') ?>
            <div class="col-auto mb-3">
                <?= Html::submitButton('Показать', ['class' => 'btn btn-primary']) ?>
            </div>
        <?php ActiveForm::end() ?>
    </div>
</div>

<?php if ($summary === null): ?>

    <div class="alert alert-warning">Период задан неверно.</div>

<?php else: ?>

    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-3 mb-4">
        <?php foreach ([
            ['Дней в периоде', $formatter->asInteger($period->days())],
            ['Донатов', $formatter->asInteger($summary['donations'])],
            ['Поддержавших', $formatter->asInteger($summary['supporters'])],
            ['Собрано', $formatter->asDecimal($summary['total'], 0) . ' ₽'],
            ['Средний донат', $formatter->asDecimal($summary['average'], 0) . ' ₽'],
            ['В среднем в день', $formatter->asDecimal($summary['perDay'], 0) . ' ₽'],
        ] as [$label, $value]): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="h4 mb-1 text-nowrap"><?= Html::encode($value) ?></div>
                        <div class="small text-body-secondary"><?= Html::encode($label) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h2 class="h5 border-bottom pb-2">По дням</h2>

            <?php if ($byDays === []): ?>
                <p class="text-body-secondary">За период донатов не было.</p>
            <?php else: ?>
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>День</th>
                            <th class="text-end">Донатов</th>
                            <th class="text-end">Сумма</th>
                            <th class="text-end">Нарастающим</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byDays as $row): ?>
                            <tr<?= $row['donations'] === 0 ? ' class="text-body-secondary"' : '' ?>>
                                <td><?= $formatter->asDate($row['day']) ?></td>
                                <td class="text-end"><?= $row['donations'] ?></td>
                                <td class="text-end text-nowrap"><?= $formatter->asDecimal($row['total'], 0) ?> ₽</td>
                                <td class="text-end text-nowrap"><?= $formatter->asDecimal($row['running_total'], 0) ?> ₽</td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            <?php endif ?>
        </div>

        <div class="col-md-6">
            <h2 class="h5 border-bottom pb-2">По пользователям</h2>

            <?php if ($byUsers === []): ?>
                <p class="text-body-secondary">За период донатов не было.</p>
            <?php else: ?>
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Кто</th>
                            <th class="text-end">Донатов</th>
                            <th class="text-end">Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byUsers as $row): ?>
                            <tr>
                                <td><?= Html::encode($row['username']) ?></td>
                                <td class="text-end"><?= $row['donations'] ?></td>
                                <td class="text-end text-nowrap"><?= $formatter->asDecimal($row['total'], 0) ?> ₽</td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            <?php endif ?>
        </div>
    </div>

<?php endif ?>
