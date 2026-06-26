---
name: symfony-development
description: Use whenever writing, modifying, or debugging Symfony code in this project — controllers, routing, services, configuration, security, serialization, console commands, doctrine/ORM, validation, dependency injection, bundles, or any Symfony feature. Consult the official Symfony documentation (https://symfony.com/doc) before implementing, instead of relying on memory.
---

# Symfony development — follow the official documentation

This project runs **Symfony 8.1** on **PHP 8.5** inside Docker. When doing any
Symfony work, the official docs at <https://symfony.com/doc> are the source of
truth. APIs, recommended patterns, and config syntax change between major
versions — do not implement from memory; verify against the docs first.

## Workflow

1. **Before implementing a Symfony feature**, fetch the relevant doc page with
   `WebFetch` and follow its current guidance. Use `/doc/current/...` (which
   tracks the latest stable, currently 8.x). Examples:
   - Routing: <https://symfony.com/doc/current/routing.html>
   - Controllers: <https://symfony.com/doc/current/controller.html>
   - Service container / DI: <https://symfony.com/doc/current/service_container.html>
   - Configuration: <https://symfony.com/doc/current/configuration.html>
   - Console commands: <https://symfony.com/doc/current/console.html>
   - Serializer (APIs): <https://symfony.com/doc/current/serializer.html>
   - Validation: <https://symfony.com/doc/current/validation.html>
   - Security: <https://symfony.com/doc/current/security.html>
   - Doctrine/database: <https://symfony.com/doc/current/doctrine.html>
   - Best practices: <https://symfony.com/doc/current/best_practices.html>

2. **Prefer official tooling and recipes.** Add libraries with
   `composer require` (via Symfony Flex) rather than hand-editing config, so
   recipes wire things up the documented way.

3. **Match the installed version.** This project is pinned to `8.1.*`. If a doc
   page documents a different version, confirm the feature exists in 8.1 before
   using it (check the "deprecated"/"new in" notes on the page).

4. **Cite the doc page** you followed in your explanation so changes are
   traceable to official guidance.

## Running commands in this project

Everything runs inside the `php` container (defined in `compose.yaml`):

```bash
docker compose exec php php bin/console <command>   # Symfony console
docker compose exec php composer require <package>  # add a library
docker compose exec php composer install            # install deps
docker compose exec php php bin/console cache:clear # clear cache
```

The app is served at <http://localhost:8088>.

## Conventions

- **API-only skeleton** (no Twig/webapp). Keep it lean — only add packages the
  feature actually needs.
- Your code goes in `src/` (PSR-4 `App\` → `src/`), routes in
  `config/routes.yaml` or via PHP attributes on controllers, per-package config
  in `config/packages/`.
- Never hand-edit `composer.lock`, `symfony.lock`, `vendor/`, or `var/`.
