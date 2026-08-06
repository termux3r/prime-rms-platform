# Restaurant Management System (RMS)
## Frontend Architecture & Design System

**Version:** 1.0
**Stack:** Bootstrap 5 (per SRS) + custom theme layer · Vanilla JS · CodeIgniter 4 server-rendered views
**Pairs with:** `RMS_Backend_Architecture_and_Prompts.md` (v2.0 endpoints referenced throughout)
**Visual reference:** `01-login.html`, `02-dashboard.html`, `03-order-pos.html`, `04-menu-items.html`

---

## 1. Design philosophy

**The subject:** this isn't a diner-facing menu or a marketing site — it's an internal operations
tool used by a cashier taking orders table-side during service and an admin reviewing performance
between shifts. The single job of the interface: turn a table into a paid, reconciled ticket, fast,
without errors. That's where the visual identity comes from — not generic "restaurant elegance."

**Signature element — the receipt.** Every screen that touches money or counts (the order panel,
the dashboard's sales figure, the billing/receipt view) uses a till-receipt treatment: tabular
monospace numerals, a dashed rule standing in for a receipt's tear-line before the total, and a
large final total. It's used with restraint — plain white cards and standard type everywhere else
— so it reads as a considered signature, not a gimmick repeated on every element.

**Palette — mineral, not cliché.** Rather than generic "warm restaurant" cream-and-terracotta, the
palette is drawn from Dallol itself: the hydrothermal field in the Danakil Depression the company is
named for, known for genuinely strange mineral colors — sulfur gold, iron rust, brine teal — against
stark salt-flat neutrals. It's a real, specific reference rather than a decorative mood board, and it
gives each accent color actual semantic meaning (see Section 2) instead of being purely decorative.

**Why light, not dark.** A cashier's screen gets looked at for hours during service; a dense data
surface (tables, forms, menus) stays more legible on a quiet light neutral than in a dark theme. The
sidebar is the one dark surface — basalt-black — so navigation reads as a fixed, confident frame
around a working surface that stays calm and readable.

**Typography.** Archivo (expanded/black weights) carries headers with an industrial, ticket-stencil
character — deliberately not an elegant display serif, since this is an operations tool, not a fine-
dining menu. Inter carries body and UI text because legibility matters more than personality in a
data-dense admin screen. IBM Plex Mono carries every number in the system — prices, quantities,
order IDs, table numbers, timestamps — which is both the visual signature and functionally useful:
tabular figures make columns of prices actually scan correctly.

---

## 2. Design system reference

### 2.1 Color

| Token | Hex | Use |
|---|---|---|
| `--surface-base` | `#F2EFE7` | App background (salt-flat stone) |
| `--surface-card` | `#FFFFFF` | Cards, panels, table rows |
| `--surface-sidebar` | `#17130E` | Sidebar only (basalt) |
| `--surface-sidebar-active` | `#241D15` | Sidebar hover/active row |
| `--ink-primary` | `#1C1A16` | Primary text, primary buttons |
| `--ink-secondary` | `#756F60` | Secondary/meta text |
| `--ink-faint` | `#A39C8A` | Placeholders, disabled |
| `--ink-inverse` | `#F2EFE7` | Text on dark sidebar |
| `--accent-sulfur` / `-ink` / `-soft` | `#B58A12` / `#7A5C0C` / `#F3E8C9` | **Pending / occupied / primary accent.** Active nav indicator, "pending" badges, occupied table tiles |
| `--accent-rust` / `-ink` / `-soft` | `#A8451D` / `#7C3315` / `#F2DED2` | **Cancelled / destructive.** Cancel actions, cancelled-order badges |
| `--accent-teal` / `-ink` / `-soft` | `#2B685F` / `#1E4A43` / `#DAE9E5` | **Paid / active / success.** Paid badges, active-status badges, category tags |
| `--border-subtle` / `--border-strong` | `#E4DFD1` / `#CFC7B2` | Hairlines, input borders |

Each accent is used **for the same meaning everywhere** — sulfur always means pending/occupied/in-
progress, teal always means paid/active/complete, rust always means cancelled/destructive. Don't
introduce a fourth accent color without a fourth real state to justify it.

### 2.2 Type scale

| Role | Family | Weight | Typical size |
|---|---|---|---|
| Page title | Archivo | 700 | 20–22px |
| Hero / display | Archivo | 700–900 | 28–38px |
| Section title | Archivo | 700 | 15px |
| Body / labels | Inter | 400–600 | 13–15px |
| Numerals (prices, IDs, timestamps, qty) | IBM Plex Mono | 500–600 | 12.5–28px depending on context |
| Eyebrow / meta | IBM Plex Mono | 500 | 11.5–12.5px, uppercase, letter-spaced |

**Rule:** if it's a number that means money, a count, an ID, or a timestamp, it's mono. If it's a
word, it's Inter. Headers are Archivo. Don't mix this up — the consistency is what makes it read as
a system rather than a decoration.

### 2.3 Layout tokens

- Radius: `--radius-sm: 6px` (inputs, buttons, small chips) · `--radius-md: 10px` (menu/floor tiles)
  · `--radius-lg: 18px` (cards, panels)
- Shadow: one card shadow throughout — `0 1px 2px rgba(23,19,14,.05), 0 10px 30px -14px rgba(23,19,14,.18)`
  — don't invent a second shadow depth; flatness vs. this one lift is the only distinction available.
- Sidebar width: 248px expanded / 72px collapsed (icon-only below 720px viewport width)
- Focus state: 2px solid `--accent-sulfur` outline, 2px offset, on every focusable element —
  non-negotiable for a system used quickly under pressure.

### 2.4 Component patterns (see sample files for full CSS)

- **Status badge**: dot + label, soft-tint background, `-ink` colored text. Used for order status,
  bill payment status, table status, menu item active/inactive.
- **Stat card**: label (Inter, small, secondary) over value (mono, large). The "highlight" variant
  (dark card, sulfur-colored value, dashed rule beneath) is reserved for the single most important
  number on a page — today's sales on the dashboard, the order total on the POS screen. Don't apply
  it to more than one card per screen or it stops meaning "this one matters most."
- **Receipt/order-summary block**: line items → dashed divider → total, mono numerals throughout,
  right-aligned prices. This is the signature; reuse it verbatim in the POS cart and the billing
  receipt view rather than inventing a second version.
- **Data table + toolbar**: search input + filter selects above, table below, footer with a result
  count (mono numbers) and pagination. This exact pattern repeats for Menu Categories, Menu Items,
  Tables (list mode), Users, and Orders (list mode) — build it once as a shared partial, not five times.
- **Floor tile**: small card, table number (mono) + status dot + label. Used standalone on the
  dashboard hero and at full size on the Tables page.

---

## 3. Tech stack & rendering model

The SRS specifies a Bootstrap 5 interface; the backend is a JSON REST API. These aren't in tension —
CI4 renders the page shell and initial data server-side via Views (satisfying "Bootstrap 5
interface," "sidebar navigation," "dashboard"), and vanilla JS progressively enhances specific
interactions by calling the same `/api/v1/...` endpoints the backend already exposes, so those
interactions don't need a full page reload:

```
Browser request → CI4 Controller (Views\*) → renders layout + partial data server-side
                                            → page includes theme.css + page-specific JS
                                            ↓
User interacts (add item to order, search a table, toggle a status)
                                            ↓
JS calls fetch() against /api/v1/... (same JWT bearer auth as any API client)
                                            ↓
JS updates the DOM directly from the JSON response — no reload
```

- **Bootstrap 5** (CDN or vendored) supplies grid/utility classes (`.container`, `.row`, `.col-*`,
  `.d-flex`) for layout scaffolding. Its default component theming (buttons, cards, badges) is
  overridden almost entirely by the custom theme in Section 2 — Bootstrap provides structure, not
  the look.
- **`public/assets/css/theme.css`** — extract this once from the `<style>` block shared across all
  four sample HTML files. It's the single source of truth for every color/type/spacing token; CI4
  views reference it, they don't redefine it.
- **Vanilla JS**, not a framework — matches "simple front end tech" and the SRS's plain
  Bootstrap-interface scope. A thin `api.js` wrapper attaches the JWT bearer header and handles
  401-triggered refresh (see the backend auth doc, Section 5) so every other script just calls
  `api.get(...)` / `api.post(...)` without repeating auth boilerplate.
- **No client-side routing** — each module is a real CI4 route/View. This matches a server-rendered,
  Bootstrap-based app and keeps the mental model simple for whoever maintains this after the internship.

---

## 4. Information architecture

Every frontend page maps to a specific backend module. Endpoint references use the v2.0 contract
from `RMS_Backend_Architecture_and_Prompts.md` Section 6.

| Page | Route | Backend module | Key endpoints called |
|---|---|---|---|
| Sign in | `/login` | Module 1 (Auth) | `POST /auth/login` |
| Dashboard | `/dashboard` | Module 9 (Dashboard) | `GET /dashboard/summary` |
| Orders — table/POS view | `/orders/{tableId}` | Module 6 (Orders) | `POST /orders`, `POST /orders/{id}/items`, `PUT .../items/{itemId}`, `DELETE .../items/{itemId}`, `POST .../complete`, `POST .../cancel` |
| Orders — list view | `/orders` | Module 6 (Orders) | `GET /orders` |
| Billing / receipt | `/orders/{id}/bill` | Module 7 (Billing) | `POST /orders/{id}/bill`, `POST /bills/{id}/pay`, `GET /bills/{id}/receipt` |
| Tables (floor management) | `/tables` | Module 5 (Tables) | `GET /tables`, `POST/PUT/DELETE /tables`, `PATCH /tables/{id}/status` |
| Menu categories | `/menu-categories` | Module 3 | `GET/POST/PUT/DELETE /menu-categories` |
| Menu items | `/menu-items` | Module 4 | `GET/POST/PUT/DELETE /menu-items` |
| Reports | `/reports` | Module 8 | `GET /reports/daily`, `GET /reports/monthly` (+ print variants) |
| Staff (Users) | `/users` | Module 2 (admin only) | `GET/POST/PUT /users`, `PATCH .../status`, `POST .../reset-password` |

Sidebar nav is 6 top-level items — **Dashboard, Orders, Tables, Menu, Reports, Staff** — with Billing
reached contextually from an order rather than as its own nav item (a bill only exists in relation to
an order), and Menu Categories reached as a tab within the Menu Items screen rather than a separate
top-level page.

---

## 5. Shared layout & partials

```
app/Views/layouts/app.php     -- sidebar + topbar + content slot, wraps every authenticated page
app/Views/layouts/auth.php    -- minimal shell, just the login split-screen
app/Views/partials/sidebar.php
app/Views/partials/topbar.php
app/Views/partials/modal_confirm.php   -- SRS requires "confirmation dialogs"; one shared modal,
                                          parameterized by message + confirm label, used for every
                                          destructive action (delete category, cancel order, etc.)
app/Views/partials/toast.php           -- lightweight success/error notification, triggered from JS
                                          after any fetch() call, not a full alert()
```

**Confirmation dialog copy convention** (per the writing guidance this system follows): state what
will happen and that it can't be undone where true — "Delete this menu item? This can't be undone,"
not a generic "Are you sure?" Buttons are named for the action, not "OK/Cancel" — "Delete item" /
"Keep item."

---

## 6. Page-by-page specs & implementation prompts

Each prompt assumes `theme.css` and the shared partials (Section 5) already exist. Build order
matches the backend's: Auth → Staff → Menu Categories/Items → Tables → Orders → Billing → Reports →
Dashboard (Dashboard last since it aggregates data from everything else).

### Prompt — Login
```
Build the CI4 View for the login page, matching 01-login.html exactly (layout, copy, spacing) --
that file IS the spec, not a rough guide. Extract its <style> block into public/assets/css/theme.css
if that file doesn't exist yet in your working copy.

Deliver:
1. app/Views/layouts/auth.php -- minimal layout, no sidebar
2. app/Views/auth/login.php -- the split-screen login form from the sample
3. public/assets/js/api.js -- fetch() wrapper: api.post('/auth/login', {username, password}) stores
   the returned access token in memory (a JS module-scope variable, NOT localStorage -- CI4 sessions
   or a short-lived in-memory token, since this is a shared/POS-style device in many restaurants) and
   the refresh token wherever the architecture doc's Section 5 recommends for this deployment
4. Wire the form's submit to call the login endpoint, redirect to /dashboard on success, show an
   inline error (not a browser alert) on 401

Match the sample's exact copy, including the "no self-signup" footnote -- that's a true statement
about this system, not filler text.
```

### Prompt — Dashboard
```
Build the CI4 View for the dashboard, matching 02-dashboard.html -- the floor-status grid is the
page's hero, ABOVE the stat cards, not below. That ordering is deliberate: a manager glancing at
this screen cares about the floor before the numbers.

Deliver:
1. app/Views/dashboard/index.php using layouts/app.php
2. Floor grid populated from GET /tables (table_number, status)
3. Stat row populated from GET /dashboard/summary -- the "Today's sales" card is the ONLY one using
   the .stat-card.highlight treatment; don't apply it to more than one card
4. Recent-orders mini table populated from GET /orders (reasonable default: last 5, most recent first)
5. All prices/counts in the mono font per Section 2.2 -- this is not optional styling, it's the
   system's numeral convention
```

### Prompt — Orders (table/POS view) -- the most complex page
```
Build the CI4 View for the order-taking screen, matching 03-order-pos.html. This is the highest-
frequency screen in the system -- cashiers use this dozens of times per shift -- so responsiveness
of the UI (not just the network) matters: update the cart optimistically in JS, then reconcile with
the server response rather than waiting on every click.

Deliver:
1. app/Views/orders/pos.php using layouts/app.php, two-pane layout (menu grid + order panel) exactly
   as in the sample
2. Category tabs filter the menu grid client-side (the full active menu for the category is small
   enough to load once via GET /menu-items and filter in JS, rather than a request per tab)
3. Clicking a menu item's + button calls POST /orders/{id}/items (creating the order first via
   POST /orders if this is the table's first item) and appends a line to the order panel
4. Quantity steppers call PUT/DELETE on the order item endpoints; the order panel's subtotal/total
   re-render from the server's response total_amount, not a client-side recalculation -- the backend
   is the source of truth for money, the UI just displays it fast
5. "Send to kitchen" calls POST /orders/{id}/complete; "Cancel order" opens the shared confirmation
   modal (Section 5) before calling POST /orders/{id}/cancel
6. Reuse the .order-summary component verbatim -- this exact markup/CSS is also the billing receipt
   view (next prompt), don't fork it into two versions
```

### Prompt — Billing / Receipt
```
Build the CI4 View for generating a bill and showing a receipt, reusing the .order-summary component
from the POS screen (03-order-pos.html) rather than designing a new layout -- a receipt IS an order
summary, just in a read-only, printable context.

Deliver:
1. app/Views/billing/receipt.php -- the same dashed-rule/mono-total treatment, plus payment method
   selection (cash/card/mobile) and a "Mark as paid" button calling POST /bills/{id}/pay
2. A print stylesheet (@media print) that hides the sidebar/topbar/buttons and shows only the
   receipt block -- the SRS explicitly requires "printable receipts"
3. After payment succeeds, show a toast (Section 5) and redirect back to the floor/dashboard, since
   the table is now free
```

### Prompt — Tables (floor management)
```
Build the CI4 View for table management, matching the floor-tile pattern from 02-dashboard.html but
at full size and with admin CRUD controls, not just a glance.

Deliver:
1. app/Views/tables/index.php -- a full floor-tile grid (not a data table -- tables are spatial,
   showing them as a list of rows loses the "floor" mental model that makes this useful at a glance)
2. Each tile: table number (mono), capacity, status badge, and (admin only) edit/delete icon buttons
   matching 04-menu-items.html's row-actions pattern
3. Status toggle (available/occupied) is a click on the tile's status badge for cashiers -- calls
   PATCH /tables/{id}/status
4. "Add table" opens a small modal form (number, capacity) -- reuse modal_confirm.php's shell but
   with form fields instead of a yes/no prompt
```

### Prompt — Menu Categories & Menu Items (the canonical CRUD-list pattern)
```
Build the CI4 Views for menu category and menu item management, matching 04-menu-items.html exactly.
This is the pattern every other data-table screen in this system reuses -- build the toolbar
(search + filters), table, and pagination footer as a shared partial/JS behavior
(public/assets/js/datatable.js) rather than one-off code, since Tables (list mode), Users, and
Orders (list mode) all need the identical interaction.

Deliver:
1. app/Views/menu_items/index.php -- category tabs at the top (categories ARE menu items' primary
   filter, per Section 4's IA decision to fold categories into this screen rather than a separate
   nav item), then the toolbar/table/pagination exactly as in the sample
2. datatable.js: generic search-as-you-type (debounced, calls GET with a ?search= param), category/
   status filter selects, and pagination controls -- parameterized so it can be reused for Tables/
   Users/Orders list views by passing a different endpoint and column config
3. "Add item" opens a form modal: name, category, price, description, image upload (accept
   image/jpeg, image/png, image/webp client-side before the request even goes out, per the backend
   doc's Section 4 upload rules -- fail fast in the UI, don't wait for a 422 from the server for
   something checkable client-side)
4. Delete icon opens the shared confirmation modal ("Delete this menu item? This can't be undone")
   before calling DELETE /menu-items/{id}
5. Status badge styling and thumbnail treatment exactly as in the sample -- this becomes the
   template other list views copy
```

### Prompt — Reports
```
Build the CI4 View for sales reports -- the one screen that's primarily about reading data rather
than acting on it, so lean toward clarity over density.

Deliver:
1. app/Views/reports/index.php -- date picker (daily) / month picker (monthly) toggle, a summary
   row using the .stat-card pattern (total orders, total revenue, average order value -- revenue in
   the highlight treatment, matching the dashboard's convention), and a simple top-5-items table
   below using the standard data-table styling
2. A "Print report" button that opens the print-friendly endpoint variant (GET .../print) in a new
   view using the same @media print approach as the receipt page
3. No charting library needed for a first pass -- the top-items table and the summary numbers carry
   the information; add a simple bar visualization later only if reviewing real data shows it's
   needed, not preemptively
```

### Prompt — Staff (Users)
```
Build the CI4 View for staff management. This follows the Menu Items CRUD-list pattern (previous
prompt) exactly -- same toolbar/table/pagination/modal structure, admin-only per RoleFilter.

Deliver:
1. app/Views/users/index.php reusing datatable.js against GET /users
2. Columns: name, username, role (badge -- reuse .badge styling with teal for admin, sulfur for
   cashier, both semantically distinct from status badges elsewhere), status, actions
3. "Add user" modal: name, username, email, password, role
4. Status toggle uses the shared confirmation modal, since deactivating the last admin is rejected
   server-side (409) -- show that error as a toast, don't try to prevent it client-side, since the
   server is the actual source of truth for who else is currently an active admin
5. "Reset password" opens a small modal with a single new-password field, calling
   POST /users/{id}/reset-password
```

---

## 7. Responsive & accessibility notes

- Sidebar collapses to icon-only (72px) below 720px viewport width; labels hide, active-state
  indicator stays.
- Stat/floor grids reflow via `auto-fill`/`minmax` — no fixed breakpoint-specific column counts to maintain.
- Every interactive element gets the 2px sulfur `:focus-visible` outline (Section 2.3) — verify this
  isn't accidentally suppressed by any Bootstrap default resets when the theme is layered in.
- Color is never the only status signal — every badge pairs a dot/color with a text label (Section 2.4).
- Print stylesheets (`@media print`) for the receipt and report views hide navigation chrome entirely.

---

## 8. Suggested build order

1. `theme.css` extracted from the four sample files (do this first — everything else references it)
2. Shared partials: layout shell, sidebar, topbar, confirmation modal, toast
3. Login
4. Staff (Users) — needed to actually create/test cashier logins against the real backend
5. Menu Categories/Items — establishes the CRUD-list + `datatable.js` pattern
6. Tables
7. Orders (POS) — the most complex page, built last among "core" screens once its dependencies (Tables, Menu Items) have real UI to test against
8. Billing/Receipt
9. Reports
10. Dashboard — last, since it aggregates every other module

Test each page against the actual backend as it's built, not against mock data — the backend is
already in testing per your last update, so there's no reason to fake the API responses here.
