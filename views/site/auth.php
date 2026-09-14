<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\LoginForm $loginModel */
/** @var app\models\SignupForm $signupModel */
/** @var string $active какая форма раскрыта: login или signup */

use yii\helpers\Html;

$this->title = $active === 'signup' ? 'Регистрация' : 'Вход';
?>
<div class="row justify-content-center site-login">
    <div class="col-md-6 col-lg-5">

        <?php // data-bs-parent заставляет вторую форму сворачиваться, когда раскрывают первую ?>
        <div id="auth-forms">

            <div class="collapse<?= $active === 'login' ? ' show' : '' ?>" id="form-login" data-bs-parent="#auth-forms">
                <h1 class="h4 mb-3">Вход</h1>

                <?= $this->render('_login-form', ['model' => $loginModel]) ?>

                <p class="text-body-secondary small mt-3 mb-0">
                    Нет аккаунта?
                    <?= Html::a('Зарегистрируйтесь', '#form-signup', ['data' => ['bs-toggle' => 'collapse']]) ?>
                </p>
            </div>

            <div class="collapse<?= $active === 'signup' ? ' show' : '' ?>" id="form-signup" data-bs-parent="#auth-forms">
                <h1 class="h4 mb-3">Регистрация</h1>

                <?= $this->render('_signup-form', ['model' => $signupModel]) ?>

                <p class="text-body-secondary small mt-3 mb-0">
                    Уже есть аккаунт?
                    <?= Html::a('Войдите', '#form-login', ['data' => ['bs-toggle' => 'collapse']]) ?>
                </p>
            </div>

        </div>
    </div>
</div>
