<?php
// Run only against the unmodified d601f302 source checkout with Laravel 10.
if (empty($argv[1])) { throw new InvalidArgumentException('Pass the original baseline checkout path.'); }
$baseline = rtrim($argv[1], '/');
require $baseline.'/vendor/autoload.php';

use Gloudemans\Shoppingcart\CartItem;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Collection;

$app = new Container;
Container::setInstance($app);
$app->instance('config', new Repository(['cart' => require $baseline.'/src/Config/cart.php']));
$item = new CartItem('sku-legacy', 'Товар XL', 19.995, 550, ['size' => 'XL', 'color' => 'red', 'nested' => ['gift' => true]]);
$item->setQuantity(2);
$item->setTaxRate(21);
$item->setDiscountRate(10);
$item->setInstance('default');
$item->associate('App\Models\Product');
$content = new Collection([$item->rowId => $item]);
$wish = new CartItem('wish-legacy', 'Saved item', 7.5, 100);
$wish->setQuantity(1);
$wish->setInstance('wishlist');
$handler = new ArraySessionHandler(120);
$session = new Store('legacy-cart', $handler);
$session->start();
$session->put('cart.default', $content);
$session->put('cart.wishlist', new Collection([$wish->rowId => $wish]));
$id = $session->getId();
$session->save();
$dir = __DIR__;
@mkdir($dir, 0777, true);
file_put_contents($dir.'/content.b64', base64_encode(serialize($content))."\n");
file_put_contents($dir.'/session.b64', base64_encode($handler->read($id))."\n");
file_put_contents($dir.'/expected.json', json_encode([
    'source_commit' => 'd601f3022ba01fb3c7e63f06923585d7d521dfc4',
    'laravel' => Composer\InstalledVersions::getPrettyVersion('laravel/framework'),
    'carbon' => Composer\InstalledVersions::getPrettyVersion('nesbot/carbon'),
    'item' => $item->toArray(),
    'total' => $item->total(),
    'taxTotal' => $item->taxTotal,
    'discountTotal' => $item->discountTotal,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");
echo "Legacy fixture created from original package: {$item->rowId}, total {$item->total()}\n";
