<?php

namespace Tests;

use App\Services\FeatureService;
use App\Services\GeneralSettingService;
use App\Services\OrganisationSetupService;
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

        // The RequireSetup middleware consults the first-run flag on every
        // request; without a database that reads as "unconfigured" and every
        // route redirects to /setup. Default to a completed install; setup
        // tests can rebind.
        $setup = Mockery::mock(OrganisationSetupService::class)->makePartial();
        $setup->shouldReceive('isCompleted')->andReturn(true)->byDefault();
        $this->app->instance(OrganisationSetupService::class, $setup);

        // General settings (maintenance mode, mandatory photo, timezone…) are
        // consulted at boot and on every request. Default to a plain install
        // with everything off; individual tests can rebind.
        $general = Mockery::mock(GeneralSettingService::class)->makePartial();
        $general->shouldReceive('get')->andReturnUsing(
            fn (string $name) => match ($name) {
                'timezone' => 'Europe/Paris',
                'default_money' => 'Euro',
                'default_money_symbol' => '€',
                'phone_prefix' => '+33',
                'min_numbers_in_phone' => '10',
                default => '0',
            }
        )->byDefault();
        $this->app->instance(GeneralSettingService::class, $general);
    }
}
