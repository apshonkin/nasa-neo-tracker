<?php

declare(strict_types=1);

namespace app\components\payment;

// Всё, что приложению нужно от платёжного сервиса. Ровно два действия,
// потому что больше и не требуется: создать платёж и спросить, оплачен ли он.
interface PaymentGatewayInterface
{
    /**
     * Создать платёж и получить адрес, куда отправить плательщика.
     *
     * @param float $amount сумма
     * @param string $description назначение платежа, видно плательщику
     * @param string $returnUrl куда шлюз вернёт пользователя после оплаты
     */
    public function create(float $amount, string $description, string $returnUrl): Payment;

    /**
     * Оплачен ли платёж. Спрашивать нужно именно у шлюза: возврат
     * пользователя на страницу успеха ничего не доказывает, этот адрес
     * можно открыть руками.
     */
    public function isPaid(string $paymentId): bool;
}
