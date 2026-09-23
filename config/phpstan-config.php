<?php

// Конфигурация только для статического анализа. По ней PHPStan узнаёт,
// какие компоненты есть у Yii::$app и какого они класса.
// Доступов и секретов здесь нет и быть не должно: файл в git.

declare(strict_types=1);

return [
    'components' => [
        'user' => [
            'identityClass' => \app\models\User::class,
        ],
        'db' => [
            'class' => \yii\db\Connection::class,
        ],
        'cache' => [
            'class' => \yii\redis\Cache::class,
        ],
        'redis' => [
            'class' => \yii\redis\Connection::class,
        ],
        'authManager' => [
            'class' => \yii\rbac\DbManager::class,
        ],
    ],
];
