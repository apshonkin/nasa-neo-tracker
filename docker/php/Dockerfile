# Многоэтапная сборка: один Dockerfile, три цели.
#   dev  - для разработки: код монтируется с хоста, есть composer
#   prod - итоговый образ: код и зависимости внутри, без dev-пакетов и composer
# docker compose собирает dev, CI проверяет, что собирается prod.

# ---- base: PHP и расширения, общая часть для dev и prod -------------------
FROM php:8.3-fpm-alpine AS base

# Инструменты сборки ставятся во временную группу .build-deps и удаляются
# в том же RUN: иначе они остались бы в слое и раздули образ.
RUN set -eux; \
    apk add --no-cache libpq icu-libs tzdata; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libpq-dev icu-dev linux-headers; \
    docker-php-ext-install -j"$(nproc)" pdo_pgsql intl opcache; \
    apk del .build-deps

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini

WORKDIR /app

# ---- dev ---------------------------------------------------------------------
FROM base AS dev

ARG UID=1000
ARG GID=1000

# www-data получает uid хоста: файлы, которые PHP создаёт в смонтированном
# каталоге (runtime, web/assets), останутся моими, а не чужими.
RUN set -eux; \
    apk add --no-cache shadow; \
    getent group "${GID}" >/dev/null || groupmod -g "${GID}" www-data; \
    usermod -u "${UID}" -g "${GID}" www-data; \
    apk del shadow

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_HOME=/tmp/composer

# ---- vendor: зависимости для прода ---------------------------------------------
# Отдельный этап, чтобы composer не попал в итоговый образ. Сначала копируем
# только composer.json и composer.lock: пока они не менялись, слой с
# зависимостями берётся из кеша, даже если код поменялся.
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
# расширения PHP есть в итоговом образе, а не в образе composer - их проверку пропускаем
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist \
        --no-scripts --optimize-autoloader --ignore-platform-reqs

# ---- prod ----------------------------------------------------------------------
FROM base AS prod

COPY docker/php/opcache-prod.ini /usr/local/etc/php/conf.d/99-opcache-prod.ini

# Код принадлежит root и только читается. Писать PHP может лишь в runtime
# и web/assets - туда Yii кладёт кеш, логи и опубликованные ассеты.
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN mkdir -p runtime web/assets \
    && chown -R www-data:www-data runtime web/assets

ENV YII_ENV=prod \
    YII_DEBUG=0

# Процесс не от root: если в приложении найдут дыру, у атакующего будут права www-data.
USER www-data
