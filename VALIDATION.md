# Validation — 2026-11-03

Base source: `d601f3022ba01fb3c7e63f06923585d7d521dfc4` from
`https://github.com/Desu-php/LaravelShoppingcart`. Changes are pushed on
`maintenance/laravel-11-13` in [PR #1](https://github.com/Desu-php/LaravelShoppingcart/pull/1).
No tag, release, merge or Packagist update has been performed.

## GitHub Actions results — 2026-11-03

[Workflow run 41597401765](https://github.com/Desu-php/LaravelShoppingcart/actions/runs/41597401765)
passed all **13 jobs** for source commit
`fd45ab1d0a15ea08eef395d592a4943682dec56d` (the PR merge checkout was
`c4cef782d57b758a742bbfff8282a77889c73048`). No test or CI fixes were required.
The PR checks are authoritative for any later documentation commit.

| Matrix case | Actual PHP | Laravel / Carbon / Testbench / PHPUnit | Outcome |
| --- | --- | --- | --- |
| PostgreSQL storage / Laravel 13 / PHP 8.3 | 8.3.36 | PostgreSQL 18 integration | 1 tests / 17 assertions; pass |
| Laravel ^9.0 / PHP 8.0 / Carbon ^2.0 | 8.0.30 | v9.52.23 / 2.73.0 / v7.58.0 / 9.6.38 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^11.0 / PHP 8.2 / Carbon ^2.72.6 | 8.2.35 | v11.57.1 / 2.73.0 / v9.18.0 / 11.5.57 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^10.0 / PHP 8.1 / Carbon ^2.67 | 8.1.34 | v10.50.4 / 2.73.0 / v8.39.0 / 10.5.66 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^10.0 / PHP 8.3 / Carbon ^2.67 | 8.3.36 | v10.50.4 / 2.73.0 / v8.39.0 / 10.5.66 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^13.0 / PHP 8.4 / Carbon ^3.8.4 | 8.4.27 | v13.39.0 / 3.15.1 / v11.3.0 / 11.5.57 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^13.0 / PHP 8.3 / Carbon ^3.8.4 | 8.3.36 | v13.39.0 / 3.15.1 / v11.3.0 / 11.5.57 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^12.0 / PHP 8.2 / Carbon ^3.8.4 | 8.2.35 | v12.70.2 / 3.15.1 / v10.12.0 / 11.5.57 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^8.0 / PHP 7.4 / Carbon ^2.0 | 7.4.33 | v8.83.29 / 2.73.0 / v6.48.0 / 9.6.38 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^11.0 / PHP 8.2 / Carbon ^3.8.4 | 8.2.35 | v11.57.1 / 3.15.1 / v9.18.0 / 11.5.57 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^12.0 / PHP 8.4 / Carbon ^3.8.4 | 8.4.27 | v12.70.2 / 3.15.1 / v10.12.0 / 11.5.57 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^13.0 / PHP 8.5 / Carbon ^3.8.4 | 8.5.12 | v13.39.0 / 3.15.1 / v11.3.0 / 11.5.57 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |
| Laravel ^11.0 / PHP 8.4 / Carbon ^3.8.4 | 8.4.27 | v11.57.1 / 3.15.1 / v9.18.0 / 11.5.57 | 102 tests / 337 assertions + 1 tests / 5 assertions; pass |

Every matrix case ran all 103 tests / 342 assertions, including publishing in
a fresh process; PostgreSQL ran 1 test / 17 assertions. This closes the previous
local gaps for PHP 8.5, Laravel 8/PHP 7.4, Laravel 9/PHP 8.0 and Laravel 11/12
with Carbon 3 on PHP 8.4. No test cases were skipped or assertions weakened.

Audits remain advisory only for the deliberately tested EOL dependency stacks:
Laravel 8/PHP 7.4 reported 8 advisories in 3 packages; Laravel 9/PHP 8.0 reported
19 in 7 packages; Laravel 10/11 reported 5 in 1 package. Older PHP also selects
older transitive dependencies, so these counts differ from the local PHP 8.1
legacy checks below. Laravel 12/13 audits reported no advisories in this run.
Compatibility success does not certify old framework dependency security.

## Local behavioral results — 2026-11-02


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

- The hosted compatibility workflow passed the recorded source commit. Require
  successful PR checks for the final release commit before tagging.
- The full Cartesian product of supported versions was not run. Latest patched
  framework releases were tested, not every minor release.
- Laravel 5.4–7, Carbon 1 and PHP 7.0–7.3 were not run. Their inherited runtime
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

The source, release documentation and recorded hosted CI are prepared.
Release approval, tagging and publication remain separate steps. Proposed version: **5.0.0**;
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
