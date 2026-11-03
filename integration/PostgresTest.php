<?php

namespace Gloudemans\Tests\Shoppingcart;

use Carbon\Carbon;
use Gloudemans\Shoppingcart\CartItem;
use Gloudemans\Shoppingcart\ShoppingcartServiceProvider;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;

// Explicit integration suite: requires a dedicated PostgreSQL test database.
class PostgresTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ShoppingcartServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('session.driver', 'array');
        $app['config']->set('database.default', 'cart-pgsql');
        $app['config']->set('cart.database.table', 'shoppingcart_test');
        $app['config']->set('database.connections.cart-pgsql', [
            'driver' => 'pgsql',
            'host' => getenv('CART_PGSQL_HOST') ?: '127.0.0.1',
            'port' => getenv('CART_PGSQL_PORT') ?: '5432',
            'database' => getenv('CART_PGSQL_DATABASE') ?: 'shoppingcart_test',
            'username' => getenv('CART_PGSQL_USERNAME') ?: 'shoppingcart',
            'password' => getenv('CART_PGSQL_PASSWORD') ?: 'shoppingcart',
            'charset' => 'utf8', 'prefix' => '', 'search_path' => 'public', 'sslmode' => 'prefer',
        ]);
    }

    public function test_postgresql_base64_storage_restore_merge_and_legacy_objects()
    {
        $this->app['migrator']->path(realpath(__DIR__.'/../src/Database/migrations'));
        $this->artisan('migrate', ['--database' => 'cart-pgsql']);
        try {
            $cart = $this->app->make('shoppingcart');
            $item = $cart->add('sku', 'Товар', 2, 19.995, 550, ['size' => 'XL']);
            $cart->setDiscount($item->rowId, 10);
            $serialized = serialize($cart->content());
            $this->assertNotFalse(strpos($serialized, "\0")); // Private properties contain NUL bytes.
            Carbon::setTestNow(Carbon::parse('2026-11-02 11:12:13'));
            $cart->store('pg-cart');
            $record = DB::table('shoppingcart_test')->first();
            $this->assertSame(base64_encode($serialized), $record->content);
            $this->assertSame('2026-11-02 11:12:13', $record->created_at);
            $cart->destroy();
            $this->assertTrue($cart->merge('pg-cart', true, true));
            $this->assertSame(2, $cart->count());
            $this->assertSame(10, $cart->get($item->rowId)->getDiscountRate());
            $this->assertSame(1, DB::table('shoppingcart_test')->count());
            $cart->destroy();
            $cart->restore('pg-cart');
            $this->assertSame($serialized, serialize($cart->content()));
            $this->assertSame(0, DB::table('shoppingcart_test')->count());
            $this->assertInstanceOf(Carbon::class, $cart->createdAt());

            $legacy = trim(file_get_contents(__DIR__.'/../tests/Fixtures/legacy/content.b64'));
            DB::table('shoppingcart_test')->insert([
                'identifier' => 'legacy', 'instance' => 'wishlist', 'content' => $legacy,
                'created_at' => '2024-02-10 12:00:00', 'updated_at' => '2024-02-11 13:00:00',
            ]);
            $cart->instance('wishlist')->restore('legacy');
            $this->assertSame('wishlist', $cart->currentInstance());
            $this->assertSame(2, $cart->count());
            $this->assertInstanceOf(CartItem::class, $cart->content()->first());
            $this->assertSame('43.55', $cart->total());
            $this->assertSame(base64_decode($legacy), serialize($cart->content()));
            $this->assertSame(0, DB::table('shoppingcart_test')->count());
            $this->assertSame(2, $cart->instance('default')->count());
        } finally {
            Carbon::setTestNow();
            $this->artisan('migrate:rollback', ['--database' => 'cart-pgsql']);
        }
    }
}
