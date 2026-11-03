<?php

namespace Gloudemans\Tests\Shoppingcart;

use Carbon\Carbon;
use Gloudemans\Shoppingcart\Cart;
use Gloudemans\Shoppingcart\CartItem;
use Gloudemans\Shoppingcart\CartItemOptions;
use Gloudemans\Shoppingcart\Facades\Cart as CartFacade;
use Gloudemans\Shoppingcart\ShoppingcartServiceProvider;
use Gloudemans\Tests\Shoppingcart\Fixtures\BuyableProduct;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Orchestra\Testbench\TestCase;

class CompatibilityTest extends TestCase
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

    private function migrateCart()
    {
        $this->app['migrator']->path(realpath(__DIR__.'/../src/Database/migrations'));
        $this->artisan('migrate', ['--database' => 'testing']);
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('shoppingcart'));
    }

    private function legacyContent()
    {
        return base64_decode(trim(file_get_contents(__DIR__.'/Fixtures/legacy/content.b64')));
    }

    private function assertLegacyCart(Cart $cart)
    {
        $expected = json_decode(file_get_contents(__DIR__.'/Fixtures/legacy/expected.json'), true);
        $this->assertSame(2, $cart->count());
        $this->assertSame(1, $cart->countItems());
        $item = $cart->get($expected['item']['rowId']);
        $this->assertInstanceOf(CartItem::class, $item);
        $this->assertInstanceOf(CartItemOptions::class, $item->options);
        $this->assertEquals($expected['item'], $item->toArray());
        $this->assertSame('App\\Models\\Product', $item->modelFQCN);
        $this->assertSame(10, $item->getDiscountRate());
        $this->assertSame(21, $item->taxRate);
        $this->assertSame($expected['total'], $cart->total());
        $this->assertEquals($expected['taxTotal'], $cart->taxFloat());
        $this->assertEquals($expected['discountTotal'], $cart->discountFloat());
    }

    public function test_fork_binding_and_namespaced_facade_use_the_session()
    {
        $first = $this->app->make('shoppingcart');
        $second = $this->app->make('shoppingcart');
        $this->assertInstanceOf(Cart::class, $first);
        $this->assertNotSame($first, $second); // Preserve the existing transient binding.
        CartFacade::add('sku', 'Product', 2, 10, 100);
        $this->assertSame(2, $first->count());
        $this->assertSame(2, $second->count());
        $this->assertSame(2, $this->app->make(Cart::class)->count());
    }

    public function test_row_id_and_buyable_contract_preserve_options_and_association()
    {
        $cart = $this->app->make('shoppingcart');
        $product = new BuyableProduct(['id' => 1, 'name' => 'Some item', 'price' => 10, 'weight' => 550]);
        $a = $cart->add($product, 2, ['size' => 'XL', 'color' => 'red']);
        $b = $cart->add($product, 1, ['color' => 'red', 'size' => 'XL']);
        $this->assertSame('07d5da5550494c62daf9993cf954303f', $a->rowId);
        $this->assertSame($a->rowId, $b->rowId);
        $this->assertSame(3, $b->qty);
        $this->assertSame(1, $cart->countItems());
        $this->assertSame(BuyableProduct::class, $b->modelFQCN);
        $this->assertSame(550.0, $b->weight);
        $changed = $cart->update($b->rowId, ['options' => ['size' => 'L'], 'qty' => 4]);
        $this->assertNotSame($b->rowId, $a->rowId);
        $this->assertSame('L', $changed->options->size);
        $this->assertFalse($cart->content()->has($a->rowId));
        $this->assertSame(4, $cart->count());
        $cart->update($changed->rowId, 0);
        $this->assertSame(0, $cart->countItems());
    }

    public function test_instances_remain_independent_when_updated_removed_and_destroyed()
    {
        $cart = $this->app->make('shoppingcart');
        $item = $cart->add('sku', 'Product', 2, 10);
        $cart->instance('wishlist')->add('sku', 'Product', 1, 10);
        $cart->instance('default')->update($item->rowId, 5);
        $this->assertSame(1, $cart->instance('wishlist')->count());
        $cart->instance('default')->remove($item->rowId);
        $this->assertSame(1, $cart->instance('wishlist')->count());
        $cart->destroy();
        $this->assertSame(0, $cart->instance('default')->count());
        $this->assertSame(0, $cart->instance('wishlist')->count());
    }

    public function test_item_events_preserve_order_and_single_item_payload()
    {
        $seen = [];
        foreach (['adding', 'added', 'updating', 'updated', 'removing', 'removed'] as $name) {
            $this->app['events']->listen('cart.'.$name, function ($item) use (&$seen, $name) {
                $seen[] = [$name, $item, func_num_args()];
            });
        }
        $cart = $this->app->make('shoppingcart');
        $item = $cart->add('sku', 'Product', 1, 10);
        $cart->update($item->rowId, 2);
        $cart->remove($item->rowId);
        $this->assertSame(['adding', 'added', 'updating', 'updated', 'removing', 'removed'], array_column($seen, 0));
        foreach ($seen as $event) {
            $this->assertSame($item, $event[1]);
            $this->assertSame(1, $event[2]);
        }
    }

    public function test_php_session_survives_a_new_store_and_cart_object()
    {
        $handler = new ArraySessionHandler(120);
        $id = str_repeat('a', 40);
        $this->app['config']->set('session.driver', 'cart-roundtrip');
        $this->app['session']->extend('cart-roundtrip', function () use ($handler) {
            return $handler;
        });
        $this->app['session']->setId($id);
        $this->app['session']->start();
        $cart = $this->app->make('shoppingcart');
        $item = $cart->add('sku', 'Product', 3, 19.995, 550, ['size' => 'XL']);
        $cart->setDiscount($item->rowId, 10);
        $cart->instance('wishlist')->add('other', 'Other', 1, 5);
        $this->app['session']->save();
        $this->app['session']->forgetDrivers();
        $this->app['session']->setId($id);
        $this->app['session']->start();
        $newCart = $this->app->make('shoppingcart');
        $restored = $newCart->get($item->rowId);
        $this->assertNotSame($item, $restored);
        $this->assertSame($item->toArray(), $restored->toArray());
        $this->assertSame(10, $restored->getDiscountRate());
        $this->assertSame(1, $newCart->instance('wishlist')->count());
    }

    public function test_session_from_original_commit_is_readable()
    {
        $handler = new ArraySessionHandler(120);
        $id = str_repeat('b', 40);
        $handler->write($id, base64_decode(trim(file_get_contents(__DIR__.'/Fixtures/legacy/session.b64'))));
        $this->app['config']->set('session.driver', 'legacy-cart');
        $this->app['session']->extend('legacy-cart', function () use ($handler) {
            return $handler;
        });
        $this->app['session']->setId($id);
        $this->app['session']->start();
        $cart = $this->app->make('shoppingcart');
        $this->assertLegacyCart($cart);
        $this->assertSame(1, $cart->instance('wishlist')->count());
        $this->assertSame('wish-legacy', $cart->content()->first()->id);
    }

    public function test_original_database_content_restores_and_is_consumed()
    {
        $this->migrateCart();
        DB::table('shoppingcart')->insert([
            'identifier' => 'legacy', 'instance' => 'default', 'content' => $this->legacyContent(),
            'created_at' => '2024-02-10 12:00:00', 'updated_at' => '2024-02-11 13:00:00',
        ]);
        $cart = $this->app->make('shoppingcart');
        $cart->restore('legacy');
        $this->assertLegacyCart($cart);
        $this->assertSame(0, DB::table('shoppingcart')->count());
        $this->assertInstanceOf(Carbon::class, $cart->createdAt());
        $this->assertSame('2024-02-10 12:00:00', $cart->createdAt()->format('Y-m-d H:i:s'));
        $this->assertSame('2024-02-11 13:00:00', $cart->updatedAt()->format('Y-m-d H:i:s'));
        $cart->store('legacy');
        $this->assertSame($this->legacyContent(), DB::table('shoppingcart')->value('content'));
    }

    public function test_new_serialized_item_is_byte_compatible_with_original_data()
    {
        $item = new CartItem('sku-legacy', 'Товар XL', 19.995, 550, ['size' => 'XL', 'color' => 'red', 'nested' => ['gift' => true]]);
        $item->setQuantity(2);
        $item->setTaxRate(21);
        $item->setDiscountRate(10);
        $item->setInstance('default');
        $item->associate('App\\Models\\Product');
        $this->assertSame($this->legacyContent(), serialize(new Collection([$item->rowId => $item])));
    }

    public function test_carbon_timestamps_keep_creation_time_and_use_application_timezone()
    {
        $this->migrateCart();
        $timezone = date_default_timezone_get();
        date_default_timezone_set('Asia/Dushanbe');
        try {
            Carbon::setTestNow(Carbon::parse('2026-11-02 11:12:13', 'Asia/Dushanbe'));
            $cart = $this->app->make('shoppingcart');
            $this->assertNull($cart->createdAt());
            $this->assertNull($cart->updatedAt());
            $cart->add('sku', 'Product', 1, 10);
            $cart->store('dated');
            $record = DB::table('shoppingcart')->first();
            $this->assertSame('2026-11-02 11:12:13', $record->created_at);
            $this->assertSame('2026-11-02 11:12:13', $record->updated_at);
            $cart->destroy();
            $cart->restore('dated');
            $this->assertInstanceOf(Carbon::class, $cart->createdAt());
            $this->assertSame('2026-11-02T11:12:13+05:00', $cart->createdAt()->format('c'));
            Carbon::setTestNow(Carbon::parse('2026-11-03 15:16:17', 'Asia/Dushanbe'));
            $cart->store('dated');
            $record = DB::table('shoppingcart')->first();
            $this->assertSame('2026-11-02 11:12:13', $record->created_at);
            $this->assertSame('2026-11-03 15:16:17', $record->updated_at);
        } finally {
            Carbon::setTestNow();
            date_default_timezone_set($timezone);
        }
    }

    public function test_database_events_keep_empty_payloads()
    {
        $this->migrateCart();
        $seen = [];
        $this->app['events']->listen('cart.*', function ($name, $payload) use (&$seen) {
            $seen[$name] = $payload;
        });
        $cart = $this->app->make('shoppingcart');
        $cart->add('sku', 'Product', 1, 10);
        $cart->store('saved');
        $cart->destroy();
        $cart->merge('saved');
        $cart->restore('saved');
        $cart->store('saved');
        $cart->erase('saved');
        foreach (['stored', 'merged', 'restored', 'erased'] as $name) {
            $this->assertArrayHasKey('cart.'.$name, $seen);
            $this->assertSame([], $seen['cart.'.$name]);
        }
    }

    public function test_logout_listener_respects_configuration_and_clears_all_instances()
    {
        $cart = $this->app->make('shoppingcart');
        $cart->add('sku', 'Product', 1, 10);
        $cart->instance('wishlist')->add('wish', 'Wish', 1, 5);
        $user = \Mockery::mock(Authenticatable::class);
        $this->app['events']->dispatch(new Logout('web', $user));
        $this->assertSame(1, $cart->count());
        $this->app['config']->set('cart.destroy_on_logout', true);
        $this->app['events']->dispatch(new Logout('web', $user));
        $this->assertSame(0, $cart->count());
        $this->assertSame(0, $cart->instance('default')->count());
    }

}
