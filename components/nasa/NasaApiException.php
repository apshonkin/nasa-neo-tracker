<?php

declare(strict_types=1);

namespace app\components\nasa;

use RuntimeException;

// всё, что может пойти не так с api nasa
class NasaApiException extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $httpCode = null)
    {
        parent::__construct($message, $httpCode ?? 0);
    }
}
