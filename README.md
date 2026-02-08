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
- `local` - runtime-корень приложения; `storage`, `logs`, `cache` создаются через `App\Path`

Demo Auth использует `config/app/auth.php` и `ArrayUserProvider`. Логин: `demo@example.test` / `password`.

## Проверки

```bash
composer test
composer cs:check
```
