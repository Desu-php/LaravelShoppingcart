# Original cart fixtures

Generated from unmodified runtime source at fork commit
`d601f3022ba01fb3c7e63f06923585d7d521dfc4`, using Laravel 10.50.4 and Carbon 2.73.0.
The baseline checkout only changed development constraints to Testbench 8 and
PHPUnit 9.6 to run on the available PHP. Runtime source was not changed.

- `content.b64`: base64 transport of the actual serialized Collection/CartItem.
  SQLite/MySQL store its decoded bytes; PostgreSQL stores the base64 string.
- `session.b64`: actual bytes saved by Illuminate's PHP session Store, containing
  independent default/wishlist collections and a generated test CSRF token.
- `expected.json`: original row ID, public array representation, totals and
  dependency/source provenance.
- `generate.php`: reproducible fixture generator; it loads classes from the
  supplied original checkout, not the new implementation.

To reproduce, check out the exact baseline in a separate folder, install a
Laravel 10 / Carbon 2 development stack there, then run from the new checkout:

```sh
php tests/Fixtures/legacy/generate.php /absolute/path/to/original-checkout
```

The generated session CSRF token changes between runs. It is synthetic, not an
application credential. Tests inspect its cart objects rather than its token.
Do not regenerate fixtures with updated source just to make a failing test pass.
