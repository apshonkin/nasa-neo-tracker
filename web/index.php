<?php

declare(strict_types=1);

// режим берётся из окружения: в прод-образе YII_ENV=prod и YII_DEBUG=0,
// без переменных - режим разработки, как раньше
defined('YII_DEBUG') or define('YII_DEBUG', filter_var(getenv('YII_DEBUG') ?: '1', FILTER_VALIDATE_BOOL));
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
