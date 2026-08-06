# Restaurant Management System (RMS) Backend — Architecture & System Overview

## 1. Project Overview & Architecture
This project is a RESTful API backend for a **Restaurant Management System (RMS)** built with **CodeIgniter 4 (PHP 8.x)** and **PostgreSQL 15+**.

The project strictly follows a **Clean MVC + Service Layer Architecture**:
- **Controllers** are kept **thin**: focused solely on HTTP handling (request parsing, routing, authorization check, returning standard JSON responses).
- **Services** contain **all business logic**: multi-table database transactions, money calculations, rule enforcements, and audit logging.
- **Models** handle **data persistence**: defining schemas, primary keys, relationships, entity casting, and soft deletion.

---

## 2. Three Main Framework Layers

```
                                  ┌───────────────────────────────┐
                                  │      Client (HTTP Request)    │
                                  └───────────────┬───────────────┘
                                                  │
                                                  ▼
                                  ┌───────────────────────────────┐
                                  │   Controllers (app/Controllers)│
                                  │   - Thin HTTP handler         │
                                  │   - Validates input           │
                                  │   - Returns standardized JSON │
                                  └───────────────┬───────────────┘
                                                  │
                                                  ▼
                                  ┌───────────────────────────────┐
                                  │    Services (app/Services)    │
                                  │   - Core Business Logic       │
                                  │   - DB Transactions           │
                                  │   - Financial Calculations    │
                                  └───────┬───────────────┬───────┘
                                          │               │
                        ┌─────────────────┘               └─────────────────┐
                        ▼                                                   ▼
        ┌───────────────────────────────┐                   ┌───────────────────────────────┐
        │     Models (app/Models)       │                   │    Audit & State Helpers      │
        │     - Database CRUD           │                   │    - AuditService             │
        │     - Schema & Soft Deletes   │                   │    - TableStateService        │
        └───────────────┬───────────────┘                   └───────────────────────────────┘
                        │
                        ▼
        ┌───────────────────────────────┐
        │ PostgreSQL Database (10 tables)│
        └───────────────────────────────┘
```

---

### Layer 1: Controllers (`app/Controllers/Api/`)
Controllers handle incoming HTTP endpoints and translate responses into a uniform JSON structure: `{ "status": "success|error", "data": ..., "message": "..." }`.

| Controller | Description & Key Endpoint Functions |
| :--- | :--- |
| [AuthController](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Controllers/Api/AuthController.php) | **Authentication**: `login()` (validates credentials, issues JWT access token + refresh token), `refresh()` (rotates refresh token), `logout()` (revokes JWT `jti`), `me()` (fetches current user). |
| [UserController](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Controllers/Api/UserController.php) | **Staff Management (Admin only)**: `index()`, `show()`, `create()`, `update()`, `setStatus()` (activate/deactivate staff), `resetPassword()`. |
| [MenuCategoryController](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Controllers/Api/MenuCategoryController.php) | **Menu Categories**: `index()`, `show()`, `create()`, `update()`, `delete()` (soft delete). |
| [MenuItemController](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Controllers/Api/MenuItemController.php) | **Menu Items**: `index()`, `show()`, `byCategory()`, `create()`, `update()`, `delete()` (soft delete). |
| [TableController](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Controllers/Api/TableController.php) | **Dining Tables**: `index()`, `show()`, `create()`, `update()`, `updateStatus()` (`available` / `occupied`), `delete()` (soft delete). |
| [OrderController](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Controllers/Api/OrderController.php) | **Order Lifecycle**: `index()`, `show()`, `create()` (start order), `addItem()`, `updateItem()`, `removeItem()`, `complete()` (lock order for bill), `cancel()`. |
| [BillingController](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Controllers/Api/BillingController.php) | **Billing & Payments**: `generate()` (creates bill from completed order), `show()`, `pay()` (record cash/card/mobile payment & release table), `receipt()` (formatted printable receipt). |
| [DashboardController](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Controllers/Api/DashboardController.php) | **Overview Metrics**: `summary()` (returns daily revenue, active orders count, table occupancy). |
| [ReportController](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Controllers/Api/ReportController.php) | **Analytics**: `daily()`, `monthly()`, `dailyPrint()`, `monthlyPrint()` (sales breakdowns and PDF/print outputs). |
| [HealthController](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Controllers/Api/HealthController.php) | **System Health**: `index()` (verifies API and database connectivity). |

---

### Layer 2: Services (`app/Services/`)
Services contain the core domain rules, multi-table transactions (`$db->transStart() / transComplete()`), and calculations.

| Service | Core Responsibilities & Main Functions |
| :--- | :--- |
| [AuthService](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Services/AuthService.php) | Hashes passwords, generates JWT tokens (HS256), validates credentials, stores hashed refresh tokens, handles token rotation & token blocklisting upon logout. |
| [UserService](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Services/UserService.php) | Handles user creation/edits with role validation (`admin`, `cashier`). Ensures the last remaining active `admin` account cannot be deactivated. |
| [OrderService](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Services/OrderService.php) | - `createOrder()`: Initializes order for a table and invokes `TableStateService` to mark table `occupied`.<br>- `addItem()` / `updateItem()` / `removeItem()`: Snapshots item price into `order_items.unit_price` (independent of future price updates) and recalculates `orders.total_amount` server-side.<br>- `completeOrder()`: Validates that order has $\ge 1$ item and locks status.<br>- `cancelOrder()`: Rejects cancellation if already paid; clears table and writes audit log. |
| [BillingService](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Services/BillingService.php) | - `generateBill()`: Computes total, tax, and grand total for a completed order.<br>- `recordPayment()`: Validates payment method (`cash`, `card`, `mobile`), updates bill status to `paid`, calls `TableStateService` to mark table `available`, and invokes `AuditService` inside a DB transaction.<br>- `buildReceipt()`: Formats itemized receipt data for print/pos. |
| [TableStateService](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Services/TableStateService.php) | Encapsulates table status transitions (`markOccupied()`, `markAvailable()`) to guarantee state consistency across order & billing modules. |
| [AuditService](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Services/AuditService.php) | Writes financial and operational events to the `audit_logs` table (e.g., payment recording, order cancellation). |
| [DashboardService](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Services/DashboardService.php) | Aggregates real-time statistics (total daily sales, active table counts, open orders). |
| [ReportService](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Services/ReportService.php) | Computes daily and monthly revenue, category performance, and top-selling items over arbitrary date ranges. |

---

### Layer 3: Models (`app/Models/`)
Models interface directly with PostgreSQL, handling CRUD, soft deletes (`deleted_at`), and data casting.

| Model | Database Table | Key Attributes & Configuration |
| :--- | :--- | :--- |
| [UserModel](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Models/UserModel.php) | `users` | User credentials, roles (`admin`, `cashier`), `status` (active/inactive). `password_hash` hidden from API serialization. |
| [RefreshTokenModel](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Models/RefreshTokenModel.php) | `refresh_tokens` | Stores hashed refresh tokens, user binding, and expiration timestamp for sliding sessions. |
| [RevokedTokenModel](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Models/RevokedTokenModel.php) | `revoked_tokens` | Token blocklist storing revoked `jti` identifiers on user logout. |
| [MenuCategoryModel](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Models/MenuCategoryModel.php) | `menu_categories` | Category names, display order, soft delete enabled (`$useSoftDeletes`). |
| [MenuItemModel](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Models/MenuItemModel.php) | `menu_items` | Item name, price, availability status, FK to category, soft delete enabled. |
| [RestaurantTableModel](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Models/RestaurantTableModel.php) | `restaurant_tables` | Table number/name, capacity, current status (`available`, `occupied`), soft delete enabled. |
| [OrderModel](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Models/OrderModel.php) | `orders` | Table FK, cashier FK, order status (`pending`, `completed`, `cancelled`), server-recalculated `total_amount`. |
| [OrderItemModel](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Models/OrderItemModel.php) | `order_items` | Order FK, menu item FK, quantity, price snapshot (`unit_price`), line item total. |
| [BillModel](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Models/BillModel.php) | `bills` | Order FK, subtotal, tax amount, total amount, payment status (`unpaid`, `paid`), payment method (`cash`, `card`, `mobile`). |
| [AuditLogModel](file:///c:/Users/Hp/rsm-backend/rms-backend/app/Models/AuditLogModel.php) | `audit_logs` | Immutable audit records capturing user ID, action type, target entity, and change payloads. |

---

## 3. End-to-End System Workflow

The lifecycle of a typical dining interaction follows this sequence:

```
[ 1. Login ] ──► Cashier / Admin authenticates ──► JWT Access Token Issued
      │
      ▼
[ 2. Table & Order ] ──► Cashier opens Order for Table ──► Table marked 'occupied'
      │
      ▼
[ 3. Order Building ] ──► Cashier adds Menu Items ──► Price snapshotted per item;
      │                                                Total recalculated on server
      ▼
[ 4. Order Completion ] ──► Cashier completes Order ──► Order status = 'completed' (Locked)
      │
      ▼
[ 5. Bill Generation ] ──► Bill generated from Order ──► Bill status = 'unpaid'
      │
      ▼
[ 6. Payment & Clearance ] ──► Payment recorded (cash/card/mobile)
      │                         ├─► Bill status = 'paid'
      │                         ├─► Table marked 'available'
      │                         └─► Financial Audit Log written
      ▼
[ 7. Analytics ] ──► Sales reflected in Admin Dashboard & Reports
```

1. **Authentication**:
   - User posts credentials to `/api/v1/auth/login`.
   - `AuthService` verifies password and issues a signed JWT access token and a refresh token.
2. **Table Assignment & Order Initialization**:
   - Cashier initiates an order for a table via `/api/v1/orders`.
   - `OrderService` inserts an `orders` record in `pending` state and calls `TableStateService` to mark `restaurant_tables.status` as `occupied`.
3. **Item Addition & Pricing Snapshot**:
   - Cashier adds menu items to the order.
   - `OrderService` copies `menu_items.price` into `order_items.unit_price` at that moment. This guarantees historical billing accuracy even if menu prices change later.
   - `orders.total_amount` is automatically recalculated from the sum of line items.
4. **Order Completion**:
   - Cashier marks the order complete via `/api/v1/orders/{id}/complete`.
   - System checks that the order contains at least 1 item before changing status to `completed`.
5. **Bill Generation & Payment**:
   - Bill is generated via `/api/v1/orders/{id}/bill`.
   - Cashier records payment via `/api/v1/bills/{id}/pay` with `payment_method` (`cash`, `card`, `mobile`).
   - Inside a database transaction (`$db->transStart()`):
     - Bill status transitions to `paid`.
     - `TableStateService` releases the table back to `available`.
     - `AuditService` writes a financial record into `audit_logs`.
6. **Reporting & Dashboard**:
   - Completed payments immediately feed real-time revenue stats in `DashboardController` and `ReportController`.

---

## 4. Database Schema & Key Guards

### Database Tables Overview
- `users`: Staff credentials, roles (`admin`, `cashier`), active status.
- `refresh_tokens`: Hashes of active refresh tokens.
- `revoked_tokens`: Blacklisted JWT identifiers (`jti`) from logouts.
- `menu_categories`: Food/beverage categories with soft delete support.
- `menu_items`: Individual dishes/drinks, prices, soft delete support.
- `restaurant_tables`: Table numbers, seats, current status (`available`, `occupied`), soft delete support.
- `orders`: Table orders with lifecycle states (`pending`, `completed`, `cancelled`).
- `order_items`: Line items capturing historical unit price snapshot at time of order.
- `bills`: Payment billing details (`unpaid`, `paid`), tax, and payment method.
- `audit_logs`: Immutable security and financial audit trails.

### Key Database Architectural Guards
1. **Single Active Order Constraint**:
   A PostgreSQL **partial unique index** enforces that a table can have at most one active order:
   ```sql
   CREATE UNIQUE INDEX idx_orders_active_table ON orders(table_id) WHERE status IN ('pending', 'completed');
   ```
2. **Soft Deletes for Referential Integrity**:
   Categories, menu items, and tables use soft deletion (`deleted_at`). Hard deletion is prevented so historical orders and bills retain valid foreign keys without crashing database queries.
3. **Server-Side Financial Integrity**:
   - Item unit prices are snapshotted on creation (`order_items.unit_price`).
   - Order total amounts are strictly recalculated on the server; client-submitted totals are ignored.
   - Paid orders are immutable (adding/removing items or cancelling a paid order returns `409 Conflict`).
