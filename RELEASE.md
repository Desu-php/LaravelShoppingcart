# Release 5.0.0 — 2026-11-03

This release uses the immutable Git tag `v5.0.0`. Composer derives the version
from that tag; no `version` field belongs in composer.json. Hosted compatibility
results are recorded in VALIDATION.md. Packagist indexing is verified separately
after the tag is pushed and the repository is registered.

## Version rationale

Before this release the inspected fork had no tags (local and remote tag lists were empty). Its
documentation/code inherit upstream's 4.2.0 lineage. Use **5.0.0** as the first
stable tag in this fork, rather than inventing a previous fork release.

The new explicit `php >=7.0` requirement matches the source's existing scalar
type declarations. Previously Composer could allow PHP 5.6 via Laravel 5.4;
those installations are now rejected by metadata. Although this corrects the
documented language floor rather than changing working Laravel 10 behavior, it
narrows installable PHP versions, so a conservative SemVer major is appropriate.
Older Laravel ranges remain available on compatible PHP versions. Runtime API,
calculations and storage formats are preserved. No `version` field is added.

## Release notes

desu-php/shoppingcart 5.0.0 adds Laravel 11, 12 and 13 with Carbon 3 support,
preserving Laravel 10, existing older dependency ranges, the Gloudemans API,
ShoppingCart facade alias and shoppingcart binding. Calculations, row IDs,
options, instance keys, events and persisted cart formats are unchanged.

The release adds explicit framework/Carbon CI combinations and regression tests
for session persistence, old serialized objects, dates, discovery and published
configuration/migrations. It also corrects PHP 8 handling of non-numeric
price/weight values and aligns provider bootstrap timing with Laravel's lifecycle.

Breaking change: Composer now enforces PHP >=7.0. Laravel 10 consumers already
meet that floor. The public API and persisted formats are unchanged. Laravel 12/13 require Carbon 3 because the
framework requires it. No cart data reset is required from this fork's current
master. Consult UPGRADING.md and VALIDATION.md before updating an application.

## Maintainer procedure for stable releases

1. Review the diff from d601f302, validation evidence and dependency advisories.
   Push the reviewed branch / merge it using the repository's normal process.
   Wait for all required compatibility jobs, including both Laravel 11 Carbon
   jobs, to pass. Do not treat Composer solving as a compatibility test.
2. In a clean checkout of the reviewed release commit:

   ```sh
   composer validate --strict
   composer install --prefer-dist
   composer test
   git status --short
   git ls-remote --tags origin
   ```

   Confirm the working tree is clean, v5.0.0 is unused, the license/credits are
   present, and the archive contains source/config/migrations. Do not add a
   `version` field to composer.json: Composer derives it from the Git tag.
3. Move the unreleased CHANGELOG section to 5.0.0 with the actual release date,
   commit that documentation update, and obtain the separate release approval.
   Only after approval:

   ```sh
   git tag -a v5.0.0 -m "Release 5.0.0"
   git push origin v5.0.0
   ```

   A GitHub Release is optional and separate; use the release notes above after
   confirming the tagged commit. Never move a published stable tag.
4. Sign into [Packagist](https://packagist.org/) as a maintainer. If the package
   is not registered, use [Submit](https://packagist.org/packages/submit) with
   `https://github.com/Desu-php/LaravelShoppingcart`. If already registered,
   confirm its repository URL and maintainer access, then use Update. The
   declared package name must remain `desu-php/shoppingcart`. Account/package
   registration status was not established by this change.
5. Configure Packagist's documented GitHub integration/webhook for updates.
   Wait until Packagist lists v5.0.0 with the correct source reference and
   requirements. Check from a clean consumer:

   ```sh
   composer show desu-php/shoppingcart --all
   composer require "desu-php/shoppingcart:^5.0"
   composer show desu-php/shoppingcart
   ```

6. Apply the exact application migration steps in UPGRADING.md; verify existing
   cart/session continuity before deploying the new application lock file.

Sources: [Packagist submission and updating](https://packagist.org/about),
[Composer version metadata](https://getcomposer.org/doc/04-schema.md#version).
