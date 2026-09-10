<?php

// Локальные значения (доступы к БД, ключи, секрет куки) лежат вне git.
if (!file_exists(__DIR__ . '/local.php')) {
    throw new \yii\base\InvalidConfigException(
        'Не найден config/local.php. Скопируйте пример и укажите свои доступы: '
        . 'cp config/local.php.example config/local.php'
    );
}

$local = require __DIR__ . '/local.php';

$params = \yii\helpers\ArrayHelper::merge(require __DIR__ . '/params.php', $local['params'] ?? []);
$db = require __DIR__ . '/db.php';
$redis = require __DIR__ . '/redis.php';

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'redis' => $redis,
        'cache' => [
            'class' => \yii\redis\Cache::class,
            'redis' => 'redis',
            // Префикс отделяет ключи этого приложения от чужих на том же сервере.
            'keyPrefix' => 'neo:',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
    /*
    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],
    */
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
    ];
    // configuration adjustments for 'dev' environment
    // requires version `2.1.21` of yii2-debug module
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return \yii\helpers\ArrayHelper::merge($config, $local);
