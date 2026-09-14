<?php

declare(strict_types=1);

namespace app\components\nasa;

use DateTimeImmutable;
use DateTimeZone;
use Yii;
use yii\base\BaseObject;

// То, что берём у NASA: картинка дня (APOD) и сближения астероидов (NeoWs)
class NasaApi extends BaseObject
{
    // NeoWs отдаёт максимум 7 дней за запрос, дальше отвечает 400
    public const MAX_FEED_DAYS = 7;

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

    /**
     * Сближения астероидов с Землёй за интервал дат, самые близкие первыми.
     *
     * @return list<array{ref_id: string, name: string, diameter_m: ?float, velocity_kmh: float, approach_at: DateTimeImmutable, miss_lunar: float, is_hazardous: bool}>
     * @throws NasaApiException
     */
    public function approaches(DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $days = (int) $from->diff($to)->days + 1;
        if ($days > self::MAX_FEED_DAYS) {
            // проверяем до запроса, чтобы не тратить квоту на заведомо плохой интервал
            throw new NasaApiException(
                sprintf('NeoWs отдаёт максимум %d дней, запрошено %d', self::MAX_FEED_DAYS, $days)
            );
        }

        $data = $this->client->get('/neo/rest/v1/feed', [
            'start_date' => $from->format('Y-m-d'),
            'end_date' => $to->format('Y-m-d'),
        ]);

        // В ответе объекты разложены по датам, и даты приходят не по порядку.
        $rows = [];
        foreach ($data['near_earth_objects'] ?? [] as $perDate) {
            foreach ($perDate as $object) {
                foreach ($object['close_approach_data'] ?? [] as $approach) {
                    $row = $this->approachRow($object, $approach);
                    if ($row !== null) {
                        $rows[] = $row;
                    }
                }
            }
        }

        usort($rows, static fn (array $a, array $b): int => $a['miss_lunar'] <=> $b['miss_lunar']);

        return $rows;
    }

    /**
     * @param array<string, mixed> $object
     * @param array<string, mixed> $approach
     * @return array<string, mixed>|null
     */
    private function approachRow(array $object, array $approach): ?array
    {
        // момент сближения приходит в миллисекундах, без него запись бесполезна
        $epochMs = (int) ($approach['epoch_date_close_approach'] ?? 0);
        if ($epochMs === 0) {
            return null;
        }

        $meters = $object['estimated_diameter']['meters'] ?? [];
        $min = $meters['estimated_diameter_min'] ?? null;
        $max = $meters['estimated_diameter_max'] ?? null;

        return [
            'ref_id' => (string) ($object['neo_reference_id'] ?? ''),
            'name' => trim((string) ($object['name'] ?? '')),
            // NASA даёт вилку диаметра, точного размера не знает никто. Храним середину.
            'diameter_m' => $min !== null && $max !== null
                ? round(((float) $min + (float) $max) / 2, 1)
                : null,
            // скорость и дистанция приходят строками, приводим к числу
            'velocity_kmh' => (float) ($approach['relative_velocity']['kilometers_per_hour'] ?? 0),
            'approach_at' => (new DateTimeImmutable('@' . intdiv($epochMs, 1000)))
                ->setTimezone(new DateTimeZone(Yii::$app->timeZone)),
            'miss_lunar' => (float) ($approach['miss_distance']['lunar'] ?? 0),
            // флаг NASA: ставится по орбите и размеру, а не по конкретному пролёту
            'is_hazardous' => (bool) ($object['is_potentially_hazardous_asteroid'] ?? false),
        ];
    }
}
