<?php

declare(strict_types=1);

namespace App\Entity;

use PhpSoftBox\Auth\Contracts\UserInterface;
use PhpSoftBox\Orm\Metadata\Attributes\Column;
use PhpSoftBox\Orm\Metadata\Attributes\Entity;
use PhpSoftBox\Orm\Metadata\Attributes\GeneratedValue;
use PhpSoftBox\Orm\Metadata\Attributes\Id;

#[Entity(table: 'users')]
final class User implements UserInterface
{
    #[Id]
    #[GeneratedValue('auto')]
    #[Column(type: 'int')]
    public int $id;

    #[Column(type: 'string')]
    public string $email;

    #[Column(type: 'string', nullable: true)]
    public ?string $name = null;

    #[Column(name: 'password_hash', type: 'string', nullable: true)]
    public ?string $passwordHash = null;

    public function id(): int|string|null
    {
        return $this->id;
    }
}
