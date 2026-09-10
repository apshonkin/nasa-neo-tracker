<?php

// Подгружает Yii для анализатора: класс Yii и константы окружения
// объявляются вручную в точках входа, автозагрузчик composer о них не знает.

declare(strict_types=1);

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
