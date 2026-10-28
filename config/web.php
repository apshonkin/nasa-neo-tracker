<?php

// Локальные значения (доступы к БД, ключи, секрет куки) лежат вне git:
// в Docker - в переменных окружения из .env, без Docker - в config/local.php.
$local = file_exists(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];

$params = \yii\helpers\ArrayHelper::merge(require __DIR__ . '/params.php', $local['params'] ?? []);
$db = require __DIR__ . '/db.php';
$redis = require __DIR__ . '/redis.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'name' => 'NEO Tracker',
    'language' => 'ru-RU',
    'timeZone' => 'Europe/Moscow',
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            //платежный сервис меняем только тут
            \app\components\payment\PaymentGatewayInterface::class
                => \app\components\payment\FakePaymentGateway::class,
            // клиент к NASA: ключ и таймауты в одном месте
            \app\components\nasa\NasaClient::class => [
                'class' => \app\components\nasa\NasaClient::class,
                'baseUrl' => $params['nasa.baseUrl'],
                'apiKey' => $params['nasa.apiKey'],
                'timeout' => $params['nasa.timeout'],
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
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
            // иерархию ролей незачем перечитывать на каждую проверку прав
            'cache' => 'cache',
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
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
            // По умолчанию правила кешируются в компоненте cache, то есть в Redis,
            // и без Redis падал бы любой запрос ещё до контроллера, включая /health.
            // Правил пять, собрать их на лету дешевле, чем сходить в кеш.
            'cache' => false,
            'rules' => [
                '' => 'site/index',
                'health' => 'health/index',
                'asteroids' => 'asteroid/index',
                'support' => 'donation/index',
                'support/stats' => 'donation/stats',
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
