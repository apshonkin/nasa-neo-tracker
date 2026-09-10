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
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                // send all mails to a file by default.
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // Значение приходит из config/local.php — в git его быть не должно.
            'cookieValidationKey' => $params['cookieValidationKey'],
        ],
        'redis' => $redis,
        'cache' => [
            'class' => \yii\redis\Cache::class,
            'redis' => 'redis',
            // Префикс отделяет ключи этого приложения от чужих на том же сервере.
            'keyPrefix' => 'neo:',
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'site/index',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return \yii\helpers\ArrayHelper::merge($config, $local);
