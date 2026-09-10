<?php

declare(strict_types=1);

namespace app\services;

// чем закончился запуск синхронизации картинки дня
enum ApodSyncStatus
{
    // запись за текущую дату NASA уже есть, в API не ходили
    case AlreadyHave;
    // Забрали новую и сохранили
    case Saved;
    // дата наступила, но NASA ещё не опубликовала снимок - для крона это не ошибка
    case NotPublishedYet;
    // Сбой: сеть, лимит запросов, ошибка сохранения.
    case Failed;
}
