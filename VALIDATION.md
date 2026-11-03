# Validation — 2026-11-02

Base source: `d601f3022ba01fb3c7e63f06923585d7d521dfc4` from
`https://github.com/Desu-php/LaravelShoppingcart`. Changes are local on
`maintenance/laravel-11-13`; no branch, tag, release or Packagist update was sent.

## Behavioral results

Each successful `composer test` runs **102 tests / 337 assertions**, then a fresh
PHP process tests config/migration publishing and rollback (**1 test / 5
assertions**). No cases were disabled or skipped. The original suite has 88
cases; 15 new cases cover compatibility and integration. Separate PostgreSQL
tests have **1 test / 17 assertions** each.

| Laravel | PHP actually run | Carbon | Testbench | PHPUnit | Result |
| --- | --- | --- | --- | --- | --- |
| 8.83.29 | 8.1.34 | 2.73.0 | 6.48.0 | 9.6.38 | 103 tests pass |
| 9.52.23 | 8.1.34 | 2.73.0 | 7.58.0 | 9.6.38 | 103 tests pass |
| 10.50.4 | 8.1.34, 8.3.24 | 2.73.0 | 8.39.0 | 10.5.66 | 103 tests pass on each |
| 11.57.1 | 8.2.35, 8.4.24 | 2.73.0 | 9.18.0 | 11.5.57 | 103 tests pass on each |
| 11.57.1 | 8.2.35, 8.3.24 | 3.15.1 | 9.18.0 | 11.5.57 | 103 tests pass on each |
| 12.70.2 | 8.2.35, 8.3.24 | 3.15.1 | 10.12.0 | 11.5.57 | 103 tests pass on each |
| 13.39.0 | 8.3.24, 8.4.24 | 3.15.1 | 11.3.0 | 11.5.57 | 103 tests pass on each |

Laravel 10 also passed an exploratory PHP 8.4.24 run; PHP 8.4 is outside
[Laravel 10's official support table](https://laravel.com/docs/13.x/releases#support-policy),
so that extra result is not a new framework support promise.

Real PostgreSQL 18 tests passed on PHP 8.3.24 with Laravel 12.70.2 and 13.39.0 /
Carbon 3.15.1. They cover base64 serialization with private-property NUL bytes,
store timestamps, merge without consuming storage, restore/delete, historical
serialized data and independence of stored/default/wishlist content. SQLite is
used for persistence tests in every full suite.

The data fixtures were generated with **unmodified baseline runtime source**,
Laravel 10.50.4 and Carbon 2.73.0. Tests read both its session and DB collections
and check item types, row IDs, nested options, discount, tax, model class and
totals. New serialized collection bytes equal the original bytes, providing a
backward-format check as well as old-to-new reads. Fixture generator/provenance
are in `tests/Fixtures/legacy/`.

## Baseline test correction

The baseline was tested first with Testbench 8 / PHPUnit 9.6.38 (development
constraints only; its old Testbench range could not run Laravel 10). Result:
88 tests, 236 assertions, one failure in the net-price summary comparison:
`253.29000000000002` versus `253.28999999999996`. The formatted `253,29` check
passed. This is binary float addition order, not a changed cart total.

Both net/gross summary cases now assert exact expected subtotal/tax strings and
equal integer minor-unit sums. There is no epsilon, removed assertion or
calculator change. All other original assertions remain. Method names use
`test_` so PHPUnit 11 does not rely on deprecated `@test` metadata.

## Dependency audit and CI policy

Composer reports **5 advisories in laravel/framework** for the Laravel 10 and 11
full-framework test stacks; these framework majors are outside upstream security
support at this validation date. Laravel 8 has 6 advisories across 2 packages;
Laravel 9 has 6 in one package. These are recorded test dependency findings, not
newly introduced cart runtime packages. The Laravel 12/13 stacks report none at
this run. An application's actual Laravel 10 lock needs its own audit; package
compatibility is not a statement that its framework is security-supported.

To exercise those old framework versions with current Composer, only the
isolated EOL compatibility jobs set `COMPOSER_NO_SECURITY_BLOCKING=1` during
resolution. CI still prints `composer audit --locked` findings; only that audit
step is advisory on EOL jobs. Tests remain required. Modern jobs retain normal
Composer security blocking and fail on audit findings. No security override is
added to composer.json or application installation instructions.

## Limits / release gate

- The new GitHub Actions workflow has been prepared and locally reviewed, but
  has **not run on GitHub** because no remote change was published. Require all
  compatibility jobs before tagging.
- PHP 8.5, Laravel 8 on PHP 7.4, Laravel 9 on PHP 8.0, Laravel 11/12 with Carbon 3
  on PHP 8.4, and the full Cartesian product of supported versions were not run
  locally. The CI includes those targeted combinations where specified in its
  matrix. Latest patched framework releases were tested, not every minor release.
- Laravel 5.4–7, Carbon 1 and PHP 7.0–8.0 were not run. Their inherited runtime
  ranges remain for existing consumers; they are not newly certified.
- No live application/its composer.lock was supplied. This fork's current master
  is the legacy-data baseline; a different application pin needs a separate diff.
- MySQL, Redis/file/cookie session backends and a live storefront were not
  integration-tested. Session serialization is tested through a real Laravel
  Store/SessionManager and handler save/start cycle; DB coverage is SQLite and
  PostgreSQL. Custom JSON session serialization is outside the object format.
- No Packagist account/package registration state was established. Publication
  and consumer installation of the future stable tag cannot be tested before
  the tag is authorized and indexed.

The source and release documentation are prepared; the complete hosted CI and
publication are separate remaining release steps. Proposed version: **5.0.0**;
the newly explicit PHP >=7.0 metadata floor is treated as a packaging breaking
change; Laravel 10 API/data behavior is preserved.

## Additional checks and source audit

- `composer validate --strict`, PHP syntax checks on all source/test/integration
  files with PHP 8.1, and `git diff --check` passed.
- The workflow YAML and its 12 explicit combinations were parsed and checked;
  the official actionlint Docker image accepted the workflow, including shell
  snippets and GitHub expressions. No new lint tool is added to the package.
- Comparison against inherited upstream commit `1c50fb5` confirms the fork's
  package name, Laravel 10 constraints, `ShoppingCart` alias and `shoppingcart`
  binding. `src/Cart.php`, the calculators, contracts, facade, config, migration
  and LICENSE remain unchanged by this maintenance update.
- Auxiliary graphify analysis helped locate relationships. Its initial snapshot
  reported 29 dangling references and possible edge collapse; it is incomplete
  and was not used as compatibility evidence. Runtime sources and tests were
  inspected directly. Graph/cache files are kept outside the package deliverable.

## Official API references reviewed

- Laravel framework composer manifests:
  [10](https://github.com/laravel/framework/blob/10.x/composer.json),
  [11](https://github.com/laravel/framework/blob/11.x/composer.json),
  [12](https://github.com/laravel/framework/blob/12.x/composer.json),
  [13](https://github.com/laravel/framework/blob/13.x/composer.json).
- [Laravel 12 Carbon 3 requirement](https://laravel.com/docs/12.x/upgrade#carbon-3)
  and [Laravel 13 upgrade guide](https://laravel.com/docs/13.x/upgrade).
- Carbon Creator implementations:
  [2.73.0](https://github.com/briannesbitt/Carbon/blob/2.73.0/src/Carbon/Traits/Creator.php),
  [3.10.3](https://github.com/briannesbitt/Carbon/blob/3.10.3/src/Carbon/Traits/Creator.php).
  Runtime tests additionally exercise the resolved 3.15.1 release. The package
  uses `now()`/`parse()` and no changed diff/timestamp-creation APIs.
- Illuminate service provider, SessionManager/Store, Collection, dispatcher and
  migration APIs inspected in the installed framework sources. No runtime
  version checks or conditional compatibility branches were needed.
- [Composer schema](https://getcomposer.org/doc/04-schema.md),
  [security-blocking environment option](https://getcomposer.org/doc/03-cli.md#composer-no-security-blocking),
  [Packagist publishing](https://packagist.org/about).
