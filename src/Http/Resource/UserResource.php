<?php

declare(strict_types=1);

namespace App\Http\Resource;

use App\Entity\User;
use PhpSoftBox\Auth\Contracts\UserInterface;
use PhpSoftBox\Resource\Resource;

final class UserResource extends Resource
{
    /**
     * @return array{id:int|string|null,email:mixed,name:mixed}
     */
    public function toArray(): array
    {
        if ($this->resource instanceof UserInterface) {
            $identity = $this->resource->identity();

            return [
                'id'    => $this->resource->id(),
                'email' => $this->resource->get('email'),
                'name'  => $identity instanceof User ? $identity->name : $this->resource->get('name'),
            ];
        }

        if ($this->resource instanceof User) {
            return [
                'id'    => $this->resource->id,
                'email' => $this->resource->email,
                'name'  => $this->resource->name,
            ];
        }

        return [
            'id'    => null,
            'email' => null,
            'name'  => null,
        ];
    }
}
