<?php

namespace Gloudemans\Tests\Shoppingcart;

use Gloudemans\Shoppingcart\Cart;
use Gloudemans\Shoppingcart\ShoppingcartServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\PackageManifest;
use Orchestra\Testbench\TestCase;

class DiscoveryTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('session.driver', 'array');
    }

    // Deliberately no getPackageProviders(): use Laravel's package manifest.
    public function test_laravel_discovers_the_provider_and_fork_alias_from_composer_metadata()
    {
        $dir = sys_get_temp_dir().'/shoppingcart-discovery-'.uniqid();
        mkdir($dir.'/vendor/composer', 0777, true);
        $composer = json_decode(file_get_contents(__DIR__.'/../composer.json'), true);
        file_put_contents($dir.'/vendor/composer/installed.json', json_encode([
            'packages' => [['name' => $composer['name'], 'extra' => $composer['extra']]],
        ]));
        file_put_contents($dir.'/composer.json', '{}');
        try {
            $manifest = new PackageManifest($this->app['files'], $dir, $dir.'/packages.php');
            $manifest->build();
            $this->assertSame([ShoppingcartServiceProvider::class], $manifest->providers());
            $this->assertSame(['ShoppingCart' => \Gloudemans\Shoppingcart\Facades\Cart::class], $manifest->aliases());
            foreach ($manifest->providers() as $provider) {
                $this->app->register($provider);
            }
            AliasLoader::getInstance($manifest->aliases())->register();
            $this->assertInstanceOf(Cart::class, $this->app->make('shoppingcart'));
            \ShoppingCart::add('discovered', 'Discovered product', 1, 10);
            $this->assertSame(1, $this->app->make('shoppingcart')->count());
        } finally {
            $this->app['files']->deleteDirectory($dir);
        }
    }
}
