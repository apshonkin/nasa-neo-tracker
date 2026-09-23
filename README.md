# NEO Tracker

Веб-приложение на Yii2: данные о сближениях околоземных астероидов из открытых API NASA,
каталог с фильтрами, картинка дня и сбор средств на «остановку астероида».

**Стек:** PHP 8.3 · Yii2 2.0.55 · PostgreSQL 17 · Redis 7 · Bootstrap 5

## Что внутри

- **Каталог сближений** — данные NeoWs, фильтры по названию и признаку опасности, сортировка
  по дате, расстоянию (в лунных орбитах), скорости и размеру. Прошедшие сближения скрыты.
- **Картинка дня (APOD)** — с деградацией: если NASA недоступна, показываем последнюю сохранённую.
- **Поддержка проекта** — донаты с имитацией платёжного шлюза, статистика по дням и пользователям.
- **Аутентификация и права** — RBAC на ролях `user` и `admin`, статистика донатов закрыта правом.

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

Миграции заводят демо-данные и два аккаунта: `admin` / `admin` (полные права) и `user` / `user`.

### Ключ NASA

По умолчанию используется `DEMO_KEY` с лимитом порядка десятка запросов в час на IP — для
знакомства хватит, для наполнения базы нет. Личный ключ бесплатно выдаётся на
[api.nasa.gov](https://api.nasa.gov/) и прописывается в `config/local.php`.

## Наполнение данными

```bash
php yii nasa/apod                        # картинка дня за текущую дату NASA
php yii nasa/asteroids                   # сближения на ближайшие 7 дней
php yii nasa/asteroids 2026-09-01 2026-09-07   # произвольный интервал
```

В проде обе команды ставятся в крон — рекомендуемое расписание указано в докблоках
`commands/NasaController.php`: APOD каждые полчаса (точное время публикации заранее не известно),
астероиды раз в сутки ночью (чаще бессмысленно — орбиты уточняются с частотой дней).

## Конфигурация

| Файл | Что настраивает | В git |
|---|---|---|
| `config/web.php`, `config/console.php` | сборка конфигурации приложения | да |
| `config/db.php` | подключение к PostgreSQL, кеш схемы | да, без доступов |
| `config/redis.php` | подключение к Redis | да |
| `config/params.php` | параметры приложения и их дефолты | да |
| `config/local.php` | доступы к БД, секрет куки, ключ NASA | **нет** |
| `config/local.php.example` | шаблон локальной конфигурации | да |
| `config/phpstan-config.php` | описание компонентов для статического анализа | да |

`local.php` накладывается поверх остальной конфигурации через `ArrayHelper::merge`,
поэтому из него можно переопределить любой компонент, не трогая трекаемые файлы.

## Качество кода

```bash
composer static   # PHPStan, уровень 8
composer cs       # PHP_CodeSniffer, стандарт Yii2 (PSR-12)
composer cs-fix   # автоисправление стиля
```

PHPStan работает на **8 уровне** с расширением для Yii2, без baseline и без подавляющих
комментариев: все найденные несоответствия исправлены типами, а не заглушены.

## Структура

```
commands/     консольные команды (синхронизация с NASA)
components/   клиент NASA и платёжный шлюз за интерфейсом
services/     бизнес-логика: APOD, астероиды, донаты
models/       ActiveRecord, формы, поисковые модели
controllers/  тонкие контроллеры: приняли запрос, позвали сервис, отдали вид
views/        шаблоны Bootstrap 5
migrations/   схема и демо-данные
```

## Полезные команды

```bash
php yii help                  # список консольных команд
php yii migrate               # применить миграции
php yii cache/flush-all       # сбросить кеш в Redis
php yii cache/flush-schema db # сбросить только кеш структуры таблиц
```

## Лицензия

MIT — см. [LICENSE.md](LICENSE.md). Данные предоставлены [NASA Open APIs](https://api.nasa.gov/).
