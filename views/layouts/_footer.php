<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\helpers\Html;

?>
<footer id="footer" class="mt-auto py-3 bg-body-tertiary">
    <div class="container">
        <div class="row text-body-secondary">
            <div class="col-md-6 text-center text-md-start">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></div>
            <div class="col-md-6 text-center text-md-end">
                <?= Html::a(
                    'Данные: NASA Open APIs',
                    'https://api.nasa.gov/',
                    ['rel' => 'external noopener', 'target' => '_blank', 'class' => 'text-body-secondary text-decoration-none'],
                ) ?>
                <span class="mx-2">·</span>
                <?= Html::a(
                    'Исходный код',
                    'https://github.com/apshonkin/nasa-neo-tracker',
                    ['rel' => 'external noopener', 'target' => '_blank', 'class' => 'text-body-secondary text-decoration-none'],
                ) ?>
            </div>
        </div>
    </div>
</footer>
