<?php

namespace Gloudemans\Tests\Shoppingcart;

use Gloudemans\Shoppingcart\CartItem;
use Gloudemans\Shoppingcart\ShoppingcartServiceProvider;
use Orchestra\Testbench\TestCase;

class CartItemTest extends TestCase
{
    public function test_non_numeric_price_is_rejected_without_a_php_type_error()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please supply a valid price.');
        new CartItem(1, 'Product', []);
    }

    public function test_null_weight_is_rejected_without_a_php_deprecation()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Please supply a valid weight.');
        new CartItem(1, 'Product', 10, null);
    }

    /**
     * Set the package service provider.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ShoppingcartServiceProvider::class];
    }

    public function test_it_can_be_cast_to_an_array()
    {
        $cartItem = new CartItem(1, 'Some item', 10.00, 550, ['size' => 'XL', 'color' => 'red']);
        $cartItem->setQuantity(2);

        $this->assertEquals([
            'id'      => 1,
            'name'    => 'Some item',
            'price'   => 10.00,
            'rowId'   => '07d5da5550494c62daf9993cf954303f',
            'qty'     => 2,
            'options' => [
                'size'  => 'XL',
                'color' => 'red',
            ],
            'tax'      => 0,
            'subtotal' => 20.00,
            'discount' => 0.0,
            'weight'   => 550.0,
        ], $cartItem->toArray());
    }

    public function test_it_can_be_cast_to_json()
    {
        $cartItem = new CartItem(1, 'Some item', 10.00, 550, ['size' => 'XL', 'color' => 'red']);
        $cartItem->setQuantity(2);

        $this->assertJson($cartItem->toJson());

        $json = '{"rowId":"07d5da5550494c62daf9993cf954303f","id":1,"name":"Some item","qty":2,"price":10,"weight":550,"options":{"size":"XL","color":"red"},"discount":0,"tax":0,"subtotal":20}';

        $this->assertEquals($json, $cartItem->toJson());
    }

    public function test_it_formats_price_total_correctly()
    {
        $cartItem = new CartItem(1, 'Some item', 10.00, 550, ['size' => 'XL', 'color' => 'red']);
        $cartItem->setQuantity(2);

        $this->assertSame('20.00', $cartItem->priceTotal());
    }
}
