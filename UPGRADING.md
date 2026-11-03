# Upgrading to 5.0.0

## PHP requirement (packaging breaking change)

Composer now explicitly requires PHP >=7.0, matching the scalar type declarations
already used in the source. The previous manifest could resolve on PHP 5.6 via
Laravel 5.4, despite that language incompatibility. Such installs are now rejected
early. This is treated conservatively as a breaking metadata change, hence 5.0.0.
Laravel 10 already requires PHP 8.1+, so its existing consumers are unaffected.
Older Laravel constraints remain available with compatible PHP versions.

## From this fork's dev-master

The API is preserved: `Gloudemans\Shoppingcart\Cart`, `CartItem`,
`Contracts\Buyable`, `Facades\Cart`, the service provider, `instance()`, the
`shoppingcart` container binding and global `ShoppingCart` alias.

1. Record `composer show desu-php/shoppingcart` and the source commit in the
   application's `composer.lock`. Keep a copy of the current lock file and test
   with representative persisted carts before deployment. The legacy fixtures
   in this update come from d601f302; another application pin must be compared
   separately if it contains additional changes.
2. Keep Laravel 10 on Carbon 2 and PHP 8.1+. For Laravel 11 use PHP 8.2+ and
   Carbon 2 or 3; Laravel 12 requires PHP 8.2+/Carbon 3 and Laravel 13 requires
   PHP 8.3+/Carbon 3. Upgrade the framework separately using its official guide.
3. **After** 5.0.0 is published/indexed, remove only this package's custom VCS
   repository entry from the application `composer.json` if switching to
   Packagist. Other repository entries stay. A retained VCS entry can still
   provide stable tags, but may take priority over Packagist.
4. Replace the development requirement and resolve the affected dependencies:

   ```sh
   composer require "desu-php/shoppingcart:^5.0" --no-update
   composer update desu-php/shoppingcart --with-all-dependencies
   composer validate --strict
   composer show desu-php/shoppingcart
   php artisan package:discover
   php artisan config:clear
   ```

   Inspect the lock diff: the package should be v5.0.0 (or a later compatible
   stable 5.x release), not dev-master. Keep the application's usual stable
   stability setting; removing `minimum-stability: dev` is only appropriate if
   no other application dependency needs it. Rebuild configuration caches using
   the application's normal deployment procedure.
5. Smoke-test add/merge, quantity/options update, totals/tax/discount, removal,
   clear, default/wishlist independence, logout behavior, session continuity and
   DB restore. Commit the application's resulting lock file.

No republishing with `--force` or new cart migration is needed for an existing
installation. Compare published `config/cart.php` with defaults before making
intentional application configuration changes.

## Existing session and database data

The class names and serialized properties are unchanged. Current fork DB records
and PHP-serialized session objects can be read without conversion. Tests restore
real baseline fixtures with options (including nested values), discount/tax,
model class name and instance metadata, and compare newly serialized content
byte-for-byte against the baseline. `restore()` still consumes its DB record;
`merge()` does not. PostgreSQL remains base64 over PHP serialization.

Keep the application key, PHP session serialization, session driver, backing
storage and cookie name stable. Laravel/framework changes can alter generated
cookie or cache prefixes independently of this package. JSON session
serialization does not preserve the existing object format.

`createdAt()` and `updatedAt()` still return mutable `Carbon\Carbon` objects
after `restore()`, and `null` before restore. Stored timestamps keep their
existing format/timezone semantics. Carbon 3's changed `diffIn*` return values
do not affect the package, but application code using those methods needs its
own review.

## From an older upstream package

Do not install two packages that export `Gloudemans\Shoppingcart` at the same
time. Review the application's package lock before changing package names.
This fork registers `ShoppingCart` / `shoppingcart`, rather than upstream's
global `Cart` alias / `cart` binding. Namespaced facade imports remain available.

For PostgreSQL data predating upstream 4.2.0's base64 transition, the current fork
already expects a different storage representation. Back up and plan a targeted
data migration based on the actual old version; that older conversion is outside
this update. Do not clear a production cart table as a routine upgrade step.
