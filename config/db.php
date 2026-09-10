<?php

return [
    'class' => \yii\db\Connection::class,
    // Доступы задаются в config/local.php (в git не попадает).
    'dsn' => 'pgsql:host=127.0.0.1;port=5432;dbname=neo_tracker',
    'username' => '',
    'password' => '',
    'charset' => 'utf8',

    // Схему таблиц незачем перечитывать на каждом запросе — держим её в Redis.
    // В отладочном режиме кеш выключен: иначе после миграции приложение ещё час
    // видело бы старую структуру таблиц.
    'enableSchemaCache' => !YII_DEBUG,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
