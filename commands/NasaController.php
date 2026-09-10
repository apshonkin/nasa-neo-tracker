<?php

declare(strict_types=1);

namespace app\commands;

use app\services\ApodService;
use app\services\ApodSyncStatus;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

// команды под крон для данных NASA
class NasaController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly ApodService $apodService,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Картинка дня: проверить, есть ли у нас снимок за текущую дату NASA, и забрать если нет.
     *
     * Ставится в крон каждые полчаса - точное время публикации APOD заранее не известно:
     *   0,30 * * * * cd /path/to/project && php yii nasa/apod >> runtime/logs/cron.log 2>&1
     */
    public function actionApod(): int
    {
        $date = $this->apodService->currentNasaDate();

        return match ($this->apodService->syncLatest()) {
            ApodSyncStatus::AlreadyHave => $this->ok("Снимок за {$date} уже есть, в API не ходили."),
            ApodSyncStatus::Saved => $this->ok(
                'Сохранили: ' . $this->apodService->getLatestStored()?->title,
                Console::FG_GREEN,
            ),
            // не ошибка: NASA ещё не выложила снимок, следующий запуск через полчаса
            ApodSyncStatus::NotPublishedYet => $this->ok("Снимок за {$date} ещё не опубликован."),
            ApodSyncStatus::Failed => $this->fail('не удалось забрать картинку дня, подробности в логе'),
        };
    }

    private function ok(string $message, ?int $color = null): int
    {
        $this->stdout($message . "\n", ...($color !== null ? [$color] : []));

        return ExitCode::OK;
    }

    private function fail(string $message): int
    {
        $this->stderr('Не получилось: ' . $message . "\n", Console::FG_RED);

        return ExitCode::UNAVAILABLE;
    }
}
