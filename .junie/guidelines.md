Project: Death Stranding tracker (Laravel 10 + Filament v3)

This document captures project-specific knowledge to speed up future development, testing, and debugging. It assumes an experienced PHP/Laravel developer.

1. Build/Configuration Instructions
- PHP/Composer/Node versions
  - PHP: 8.1+ (project targets ^8.1).
  - Composer dependencies are declared in composer.json; dev-tooling includes ECS, PHPStan, Rector, PHPUnit 10.
  - Node is used for Vite assets; versions are flexible but Node 18 LTS works well.
- Environment files
  - Copy .env.example to .env (a Composer post-root hook also does this automatically for fresh checkouts).
  - Generate APP_KEY: php artisan key:generate
- Database
  - App runtime uses MySQL. Create a database named stranding and configure credentials in .env.
  - Migration strategy: php artisan migrate --seed or php artisan migrate:fresh --seed
  - Seeders provide canonical datasets relied on by tests and UI: Users, Districts, Locations, DeliveryCategories, Orders.
- Vite / Frontend assets
  - npm install
  - npm run dev for development; npm run build for production.
- Filament admin panel
  - Filament v3 is integrated. The seeded user user@example.com (password password) has access to the /app panel after seeding. APP_URL in .env should point to your local domain (e.g., https://stranding.test) for asset URLs.
- Post-update hooks
  - Composer post-autoload-dump runs filament:upgrade, which may modify Filament assets/config if versions change. Review changes after dependency updates.

2. Testing Information
- Test runner and configuration
  - PHPUnit 10 is configured via phpunit.xml. Tests default to an in-memory SQLite database:
    - DB_CONNECTION=sqlite, DB_DATABASE=:memory:
    - Ensure the pdo_sqlite extension is enabled in the PHP used by the test runner.
  - If you cannot enable SQLite, you can override to MySQL by adjusting phpunit.xml or by providing a phpunit.xml.dist override; however, the default flow is faster and hermetic.
- Running the test suite
  - Composer script: composer tests (maps to phpunit)
  - Direct: vendor\bin\phpunit (or phpunit on PATH) using phpunit.xml at repo root.
- Database state during tests
  - Tests bootstrap the full Laravel app (tests/CreatesApplication.php). Migrations are expected to run within test cases that depend on schema/seeded data. The provided feature tests assume the seeders can be invoked and that the in-memory database is usable.
- Adding new tests
  - Place unit tests under tests/Unit and feature/integration tests under tests/Feature, matching current suite layout in phpunit.xml.
  - Extend Tests\TestCase.
  - Prefer in-memory SQLite for speed; when a test requires MySQL-specific behavior, use RefreshDatabase with a MySQL testing database and set env via annotations or a phpunit.xml override.
- Example: creating and running a simple test
  - Example file path: tests/Unit/SmokeTest.php
  - Content example:
    <?php
    declare(strict_types=1);
    namespace Tests\Unit;
    use PHPUnit\Framework\TestCase;
    final class SmokeTest extends TestCase
    {
        public function test_truthy(): void
        {
            $this->assertTrue(true);
        }
    }
  - To run only this test: vendor\bin\phpunit --filter SmokeTest or composer tests -- --filter SmokeTest
  - Note: For full Laravel container bootstrapping, extend Tests\TestCase instead and place the file under tests/Feature.
- Verifying the provided examples
  - The repository already contains working tests exercising seeding and models, and composer tests executes them using the default phpunit.xml configuration.
  - A sample Unit test (SmokeTest) was created and executed locally on 2025-09-03 with PHPUnit 10.5.20 (Runtime PHP 8.2.20) using vendor\\bin\\phpunit --filter SmokeTest; it passed and was then removed from the repo to keep the tree clean.

3. Additional Development Information
- Code style
  - Easy Coding Standard (ECS) is configured (ecs.php). Use composer fix-cs to auto-fix; composer check-cs in CI/local checks.
  - Rector is configured (rector.php). Use composer rector-dr for dry run and composer rector to apply rules when performing refactors.
  - PHPStan (phpstan.neon) is included with Laravel extensions; run composer phpstan to analyze. Baseline generation is available via composer phpstan-baseline.
  - Parallel-lint is available via composer lint.
  - A convenience meta-task exists: composer all runs checks and tests in a sensible order.
- Filament/Widgets patterns
  - Widgets like App\Filament\App\Widgets\CompleteOrdersEastChart compute datasets via Eloquent with scoped counts and authenticated user filtering. When modifying or adding widgets:
    - Prefer withCount for aggregate performance.
    - Maintain per-user filtering on deliveries (where('user_id', auth()->id())).
    - Use DeliveryStatus enum values consistently for status checks.
    - Keep chart options minimal and use stacked axes when comparing related series.
- Seeding assumptions
  - Feature tests rely on canonical seeders providing: two users (admin@example.com and user@example.com), three districts (Western, Central, Eastern), 40 locations, four delivery categories, and 540 orders.
  - When adding new seed data or changing enums/IDs, ensure tests that assert counts are updated accordingly.
- Testing tips specific to this repo
  - Because phpunit.xml sets an in-memory SQLite database, schema must be migrated at runtime for tests that touch the database. If you add tests that depend on schema, ensure they call migrations/seeders in setUp or use traits like RefreshDatabase/DatabaseMigrations.
  - Eloquent factories reside in database/factories; prefer factories for model-centric tests rather than hand-rolled inserts.
  - If you introduce time-sensitive logic (e.g., delivery trends), prefer Carbon::setTestNow in tests and avoid relying on real time.
- Local dev URL and HTTPS
  - APP_URL is set to a https:// domain in README examples. If using self-signed HTTPS locally, ensure your dev server (e.g., Laravel Valet, Laragon, Sail) is configured to serve that domain. Update APP_URL to match your actual local domain to avoid asset URL issues.

4. Quick commands reference
- Install dependencies: composer install; npm install
- App setup: cp .env.example .env; php artisan key:generate; create DB; php artisan migrate --seed
- Dev server: php artisan serve (or use your local stack); npm run dev for assets
- Tests: composer tests
- Static analysis & style: composer all

Notes
- This file is intentionally high signal for this codebase and omits generic Laravel docs that are widely known.
