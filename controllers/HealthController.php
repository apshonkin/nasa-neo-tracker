<?php

declare(strict_types=1);

namespace app\controllers;

use Throwable;
use Yii;
use yii\web\Controller;
use yii\web\Response;

// Проверка живости для docker compose, балансировщика и smoke-теста после деплоя.
// Отвечает 200, если доступны база и Redis, иначе 503 - так снаружи видно,
// что контейнер запущен, но работать не может.
final class HealthController extends Controller
{
    public function actionIndex(): Response
    {
        $checks = [
            'db' => $this->probe(static fn () => Yii::$app->db->createCommand('select 1')->queryScalar()),
            'redis' => $this->probe(static fn () => Yii::$app->redis->executeCommand('PING')),
        ];
        $healthy = !in_array(false, $checks, true);

        $response = $this->asJson([
            'status' => $healthy ? 'ok' : 'fail',
            'checks' => array_map(static fn (bool $ok): string => $ok ? 'ok' : 'fail', $checks),
        ]);
        $response->statusCode = $healthy ? 200 : 503;

        return $response;
    }

    private function probe(callable $check): bool
    {
        try {
            $check();

            return true;
        } catch (Throwable $e) {
            // наружу только ok/fail, причина - в лог: адреса и ошибки базы светить незачем
            Yii::error('health: ' . $e->getMessage(), 'health');

            return false;
        }
    }
}
