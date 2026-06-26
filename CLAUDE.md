# onepoint

A Symfony **API** application (minimal skeleton, no webapp) running in Docker.

- **Symfony:** 8.1 (API/microservice skeleton)
- **PHP:** 8.5 (FPM, Alpine)
- **nginx:** 1.31.2 (Alpine)
- **App URL:** http://localhost:8088

## Rules

- **Follow the official Symfony documentation** (https://symfony.com/doc) when
  writing or changing any Symfony code. Verify APIs and patterns against the
  docs for the installed version (8.1) instead of relying on memory. See the
  `symfony-development` skill for the workflow and key doc links.
- Run all PHP/Symfony/Composer commands inside the `php` container, e.g.
  `docker compose exec php php bin/console <cmd>` and
  `docker compose exec php composer <cmd>`.
- Add libraries via `composer require` (Symfony Flex recipes) rather than
  hand-wiring configuration.
- Keep the project lean — it is API-only; only add packages a feature needs.
- Never hand-edit generated files: `composer.lock`, `symfony.lock`, `vendor/`,
  `var/`.

## Layout

- `src/` — application code (PSR-4 `App\` → `src/`); controllers in `src/Controller/`
- `config/` — routes, services, per-package config
- `public/index.php` — single HTTP entry point
- `compose.yaml`, `docker/` — container environment
