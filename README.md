# PhotoStock

MVP-пример тестового задания на Laravel 13: авторизация через Sanctum, приватные API для изображений и простой frontend на Blade + Bootstrap 5 + axios CDN.

## Stack

- PHP 8.3+
- Laravel 13
- Laravel Sanctum
- Blade
- Bootstrap 5 CDN
- axios CDN
- Pest

## Features

- Регистрация, логин и logout через Bearer token
- Загрузка только `png`, `jpg`, `jpeg`
- Ограничение файла до `5 MB`
- Список только своих изображений
- Просмотр только своих изображений
- Удаление только своих изображений

## API

- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`
- `GET /api/images`
- `POST /api/images`
- `GET /api/images/{id}`
- `DELETE /api/images/{id}`

## Run

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Run with Docker

```bash
docker compose up --build
```

Приложение будет доступно на `http://localhost:8000`.
Для Docker-режима отдельный `.env` не нужен: базовые переменные уже описаны в `docker-compose.yml`.

Что делает контейнер:

- собирает PHP 8.3 CLI образ
- устанавливает Composer dependencies
- использует SQLite для простого demo-запуска
- создаёт `storage/app/database.sqlite`
- автоматически выполняет `php artisan migrate`
- поднимает Laravel на `0.0.0.0:8000`

Для остановки:

```bash
docker compose down
```

## Tests

```bash
php artisan test
```

## Frontend flow

- Откройте `/`
- Зарегистрируйтесь или войдите
- Токен сохраняется в `localStorage`
- На `/dashboard` можно загружать, смотреть и удалять свои изображения
