**CargoTransportation — backend (Laravel API)**

Коротко: это headless Laravel-приложение — API для сервиса грузоперевозок. Фронтенд живёт в отдельном репозитории (Vue + TypeScript). В этом репо реализована бизнес-логика, модели и API-эндпоинты.

**Стек и версии (ориентировочно)**
- PHP: 8.x
- Laravel: 13.x
- DB: sqlite (по умолчанию для разработки), можно подключить MySQL/Postgres
- Тесты: PHPUnit

**Что реализовано**
- Регистрация пользователя и назначение ролей (`POST /api/user/register`)
- Логин по телефону с выдачей токена Sanctum (`POST /api/user/login`)
- Удаление аккаунта (`DELETE /api/user/destroy`)
- Создание заказа с автоматическим назначением машины (`POST /api/orders`)
- Добавление машины водителем (авторизованный маршрут) (`POST /api/vehicles/create`)
- Валидация запросов через `FormRequest` с понятными русскими сообщениями

**Основные файлы**
- Модели: [app/Models](app/Models)
- Контроллеры: [app/Http/Controllers](app/Http/Controllers)
- Маршруты: [routes/api.php](routes/api.php)
- Миграции: [database/migrations](database/migrations)
- Формы/валидация: [app/Http/Requests](app/Http/Requests)

**API (кратко)**
- `POST /api/user/register` — регистрация
  - Тело: `{ name, phone, password, roles: string[] }`
  - Ответ: `201` JSON с пользователем

- `POST /api/user/login` — логин
  - Тело: `{ phone, password }`
  - Ответ: `200` JSON с `access_token` (Sanctum)

- `DELETE /api/user/destroy` — удалить аккаунт
  - Тело: `{ phone, password }` (валидация)
  - Ответ: `204 No Content` (рекомендуется) или `200` с JSON — обсуждаемо

- `POST /api/orders` — создать заказ
  - Тело: `{ cargo_name, weight_kg, route_from, route_to }`
  - Ответ: `201` JSON с `order` и назначенной `vehicle` или `422` при ошибке

- `POST /api/vehicles/create` — добавить машину (auth:sanctum)
  - Тело: `{ mark, model, number, max_weight }`
  - Ответ: `201` JSON с `vehicle`

Примечание: детальные схемы запросов/ответов лучше держать в Postman или OpenAPI (я помогу сформировать).

**Запуск локально**

1. Установить зависимости

```bash
composer install
cp .env.example .env
php artisan key:generate
```

2. Миграции

```bash
php artisan migrate
```

3. Запустить сервер

```bash
php artisan serve
```

4. (Опционально) фронтенд локально или отдельный репо

Если используете Vite в этом репо (необязательно для headless):

```bash
npm install
npm run dev
```

**Тестирование**

```bash
php artisan test
```

**Postman / документация**
- Рекомендуется экспортировать Postman-коллекцию и положить в `docs/postman/postman_collection.json`.
- В коллекции полезно задать переменные `base_url` и `token` и тест, который сохраняет `access_token` после логина.

Если хочешь, могу:
- привести `DELETE /api/user/destroy` к 204 (сделать метод возвращающим `noContent()`),
- сгенерировать Postman-коллекцию на основе текущих маршрутов,
- или составить OpenAPI-спецификацию.

---

Файл с документацией API и примером запросов могу добавить прямо сейчас — скажи, что предпочтительнее: Postman-коллекция (JSON) или OpenAPI (YAML/JSON).
