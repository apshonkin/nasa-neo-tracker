<?php

return [
    // Секрет для подписи куки. Реальное значение — в config/local.php (вне git).
    'cookieValidationKey' => '',

    // NASA API. Ключ - в config/local.php, здесь только дефолт.
    // у DEMO_KEY лимит 30 запросов в час на IP, у своего ключа - 1000
    'nasa.apiKey' => 'DEMO_KEY',
    'nasa.baseUrl' => 'https://api.nasa.gov',
    'nasa.timeout' => 10,
];
