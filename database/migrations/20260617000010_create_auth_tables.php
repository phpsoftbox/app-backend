<?php

declare(strict_types=1);

use PhpSoftBox\Database\Migrations\AbstractMigration;
use PhpSoftBox\Database\SchemaBuilder\TableBlueprint;

return new class () extends AbstractMigration {
    public function up(): void
    {
        $this->schema()->create('user_tokens', static function (TableBlueprint $table): void {
            $table->comment('Токены пользователей');

            $table->id()->comment('Внутренний идентификатор записи');
            $table->string('user_id', 64)->comment('Идентификатор пользователя');
            $table->string('token_type', 32)->comment('Тип токена');
            $table->string('audience', 64)
                ->nullable()
                ->comment('API или другой получатель, для которого выдан credential');
            $table->string('selector', 64)->comment('Публичный селектор токена');
            $table->string('token_hash', 128)->comment('Хеш секретной части токена');
            $table->datetime('expires_datetime')->nullable()->comment('Дата и время истечения токена');
            $table->datetime('revoked_datetime')->nullable()->comment('Дата и время отзыва токена');
            $table->datetime('last_used_datetime')->nullable()->comment('Дата и время последнего использования');
            $table->string('created_ip', 45)->nullable()->comment('Сетевой адрес при создании');
            $table->string('created_user_agent', 512)->nullable()->comment('Пользовательский агент при создании');
            $table->string('last_used_ip', 45)->nullable()->comment('Сетевой адрес последнего использования');
            $table->string('last_used_user_agent', 512)->nullable()->comment('Пользовательский агент последнего использования');
            $table->json('metadata')->nullable()->comment('Дополнительные данные токена');
            $table->datetime('created_datetime')->comment('Дата и время создания');

            $table->unique(['selector'], 'user_tokens_selector_unique');
            $table->index(
                ['user_id', 'token_type', 'audience'],
                'user_tokens_user_id_token_type_audience_index',
            );
            $table->index(['expires_datetime'], 'user_tokens_expires_datetime_index');
            $table->index(['revoked_datetime'], 'user_tokens_revoked_datetime_index');
        });

        $this->schema()->create('roles', static function (TableBlueprint $table): void {
            $table->comment('Роли авторизации');

            $table->id()->comment('Внутренний идентификатор роли');
            $table->string('name', 100)->comment('Системное имя роли');
            $table->string('label', 255)->nullable()->comment('Название роли');
            $table->boolean('admin_access')->default(false)->comment('Доступ в административную область');
            $table->datetime('created_datetime')->nullable()->comment('Дата и время создания');
            $table->datetime('updated_datetime')->nullable()->comment('Дата и время обновления');

            $table->unique(['name'], 'roles_name_unique');
        });

        $this->schema()->create('permissions', static function (TableBlueprint $table): void {
            $table->comment('Права доступа');

            $table->id()->comment('Внутренний идентификатор права');
            $table->string('name', 150)->comment('Системное имя права');
            $table->string('label', 255)->nullable()->comment('Название права');
            $table->datetime('created_datetime')->nullable()->comment('Дата и время создания');
            $table->datetime('updated_datetime')->nullable()->comment('Дата и время обновления');

            $table->unique(['name'], 'permissions_name_unique');
        });

        $this->schema()->create('user_roles', static function (TableBlueprint $table): void {
            $table->comment('Связь пользователей и ролей');

            $table->string('user_id', 64)->comment('Идентификатор пользователя');
            $table->integer('role_id')->comment('Идентификатор роли');

            $table->unique(['user_id', 'role_id'], 'user_roles_user_id_role_id_unique');
            $table->index(['role_id'], 'user_roles_role_id_index');
        });

        $this->schema()->create('role_permissions', static function (TableBlueprint $table): void {
            $table->comment('Связь ролей и прав доступа');

            $table->integer('role_id')->comment('Идентификатор роли');
            $table->integer('permission_id')->comment('Идентификатор права');

            $table->unique(['role_id', 'permission_id'], 'role_permissions_role_id_permission_id_unique');
            $table->index(['permission_id'], 'role_permissions_permission_id_index');
        });

        $this->schema()->create('user_permissions', static function (TableBlueprint $table): void {
            $table->comment('Связь пользователей и прав доступа');

            $table->string('user_id', 64)->comment('Идентификатор пользователя');
            $table->integer('permission_id')->comment('Идентификатор права');

            $table->unique(['user_id', 'permission_id'], 'user_permissions_user_id_permission_id_unique');
            $table->index(['permission_id'], 'user_permissions_permission_id_index');
        });
    }

    public function down(): void
    {
        $this->schema()->dropIfExists('user_permissions');
        $this->schema()->dropIfExists('role_permissions');
        $this->schema()->dropIfExists('user_roles');
        $this->schema()->dropIfExists('permissions');
        $this->schema()->dropIfExists('roles');
        $this->schema()->dropIfExists('user_tokens');
    }
};
