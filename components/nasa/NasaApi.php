<?php

declare(strict_types=1);

namespace app\components\nasa;

use DateTimeImmutable;
use yii\base\BaseObject;

class NasaApi extends BaseObject
{
    public function __construct(
        private readonly NasaClient $client,
        array $config = [],
    ) {
        parent::__construct($config);
    }

    /**
     * Картинка дня. Без даты - за сегодня.
     *
     * @return array{date: string, title: string, explanation: ?string, media_type: string, url: ?string, hdurl: ?string, thumbnail_url: ?string, copyright: ?string}
     * @throws NasaApiException
     */
    public function picture(?DateTimeImmutable $date = null): array
    {
        $query = $date !== null ? ['date' => $date->format('Y-m-d')] : [];
        // thumbs=true нужен, чтобы для видео пришёл кадр-превью: иначе показывать нечего
        $query['thumbs'] = 'true';
        // без кеша: от лишних запросов защищает крон, а в базе уже лежит своя копия
        $data = $this->client->get('/planetary/apod', $query);

        return [
            'date' => (string) ($data['date'] ?? ''),
            'title' => (string) ($data['title'] ?? 'Без названия'),
            'explanation' => $data['explanation'] ?? null,
            // Иногда вместо картинки приходит видео с ютуба - это надо учитывать при отображении.
            'media_type' => (string) ($data['media_type'] ?? 'image'),
            'url' => $data['url'] ?? null,
            'hdurl' => $data['hdurl'] ?? null,
            'thumbnail_url' => $data['thumbnail_url'] ?? null,
            // в копирайте бывают переносы строк, убираем
            'copyright' => isset($data['copyright'])
                ? trim(preg_replace('/\s+/u', ' ', $data['copyright']))
                : null,
        ];
    }
}
