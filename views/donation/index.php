<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\forms\DonationForm $form */
/** @var app\models\Donation[] $latest */
/** @var array $top */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Поддержи проект';
$formatter = Yii::$app->formatter;
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>Проект живёт на энтузиазме и открытых данных NASA. Если он вам полезен — поддержите.</p>

<div class="row">
    <div class="col-md-5">
        <?php if (Yii::$app->user->isGuest): ?>
            <div class="alert alert-secondary">
                Чтобы поддержать проект, <?= Html::a('войдите', ['/site/login']) ?>.
            </div>
        <?php else: ?>
            <?php $activeForm = ActiveForm::begin(['action' => ['create']]) ?>
                <?= $activeForm->field($form, 'amount')->input('number', ['min' => 1, 'step' => 1]) ?>
                <?= $activeForm->field($form, 'comment')->textInput(['maxlength' => 255, 'placeholder' => 'необязательно']) ?>
                <?= Html::submitButton('Поддержать', ['class' => 'btn btn-primary']) ?>
            <?php ActiveForm::end() ?>
        <?php endif ?>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <h2 class="h5">Последние поддержавшие</h2>

        <?php if ($latest === []): ?>
            <p class="text-body-secondary">Пока никого. Будьте первым.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Кто</th>
                        <th class="text-end">Сумма</th>
                        <th>Когда</th>
                        <th>Комментарий</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($latest as $donation): ?>
                        <tr>
                            <td><?= Html::encode($donation->user->username) ?></td>
                            <td class="text-end text-nowrap"><?= $formatter->asDecimal($donation->amount, 0) ?> ₽</td>
                            <td class="text-nowrap"><?= $formatter->asRelativeTime($donation->paid_at) ?></td>
                            <td><?= Html::encode($donation->comment ?? '') ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>

    <div class="col-md-6">
        <h2 class="h5">Топ поддержавших</h2>

        <?php if ($top === []): ?>
            <p class="text-body-secondary">Пока пусто.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Кто</th>
                        <th class="text-end">Всего</th>
                        <th class="text-end">Донатов</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= Html::encode($row['username']) ?></td>
                            <td class="text-end text-nowrap"><?= $formatter->asDecimal($row['total'], 0) ?> ₽</td>
                            <td class="text-end"><?= $row['donations'] ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        <?php endif ?>
    </div>
</div>
