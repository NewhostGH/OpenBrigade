<?php

namespace Tests;

use App\Services\FeatureService;
use App\Services\PermissionResolver;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Views render the full layout, which pulls in @vite assets. CI does not
        // build the frontend, so stub Vite to keep tests independent of a built
        // manifest.
        $this->withoutVite();

        // The navbar composer reads section/role context from the resolver on
        // every page render. Tests run without a database, so default to a
        // no-op resolver; individual tests can rebind it as needed.
        $resolver = Mockery::mock(PermissionResolver::class)->makePartial();
        $resolver->shouldReceive('activeSectionId')->andReturn(null)->byDefault();
        $resolver->shouldReceive('activeRoleId')->andReturn(null)->byDefault();
        $resolver->shouldReceive('userRoles')->andReturn(collect())->byDefault();
        $this->app->instance(PermissionResolver::class, $resolver);

        // The section selector and sidebar query the ob_feature table on every
        // render. Tests run without migrations, so default feature checks to off
        // (which short-circuits the DB lookups); individual tests can rebind.
        $features = Mockery::mock(FeatureService::class)->makePartial();
        $features->shouldReceive('isEnabled')->andReturn(false)->byDefault();
        $this->app->instance(FeatureService::class, $features);
    }
}
