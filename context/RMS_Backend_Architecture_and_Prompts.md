# Restaurant Management System (RMS)
## Backend Architecture & Feature-by-Feature Implementation Prompts

**Version:** 3.1 (third pass — small addendum, most substantive issues already resolved)
**Stack:** CodeIgniter 4 · PostgreSQL 15+ · PHP 8.x · MVC · REST API + Bootstrap 5 views
**Prepared for:** Dallol Tech internship task
**Based on:** RMS SRS v1.0

---

## 0. Review History

### 0.1 v1.0 → v2.0 (first pass)
| # | Area | v1.0 | v2.0 |
|---|---|---|---|
| 1 | Deleting a used menu item / table | Threw an uncaught DB foreign-key error (500) | Soft delete via `deleted_at` — no crash, history preserved |
| 2 | Table status (occupied/available) | Set independently in two services, could drift | Centralized in one `TableStateService` |
| 3 | Staff/user accounts | No module existed at all | New Module 2: Staff/User Management |
| 4 | Logout | JWT stayed valid until natural expiry | `revoked_tokens` blocklist checked on every request |
| 5 | Login brute-force | Throttle filter drawn in the diagram, never wired up | Applied to `/auth/login` explicitly |
| 6 | Password hash | No explicit protection from leaking into JSON | Hidden field, enforced in Entity/Model |
| 7 | Menu item images | Stored under `writable/`, not web-reachable | Stored under `public/uploads`, with MIME/size validation |
| 8 | Indexes | Only the one partial unique index | Explicit indexes on every FK + reporting date columns |
| 9 | Audit trail | None | `audit_logs` table, written inside the same transaction as the action |
| 10 | Testing | Not addressed | Dedicated Testing Strategy section |
| 11 | Cancel-order endpoint | `DELETE /orders/{id}` (inconsistent with `POST /complete`) | `POST /orders/{id}/cancel` with optional `reason` |
| 12 | Auth token lifetime | Single long-lived JWT only | Access + refresh token pattern (with a documented simpler fallback) |

### 0.2 v2.0 → v3.0 (this pass)
| # | Area | v2.0 | v3.0 |
|---|---|---|---|
| 1 | Timestamps | Plain `TIMESTAMP` everywhere | `TIMESTAMPTZ` everywhere + explicit local-timezone bucketing for "today"/daily reports |
| 2 | Cancelling an order with an existing bill | Only blocked if the bill was already paid | Blocked if any bill row exists at all, paid or not |
| 3 | Double-submitting a payment | No explicit guard | `recordPayment` rejects with 409 if already paid |
| 4 | List endpoint pagination | No stated limit | Default 20/page, server-clamped to a max of 100 |
| 5 | Order detail retrieval | No explicit anti-N+1 guidance | Must join/batch-fetch order items + names in one query |
| 6 | Order total computation | PHP-side `bcmath` | Simpler: Postgres `SUM(subtotal)` computes the total directly |
| 7 | CSRF | Not addressed | Clarified: only needed for session-rendered forms, not the JWT API |
| 8 | Error responses | Mapped to HTTP codes | + explicit rule: full detail logged server-side, never leaked to the client |
| 9 | Secrets | Not addressed | JWT secret + DB credentials must come from `.env`, never hardcoded |
| 10 | JWT algorithm | Unspecified | HS256, explicitly justified over RS256 |
| 11 | Health/monitoring | Not addressed | Public `GET /api/v1/health` |
| 12 | Concurrent order edits | Not addressed | Explicitly deferred — a documented decision, not a gap |

### 0.3 v3.0 → v3.1 (this pass — deliberately small)
| # | Area | v3.0 | v3.1 |
|---|---|---|---|
| 1 | Adding an item to an order | No check on the menu item's own status | `addItem` rejects adding a menu item whose status isn't `'active'` |
| 2 | Manually toggling a table to "available" | No check for an active order first (harmless — the DB constraint still blocked real double-booking — but could mislead the table-map UI) | `updateStatus` rejects setting `'available'` while an active order exists on that table |

This round is intentionally narrow. The previous two passes found a missing module, several crash-causing bugs, and a real timezone bug; this one found two edge-case guards. That trend is the signal that a paper review of an unchanged spec has largely run its course — the next round of real findings will come from reviewing actual generated code, not this document again.

Everything below reflects v3.1 in full — you don't need to cross-reference earlier versions to use this document.

---

## 1. How to use this document

1. **Architecture layer** — folder structure, database schema, auth model, full REST API contract, testing plan.
2. **Prompt layer** — at the end of every module, a ready-to-paste prompt for an AI coding assistant to generate that module's controller, service, model, and routes consistently with the rest of the system.

**Build order:** Auth → Staff/Users → Menu Categories → Menu Items → Tables → Orders → Billing → Reports → Dashboard.

> Bootstrapping note: the very first admin account is created by a database seeder (`php spark db:seed`), not through the API — there's no signup endpoint by design. The Users module is what lets that first admin create everyone else afterward.

---

## 2. High-Level Architecture

```
Client (Bootstrap 5 / Postman / Mobile)
        │  HTTPS + JSON
        ▼
Routes (app/Config/Routes.php)
        │
        ▼
Filters (JwtAuth, Role, Throttle)      ← app/Filters/
        │
        ▼
Controllers (thin, HTTP concerns only)  ← app/Controllers/Api/
        │
        ▼
Services (business logic, transactions, table-state, audit logging) ← app/Services/
        │
        ▼
Models (query builder, validation, soft delete) ← app/Models/
        │
        ▼
PostgreSQL (via CI4 Query Builder / raw SQL for reports)
```

Decisions carried over from earlier versions, still correct:
- **API-first**, JSON over `/api/v1/...`, keeping the door open for the SRS's future mobile client.
- **Service layer** owns business rules; controllers only translate HTTP ↔ Service calls.
- **DB transactions** wrap every multi-table write.
- **`TableStateService`** is the only place that changes `restaurant_tables.status`.
- **Audit logging** happens inside the same transaction as the state change it records.

New in v3.0:
- **`TIMESTAMPTZ` everywhere** plus explicit local-timezone bucketing for anything that means "today" — see Section 4.
- **Order totals are summed in SQL**, not accumulated in a PHP loop — simpler than the `bcmath` approach from v2.0, and removes the concern entirely rather than managing it.
- **`cancelOrder` now blocks on any existing bill**, not just a paid one, closing a gap where a cancelled order could leave a dangling unpaid bill behind.
- **A public health-check endpoint** for deployment/monitoring.
- **Explicitly documented non-fix:** no optimistic locking on concurrent order edits — a deliberate scope call at this system's scale, not an oversight.

---

## 3. Project Folder Structure (CodeIgniter 4)

```
app/
├── Config/
│   ├── Routes.php
│   ├── Filters.php
│   ├── Exceptions.php        (custom handler → JSON error mapping; logs full detail, never leaks it)
│   └── Database.php
├── Controllers/
│   └── Api/
│       ├── HealthController.php      (NEW — GET /api/v1/health)
│       ├── AuthController.php
│       ├── UserController.php
│       ├── DashboardController.php
│       ├── MenuCategoryController.php
│       ├── MenuItemController.php
│       ├── TableController.php
│       ├── OrderController.php
│       ├── BillingController.php
│       └── ReportController.php
├── Filters/
│   ├── JwtAuthFilter.php
│   ├── RoleFilter.php
│   └── ThrottleFilter.php     (strict on /auth/login; optionally a looser global variant)
├── Models/
│   ├── UserModel.php
│   ├── RefreshTokenModel.php
│   ├── RevokedTokenModel.php
│   ├── MenuCategoryModel.php
│   ├── MenuItemModel.php
│   ├── RestaurantTableModel.php
│   ├── OrderModel.php
│   ├── OrderItemModel.php
│   ├── BillModel.php
│   └── AuditLogModel.php
├── Services/
│   ├── AuthService.php
│   ├── UserService.php
│   ├── TableStateService.php
│   ├── OrderService.php
│   ├── BillingService.php
│   ├── ReportService.php
│   └── AuditService.php
├── Entities/
│   ├── User.php               (hides password_hash from array/JSON output)
│   ├── Order.php
│   └── Bill.php
├── Validation/
└── Database/
    ├── Migrations/
    └── Seeds/                 (includes the first-admin seeder)
```

---

## 4. Database Schema

```sql
-- users
CREATE TABLE users (
    id             SERIAL PRIMARY KEY,
    name           VARCHAR(100) NOT NULL,
    username       VARCHAR(50)  UNIQUE NOT NULL,
    email          VARCHAR(150) UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    role           VARCHAR(20)  NOT NULL CHECK (role IN ('admin','cashier')),
    status         VARCHAR(20)  NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive')),
    created_at     TIMESTAMPTZ DEFAULT NOW(),
    updated_at     TIMESTAMPTZ DEFAULT NOW()
);
-- No deleted_at here by design: a user is never hard-deleted (orders.cashier_id would break the
-- same way menu_items/tables did). Deactivation via `status` is the only supported removal path.

-- refresh_tokens
CREATE TABLE refresh_tokens (
    id         SERIAL PRIMARY KEY,
    user_id    INT NOT NULL REFERENCES users(id),
    token_hash VARCHAR(255) NOT NULL,   -- SHA-256 of the raw token; raw value is never stored
    expires_at TIMESTAMPTZ NOT NULL,
    revoked    BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX idx_refresh_tokens_user ON refresh_tokens(user_id);

-- revoked_tokens — blocklist for logout / explicit revocation of access tokens
CREATE TABLE revoked_tokens (
    jti        VARCHAR(36) PRIMARY KEY,  -- the JWT's unique ID claim
    expires_at TIMESTAMPTZ NOT NULL,      -- copy of the token's own exp, so cleanup can purge safely
    revoked_at TIMESTAMPTZ DEFAULT NOW()
);

-- menu_categories
CREATE TABLE menu_categories (
    id          SERIAL PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    description TEXT,
    status      VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive')),
    deleted_at  TIMESTAMPTZ NULL,         -- soft delete
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);

-- menu_items
CREATE TABLE menu_items (
    id          SERIAL PRIMARY KEY,
    category_id INT NOT NULL REFERENCES menu_categories(id) ON DELETE RESTRICT,
    name        VARCHAR(150) NOT NULL,
    description TEXT,
    price       NUMERIC(10,2) NOT NULL CHECK (price >= 0),
    image       VARCHAR(255),
    status      VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive')),
    deleted_at  TIMESTAMPTZ NULL,         -- soft delete
    created_at  TIMESTAMPTZ DEFAULT NOW(),
    updated_at  TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX idx_menu_items_category ON menu_items(category_id);

-- restaurant_tables
CREATE TABLE restaurant_tables (
    id           SERIAL PRIMARY KEY,
    table_number VARCHAR(20) UNIQUE NOT NULL,
    capacity     INT NOT NULL DEFAULT 4,
    status       VARCHAR(20) NOT NULL DEFAULT 'available' CHECK (status IN ('available','occupied')),
    deleted_at   TIMESTAMPTZ NULL,        -- soft delete
    created_at   TIMESTAMPTZ DEFAULT NOW(),
    updated_at   TIMESTAMPTZ DEFAULT NOW()
);

-- orders
CREATE TABLE orders (
    id           SERIAL PRIMARY KEY,
    table_id     INT NOT NULL REFERENCES restaurant_tables(id),
    cashier_id   INT NOT NULL REFERENCES users(id),
    status       VARCHAR(20) NOT NULL DEFAULT 'pending'
                 CHECK (status IN ('pending','completed','paid','cancelled')),
    total_amount NUMERIC(10,2) NOT NULL DEFAULT 0,
    created_at   TIMESTAMPTZ DEFAULT NOW(),
    updated_at   TIMESTAMPTZ DEFAULT NOW()
);
CREATE UNIQUE INDEX one_active_order_per_table
    ON orders (table_id)
    WHERE status IN ('pending','completed');
CREATE INDEX idx_orders_table      ON orders(table_id);
CREATE INDEX idx_orders_cashier    ON orders(cashier_id);
CREATE INDEX idx_orders_created_at ON orders(created_at);

-- order_items
CREATE TABLE order_items (
    id           SERIAL PRIMARY KEY,
    order_id     INT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    menu_item_id INT NOT NULL REFERENCES menu_items(id),
    quantity     INT NOT NULL CHECK (quantity > 0),
    unit_price   NUMERIC(10,2) NOT NULL,
    subtotal     NUMERIC(10,2) NOT NULL
);
CREATE INDEX idx_order_items_order     ON order_items(order_id);
CREATE INDEX idx_order_items_menu_item ON order_items(menu_item_id);

-- bills
CREATE TABLE bills (
    id             SERIAL PRIMARY KEY,
    order_id       INT UNIQUE NOT NULL REFERENCES orders(id),
    total_amount   NUMERIC(10,2) NOT NULL,
    payment_status VARCHAR(20) NOT NULL DEFAULT 'unpaid' CHECK (payment_status IN ('unpaid','paid')),
    payment_method VARCHAR(20) CHECK (payment_method IN ('cash','card','mobile')),
    paid_at        TIMESTAMPTZ,
    created_at     TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX idx_bills_paid_at ON bills(paid_at);

-- audit_logs
CREATE TABLE audit_logs (
    id          SERIAL PRIMARY KEY,
    user_id     INT REFERENCES users(id),
    action      VARCHAR(100) NOT NULL,   -- e.g. 'order.cancelled', 'bill.paid', 'menu_item.price_changed'
    entity_type VARCHAR(50)  NOT NULL,   -- e.g. 'order', 'bill', 'menu_item'
    entity_id   INT NOT NULL,
    meta        JSONB,                   -- e.g. {"old_price": 120.00, "new_price": 135.00}
    created_at  TIMESTAMPTZ DEFAULT NOW()
);
CREATE INDEX idx_audit_logs_entity ON audit_logs(entity_type, entity_id);
```

**Soft delete:** `menu_categories`, `menu_items`, and `restaurant_tables` each use CI4's native soft delete:

```php
protected $useSoftDeletes = true;
protected $deletedField   = 'deleted_at';
```

`delete($id)` sets `deleted_at` instead of removing the row; `find()`/`findAll()` exclude soft-deleted rows automatically. The FK `ON DELETE RESTRICT` stays purely as a safety net for a future hard-purge script — the API itself never triggers it. When rendering a historical order or report referencing a since-deleted item, use `withDeleted()` so old receipts still show the item's name correctly.

**Timezone correctness (v3.0 fix):** v2.0 used plain `TIMESTAMP` columns throughout, which store a literal clock value with no timezone awareness — `created_at::date = CURRENT_DATE` then depends entirely on whatever timezone the database session happens to be running in, and can silently put an order placed at 11:45pm local time into the wrong day's report if the server runs in UTC. Fixed by switching every timestamp column to `TIMESTAMPTZ` (Postgres stores the true instant and converts on display/comparison) and having `ReportService`/`DashboardService` bucket by day using an explicit conversion:

```sql
WHERE (created_at AT TIME ZONE :restaurant_tz)::date = CURRENT_DATE
```

— not a bare `::date` cast. The restaurant's timezone should be one configured constant (`APP_TIMEZONE` in `.env`), never hardcoded inside a query.

**Money precision — simplified further:** `NUMERIC(10,2)` end-to-end is right. Rather than introducing `bcmath` into the PHP Service layer (the v2.0 recommendation), remove the risk entirely: let Postgres compute the order total directly, inside the same transaction as the item change —

```sql
UPDATE orders SET total_amount = COALESCE(
    (SELECT SUM(subtotal) FROM order_items WHERE order_id = :id), 0
) WHERE id = :id;
```

`NUMERIC` arithmetic in Postgres is exact by definition, so this sidesteps the need for a PHP decimal library altogether. The one multiplication that still happens in PHP — `quantity × unit_price` for a single new line item — is a single operation, not a compounding loop, and is low-risk enough to leave as plain arithmetic.

---

## 5. Authentication, Sessions & Role-Based Access Control

**Token strategy — recommended vs. simpler fallback.**
- **Recommended:** short-lived access token (30–60 min) + rotating refresh token (7 days) + a `revoked_tokens` blocklist for immediate logout.
- **Simpler, acceptable fallback:** one longer-lived access token (e.g. 8h) plus the blocklist only, no refresh token. Reasonable if this stays a single-location internal tool and the timeline is tight; reconsider once a mobile client is actually being built.

Non-negotiable either way:
- **Login** (`POST /auth/login`): verify `password_hash` via `password_verify()`, issue a JWT containing `user_id`, `role`, and a unique `jti` claim.
- **JWT algorithm: HS256**, signed with a shared secret from `.env` — not RS256. RS256's asymmetric key pair earns its complexity when multiple independently-deployed services need to verify tokens without sharing a signing secret; a single CI4 monolith issuing and verifying its own tokens has no such need, so HS256 is the right-sized choice, not a shortcut.
- **Secrets discipline:** the JWT signing secret and DB credentials come from `.env` only, never hardcoded in source, and `.env` is gitignored.
- **Throttle** `/auth/login` specifically (CI4's Throttler service inside a custom filter, keyed by IP + username — e.g. cap at 5 attempts/minute). Optionally extend a looser version of the same filter across all `/api/*` routes (e.g. 60–120 req/min per authenticated user) as defense in depth against a buggy client or runaway script — separate and much less strict than the login-specific limit.
- **Logout** inserts the current token's `jti` into `revoked_tokens` with its original expiry.
- **JwtAuthFilter** validates signature + expiry, then checks `jti` against `revoked_tokens`.
- **RoleFilter** applied per route group (`admin` vs `cashier|admin`).
- **Never serialize `password_hash`** — enforced in the `User` Entity, not just by controller convention.
- **CORS**: only on routes a separate-origin client will actually call (a future mobile app). The same-origin Bootstrap frontend doesn't need it.
- **CSRF**: orthogonal to the JWT API. If any part of the Bootstrap UI is server-rendered by CI4 and submits a traditional HTML form (not fetch + JWT), that specific route needs CI4's CSRF filter. Bearer tokens in an `Authorization` header aren't automatically attached by the browser the way cookies are, so CSRF doesn't apply to the JSON API itself — but don't assume it's covered everywhere just because the API is covered.
- **HTTPS in production** — infrastructure, not application code, but explicit: a JWT over plain HTTP is trivially interceptable.

---

## 6. Full REST API Contract

Base path: `/api/v1`. All responses: `{ "status": "success|error", "data": ..., "message": "..." }`.

**Pagination convention:** every list endpoint accepts `?page=` and `?per_page=`, defaulting to 20 per page. `per_page` is clamped server-side to a maximum of 100 regardless of what's requested — don't error on an out-of-range value, just clamp it.

### 6.0 System Health — NEW
| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/health` | Public | Checks DB connectivity; `200 {status:"ok"}` or `503` if unreachable |

### 6.1 Auth (FR-001 to FR-005)
| Method | Endpoint | Role | Description |
|---|---|---|---|
| POST | `/auth/login` | Public | Validate credentials, return access + refresh token |
| POST | `/auth/refresh` | Public (valid refresh token) | Rotate refresh token, issue new access token |
| POST | `/auth/logout` | Any | Blocklist current access token's `jti`; revoke its refresh token |
| GET | `/auth/me` | Any | Return current authenticated user (never `password_hash`) |

### 6.2 Staff / User Management (fills SRS §2.3, no FR numbers assigned in SRS)
| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/users` | Admin | List staff accounts |
| GET | `/users/{id}` | Admin | Get one account |
| POST | `/users` | Admin | Create a cashier/admin account |
| PUT | `/users/{id}` | Admin | Update name/email/role |
| PATCH | `/users/{id}/status` | Admin | Activate/deactivate — blocked if this is the last active admin |
| POST | `/users/{id}/reset-password` | Admin | Admin sets a new password directly |

No hard-delete endpoint for users, for the same referential-integrity reason as menu items and tables.

### 6.3 Dashboard (FR-006 to FR-009)
| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/dashboard/summary` | Admin, Cashier | Total menu items, tables, today's orders, today's sales (timezone-correct "today") |

### 6.4 Menu Categories (FR-010 to FR-013)
| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/menu-categories` | Any | List categories (paginated, searchable) |
| GET | `/menu-categories/{id}` | Any | Get one category |
| POST | `/menu-categories` | Admin | Create category |
| PUT | `/menu-categories/{id}` | Admin | Update category |
| DELETE | `/menu-categories/{id}` | Admin | Soft delete |

### 6.5 Menu Items (FR-014 to FR-019)
| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/menu-items` | Any | List items (filter by `category_id`, search, paginate) |
| GET | `/menu-items/{id}` | Any | Get one item |
| POST | `/menu-items` | Admin | Create item (name, category_id, price, image) |
| PUT | `/menu-items/{id}` | Admin | Update item |
| DELETE | `/menu-items/{id}` | Admin | Soft delete |
| GET | `/menu-categories/{id}/items` | Any | Items within a category |

### 6.6 Restaurant Tables (FR-020 to FR-023)
| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/tables` | Any | List all tables with status |
| GET | `/tables/{id}` | Any | Get one table |
| POST | `/tables` | Admin | Add table |
| PUT | `/tables/{id}` | Admin | Edit table |
| DELETE | `/tables/{id}` | Admin | Soft delete (blocked if the table currently has an active order) |
| PATCH | `/tables/{id}/status` | Admin, Cashier | Toggle Available/Occupied via `TableStateService` |

### 6.7 Order Management (FR-024 to FR-030)
| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/orders` | Admin, Cashier | List orders (filter by status/table/date) |
| GET | `/orders/{id}` | Admin, Cashier | Order detail with line items (single join, not N+1) |
| POST | `/orders` | Cashier | Create order for a table |
| POST | `/orders/{id}/items` | Cashier | Add menu item + quantity |
| PUT | `/orders/{id}/items/{itemId}` | Cashier | Update item quantity |
| DELETE | `/orders/{id}/items/{itemId}` | Cashier | Remove item |
| POST | `/orders/{id}/complete` | Cashier | Lock order, ready for billing |
| POST | `/orders/{id}/cancel` | Cashier, Admin | Cancel — rejected if **any** bill exists for this order, paid or not; optional `{ "reason": "..." }` body |

### 6.8 Billing (FR-031 to FR-034)
| Method | Endpoint | Role | Description |
|---|---|---|---|
| POST | `/orders/{id}/bill` | Cashier | Generate bill from a completed order |
| GET | `/bills/{id}` | Cashier, Admin | Bill detail |
| POST | `/bills/{id}/pay` | Cashier | Record payment — rejected with 409 if already paid |
| GET | `/bills/{id}/receipt` | Cashier | Printable receipt payload |

### 6.9 Sales Reports (FR-035 to FR-038)
| Method | Endpoint | Role | Description |
|---|---|---|---|
| GET | `/reports/daily?date=YYYY-MM-DD` | Admin | Orders, revenue, top items for a day (timezone-correct) |
| GET | `/reports/monthly?year=YYYY&month=MM` | Admin | Aggregated monthly revenue/orders |
| GET | `/reports/daily/print?date=` | Admin | Print-friendly daily report |
| GET | `/reports/monthly/print?year=&month=` | Admin | Print-friendly monthly report |

---

## 7. Response, Error & Exception Conventions

```json
// success
{ "status": "success", "data": { }, "message": "Order created" }

// error
{ "status": "error", "message": "Validation failed", "errors": { "price": "Price must be a positive number" } }
```

Handle this centrally via `app/Config/Exceptions.php` (or a shared `BaseApiController`):

| Condition | Signal | HTTP Status |
|---|---|---|
| Unique violation (duplicate username, duplicate active order per table) | Postgres `23505` | 409 Conflict |
| Foreign key violation (safety net only — soft delete prevents this in practice) | Postgres `23503` | 409 Conflict |
| Check constraint violation (bad enum value) | Postgres `23514` | 422 Unprocessable Entity |
| CI4 Model validation failure | — | 422 Unprocessable Entity |
| JWT missing / invalid / expired / revoked | — | 401 Unauthorized |
| Role mismatch | — | 403 Forbidden |
| Record not found | — | 404 Not Found |
| Business-rule violation (billing an incomplete order, cancelling a billed order, paying twice) | custom exception class | 409 Conflict |

**Never leak internals:** the handler logs the full exception (message, stack trace, request context) server-side via CI4's logger, but the JSON response returned to the client contains only the generic categorized message from the table above — never raw DB error text or a PHP stack trace. A leaked stack trace can hand an attacker file paths, package versions, or query structure for free.

---

## 8. Feature-by-Feature Implementation Prompts

Paste each prompt in build order. Every prompt after Module 1 assumes the global exception handler, `TableStateService`, and `AuditService` already exist.

### Prompt — Module 1: Authentication (+ health check)
```
You are an expert CodeIgniter 4 + PostgreSQL backend developer working in an MVC + thin-controller/service-layer
architecture. Build the Authentication module for a Restaurant Management System API, plus basic app bootstrapping.

Context:
- users table: id, name, username, email, password_hash, role (admin|cashier), status, created_at, updated_at
- refresh_tokens table: id, user_id, token_hash (SHA-256 of raw token), expires_at, revoked, created_at
- revoked_tokens table: jti (PK), expires_at, revoked_at
- Access tokens are JWT (firebase/php-jwt), signed HS256 with a secret sourced from .env (never hardcoded),
  30-60 min expiry, payload contains user_id, role, and a unique jti (uuid)
- Refresh tokens are opaque random strings, 7-day expiry, stored only as a hash, rotated on every use
- Response format: { "status": "success|error", "data": ..., "message": "..." }

Deliver:
1. app/Models/UserModel.php, RefreshTokenModel.php, RevokedTokenModel.php with validation rules
2. app/Entities/User.php: hide password_hash from array()/toArray()/JSON output entirely
3. app/Services/AuthService.php:
   - login(): verify password_verify(), issue access token (with jti) + refresh token, store refresh token's hash
   - refresh(): validate presented refresh token's hash against a non-revoked, non-expired row; on success, revoke
     the old row and issue a new access + refresh token pair (rotation); if a revoked token is presented again,
     revoke ALL refresh tokens for that user_id (reuse detection — treat it as a possible theft signal)
   - logout(): insert current token's jti into revoked_tokens with its original exp; revoke its refresh token
   - me(): decode token, return the User entity
4. app/Controllers/Api/AuthController.php: login(), refresh(), logout(), me()
5. app/Filters/JwtAuthFilter.php: validate signature + expiry, then check jti against revoked_tokens
6. app/Filters/ThrottleFilter.php: strict on /auth/login (~5 attempts/minute per IP+username); optionally a
   looser variant (60-120 req/min) applicable globally across /api/* as defense in depth
7. app/Config/Exceptions.php (or a BaseApiController): implement the error-mapping table from the architecture
   doc's Section 7 — log full exception detail server-side, return only the generic categorized message to the
   client, never raw DB error text or a stack trace
8. app/Controllers/Api/HealthController.php + route: GET /api/v1/health, public, pings the DB connection,
   returns 200 {status:"ok"} or 503 on failure
9. Routes for all auth endpoints, the health endpoint, and the exception handler wiring

Do not put business logic in the controller. Never let password_hash appear in any response, including errors.
```

### Prompt — Module 2: Staff / User Management
```
You are an expert CodeIgniter 4 + PostgreSQL backend developer, continuing the Restaurant Management System API.
The SRS lists "Manage users" as an Administrator responsibility (§2.3) but never defines FRs or endpoints for it —
this module fills that gap. There is no self-service signup by design.

Table: users (id, name, username, email, password_hash, role: admin|cashier, status: active|inactive,
created_at, updated_at) — reuse the User entity/model from the Auth module.

Deliver:
1. app/Services/UserService.php:
   - createUser(data): hash password with password_hash(PASSWORD_BCRYPT) before insert
   - updateUser(id, data): updates name/email/role only, never password
   - setStatus(id, status): reject with 409 if this would deactivate the last remaining active admin account
   - resetPassword(id, newPassword): admin-set reset, re-hash and update
2. app/Controllers/Api/UserController.php: index, show, create, update, setStatus, resetPassword — admin-only
3. Routes under /api/v1/users, RoleFilter('admin') on every route in this group
4. No DELETE endpoint for users — orders.cashier_id references users(id) with no ON DELETE clause, so a hard
   delete would break historical orders

Never let password_hash leave the API in any response.
```

### Prompt — Module 3: Menu Categories
```
You are an expert CodeIgniter 4 + PostgreSQL backend developer. Build the Menu Category module, following the
existing Auth/Users modules' structure (thin controllers, Service layer, JWT filter already global, RoleFilter
restricting writes to 'admin').

Table: menu_categories (id, name, description, status, deleted_at, created_at, updated_at)

Deliver:
1. app/Models/MenuCategoryModel.php: $useSoftDeletes = true, $deletedField = 'deleted_at'; validation (name
   required, unique, max 100 chars)
2. app/Controllers/Api/MenuCategoryController.php: index (paginated per the architecture doc's convention —
   default 20/page, clamp per_page to 100 — plus search by name), show, create, update, delete
3. delete() relies on the Model's soft delete — no manual "check for referencing items" logic needed
4. Routes under /api/v1/menu-categories, RoleFilter('admin') on writes, open read access otherwise
```

### Prompt — Module 4: Menu Items
```
You are an expert CodeIgniter 4 + PostgreSQL backend developer. Build the Menu Item module, matching the Menu
Category module's pattern.

Table: menu_items (id, category_id FK -> menu_categories, name, description, price NUMERIC(10,2), image, status,
deleted_at, created_at, updated_at)

Deliver:
1. app/Models/MenuItemModel.php: $useSoftDeletes = true, $deletedField = 'deleted_at'; validation (name required,
   category_id must exist, price numeric >= 0)
2. app/Controllers/Api/MenuItemController.php: index (paginated per the architecture doc's convention, filter by
   category_id, search), show, create, update, delete (soft delete)
3. GET /api/v1/menu-categories/{id}/items scoped to one category
4. Image upload: validate MIME type (jpeg/png/webp only) and size (cap ~2MB), store under
   public/uploads/menu-items (CI4's public webroot) — NOT writable/uploads
5. When displaying a menu item on a historical order/receipt/report, use withDeleted() so a since-deleted item
   still renders correctly

Validate category_id exists before insert/update; return 422 if it doesn't.
```

### Prompt — Module 5: Restaurant Tables
```
You are an expert CodeIgniter 4 + PostgreSQL backend developer. Build the Restaurant Table module.

Table: restaurant_tables (id, table_number UNIQUE, capacity, status: available|occupied, deleted_at,
created_at, updated_at)

Deliver:
1. app/Models/RestaurantTableModel.php: $useSoftDeletes = true, $deletedField = 'deleted_at'; validation
   (table_number required + unique, capacity integer > 0)
2. app/Controllers/Api/TableController.php: index, show, create, update, delete, and updateStatus() for
   PATCH /api/v1/tables/{id}/status — updateStatus() must call TableStateService, not set the column directly
3. updateStatus() must reject with 409 an attempt to manually set status = 'available' while an active order
   (pending/completed) still exists for that table. The orders-table unique index already prevents an actual
   double-booking regardless, but letting the status flag drift from reality would still show a table as free
   on the table map while it's mid-service
4. delete() checks for an ACTIVE order on the table and rejects with 409 if found — a business rule (don't
   remove a table mid-service from the map), not a workaround for a DB crash, since soft delete already
   prevents any FK violation regardless of history
5. Routes: admin-only for create/update/delete, admin+cashier for updateStatus, open read for index/show

Build app/Services/TableStateService.php here if it doesn't exist yet: markOccupied(tableId) and
markAvailable(tableId), each a single point of truth other modules (Orders, Billing) will call into.
```

### Prompt — Module 6: Order Management
```
You are an expert CodeIgniter 4 + PostgreSQL backend developer. Build the Order Management module.

Tables:
- orders (id, table_id FK, cashier_id FK -> users, status: pending|completed|paid|cancelled, total_amount, timestamps)
- order_items (id, order_id FK CASCADE, menu_item_id FK, quantity, unit_price, subtotal)
- bills (id, order_id UNIQUE FK, ...) — referenced here for the cancel-order rule below
- A partial unique index enforces one active order (status pending/completed) per table_id at the DB level.

Deliver:
1. app/Models/OrderModel.php and OrderItemModel.php with validation rules
2. app/Services/OrderService.php, each method wrapped in a DB transaction:
   - createOrder(tableId, cashierId): create the order, call TableStateService::markOccupied(tableId)
   - addItem(orderId, menuItemId, qty): reject with 422 if the menu item's status isn't 'active' — a discontinued
     item shouldn't be orderable even though it still exists (and stays visible via withDeleted()) for historical
     display; otherwise snapshot current menu_items.price into unit_price, compute subtotal (a single quantity ×
     unit_price multiplication in PHP is fine), then recompute orders.total_amount with a direct SQL aggregate —
     `UPDATE orders SET total_amount = COALESCE((SELECT SUM(subtotal) FROM order_items WHERE order_id = ?), 0)
     WHERE id = ?` — rather than summing in a PHP loop; never trust a client-sent total
   - updateItemQuantity(orderId, orderItemId, qty) / removeItem(...): same SQL-side recalculation
   - completeOrder(orderId): reject with 409 if the order has zero items
   - cancelOrder(orderId, reason = null): reject with 409 if status = 'paid' OR if any row exists in bills for
     this order_id at all (an unpaid-but-generated bill still blocks cancellation — otherwise you're left with a
     bill pointing at a cancelled order); call TableStateService::markAvailable(tableId); write an audit_logs
     row ('order.cancelled', cashier's user_id, reason if given) in the same transaction
3. app/Controllers/Api/OrderController.php: index (filter by status/table/date, paginated per convention), show
   (fetch order + order_items + menu_item names via a single join or whereIn() batch query — do not loop and
   query menu_items per row), create, addItem, updateItem, removeItem, complete, cancel
4. Routes matching Section 6.7, RoleFilter('cashier|admin'); cancel is POST .../cancel, not DELETE

Every write here is transactional and total_amount is always server-derived — this is the module where
correctness matters most.
```

### Prompt — Module 7: Billing
```
You are an expert CodeIgniter 4 + PostgreSQL backend developer. Build the Billing module on top of the existing
Order module.

Table: bills (id, order_id UNIQUE FK -> orders, total_amount, payment_status: unpaid|paid, payment_method:
cash|card|mobile, paid_at, created_at)

Deliver:
1. app/Models/BillModel.php with validation (payment_method must be one of the enum values)
2. app/Services/BillingService.php, each wrapped in a transaction:
   - generateBill(orderId): reject with 409 if order.status != 'completed' or a bill already exists for it; copy
     orders.total_amount into bills.total_amount
   - recordPayment(billId, method): reject with 409 immediately if payment_status is already 'paid' (idempotency
     guard against a double-submitted payment request); otherwise set payment_status = 'paid', payment_method,
     paid_at = now(); flip the linked order.status to 'paid'; call TableStateService::markAvailable(tableId) —
     do not set the table column directly; write an audit_logs row ('bill.paid', amount, method) in the same
     transaction
   - buildReceipt(billId): assemble a printable payload (restaurant info, table, items, quantities, subtotal,
     total, payment method, timestamp) as structured JSON
3. app/Controllers/Api/BillingController.php: generate, show, pay, receipt
4. Enforce immutability: OrderService's addItem/updateItem/removeItem must reject (409) if the order's bill is
   already 'paid'
5. Routes matching Section 6.8, RoleFilter('cashier|admin')
```

### Prompt — Module 8: Sales Reports
```
You are an expert CodeIgniter 4 + PostgreSQL backend developer. Build the Sales Reports module, admin-only,
read-heavy, using query builder aggregate functions rather than looping in PHP.

Deliver:
1. app/Services/ReportService.php:
   - dailyReport(date): total orders, total revenue (sum of paid bills for that date), top 5 items by quantity
     sold, average order value. Bucket "for that date" using `(paid_at AT TIME ZONE :restaurant_tz)::date = :date`
     — NOT a bare `paid_at::date` cast — where :restaurant_tz comes from the APP_TIMEZONE config value, so a
     bill paid near midnight lands in the correct day's report regardless of the DB server's own timezone
   - monthlyReport(year, month): same aggregates grouped by day (same timezone-aware bucketing), plus a monthly total
2. app/Controllers/Api/ReportController.php: daily(), monthly(), dailyPrint(), monthlyPrint()
3. Use PostgreSQL date functions (DATE_TRUNC, range filtering) via the query builder's selectRaw/groupBy/having —
   idx_bills_paid_at and idx_orders_created_at exist specifically so these aggregates are index-backed
4. When listing top items sold, join through menu_items using withDeleted() so a since-deleted item still shows
   its name in historical reports
5. Routes matching Section 6.9, RoleFilter('admin')

Only count revenue from bills.payment_status = 'paid' — pending/unpaid bills must never appear in revenue totals.
```

### Prompt — Module 9: Dashboard
```
You are an expert CodeIgniter 4 + PostgreSQL backend developer. Build the Dashboard summary endpoint.

Deliver:
1. app/Services/DashboardService.php: summary() returns { total_menu_items, total_tables, today_orders, today_sales }
   - total_menu_items: COUNT from menu_items where status = 'active' (soft-deleted rows already excluded)
   - total_tables: COUNT from restaurant_tables
   - today_orders: COUNT from orders where (created_at AT TIME ZONE :restaurant_tz)::date = CURRENT_DATE —
     timezone-aware, not a bare ::date cast (index-backed via idx_orders_created_at)
   - today_sales: SUM from bills where payment_status = 'paid' and (paid_at AT TIME ZONE :restaurant_tz)::date =
     CURRENT_DATE (index-backed via idx_bills_paid_at)
2. app/Controllers/Api/DashboardController.php: summary() action
3. Route: GET /api/v1/dashboard/summary, accessible to admin and cashier

Single query per metric is fine at this scale (SRS specifies 20 concurrent users); note in a comment that Redis
caching with a short TTL would be the next optimization if load grows.
```

---

## 9. Non-Functional Requirements Mapped to Implementation

| SRS Requirement | Implementation |
|---|---|
| Page load < 3s, 20 concurrent users | Indexed FKs and date columns (Section 4); no N+1 queries in Order/Report services |
| Passwords securely hashed | `password_hash()`/`password_verify()`; never plain-text, never serialized back out |
| Role-based authorization | `RoleFilter` per route group, checked against the JWT's `role` claim |
| Session expiry after inactivity | Short-lived access token + refresh rotation, or blocklist-only fallback (Section 5) |
| Data consistency during transactions | `$db->transStart()`/`transComplete()` around every multi-table write |
| Modular, documented source code | Controller → Service → Model separation held strictly across every module |
| Auditability | `audit_logs` written inside the same transaction as the action it records |
| Testability | See Section 10 |
| Correct day-boundary reporting | `TIMESTAMPTZ` + `AT TIME ZONE` bucketing, not a bare `::date` cast (Section 4) |

---

## 10. Testing Strategy

Use CI4's built-in test tooling (`CodeIgniter\Test\CIUnitTestCase`, `FeatureTestTrait`) plus PHPUnit.

**Unit-test the Service layer directly:**
- `OrderService`: rejects completing an order with zero items; rejects a second active order on an
  already-occupied table; rejects cancelling an order that has any bill (paid or unpaid); `total_amount`
  recalculates correctly after add/update/remove.
- `BillingService`: rejects billing an order that isn't `completed`; rejects a second bill for the same order;
  rejects a second payment attempt on an already-paid bill; payment correctly flips both order status and table state.
- `UserService`: rejects deactivating the last remaining active admin.
- `ReportService`/`DashboardService`: an order/bill timestamped near a local midnight boundary lands in the
  correct day's totals — this is the one worth writing first, since it's the subtlest bug to catch by hand.

**Feature-test the API surface** for at least the happy path and top 1–2 business-rule violations per module,
asserting both HTTP status and JSON shape.

**Seed data via CI4 seeders**, not scattered hand-written fixtures.

---

## 11. Suggested Next Steps

1. Run the schema in Section 4 as CI4 migrations (`php spark make:migration`), not raw SQL.
2. Confirm the restaurant's actual timezone and set `APP_TIMEZONE` in `.env` before writing any date-bucketed
   report query — this is the detail most likely to be wrong if skipped.
3. Wire the global exception handler, `TableStateService`, and `AuditService` first — every later module's
   prompt assumes they already exist.
4. Build modules in the order given; Orders depends on Tables, Menu Items, and a seeded admin/cashier account.
5. Stand up the PHPUnit scaffold before writing `OrderService`/`BillingService` — write the timezone-boundary
   test and the double-payment test as you build those methods, not after.
6. Add a Postman collection mirroring Section 6 as you build.
7. Once all 9 modules pass manual testing, revisit the SRS's "Future Enhancements" (its Section 10) for the
   next sprint — none of it belongs in this version's scope.
