<?php

return [
    'class' => \yii\redis\Connection::class,
    // в Docker адрес приходит из окружения: там это имя сервиса, а не localhost
    'hostname' => getenv('REDIS_HOST') ?: '127.0.0.1',
    'port' => (int) (getenv('REDIS_PORT') ?: 6379),
    // Номер базы Redis (0-15). Свой номер на проект — чтобы `flushdb` одного
    // приложения не сносил кеш другого, если сервер общий.
    'database' => (int) (getenv('REDIS_DATABASE') ?: 0),
];
