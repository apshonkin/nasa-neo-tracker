<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\SignupForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>
<?php $form = ActiveForm::begin(['id' => 'signup-form', 'action' => ['/site/signup']]) ?>
    <?= $form->field($model, 'username')->textInput() ?>
    <?= $form->field($model, 'email')->input('email') ?>
    <?= $form->field($model, 'password')->passwordInput()->hint('Не короче 6 символов') ?>
    <?= Html::submitButton('Зарегистрироваться', ['class' => 'btn btn-primary w-100']) ?>
<?php ActiveForm::end() ?>
