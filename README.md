# RMS Backend (CodeIgniter 4 / PostgreSQL)

This is the `app/` layer, migrations, seeder, and config for the Restaurant Management
System backend, built around **architecture v2.0** from `RMS_Backend_Architecture_and_Prompts.md`.

## This zip is not a runnable project by itself

It deliberately does NOT include CodeIgniter 4's generic framework scaffold
(`public/index.php`, `.htaccess`, `writable/`, `spark`, `vendor/`) -- that boilerplate is
identical for every CI4 project and is best generated fresh from the official installer
rather than hand-copied, so it stays correct for whatever CI4 version you install.

### Setup

1. `composer create-project codeigniter4/appstarter rms-backend`
2. Copy this zip's `app/` folder into the generated project, replacing the empty one
3. Merge this zip's `composer.json` `require` block into the generated project's `composer.json`
   (don't overwrite it wholesale), then `composer update`
4. Copy `.env.example` values into the generated project's `.env`, filling in real secrets
5. `php spark migrate` (runs everything in `app/Database/Migrations`)
6. `php spark db:seed FirstAdminSeeder`
7. `php spark serve`

### What's in here

- `app/Controllers/Api/*` -- stub controllers, one per module, each pointing back at its
  matching "Prompt" block in the architecture doc (Section 8)
- `app/Models`, `app/Services`, `app/Filters`, `app/Entities` -- same pattern: real
  namespaces/class shells with a TODO and a doc reference, not full implementations
- `app/Config/Routes.php` -- the full, real route map (Section 6)
- `app/Database/Migrations` -- real migrations for all 10 tables (Section 4's schema)
- `app/Database/Seeds/FirstAdminSeeder.php` -- bootstraps the first admin account
- `ARCHITECTURE_AGENT.md` -- condensed architecture reference, meant to sit at the project
  root so an AI coding agent has the load-bearing rules on hand without re-reading the
  full architecture doc every session

### Build order

Auth -> Staff/Users -> Menu Categories -> Menu Items -> Tables -> Orders -> Billing ->
Reports -> Dashboard. Full detail and per-module prompts: `RMS_Backend_Architecture_and_Prompts.md`.

### A note on scope

This is deliberately based on architecture **v2.0**, not the later v3.x refinements
(v3 added TIMESTAMPTZ/timezone-safe reporting, an idempotency guard on repeated
payments, a couple of narrower edge-case guards, and a health-check endpoint). v2.0 is
a complete, coherent, professionally-defensible architecture on its own -- it fixed the
structural issues from the first draft (a missing Users module, crash-causing deletes,
a logout that didn't revoke anything). If you want any of the v3 refinements folded in
later, they're documented in the full architecture doc's changelog (Section 0).
