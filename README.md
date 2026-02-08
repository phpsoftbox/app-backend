# App Backend

Минимальный backend‑скелет приложения на PhpSoftBox.

## Установка через create-project

```bash
composer create-project phpsoftbox/app-backend my-app
```

## Быстрый старт

1) Окружение после `create-project` будет создано автоматически:

```bash
cp config/env/.env.example config/env/.env
```

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

## Проверки

```bash
composer test
composer cs:check
```
