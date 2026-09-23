<?php

declare(strict_types=1);

namespace Tests\Unit\components\nasa;

use app\components\nasa\NasaApiException;
use Codeception\Test\Unit;
use Tests\Support\Double\FakeTransportNasaClient;

// Где клиент повторяет запрос, а где нет. Сети тут нет: ответы подставляем сами.
final class NasaClientTest extends Unit
{
    // ответ пришёл - разбираем и второй раз не ходим
    public function testReturnsDataOnFirstAttempt(): void
    {
        $client = new FakeTransportNasaClient([
            [200, '{"date":"2026-09-24","title":"Кольца Сатурна"}'],
        ]);

        $data = $client->get('/planetary/apod');

        self::assertSame('Кольца Сатурна', $data['title']);
        self::assertSame(1, $client->attempts, 'успешный ответ повторять незачем');
    }

    // на 5xx пробуем ещё: у сервера мог быть разовый сбой
    public function testRetriesOnServerErrorsAndSucceedsOnThirdAttempt(): void
    {
        $client = new FakeTransportNasaClient([
            [500, 'Internal Server Error'],
            [503, 'Service Unavailable'],
            [200, '{"date":"2026-09-24"}'],
        ]);

        $data = $client->get('/planetary/apod');

        self::assertSame('2026-09-24', $data['date']);
        self::assertSame(3, $client->attempts);
    }

    // больше maxAttempts не пробуем, иначе будем долбить сервер бесконечно
    public function testGivesUpAfterMaxAttempts(): void
    {
        $client = new FakeTransportNasaClient([
            [500, ''],
            [500, ''],
            [500, ''],
        ]);

        try {
            $client->get('/planetary/apod');
            self::fail('после трёх неудач ждём исключение');
        } catch (NasaApiException $e) {
            self::assertSame(500, $e->httpCode);
        }

        self::assertSame(3, $client->attempts, 'попыток должно быть ровно три');
    }

    // 429 - кончилась квота на час, повторы её не вернут
    public function testDoesNotRetryOnRateLimit(): void
    {
        $client = new FakeTransportNasaClient([
            [429, '{"error":{"code":"OVER_RATE_LIMIT"}}'],
            [200, '{"date":"не должно дойти до этого ответа"}'],
        ]);

        try {
            $client->get('/planetary/apod');
            self::fail('на 429 ждём исключение');
        } catch (NasaApiException $e) {
            self::assertSame(429, $e->httpCode);
            // в сообщении должно быть видно, что дело в лимите
            self::assertStringContainsString('лимит', $e->getMessage());
        }

        self::assertSame(1, $client->attempts, 'на 429 повторять нельзя');
    }

    // 404 - сами напутали с параметрами, повтор не поможет
    public function testDoesNotRetryOnClientErrorAndKeepsServerMessage(): void
    {
        $client = new FakeTransportNasaClient([
            [404, '{"error":{"message":"No data available for date: 2100-01-01"}}'],
        ]);

        try {
            $client->get('/planetary/apod', ['date' => '2100-01-01']);
            self::fail('на 404 ждём исключение');
        } catch (NasaApiException $e) {
            self::assertSame(404, $e->httpCode);
            self::assertStringContainsString('No data available for date', $e->getMessage());
        }

        self::assertSame(1, $client->attempts, 'на 404 повторять незачем');
    }

    // вместо json бывает приходит страница ошибки от прокси
    public function testFailsClearlyWhenResponseIsNotJson(): void
    {
        $client = new FakeTransportNasaClient([
            [200, '<html><body>502 Bad Gateway</body></html>'],
        ]);

        try {
            $client->get('/planetary/apod');
            self::fail('на не-json ждём исключение');
        } catch (NasaApiException $e) {
            self::assertStringContainsString('не JSON', $e->getMessage());
        }
    }
}
