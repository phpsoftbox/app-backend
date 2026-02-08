<?php

declare(strict_types=1);

namespace App\Database\Fixtures\User;

use PhpSoftBox\TestUtils\Fixture\FixtureContext;
use PhpSoftBox\TestUtils\Fixture\FixtureInterface;

final class DemoUserFixture implements FixtureInterface
{
    public function load(FixtureContext $context): void
    {
        $context->refs()->set('users.demo', [
            'id'    => 1,
            'email' => 'demo@example.test',
            'name'  => 'Demo User',
        ]);
    }
}
