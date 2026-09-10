<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Apod|null $apod */

use yii\bootstrap5\Modal;
use yii\helpers\Html;
use yii\widgets\DetailView;

// заголовок только для вкладки браузера, на странице его не показываем
$this->title = 'Фотография дня от NASA';
?>
<?php if ($apod === null): ?>

    <div class="alert alert-warning">
        Картинки пока нет. Забрать: <code>php yii nasa/apod</code>
    </div>

<?php else: ?>

    <?php if ($apod->isImage() && $apod->url !== null): ?>

        <p class="text-center">
            <?= Html::a(
                Html::img($apod->url, ['alt' => $apod->title, 'class' => 'img-fluid']),
                '#apod-full',
                ['data' => ['bs-toggle' => 'modal'], 'title' => 'Открыть на весь экран'],
            ) ?>
        </p>

        <?php Modal::begin([
            'id' => 'apod-full',
            // size у виджета - это просто класс на диалоге, поэтому сюда влезает modal-fullscreen
            'size' => 'modal-fullscreen',
            'title' => Html::encode($apod->title),
        ]) ?>
            <?= Html::img($apod->hdurl ?? $apod->url, [
                'alt' => $apod->title,
                'class' => 'img-fluid mh-100 mx-auto d-block',
            ]) ?>
        <?php Modal::end() ?>

    <?php elseif ($apod->getPreviewUrl() !== null): ?>

        <p class="text-center">
            <?= Html::img($apod->getPreviewUrl(), ['alt' => $apod->title, 'class' => 'img-fluid']) ?>
        </p>

    <?php endif ?>

    <?php if ($apod->isVideo()): ?>
        <p>Это видео: <?= Html::a('смотреть', $apod->url, ['target' => '_blank', 'rel' => 'noopener']) ?></p>
    <?php endif ?>

    <?php if ($apod->explanation !== null): ?>
        <blockquote class="blockquote fs-6">
            <?= Html::tag('p', Yii::$app->formatter->asNtext($apod->explanation)) ?>
            <?php if ($apod->copyright !== null): ?>
                <footer class="blockquote-footer"><?= Html::encode($apod->copyright) ?></footer>
            <?php endif ?>
        </blockquote>
    <?php endif ?>

    <?= DetailView::widget([
        'model' => $apod,
        'attributes' => [
            'date:date',
            'title',
            'media_type',
            // ссылок может не быть, пустые строки в таблице не показываем
            [
                'attribute' => 'url',
                'format' => 'url',
                'visible' => $apod->url !== null,
            ],
            [
                'attribute' => 'hdurl',
                'format' => 'url',
                'visible' => $apod->hdurl !== null,
            ],
            'created_at:datetime',
        ],
    ]) ?>

<?php endif ?>
