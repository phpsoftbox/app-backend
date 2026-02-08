<?php

declare(strict_types=1);

namespace App\Tests\Integration\Fixture;

use App\Database\Fixtures\User\DemoUserFixture;
use App\Tests\Support\IntegrationTestCase;
use PhpSoftBox\TestUtils\Fixture\FixtureRunner;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
final class FixtureRunnerIntegrationTest extends IntegrationTestCase
{
    public function testFixtureRunnerLoadsReferences(): void
    {
        $runner  = self::container()->get(FixtureRunner::class);
        $context = $runner->createContext();

        $references = $runner->load($context, new DemoUserFixture());

        $this->assertTrue($references->has('users.demo'));
        $this->assertSame('demo@example.test', $references->get('users.demo')['email']);
    }
}
