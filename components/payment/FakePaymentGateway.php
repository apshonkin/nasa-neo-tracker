<?php

declare(strict_types=1);

namespace app\components\payment;

use app\models\Donation;
use Yii;
use yii\helpers\Url;

// Имитация платёжного сервиса
final class FakePaymentGateway implements PaymentGatewayInterface
{
    public function create(float $amount, string $description, string $returnUrl): Payment
    {
        // $returnUrl всегда надо , но не нам
        $id = 'fake_' . Yii::$app->security->generateRandomString(16);

        return new Payment($id, Url::to(['/payment/pay', 'id' => $id], true));
    }

    public function isPaid(string $paymentId): bool
    {
        return $this->find($paymentId)?->isPaid() === true;
    }

    public function find(string $paymentId): ?Donation
    {
        return Donation::findOne(['payment_id' => $paymentId]);
    }

    // у настоящего шлюза этой кнопки нет: на неё жмут на его стороне
    public function pay(string $paymentId): bool
    {
        $donation = $this->find($paymentId);

        return $donation !== null && $donation->markPaid();
    }
}
