# Changelog

## [5.0.0] — 2026-11-03

### Added

- Laravel 11, 12 and 13 dependency support, with Carbon 3 where Laravel allows it.
- Explicit Laravel/PHP/Testbench/PHPUnit/Carbon CI combinations, including both
  Carbon 2 and 3 on Laravel 11 and legacy Laravel 8/9 jobs.
- Regression coverage for the fork's binding/facade, Buyable and row IDs, options,
  instance isolation, event order and payloads, PHP session persistence, original
  serialized data, mutable Carbon timestamps/timezone and publishing/migrations.
- Package metadata, upgrade instructions, validation report and release procedure.
- Preserved upstream acknowledgements; contributor-list updates are now manually
  dispatched so automated commits cannot overwrite release documentation.

### Fixed

- Validate price/weight with `is_numeric()` directly. Impossible negative string
  length checks caused PHP 8 type errors/deprecations for malformed values instead
  of the intended `InvalidArgumentException`. Valid numeric inputs are unchanged.
- Bootstrap publish mappings and the logout listener in the provider's `boot()`
  phase, after application providers have registered their dependencies.
- Modern PHPUnit test discovery/configuration. All original test cases remain;
  two monetary summary assertions now check exact expected amounts and integer
  minor-unit equality instead of binary floating-point associativity.
- README installation name, facade alias, weight/options argument positions and
  Buyable method signatures. Original contributor credits remain.

### Compatibility / breaking changes

Composer now explicitly requires PHP >=7.0, reflecting the scalar type
declarations already present in the source. The old Composer ranges could allow
installation on PHP 5.6; it is now rejected at resolution time. Treat this
packaging constraint as a breaking change and use a major release. Laravel 10
applications are unaffected. Laravel 10 and the existing older Laravel dependency
ranges remain. No changes to calculators, row ID generation, item properties,
contracts, event names/payloads, instance session keys or database serialization.
Carbon 1 and Laravel 5.4–7 constraints are inherited, not newly verified.
See [VALIDATION.md](VALIDATION.md) for tested combinations and limits.

## Historical baseline

The fork's baseline is `d601f3022ba01fb3c7e63f06923585d7d521dfc4` (master at
inspection). Before this release it had no Git tags: both `git tag -l` and
`git ls-remote --tags origin` were empty during the baseline audit. There is therefore no previous stable **fork** release to invent.

The inherited README identifies upstream 4.2.0's PostgreSQL base64 storage change.
Version 5.0.0 deliberately continues that documented lineage. Upstream version
numbers must not be mistaken for tags published by this fork.
