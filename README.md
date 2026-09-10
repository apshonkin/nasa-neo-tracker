# NEO Tracker

Веб-приложение на Yii2: данные о сближениях околоземных астероидов из открытых API NASA,
каталог с фильтрами, карточки объектов и сбор средств на «остановку астероида».

**Стек:** PHP 8.3 · Yii2 2.0.55 · PostgreSQL 17 · Redis 7 · Bootstrap 5

> Проект в работе. Порядок сборки по шагам — в отдельном журнале разработки.

## Требования

- PHP 8.3+ с расширениями `pdo_pgsql`, `curl`, `openssl`
- PostgreSQL 13+
- Redis 6+ (используется под кеш; расширение `ext-redis` не нужно — `yii2-redis` работает через `predis`)
- Composer 2

## Установка

```bash
composer install
```

Создать роль и базу (нужны права суперюзера PostgreSQL):

```bash
sudo -u postgres psql \
  -c "CREATE ROLE neo_tracker LOGIN PASSWORD 'neo_tracker' CREATEDB" \
  -c "CREATE DATABASE neo_tracker OWNER neo_tracker"
```

Прописать локальные настройки — доступы к базе, секрет для куки, ключ NASA:

```bash
cp config/local.php.example config/local.php
php -r "echo bin2hex(random_bytes(16)), PHP_EOL;"   # значение для cookieValidationKey
$EDITOR config/local.php
```

Файл `config/local.php` в git не попадает. Без него приложение не запустится и скажет об этом.

Применить миграции и запустить:

```bash
php yii migrate --interactive=0
php yii serve --port=8899 --docroot=web
```

Открыть http://localhost:8899

## Конфигурация

| Файл | Что настраивает | В git |
|---|---|---|
| `config/web.php`, `config/console.php` | сборка конфигурации приложения | да |
| `config/db.php` | подключение к PostgreSQL, кеш схемы | да, без доступов |
| `config/redis.php` | подключение к Redis | да |
| `config/params.php` | параметры приложения и их дефолты | да |
| `config/local.php` | доступы к БД, секрет куки, ключ NASA | **нет** |
| `config/local.php.example` | шаблон локальной конфигурации | да |

`local.php` накладывается поверх остальной конфигурации через `ArrayHelper::merge`,
поэтому из него можно переопределить любой компонент, не трогая трекаемые файлы.

## Полезные команды

```bash
php yii help                  # список консольных команд
php yii migrate               # применить миграции
php yii cache/flush-all       # сбросить кеш в Redis
php yii cache/flush-schema db # сбросить только кеш структуры таблиц
```
