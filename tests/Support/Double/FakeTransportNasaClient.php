<?php

declare(strict_types=1);

namespace Tests\Support\Double;

use app\components\nasa\NasaClient;

// Тот же клиент, только вместо curl отдаёт заранее заготовленные ответы.
// Так можно проверить повторы, не ходя в сеть и не ожидая пауз.
final class FakeTransportNasaClient extends NasaClient
{
    // сколько раз клиент попробовал сходить за ответом
    public int $attempts = 0;

    /** @var list<string> адреса запросов, по порядку */
    public array $requestedUrls = [];

    /**
     * @param list<array{0: int, 1: string}> $responses очередь ответов: код и тело
     * @param string|null $transportError вместо ответа вернуть ошибку сети
     */
    public function __construct(
        private array $responses,
        private readonly ?string $transportError = null,
    ) {
        parent::__construct();
    }

    /**
     * @return array{0: string, 1: int, 2: ?string}
     */
    protected function request(string $url): array
    {
        $this->attempts++;
        $this->requestedUrls[] = $url;

        if ($this->transportError !== null) {
            return ['', 0, $this->transportError];
        }

        $response = array_shift($this->responses);

        if ($response === null) {
            throw new \LogicException('Ответы в очереди кончились, а клиент просит ещё');
        }

        [$code, $body] = $response;

        return [$body, $code, null];
    }

    // в тестах ждать между попытками незачем
    protected function pause(int $attempt): void
    {
    }
}
