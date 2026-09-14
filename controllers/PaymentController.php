<?php

declare(strict_types=1);

namespace app\controllers;

use app\components\payment\FakePaymentGateway;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

//типа банк
class PaymentController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly FakePaymentGateway $gateway,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['confirm' => ['post']],
            ],
        ];
    }

    public function actionPay(string $id): string
    {
        return $this->render('pay', ['id' => $id, 'donation' => $this->findDonation($id)]);
    }

    public function actionConfirm(string $id): Response
    {
        $donation = $this->findDonation($id);
        $this->gateway->pay($id);

        return $this->redirect(['/donation/back', 'id' => $donation->id]);
    }

    public function actionCancel(string $id): Response
    {
        return $this->redirect(['/donation/back', 'id' => $this->findDonation($id)->id]);
    }

    private function findDonation(string $paymentId): \app\models\Donation
    {
        $donation = $this->gateway->find($paymentId);

        if ($donation === null) {
            throw new NotFoundHttpException('Платёж не найден.');
        }

        return $donation;
    }
}
