<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\Donation;
use app\models\forms\DonationForm;
use app\models\forms\PeriodForm;
use app\services\DonationService;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class DonationController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly DonationService $service,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                // списки видны всем, поддержать может только вошедший,а статистика - только у кого есть роль
                'only' => ['create', 'back', 'stats'],
                'rules' => [
                    ['actions' => ['create', 'back'], 'allow' => true, 'roles' => ['@']],
                    ['actions' => ['stats'], 'allow' => true, 'roles' => ['viewDonationStats']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['create' => ['post']],
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index', [
            'form' => new DonationForm(),
            'latest' => $this->service->latest(10),
            'top' => $this->service->top(10),
        ]);
    }

    public function actionCreate(): Response|string
    {
        $form = new DonationForm();

        if ($form->load($this->request->post()) && $form->validate()) {
            $payment = $this->service->start(
                (int) Yii::$app->user->id,
                (float) $form->amount,
                $form->comment,
            );

            if ($payment !== null) {
                // уходим на страницу шлюза
                return $this->redirect($payment->confirmationUrl);
            }

            Yii::$app->session->setFlash('error', 'Не получилось создать платёж, попробуйте ещё раз.');
        }

        return $this->render('index', [
            'form' => $form,
            'latest' => $this->service->latest(10),
            'top' => $this->service->top(10),
        ]);
    }

    /**
     * Возврат со страницы шлюза. Сам по себе он ничего не подтверждает -
     * оплату проверяет сервис, спрашивая шлюз.
     */
    public function actionBack(int $id): Response
    {
        $donation = Donation::findOne(['id' => $id, 'user_id' => Yii::$app->user->id]);

        if ($donation === null) {
            throw new NotFoundHttpException('Платёж не найден.');
        }

        if ($this->service->confirm($donation)) {
            Yii::$app->session->setFlash('success', 'Спасибо за поддержку!');
        } else {
            Yii::$app->session->setFlash('warning', 'Платёж не оплачен.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Статистика поддержки. Доступна по праву viewDonationStats,
     * а не по роли: права выдаются точечно.
     */
    public function actionStats(): string
    {
        $period = new PeriodForm();
        $period->load($this->request->queryParams);

        if (!$period->validate()) {
            // с кривым периодом считать нечего, показываем форму с ошибкой
            return $this->render('stats', [
                'period' => $period,
                'summary' => null,
                'byDays' => [],
                'byUsers' => [],
            ]);
        }

        $from = $period->fromDate();
        $to = $period->toDate();

        return $this->render('stats', [
            'period' => $period,
            'summary' => $this->service->summary($from, $to, $period->days()),
            'byDays' => $this->service->byDays($from, $to),
            'byUsers' => $this->service->byUsers($from, $to),
        ]);
    }
}
