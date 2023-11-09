<?php

namespace Tumainimosha\MpesaPush\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Tumainimosha\MpesaPush\MpesaPushServiceProvider;

class TestCase extends BaseTestCase
{
    public function setup(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
        $this->artisan('migrate', ['--database' => 'testing']);
        $this->loadLaravelMigrations(['--database' => 'testing']);
        $this->withFactories(__DIR__ . '/../src/database/factories');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'dummy-app-key');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [MpesaPushServiceProvider::class];
    }
}
