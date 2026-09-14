<?php

declare(strict_types=1);

namespace app\components\payment;

// то, что шлюз ответил на создание платежа
final class Payment
{
    public function __construct(
        // идентификатор платежа на стороне шлюза, храним его у себя
        public readonly string $id,
        // куда отправить пользователя платить
        public readonly string $confirmationUrl,
    ) {
    }
}
