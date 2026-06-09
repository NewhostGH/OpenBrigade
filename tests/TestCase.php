<?php

namespace Tests;

use App\Services\PermissionResolver;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The navbar composer reads section/role context from the resolver on
        // every page render. Tests run without a database, so default to a
        // no-op resolver; individual tests can rebind it as needed.
        $resolver = Mockery::mock(PermissionResolver::class)->makePartial();
        $resolver->shouldReceive('activeSectionId')->andReturn(null)->byDefault();
        $resolver->shouldReceive('activeRoleId')->andReturn(null)->byDefault();
        $resolver->shouldReceive('userSections')->andReturn(collect())->byDefault();
        $resolver->shouldReceive('userRoles')->andReturn(collect())->byDefault();
        $this->app->instance(PermissionResolver::class, $resolver);
    }
}
