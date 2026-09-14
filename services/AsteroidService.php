<?php

declare(strict_types=1);

namespace app\services;

use app\components\nasa\NasaApi;
use app\models\AsteroidApproach;
use DateTimeImmutable;

class AsteroidService
{
    public function __construct(private readonly NasaApi $api)
    {
    }

    /**
     * Забрать список сближений и сохранить. Без дат = сегодня +7дней, один запрос к API.
     *
     * @return int сколько сближений сохранено
     * @throws \app\components\nasa\NasaApiException
     */
    public function sync(?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): int
    {
        $from ??= new DateTimeImmutable('today');
        $to ??= $from->modify('+' . (NasaApi::MAX_FEED_DAYS - 1) . ' days');

        $rows = $this->api->approaches($from, $to);
        if ($rows === []) {
            return 0;
        }

        $db = AsteroidApproach::getDb();
        $transaction = $db->beginTransaction();

        try {
            foreach ($rows as $row) {
                // upsert, а не insert: одно и то же сближение приходит каждый день, но м.б. меняется расстояние
                $db->createCommand()->upsert(AsteroidApproach::tableName(), [
                    'neo_reference_id' => $row['ref_id'],
                    'approach_at' => $row['approach_at']->format('Y-m-d H:i:sP'),
                    'name' => $row['name'],
                    'diameter_m' => $row['diameter_m'],
                    'velocity_kmh' => $row['velocity_kmh'],
                    'miss_distance_lunar' => $row['miss_lunar'],
                    'is_hazardous' => $row['is_hazardous'],
                ])->execute();
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            // либо сохранили целиком, либо не записалось вообще
            $transaction->rollBack();

            throw $e;
        }

        return count($rows);
    }
}
