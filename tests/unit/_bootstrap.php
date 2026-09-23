<?php

// То же, что делает web/index.php перед стартом: объявить константы и подключить Yii.

declare(strict_types=1);

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require dirname(__DIR__, 2) . '/vendor/yiisoft/yii2/Yii.php';

// приложение не поднимаем, но без @app не найдутся классы app\*
Yii::setAlias('@app', dirname(__DIR__, 2));
