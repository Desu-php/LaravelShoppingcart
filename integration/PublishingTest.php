<?php

namespace Gloudemans\Tests\Shoppingcart;

use Gloudemans\Shoppingcart\ShoppingcartServiceProvider;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;

// Run in its own PHP process: the historical named migration must be loaded
// from its published path, not from a previously loaded source path.
class PublishingTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ShoppingcartServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('session.driver', 'array');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]);
    }

    public function test_published_config_and_migration_work_without_vendor_edits()
    {
        try {
            $this->artisan('vendor:publish', ['--provider' => ShoppingcartServiceProvider::class, '--tag' => 'config', '--force' => true]);
            $this->assertFileExists(config_path('cart.php'));
            $this->assertSame(require __DIR__.'/../src/Config/cart.php', require config_path('cart.php'));
            $this->artisan('vendor:publish', ['--provider' => ShoppingcartServiceProvider::class, '--tag' => 'migrations', '--force' => true]);
            $this->assertFileExists(database_path('migrations/2018_12_23_120000_create_shoppingcart_table.php'));
            $this->app['config']->set('cart.database.table', 'custom_carts');
            $this->artisan('migrate', ['--database' => 'testing']);
            $this->assertTrue(DB::getSchemaBuilder()->hasTable('custom_carts'));
            $this->artisan('migrate:rollback', ['--database' => 'testing']);
            $this->assertFalse(DB::getSchemaBuilder()->hasTable('custom_carts'));
        } finally {
            foreach ([config_path('cart.php'), database_path('migrations/2018_12_23_120000_create_shoppingcart_table.php')] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
}
