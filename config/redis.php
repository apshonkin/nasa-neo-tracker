<?php

return [
    'class' => \yii\redis\Connection::class,
    'hostname' => '127.0.0.1',
    'port' => 6379,
    // Номер базы Redis (0-15). Свой номер на проект — чтобы `flushdb` одного
    // приложения не сносил кеш другого, если сервер общий.
    'database' => 0,
];
