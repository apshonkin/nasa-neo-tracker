<?php

declare(strict_types=1);

namespace app\components\nasa;

use Yii;
use yii\base\Component;
use yii\helpers\Json;

// Транспорт до api.nasa.gov. Ключ, таймауты и повторы держим здесь,
// чтобы вызывающий код про это не думал.
class NasaClient extends Component
{
    public string $baseUrl = 'https://api.nasa.gov';
    public string $apiKey = 'DEMO_KEY';
    public int $timeout = 10;
    public int $maxAttempts = 3;

    /**
     * @param array<string, scalar> $query
     * @return array<string, mixed>
     * @throws NasaApiException
     */
    public function get(string $path, array $query = []): array
    {
        $query['api_key'] = $this->apiKey;
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/') . '?' . http_build_query($query);

        $lastError = null;

        for ($attempt = 1; $attempt <= $this->maxAttempts; $attempt++) {
            [$body, $code, $curlError] = $this->request($url);

            if ($curlError !== null) {
                $lastError = new NasaApiException('Сеть недоступна: ' . $curlError);
            } elseif ($code === 429) {
                // лимит кончился, повторять бесполезно - ждать до следующего часа
                throw new NasaApiException('Кончился часовой лимит запросов к NASA', 429);
            } elseif ($code >= 500) {
                $lastError = new NasaApiException("NASA вернула {$code}", $code);
            } elseif ($code !== 200) {
                // 4xx - это мы виноваты: неверный ключ или параметры. Повтор не поможет.
                throw new NasaApiException($this->errorMessage($body, $code), $code);
            } else {
                Yii::info("GET {$path} {$code}, попытка {$attempt}", 'nasa');

                return $this->decode($body);
            }

            if ($attempt < $this->maxAttempts) {
                usleep(200_000 * $attempt);
            }
        }

        Yii::error("GET {$path} не удался за {$this->maxAttempts} попыток", 'nasa');

        throw $lastError ?? new NasaApiException('Запрос к NASA не удался');
    }

    /**
     * @return array{0: string, 1: int, 2: ?string}
     */
    private function request(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_USERAGENT => 'nasa-neo-tracker/1.0',
        ]);

        $body = curl_exec($ch);
        $curlError = curl_errno($ch) !== 0 ? curl_error($ch) : null;
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [is_string($body) ? $body : '', $code, $curlError];
    }

    /**
     * @return array<string, mixed>
     * @throws NasaApiException
     */
    private function decode(string $body): array
    {
        try {
            $data = Json::decode($body);
        } catch (\Throwable) {
            throw new NasaApiException('NASA вернула не JSON: ' . mb_substr($body, 0, 200));
        }

        if (!is_array($data)) {
            throw new NasaApiException('NASA вернула неожиданный ответ');
        }

        return $data;
    }

    // текст ошибки NASA кладёт в разные поля, поэтому проверяем несколько
    private function errorMessage(string $body, int $code): string
    {
        $decoded = json_decode($body, true);
        $message = $decoded['error']['message'] ?? $decoded['error_message'] ?? $decoded['msg'] ?? null;

        return $message !== null ? "NASA {$code}: {$message}" : "NASA вернула {$code}";
    }
}
