<?php

namespace NovaChunkedVideo\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\NovaCoreServiceProvider;
use NovaChunkedVideo\Tests\Fixtures\NovaServiceProvider;
use Orchestra\Testbench\Database\MigrateProcessor;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected string $defaultStorageName = 'default_test_storage';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake($this->defaultStorageName);
    }

    protected function getPackageProviders($app)
    {
        return [
            NovaCoreServiceProvider::class,
            NovaServiceProvider::class,
            \NovaChunkedVideo\ServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();

        $migrator = new MigrateProcessor($this, [
            '--path'     => __DIR__.'/Fixtures/migrations',
            '--realpath' => true,
        ]);
        $migrator->up();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('nova.storage_disk', $this->defaultStorageName);
    }
}
