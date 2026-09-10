<?php

declare(strict_types=1);

namespace app\services;

use app\components\nasa\NasaApi;
use app\components\nasa\NasaApiException;
use app\models\Apod;
use DateTimeImmutable;
use DateTimeZone;
use Yii;

// Смысл сервиса: главная не должна падать из-за того, что у NASA плохой день.
class ApodService
{
    // Картинка публикуется по времени New-York, так что сравниваем с ним.
    public const NASA_TIMEZONE = 'America/New_York';

    public function __construct(private readonly NasaApi $api)
    {
    }

    // дата, за которую снимок у NASA уже мог выйти
    public function currentNasaDate(): string
    {
        return (new DateTimeImmutable('now', new DateTimeZone(self::NASA_TIMEZONE)))->format('Y-m-d');
    }

    /**
     * Для крона: раз в полчаса проверяем, есть ли у нас снимок за текущую дату NASA.
     * Есть - в API не идём вообще. Нет - один запрос.
     */
    public function syncLatest(): ApodSyncStatus
    {
        $date = $this->currentNasaDate();

        if (Apod::find()->where(['date' => $date])->exists()) {
            return ApodSyncStatus::AlreadyHave;
        }

        try {
            $apod = $this->fetchAndStore(new DateTimeImmutable($date, new DateTimeZone(self::NASA_TIMEZONE)));
        } catch (NasaApiException $e) {
            // снимок за сегодня может быть ещё не выложен - это ожидаемо, не сбой
            if (in_array($e->httpCode, [400, 404], true)) {
                Yii::info("APOD за {$date} ещё не опубликован: " . $e->getMessage(), 'nasa');

                return ApodSyncStatus::NotPublishedYet;
            }

            Yii::warning('APOD не забрали: ' . $e->getMessage(), 'nasa');

            return ApodSyncStatus::Failed;
        }

        return $apod !== null ? ApodSyncStatus::Saved : ApodSyncStatus::Failed;
    }

    // очередь: своя база → API → последняя сохранённая.
    public function getToday(): ?Apod
    {
        $today = new DateTimeImmutable('today');

        $stored = Apod::findOne(['date' => $today->format('Y-m-d')]);
        if ($stored !== null) {
            return $stored;
        }

        try {
            return $this->fetchAndStore($today) ?? $this->getLatestStored();
        } catch (NasaApiException $e) {
            // не падаем: вчерашняя картинка лучше пустого места
            Yii::warning('APOD недоступен, берём последнюю сохранённую: ' . $e->getMessage(), 'nasa');

            return $this->getLatestStored();
        }
    }

    /**
     * Забрать за дату и сохранить. Без даты - это сегодня.
     *
     * @throws NasaApiException
     */
    public function fetchAndStore(?DateTimeImmutable $date = null): ?Apod
    {
        $data = $this->api->picture($date);
        if ($data['date'] === '') {
            Yii::warning('APOD вернул ответ без даты, сохранять нечего', 'nasa');

            return null;
        }

        // за эту дату запись могла уже быть - тогда обновляем, а не плодим вторую.
        $apod = Apod::findOne(['date' => $data['date']]) ?? new Apod();
        $apod->date = $data['date'];
        $apod->title = $data['title'];
        $apod->explanation = $data['explanation'];
        $apod->media_type = $data['media_type'];
        $apod->url = $data['url'];
        $apod->hdurl = $data['hdurl'];
        $apod->thumbnail_url = $data['thumbnail_url'];
        $apod->copyright = $data['copyright'];

        if (!$apod->save()) {
            Yii::error('APOD не сохранился: ' . json_encode($apod->getFirstErrors(), JSON_UNESCAPED_UNICODE), 'nasa');

            return null;
        }

        return $apod;
    }

    public function getLatestStored(): ?Apod
    {
        return Apod::find()->orderBy(['date' => SORT_DESC])->one();
    }
}
