<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

?>
<?php $form = ActiveForm::begin(['id' => 'login-form', 'action' => ['/site/login']]) ?>
    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= $form->field($model, 'rememberMe')->checkbox() ?>
    <?= Html::submitButton('Войти', ['class' => 'btn btn-primary w-100', 'name' => 'login-button']) ?>
<?php ActiveForm::end() ?>

<div class="alert alert-secondary small mt-3 mb-0">
    Учётные записи для проверки:<br>
    <strong>admin / admin</strong> — администратор<br>
    <strong>user / user</strong> — обычный пользователь
</div>
