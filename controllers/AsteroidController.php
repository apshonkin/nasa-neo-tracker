<?php

declare(strict_types=1);

namespace app\controllers;

use app\models\search\AsteroidApproachSearch;
use Yii;
use yii\web\Controller;

class AsteroidController extends Controller
{
    public function actionIndex(): string
    {
        $searchModel = new AsteroidApproachSearch();

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams),
        ]);
    }
}
