<?php

declare(strict_types=1);

namespace App\Http\Request;

use PhpSoftBox\Request\RequestSchema;
use PhpSoftBox\Validator\Rule\StringValidation;

use function is_string;
use function strtolower;
use function trim;

final class LoginRequest extends RequestSchema
{
    public function rules(): array
    {
        return [
            'email' => [
                new StringValidation()->required()->email()->max(255),
            ],
            'password' => [
                new StringValidation()->required()->min(6)->max(255),
            ],
        ];
    }

    public function filters(): array
    {
        return [
            'email' => [
                static fn (mixed $value): mixed => is_string($value) ? trim($value) : $value,
                static fn (mixed $value): mixed => is_string($value) ? strtolower($value) : $value,
            ],
            'password' => static fn (mixed $value): mixed => is_string($value) ? trim($value) : $value,
        ];
    }

    public function attributes(): array
    {
        return [
            'email'    => 'email',
            'password' => 'password',
        ];
    }
}
