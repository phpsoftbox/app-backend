# App Backend

Минимальный backend‑скелет приложения на PhpSoftBox.

## Установка через create-project

```bash
composer create-project phpsoftbox/app-backend my-app
```

## Быстрый старт

1) Окружение после `create-project` будет создано автоматически — `config/env/.env` из примера со случайным
   `APP_KEY` (`composer app:generate-key`). Вручную:

```bash
cp config/env/.env.example config/env/.env
composer app:generate-key
```

`APP_ENV` — `dev`, `test`, `demo` или `prod` (`production`, `development`, `local`, `testing` приводятся к ним);
неизвестное значение — ошибка конфигурации.

2) Установи зависимости:

```bash
composer install
yarn install
```

3) Запусти Vite:

```bash
yarn dev
```

## Структура

- `src/Cli` - handlers консольных команд приложения
- `src/Feature/{FeatureName}/Command/*` - command + handler бизнес-сценариев
- `src/Http/Action` - invokable HTTP actions
- `src/Http/Request` - `RequestSchema`
- `src/Http/Resource` - API resources
- `src/Inertia` - базовые shared data providers для Inertia
- `src/Rule` - кастомные validation rules
- `database/fixtures` - fixtures для интеграционных тестов и seed-like сценариев
- `database/migrations` - base-skeleton миграции, включая `users`, `user_tokens`, roles/permissions
- `local` - runtime-корень приложения; `storage`, `logs`, `cache` создаются через `App\Path`

Auth recipe использует `DatabaseUserProvider`, `SessionGuard`, remember-cookie
restore flow и `AreaAccessMiddleware` для admin area. Минимальные таблицы
`users`, `user_tokens`, `roles`, `permissions`, `role_permissions`,
`user_roles`, `user_permissions` лежат в `database/migrations`.

Для кастомного приложения package-миграции можно опубликовать из vendor:

```bash
php psb db:migrate:publish --package=phpsoftbox/auth
php psb db:migrate:publish --package=phpsoftbox/session
```

По умолчанию session хранится через native PHP session. DB session storage
включается явно через `APP_SESSION_DRIVER=database`.

SSR для Inertia выключен по умолчанию. Включайте его явно через `INERTIA_SSR=1`
и настройку `VITE_SSR_URL`, когда SSR server реально запущен.

## Продакшен

- `APP_ENV=prod`: DI-контейнер компилируется в `local/cache/di`. При деплое после `composer install` сбросьте его до
  перезапуска воркеров, иначе останется контейнер от прошлой версии кода:

  ```bash
  php psb container:cache:clear
  php psb config:cache:clear   # если включён кеш конфигурации
  ```

- За балансировщиком задайте `APP_TRUSTED_PROXIES` — иначе IP клиента и схема будут адресом и схемой балансировщика.
- Необработанные исключения пишутся в `local/logs/app.log` (`App\Runtime\FileLogger`, по строке JSON на запись).
  Для ротации и каналов замените `LoggerInterface` в `config/definitions/http.php` на полноценный логгер.
- Долгоживущий процесс (воркер очереди, RoadRunner/Swoole): после каждой задачи или запроса вызывайте
  `$container->get(ServicesResetter::class)->reset()` — сбрасывает warmup БД, identity map ORM, кеш прав и очередь
  cookie, состояние Inertia (share, хлебные крошки, meta, вкладки) (`config/definitions/runtime.php`). Для воркера `phpsoftbox/queue` — параметр `resetState` у `Worker`.
- Эндпоинты профайлера (`PROFILER_ENDPOINT`, по умолчанию `/__profiler/api/traces`) регистрируются только в `dev`
  при `PROFILER_ENABLED=1`: они отдают трассы запросов без авторизации.

## Проверки

```bash
composer test
composer cs:check
```
