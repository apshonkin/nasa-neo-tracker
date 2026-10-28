<?php

return [
    // Секрет для подписи куки: из окружения или из config/local.php (вне git).
    // Пустым оставлять нельзя - Yii откажется стартовать, и это правильно.
    'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY') ?: '',

    // NASA API. Свой ключ - в окружении или в config/local.php.
    // у DEMO_KEY лимит 30 запросов в час на IP, у своего ключа - 1000
    'nasa.apiKey' => getenv('NASA_API_KEY') ?: 'DEMO_KEY',
    'nasa.baseUrl' => 'https://api.nasa.gov',
    'nasa.timeout' => 10,
];
