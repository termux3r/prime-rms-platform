# RMS Backend — Agent Context (brief)

Condensed reference for an AI coding agent working in this repo. This project targets
**architecture v2.0**. Full detail, exact schema DDL, and per-module implementation
prompts: `RMS_Backend_Architecture_and_Prompts.md` (keep that doc nearby; this file is
the quick-reference, not a replacement for it).

## Stack
CodeIgniter 4 · PostgreSQL 15+ · PHP 8.x · MVC · REST JSON API under `/api/v1`.

## Non-negotiable conventions
- **Controllers are thin** — HTTP concerns only. Business logic lives in `app/Services/`.
  Models only handle persistence, validation, and soft delete.
- **Every multi-table write** wraps in `$db->transStart()` / `transComplete()`.
- **Response shape:** `{ "status": "success|error", "data": ..., "message": "..." }`.
- **Auth:** JWT, HS256, secret from `.env`. Access token ~30–60 min + refresh token
  (7 days, stored only as a hash, rotated on every use). Logout blocklists the token's
  `jti` in `revoked_tokens`. `JwtAuthFilter` checks signature + expiry + blocklist on
  every `/api/*` request except login/refresh.
- **RBAC:** `RoleFilter` per route group — `admin` vs `admin,cashier` — matching the
  SRS's two user classes.
- **Soft delete:** `menu_categories`, `menu_items`, `restaurant_tables` use CI4's
  `$useSoftDeletes` / `$deletedField = 'deleted_at'`. Never hard-delete these — their
  FKs have no `ON DELETE CASCADE`, so a physical delete on anything with order history
  throws. Users are never hard-deleted either — deactivate via `status` only.
- **`restaurant_tables.status`** (occupied/available) changes ONLY through
  `TableStateService::markOccupied()` / `markAvailable()` — never set directly by
  `OrderService` or `BillingService`.
- **Audit trail:** money-relevant state changes (order cancellation, payment recorded)
  write an `audit_logs` row inside the same transaction, via `AuditService`.
- **`password_hash`** must never be serialized in any API response — hidden on the
  `User` entity, not just by controller convention.

## Database (full DDL: architecture doc Section 4)
Tables: `users`, `refresh_tokens`, `revoked_tokens`, `menu_categories`, `menu_items`,
`restaurant_tables`, `orders`, `order_items`, `bills`, `audit_logs`.

The load-bearing constraint: a **partial unique index** on
`orders(table_id) WHERE status IN ('pending','completed')` guarantees one active order
per table at the database level — don't rely on an application-level check alone for
this rule; the index is the actual source of truth.

## Business rules an agent must not violate
- An order needs ≥1 item before it can be completed (`POST .../complete`).
- Once a bill is `paid`, its order is immutable — item add/update/remove rejected (409).
- Cancelling an order (`POST .../cancel`) is rejected if the order is already `paid`.
- The last remaining active admin account cannot be deactivated.
- `order_items.unit_price` is a snapshot of the menu price at add-time — never
  re-derive it from the current `menu_items.price` for an existing line item.
- `orders.total_amount` is always server-recalculated from `order_items` — never trust
  a client-sent total.

## Build order
Auth → Staff/Users → Menu Categories → Menu Items → Tables → Orders → Billing →
Reports → Dashboard.

The first admin account comes from `app/Database/Seeds/FirstAdminSeeder.php`, not the
API — there is no public signup route by design.

## Where to find more
- Per-module implementation prompts → architecture doc, **Section 8**
- Full REST contract (every endpoint, role, status code) → **Section 6**
- Error / HTTP status conventions → **Section 7**
- Folder layout this project follows → **Section 3**

## Scope note
This project deliberately targets v2.0, not the later v3.x hardening (timezone-safe
reporting, a payment-idempotency guard, a couple of narrower edge-case guards, a
health-check endpoint). If asked to add any of those, they're fully specified in the
architecture doc's Section 0 changelog — implement from there rather than guessing.
