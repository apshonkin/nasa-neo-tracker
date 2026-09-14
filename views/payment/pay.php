<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $id */
/** @var app\models\Donation $donation */

use yii\bootstrap5\Html;

$this->title = 'Оплата';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="alert alert-warning">
            Это страница-заглушка вместо платёжного сервиса. Настоящих денег она не списывает.
        </div>

        <table class="table">
            <tr>
                <th>Назначение</th>
                <td>Поддержка проекта NEO Tracker</td>
            </tr>
            <tr>
                <th>Сумма</th>
                <td><?= Yii::$app->formatter->asDecimal($donation->amount, 2) ?> ₽</td>
            </tr>
            <tr>
                <th>Платёж</th>
                <td><code><?= Html::encode($id) ?></code></td>
            </tr>
        </table>

        <?= Html::beginForm(['/payment/confirm', 'id' => $id]) ?>
            <?= Html::submitButton('Оплатить', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Отмена', ['/payment/cancel', 'id' => $id], ['class' => 'btn btn-outline-secondary']) ?>
        <?= Html::endForm() ?>
    </div>
</div>
