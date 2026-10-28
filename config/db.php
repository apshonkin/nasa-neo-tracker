<?php

return [
    'class' => \yii\db\Connection::class,
    // Доступы приходят из окружения (в Docker - из .env) или из config/local.php.
    // Значения по умолчанию - для запуска без Docker на локальном PostgreSQL.
    'dsn' => sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        getenv('DB_HOST') ?: '127.0.0.1',
        getenv('DB_PORT') ?: '5432',
        getenv('DB_NAME') ?: 'neo_tracker',
    ),
    'username' => getenv('DB_USER') ?: '',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => 'utf8',

    // Схему таблиц незачем перечитывать на каждом запросе — держим её в Redis.
    // В отладочном режиме кеш выключен: иначе после миграции приложение ещё час
    // видело бы старую структуру таблиц.
    'enableSchemaCache' => !YII_DEBUG,
    'schemaCacheDuration' => 3600,
    'schemaCache' => 'cache',
];
