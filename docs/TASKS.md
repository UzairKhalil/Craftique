# Craftique — TASKS.md

> Implementation task breakdown for [PROJECT_PLAN.md](PROJECT_PLAN.md)
> Version 1.0 · 2026-08-03 · **433 tasks across 18 milestones** (+22 deferred)
> Status: ready to execute. Implementation begins at **T-M0-001**.

---

## How to use this document

**One task at a time.** For every task, in order:

1. State what will be built and why.
2. Implement it.
3. Test it (write the tests named in _Done when_).
4. Fix what fails.
5. Mark the task `[x]` in this file.
6. Leave the code commit-ready.
7. **Stop and wait for approval.** Never jump ahead.

### Task conventions

| Field         | Meaning                                                                                                       |
| ------------- | ------------------------------------------------------------------------------------------------------------- |
| **ID**        | `T-M{milestone}-{seq}` — stable, referenced in commits (`feat(catalog): variant matrix generator [T-M4-021]`) |
| **Task**      | One deliverable, sized for a single session (≈1–4 hours)                                                      |
| **Dep**       | Task IDs that must be complete first. `—` means it can start any time within its milestone                    |
| **Done when** | The acceptance check. If it names a test, that test must exist and pass                                       |

Every task also inherits the **Definition of Done** from PROJECT_PLAN.md §33: tests pass, Larastan + TS clean, Pint + ESLint clean, no N+1, policies tested both ways, 375px verified, keyboard pass, loading/empty/error states, reversible migration, docs updated.

### Legend

`[ ]` not started · `[~]` in progress · `[x]` done · `[!]` blocked · `[-]` deferred to v1.1/v2

---

## Assumptions taken (in lieu of answers to the Appendix A questions)

These were unanswered when implementation was approved. Each is implemented as **configuration, not hardcoding**, so changing any of them later is a config edit plus a seeder change — not a refactor.

| #   | Question                 | Assumption taken                                                                                                      | Cost to change later                |
| --- | ------------------------ | --------------------------------------------------------------------------------------------------------------------- | ----------------------------------- |
| 1   | Target market / currency | Single-country launch, `USD`, `en`, config-driven via `config/craftique.php`; schema is multi-currency and i18n ready | Low — config + tax/shipping seeders |
| 2   | Launch vertical          | All categories built; **merchandising** focuses the homepage on jewellery + resin at launch                           | None — merchandising is data        |
| 3   | Commission               | Flat **10%**, seeded as a `global` commission rule; category/vendor/plan overrides work from day one                  | None — admin UI                     |
| 4   | Payment model            | Aggregator ledger + Stripe driver + COD; Stripe Connect is a driver added later without touching the ledger           | Low — new driver                    |
| 5   | Team size                | Solo; tasks are ordered as a single serial chain with dependencies marked so they can be parallelised                 | None                                |
| 6   | Deployment               | VPS + Nginx + Redis + MySQL, Forge-style provisioning; containerised parity via Sail locally                          | Medium — M17 only                   |
| 7   | Branding                 | No brand supplied → **M1 proposes** the palette, type pairing, and logotype for approval                              | None — M1 gate                      |

**Still a genuine blocker, not an assumption:** payment-aggregation licensing (Risk R1). It must be resolved **before T-M7-001**. Everything up to M6 can be built without it.

---

## Progress summary

| Milestone | Focus                           | Tasks   | Done   |
| --------- | ------------------------------- | ------- | ------ |
| M0        | Foundation & tooling            | 15      | 13     |
| M1        | Design system & app shell       | 24      | 0      |
| M2        | Identity & accounts             | 22      | 0      |
| M3        | Vendor onboarding               | 22      | 0      |
| M4        | Catalog                         | 55      | 0      |
| M5        | Cart & multi-vendor order model | 28      | 0      |
| M6        | Shipping & tax                  | 18      | 0      |
| M7        | Payments, commission & ledger   | 30      | 0      |
| M8        | Fulfilment & customer orders    | 24      | 0      |
| M9        | Chat                            | 26      | 0      |
| M10       | Custom orders                   | 22      | 0      |
| M11       | Reviews, wishlists & discovery  | 24      | 0      |
| M12       | Search                          | 16      | 0      |
| M13       | Notifications                   | 16      | 0      |
| M14       | Analytics, earnings & payouts   | 22      | 0      |
| M15       | Admin & platform operations     | 28      | 0      |
| M16       | Promotions & growth             | 22      | 0      |
| M17       | Hardening & launch              | 20      | 0      |
|           | **Total**                       | **434** | **13** |

---

## M0 — Foundation & tooling

**Goal:** a correctly structured, fully linted, CI-green empty application.
**Exit:** `composer test` and `npm run build` pass in CI; architecture tests enforce the module boundaries; the app boots against MySQL.

| ID            | Task                                                                                                                                                                 | Dep                | Done when                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| ------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| [x] T-M0-001  | Switch `DB_CONNECTION` to MySQL 8; create the `craftique` database; update `.env`, `.env.example`, `phpunit.xml` (test DB `craftique_test`)                          | —                  | ✅ `migrate:fresh` green on MySQL 8.4.10, test suite green against `craftique_test`, zero SQLite references (incl. `config/queue.php` fallbacks and the `composer.json` setup script)                                                                                                                                                                                                                                                                                                              |
| [x] T-M0-001a | **Decide and install the database server.** XAMPP ships MariaDB 10.4.32 (EOL, no `SKIP LOCKED`, no `utf8mb4_0900_ai_ci`), not MySQL 8                                | T-M0-001           | ✅ MySQL 8.4.10 LTS installed at `C:\mysql8` on port **3307** alongside XAMPP's MariaDB (untouched, still serving other projects). All 6 capability probes pass. Decision recorded in [ADR-0009](adr/0009-mysql-8-everywhere.md); runbook at [environment.md](runbooks/environment.md); control scripts `scripts/mysql8.{sh,bat}`                                                                                                                                                                  |
| [x] T-M0-002  | Verify PHP extensions (`pdo_mysql`, `gd`/`imagick`, `redis`, `intl`, `zip`, `bcmath`); document required versions in `docs/runbooks/environment.md`                  | —                  | ✅ `craftique:doctor` added (table + `--json`, non-zero exit on failure), 6 tests / 171 assertions. Enabled `intl` + `sodium`, installed `phpredis` 6.2.0 (TS/VS16/x64) — all 14 required extensions now PASS. Requirements documented in [environment.md](runbooks/environment.md)                                                                                                                                                                                                                |
| [x] T-M0-003  | Install Redis; point `CACHE_STORE`, `SESSION_DRIVER`, `QUEUE_CONNECTION`, `BROADCAST_CONNECTION` at it with a documented database-driver fallback                    | T-M0-001           | ✅ Redis 5.0.14 at `C:\redis` (portable, no admin). All 4 drivers on Redis. Verified end to end: dispatch → Redis db 0 → worker → cache write db 1 → `Cache::get`. Doctor now does a real cache round-trip and **fails** (not warns) when a driver depends on an unreachable Redis. Fallback documented in [environment.md](runbooks/environment.md); scripts `scripts/redis.{sh,bat}`                                                                                                             |
| [x] T-M0-004  | Install Breeze (Inertia + React + TypeScript + SSR); confirm Vite build and SSR entry                                                                                | —                  | ✅ `breeze:install react --typescript --ssr --dark`. Breeze ships **Tailwind 3 + React 18**, so its `npm install` failed against our Tailwind 4 and was corrected per ADR-001/§9.5: now React **19.2.8**, Tailwind **4.3.3** (`@tailwindcss/vite`, no postcss/autoprefixer/`tailwind.config.js`), Inertia **2.3.27**, TS 5.9.3. Client + SSR bundles build, SSR server boots on 13714, 39 tests / 295 assertions green                                                                             |
| [x] T-M0-005  | Configure TypeScript `strict: true`, path aliases (`@/`), and `resources/js/types/`                                                                                  | T-M0-004           | ✅ `strict` plus `noUncheckedIndexedAccess`, `noImplicitOverride`, `noFallthroughCasesInSwitch`, `noUnusedLocals/Parameters`, `allowUnreachableCode: false`. `@/*` alias, `types/generated.d.ts` placeholder per §9.5. `npx tsc --noEmit` clean, build green                                                                                                                                                                                                                                       |
| [x] T-M0-006  | Add ESLint (+ `jsx-a11y`, `react-hooks`) and Prettier with the Tailwind class-sorting plugin; npm scripts `lint`, `lint:fix`, `format`                               | T-M0-004           | ✅ ESLint 9 flat config (typescript-eslint 8, react, react-hooks, jsx-a11y) + Prettier with organize-imports and tailwindcss plugins. a11y rules are **errors** per §29. Caught and fixed 44 real issues in the Breeze scaffolding, including an `<img>` with no `alt`. Scripts: `lint`, `lint:fix`, `format`, `format:check`, `types` — all clean                                                                                                                                                 |
| [x] T-M0-007  | Install Pest, convert the default tests, add `tests/Unit`, `tests/Feature`, `tests/Architecture` structure                                                           | —                  | ✅ Pest 3.8 + pest-plugin-laravel. `tests/Pest.php` binds TestCase to Feature and Architecture, adds a `toBeMinorUnits` expectation guarding ADR-0004. RefreshDatabase deliberately opt-in, not global (a test that breaks the DB connection cannot sit in a wrapping transaction). Architecture suite registered in `phpunit.xml`. `composer test` green: 40 passed                                                                                                                               |
| [x] T-M0-008  | Install Larastan at level 6 with `phpstan.neon`; fix scaffold findings                                                                                               | T-M0-007           | ✅ Larastan 3.10 at level 6 over app, config, database, routes, tests. Found 8 real issues, all fixed at source with zero baseline entries or ignores: `User` now implements `MustVerifyEmail` (FR-AUTH-1), a nullable `$request->user()` deref in `VerifyEmailController`, an `env()` call that returns null under cached config, and dead version-check code. Composer scripts `analyse`, `format`, `format:check`, `check`                                                                      |
| [x] T-M0-009  | Configure Pint (Laravel preset + strict types rule) and Rector (Laravel + PHP 8.2 sets, dry-run in CI)                                                               | —                  | ✅ `pint.json` (Laravel preset + `declare_strict_types`, `void_return`, ordered imports, trailing commas) applied across 44 files. `rector.php` with PHP 8.2, dead-code, code-quality, type-declaration and Laravel sets; 10 files applied so the CI `--dry-run` gate starts clean. Strict types surfaced a real `Stringable`→`string` bug in Breeze `LoginRequest::throttleKey()`. `pint --test`, `phpstan`, `rector --dry-run` all clean                                                         |
| [x] T-M0-010  | Create the `app/Domains/*` skeleton from PROJECT_PLAN §10 with a `README.md` per domain stating its responsibility                                                   | —                  | ✅ All 18 domains under `app/Domains`, each with a README stating what it owns, what it does not, and its ADR-0014 boundary rule. `DomainStructureTest` asserts the set is exactly the documented 18 (catching both additions and deletions), that every README documents its responsibility, and that the `App\` PSR-4 prefix autoloads them. 77 tests green                                                                                                                                      |
| [x] T-M0-011  | Write architecture tests: domains may not import each other's internals; controllers are `final`; no `dd()`/`dump()`/`ray()`; models live only in `Domains/*/Models` | T-M0-010, T-M0-007 | ✅ `BoundariesTest` (source-scanned, since arch() over empty namespaces asserts nothing) blocks cross-domain imports while allowing `Events`, bans debug helpers, and requires `declare(strict_types=1)`. `ConventionsTest` requires controllers/requests/middleware/jobs/commands to be final and correctly based, and keeps Eloquent models out of HTTP and console layers. **Verified by planting a real violation**: caught and named it; an `Events` import correctly passed. 104 tests green |
| [x] T-M0-012  | Create `config/craftique.php` (currency, locale, country, order-number prefix, hold days, limits) and bind a typed `CraftiqueConfig` accessor                        | —                  | ✅ `config/craftique.php` holds every tunable business rule (market, order lifecycle, commission/payout, catalog limits, chat, custom requests, vendors), each annotated with the FR it implements. `CraftiqueConfig` + 6 readonly value objects give typed access with validation at boot — a wrong type or empty value throws rather than becoming a silent null inside a money calculation. Singleton-bound; 7 tests. Also enabled `preventLazyLoading` outside production per §26.1            |
| [ ] T-M0-013  | GitHub Actions CI: MySQL + Redis services, `composer test`, phpstan, pint, eslint, tsc, `npm run build`, `composer audit`, `npm audit`                               | T-M0-005…009       | CI green on a pull request; a deliberate lint error fails the build                                                                                                                                                                                                                                                                                                                                                                                                                                |
| [ ] T-M0-014  | Repo hygiene: `CONTRIBUTING.md` (conventional commits), PR template, `.editorconfig` review, `docs/adr/0001-*.md` … `0014-*.md` extracted from PROJECT_PLAN §3       | —                  | 14 ADR files exist; PR template renders on a test PR                                                                                                                                                                                                                                                                                                                                                                                                                                               |

---

## M1 — Design system & app shell

**Goal:** the visual language and every UI primitive, accessible and documented, before any feature uses them.
**Exit:** the component gallery renders every primitive; axe reports zero violations; contrast verified in light and dark.

| ID           | Task                                                                                                                                                      | Dep                | Done when                                                                              |
| ------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------ | -------------------------------------------------------------------------------------- |
| [ ] T-M1-001 | **Brand proposal** — 3 palette + type-pairing directions rendered on a sample product card and PDP hero for approval                                      | —                  | Three options presented; one approved and recorded in `docs/design/brand.md`           |
| [ ] T-M1-002 | Tailwind v4 design tokens in `resources/css/app.css`: colour scales, type scale, spacing rhythm, radii, shadows, motion durations/easings, z-index ladder | T-M1-001           | Tokens are the only source of design values; no hardcoded hex in components            |
| [ ] T-M1-003 | Dark mode: token overrides via `prefers-color-scheme` + an explicit `data-theme` toggle that wins in both directions                                      | T-M1-002           | Theme toggle persists to `localStorage` and to `user_preferences` when logged in       |
| [ ] T-M1-004 | Self-hosted fonts (2 families, ≤4 weights), subset, preloaded, `font-display: swap`                                                                       | T-M1-001           | No layout shift on font load; CLS 0 on the gallery page                                |
| [ ] T-M1-005 | `cn()` helper (clsx + tailwind-merge) and the `cva` variant convention documented                                                                         | T-M1-002           | Documented in `docs/design/components.md`                                              |
| [ ] T-M1-006 | Component gallery route (`/_design`, local/staging only) listing every primitive with all variants and states                                             | T-M1-002           | Route 404s in production; renders all primitives elsewhere                             |
| [ ] T-M1-007 | `Button` — variants (primary, secondary, ghost, destructive, link), sizes, loading, disabled, icon-only with accessible name                              | T-M1-005           | Keyboard-operable; loading state announces via `aria-busy`                             |
| [ ] T-M1-008 | Form primitives: `Input`, `Textarea`, `Label`, `FieldError`, `FieldHint`, `Fieldset` with `aria-describedby` wiring                                       | T-M1-005           | Errors announced; label click focuses input                                            |
| [ ] T-M1-009 | `Select`, `Combobox` (searchable), `MultiSelect` on Radix                                                                                                 | T-M1-008           | Full keyboard nav; type-ahead; screen-reader announces the option count                |
| [ ] T-M1-010 | `Checkbox`, `Radio`, `Switch`, `Slider` (price range)                                                                                                     | T-M1-008           | Space/arrow keys work; states are not colour-only                                      |
| [ ] T-M1-011 | `Dialog`, `Sheet` (side), `Drawer` (bottom, mobile) with focus trap and restore                                                                           | T-M1-005           | Escape closes, focus returns to trigger, background is `aria-hidden` and scroll-locked |
| [ ] T-M1-012 | `Popover`, `Tooltip`, `DropdownMenu`, `ContextMenu`                                                                                                       | T-M1-005           | Tooltips are keyboard-reachable and never the only source of information               |
| [ ] T-M1-013 | `Tabs`, `Accordion`, `Collapsible`                                                                                                                        | T-M1-005           | Arrow-key navigation; correct ARIA roles                                               |
| [ ] T-M1-014 | `Toast` system (sonner) with success/error/info/undo and an `aria-live` region                                                                            | T-M1-005           | Toasts announce once; undo action works                                                |
| [ ] T-M1-015 | `Badge`, `Avatar`, `Card`, `Separator`, `Tag`, `Rating` (stars, read + interactive)                                                                       | T-M1-005           | Rating input is keyboard-operable and labelled                                         |
| [ ] T-M1-016 | `Table` (sticky header, sortable headers, row selection, column chooser) + `Pagination` (offset and cursor)                                               | T-M1-005           | Sortable headers announce direction via `aria-sort`                                    |
| [ ] T-M1-017 | `Skeleton`, `Spinner`, `EmptyState`, `ErrorState` with retry                                                                                              | T-M1-005           | Every one is used at least once in the gallery                                         |
| [ ] T-M1-018 | `CommandPalette` (⌘K) shell — no data wiring yet                                                                                                          | T-M1-009           | Opens, filters static items, closes on Escape, returns focus                           |
| [ ] T-M1-019 | `MoneyDisplay`, `DateDisplay`, `RelativeTime` components reading currency/locale/timezone from shared props                                               | T-M1-002, T-M0-012 | Renders `{amount, currency}` payloads correctly; never formats a float                 |
| [ ] T-M1-020 | `StorefrontLayout`: header, mega-menu, search trigger, cart/wishlist/bell/account, footer, mobile bottom tab bar; persistent across Inertia navigations   | T-M1-007…017       | Layout state (open menus, scroll) survives page navigation                             |
| [ ] T-M1-021 | `AuthLayout`, `AccountLayout` (sidebar), `VendorLayout` (collapsible sidebar + store switcher), `AdminLayout` (dense)                                     | T-M1-020           | All four render at 375px and 1440px without overflow                                   |
| [ ] T-M1-022 | `HandleInertiaRequests` shared props: auth user, permissions summary, cart count, unread counts, flash messages, locale/currency, feature flags           | T-M1-020           | Typed in `types/generated.d.ts`; one query budget, no N+1                              |
| [ ] T-M1-023 | Global error boundaries + Inertia error pages: 403, 404, 419, 429, 500, 503, offline                                                                      | T-M1-020           | Each renders with a route home; no dead ends                                           |
| [ ] T-M1-024 | Accessibility baseline: skip link, focus-visible ring, `prefers-reduced-motion` respected globally, axe test over the gallery in Playwright               | T-M1-006…023       | axe reports zero violations on `/_design`                                              |

---

## M2 — Identity & accounts

**Goal:** every authentication and account-management path, with roles and policies in place.
**Exit:** register → verify → 2FA → manage addresses → revoke sessions, all tested; permission seeder runs.

| ID           | Task                                                                                                                                         | Dep                | Done when                                                                               |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------------------- | ------------------ | --------------------------------------------------------------------------------------- |
| [ ] T-M2-001 | Migration: extend `users` per PROJECT_PLAN §11.1 (uuid, names, phone, locale, timezone, currency, status, marketing, referrer, soft deletes) | T-M0-001           | Migration up/down clean; `UserFactory` covers every state                               |
| [ ] T-M2-002 | `User` model in `Domains/Identity/Models` with casts, `HasUuid`, enums, and `config/auth.php` repointed                                      | T-M2-001           | Auth still works; a unit test asserts casts and enum resolution                         |
| [ ] T-M2-003 | Migrations + models: `social_accounts`, `user_preferences`, `user_devices`                                                                   | T-M2-002           | Relationships tested; encrypted casts verified on tokens                                |
| [ ] T-M2-004 | Migration + model: `addresses` with defaults handling                                                                                        | T-M2-002           | Setting a new default unsets the previous one inside one transaction (test)             |
| [ ] T-M2-005 | Install `spatie/laravel-permission` in teams mode; migrations published and configured for `team_id = vendor_id`                             | T-M2-002           | Team-scoped role assignment tested for one user in two vendors                          |
| [ ] T-M2-006 | `RolesAndPermissionsSeeder`: all roles and the full permission list from PROJECT_PLAN §25.2                                                  | T-M2-005           | Seeder is idempotent; a test asserts the matrix row-for-row                             |
| [ ] T-M2-007 | `RegisterUser` action + `RegisterRequest`; assigns the `customer` role, fires `UserRegistered`, sends verification                           | T-M2-006           | Feature test covers success, duplicate email, weak password                             |
| [ ] T-M2-008 | Login, logout, remember-me, throttling (5/min per email+IP, progressive lockout)                                                             | T-M2-007           | Test asserts 429 after the limit and that timing does not leak account existence        |
| [ ] T-M2-009 | Email verification flow + `EnsureEmailIsVerified` on purchase-adjacent routes                                                                | T-M2-007           | Unverified user is blocked from review/chat/custom-request routes (test)                |
| [ ] T-M2-010 | Password reset: request, signed single-use 60-minute link, reset, session invalidation                                                       | T-M2-008           | Reused token fails; all other sessions are logged out on reset                          |
| [ ] T-M2-011 | Socialite: Google, Facebook, Apple; link by verified email; block linking to an unverified address                                           | T-M2-003           | Feature tests with a faked Socialite driver for new, existing, and conflicting accounts |
| [ ] T-M2-012 | 2FA: enable (QR + secret), confirm, recovery codes, challenge screen, disable                                                                | T-M2-008           | Full enable → challenge → recovery-code login covered by tests                          |
| [ ] T-M2-013 | Enforce 2FA for vendor owners and platform staff via middleware                                                                              | T-M2-012, T-M2-006 | A staff user without 2FA is redirected to setup (test)                                  |
| [ ] T-M2-014 | Session management: list devices/IP/last-active, revoke one, revoke all others                                                               | T-M2-008           | Revoked session is rejected on next request (test)                                      |
| [ ] T-M2-015 | New-device / new-location login notification email                                                                                           | T-M2-014           | Notification queued on a new device fingerprint, not on a known one                     |
| [ ] T-M2-016 | Auth UI: login, register, forgot, reset, verify, 2FA challenge pages + the inline auth modal                                                 | T-M1-021, T-M2-012 | Keyboard-only journey completes; errors are announced                                   |
| [ ] T-M2-017 | Account: profile page (details, avatar upload, locale/timezone/currency)                                                                     | T-M1-021, T-M2-002 | Avatar upload validates MIME and size; profile update tested                            |
| [ ] T-M2-018 | Account: security page (password change, 2FA, sessions, connected accounts)                                                                  | T-M2-012…016       | Password change requires the current password (test)                                    |
| [ ] T-M2-019 | Account: address book UI (list, create, edit, delete, set defaults)                                                                          | T-M2-004           | Deleting an address referenced by an open order is blocked with a clear message         |
| [ ] T-M2-020 | `UserPolicy`, `AddressPolicy` + `authorize()` on every route                                                                                 | T-M2-006           | Every policy method has an allow test and a deny test                                   |
| [ ] T-M2-021 | Account deletion request: 30-day soft delete, PII anonymisation job, financial-record retention                                              | T-M2-020           | Test asserts orders survive with the customer anonymised                                |
| [ ] T-M2-022 | `AdminUserSeeder` + `DemoUsersSeeder` (customers, staff roles) for local development                                                         | T-M2-006           | `php artisan db:seed` produces a working admin login                                    |

---

## M3 — Vendor onboarding

**Goal:** stores exist, are verified, and are provably isolated from one another.
**Exit:** apply → admin approves → vendor dashboard; a cross-vendor access test suite passes.

| ID           | Task                                                                                                            | Dep                | Done when                                                                                                 |
| ------------ | --------------------------------------------------------------------------------------------------------------- | ------------------ | --------------------------------------------------------------------------------------------------------- |
| [ ] T-M3-001 | Migration + model: `vendors` (full column set, indexes, checks)                                                 | T-M2-002           | `VendorFactory` with states: pending, approved, suspended, vacation                                       |
| [ ] T-M3-002 | Migration + model: `vendor_users` with the one-owner-per-vendor generated-column constraint                     | T-M3-001           | Inserting a second owner fails at the database level (test)                                               |
| [ ] T-M3-003 | Migration + model: `vendor_verifications` (+ MediaLibrary private collection for documents)                     | T-M3-001           | Documents are stored on the private disk and are not publicly reachable (test)                            |
| [ ] T-M3-004 | Migration + models: `vendor_themes`, `vendor_followers`                                                         | T-M3-001           | Follow/unfollow toggles and maintains `followers_count`                                                   |
| [ ] T-M3-005 | `VendorStatus`, `VendorRole`, `VerificationType` enums with behaviour (`canTransact()`, `label()`, `color()`)   | T-M3-001           | Unit-tested transitions                                                                                   |
| [ ] T-M3-006 | `BelongsToVendor` trait + global scope; `withoutVendorScope()` restricted to admin context                      | T-M3-002           | A test proves a scoped query cannot return another vendor's rows                                          |
| [ ] T-M3-007 | `SetVendorContext` middleware resolving the acting vendor from the authenticated user, never from request input | T-M3-006           | Spoofing `vendor_id` in the payload has no effect (test)                                                  |
| [ ] T-M3-008 | Handle validation: format, length, reserved-word blocklist, uniqueness, case-insensitive                        | T-M3-001           | `admin`, `api`, `support` etc. are rejected (test)                                                        |
| [ ] T-M3-009 | `CreateVendor` action + Form Request; assigns the `owner` team role                                             | T-M3-002, T-M2-006 | One user cannot exceed the configured store limit                                                         |
| [ ] T-M3-010 | Onboarding wizard step 1 — store identity (name, handle, tagline, story, logo, banner)                          | T-M3-009, T-M1-021 | Step saves independently and is resumable after logout                                                    |
| [ ] T-M3-011 | Wizard step 2 — categories and craft types                                                                      | T-M3-010           | Selections persist; at least one required                                                                 |
| [ ] T-M3-012 | Wizard step 3 — location and shipping-from details, default lead times                                          | T-M3-010           | Lead-time min ≤ max enforced client and server side                                                       |
| [ ] T-M3-013 | Wizard step 4 — payout method placeholder (details captured, encrypted, unverified)                             | T-M3-010           | Bank details are encrypted at rest (test decrypts via the model cast only)                                |
| [ ] T-M3-014 | Wizard step 5 — policies (returns, shipping, custom-order acceptance)                                           | T-M3-010           | Defaults offered; markdown sanitised on save                                                              |
| [ ] T-M3-015 | Wizard step 6 — KYC upload and submit for review; status → `under_review`                                       | T-M3-003, T-M3-014 | `SubmitForVerification` fires `VendorSubmitted`; re-submission after rejection works                      |
| [ ] T-M3-016 | Vendor status banner + gating: pending/under-review/rejected/suspended/vacation states across the vendor shell  | T-M3-015           | Each status renders the correct banner and blocks the correct actions                                     |
| [ ] T-M3-017 | Admin: vendor application queue (list, filters, SLA age)                                                        | T-M3-015, T-M1-021 | Sorted oldest-first by default; counts match the database                                                 |
| [ ] T-M3-018 | Admin: vendor application detail with document viewer, approve / reject (reason) / request more info            | T-M3-017           | `ApproveVendor` sets status, verification level, `approved_at`, and notifies; rejection requires a reason |
| [ ] T-M3-019 | `VendorPolicy` + `VendorStaffPolicy` covering owner/manager/staff and admin overrides                           | T-M3-006, T-M2-006 | Every method has allow and deny tests for all five actor types                                            |
| [ ] T-M3-020 | Staff invitations: invite by email, accept flow, role change, revoke                                            | T-M3-019           | Expired invitation token is rejected; revoked staff loses access immediately                              |
| [ ] T-M3-021 | **Cross-vendor isolation test suite** — for every vendor route, assert 403/404 for a foreign vendor             | T-M3-019           | A dedicated Pest test file iterates the route list; it fails if a new unprotected route is added          |
| [ ] T-M3-022 | Vendor dashboard shell with an empty state and the onboarding checklist component                               | T-M3-016           | Checklist reflects real completion percentages                                                            |

---

## M4 — Catalog

**Goal:** vendors can list rich, variable, personalisable products; buyers can browse and configure them.
**Exit:** a vendor publishes a 3-axis variable product with personalisation; a guest finds it via category browse and sees a correct configured price.

### M4.1 Taxonomy

| ID           | Task                                                                                                                                               | Dep                | Done when                                                                                |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------ | ---------------------------------------------------------------------------------------- |
| [ ] T-M4-001 | Migration + model: `categories` with materialised `path`, `depth`, counters                                                                        | T-M0-001           | `CategoryFactory` builds trees; `CHECK (parent_id <> id)` holds                          |
| [ ] T-M4-002 | `CategoryTree` service: subtree query, ancestors, breadcrumb, move-node with transactional path rewrite                                            | T-M4-001           | Moving a node with 3 levels of descendants rewrites every path in one transaction (test) |
| [ ] T-M4-003 | Cycle prevention on category move/create                                                                                                           | T-M4-002           | Attempting to move a node under its own descendant throws (test)                         |
| [ ] T-M4-004 | `CategorySeeder`: the full handmade taxonomy (resin, jewellery, crochet, candles, decor, calligraphy, bouquets, gifts, wedding) with subcategories | T-M4-001           | Seeder is idempotent; depth ≤ 3                                                          |
| [ ] T-M4-005 | Migrations + models: `tags`, `taggables` with a polymorphic `HasTags` trait                                                                        | T-M0-001           | Attach/detach maintains `usage_count`                                                    |
| [ ] T-M4-006 | Migrations + models: `attributes`, `attribute_values`, `attribute_category`                                                                        | T-M4-001           | Facet definitions resolve per category (test)                                            |
| [ ] T-M4-007 | `AttributeSeeder`: material, colour family, occasion, technique, size class                                                                        | T-M4-006           | Values seeded with hex codes for colour swatches                                         |
| [ ] T-M4-008 | Migrations + models: `collections`, `collection_product` (manual and rule-based)                                                                   | T-M4-001           | Automatic collection rules resolve to the right product set (test)                       |
| [ ] T-M4-009 | Admin: category tree editor (drag reorder, nest, create, edit, deactivate)                                                                         | T-M4-002, T-M1-021 | Reordering persists; the tree cannot be left in an invalid state                         |
| [ ] T-M4-010 | Admin: attributes, attribute values, and tag management screens                                                                                    | T-M4-006           | Deleting an attribute in use is blocked with an explanatory message                      |

### M4.2 Product core

| ID           | Task                                                                                                             | Dep                | Done when                                                                                                    |
| ------------ | ---------------------------------------------------------------------------------------------------------------- | ------------------ | ------------------------------------------------------------------------------------------------------------ |
| [ ] T-M4-011 | Migration + model: `products` (full column set, indexes, checks, soft deletes)                                   | T-M4-001           | `ProductFactory` with states: draft, pending, published, archived, variable, personalizable, one-of-a-kind   |
| [ ] T-M4-012 | `ProductType` and `ProductStatus` enums with behaviour (`requiresInventory()`, `isPubliclyVisible()`)            | T-M4-011           | Unit-tested                                                                                                  |
| [ ] T-M4-013 | Migration + model: `product_categories` (secondary, max 4, application-enforced)                                 | T-M4-011           | Fifth attachment is rejected (test)                                                                          |
| [ ] T-M4-014 | Migration + model: `product_attribute_values` with typed value columns                                           | T-M4-006, T-M4-011 | Attribute filtering by select, number range, and boolean all tested                                          |
| [ ] T-M4-015 | Migrations + models: `product_options`, `product_option_values` (max 3 axes)                                     | T-M4-011           | Fourth option axis rejected (test)                                                                           |
| [ ] T-M4-016 | Migration + model: `product_variants` with vendor-scoped unique SKU and stock checks                             | T-M4-011           | Duplicate SKU within a vendor fails at the database level (test)                                             |
| [ ] T-M4-017 | Migration + model: `variant_option_values` pivot                                                                 | T-M4-015, T-M4-016 | Variant resolves its option combination; duplicate combinations rejected                                     |
| [ ] T-M4-018 | Migration + model: `personalization_fields` with typed config and price deltas                                   | T-M4-011           | All 8 field types validate correctly server-side (test per type)                                             |
| [ ] T-M4-019 | Migration + models: `digital_files`, `digital_downloads`                                                         | T-M4-011           | Files land on the private disk; signed download URL expires                                                  |
| [ ] T-M4-020 | `Product` denormalisation observer: min/max price, stock sum, `has_variants`                                     | T-M4-016           | Saving a variant updates the parent within the same request (test)                                           |
| [ ] T-M4-021 | `GenerateVariants` action: cartesian product of options, ≤100 variants, preserves existing rows on re-generation | T-M4-017           | 3×4×5 generates 60 variants; re-running preserves stock and prices (test)                                    |
| [ ] T-M4-022 | `CreateProduct` / `UpdateProduct` actions + Form Requests with full validation                                   | T-M4-011…021       | Validation covers every field; a test asserts vendors cannot set another vendor's category-restricted fields |
| [ ] T-M4-023 | `PublishProduct` action: completeness validation, moderation gate, auto-trust rule, `ProductPublished` event     | T-M4-022           | Publishing without an image or price fails with field-level errors (test)                                    |
| [ ] T-M4-024 | `DuplicateProduct` action (copies media, variants, personalisation; resets status and SKUs)                      | T-M4-022           | Duplicate is a draft with unique SKUs (test)                                                                 |
| [ ] T-M4-025 | `ProductPolicy` (vendor CRUD, staff read, moderator approve, admin all)                                          | T-M4-022, T-M3-019 | Allow and deny tests for all actor types                                                                     |

### M4.3 Media

| ID           | Task                                                                                                                          | Dep      | Done when                                                                                  |
| ------------ | ----------------------------------------------------------------------------------------------------------------------------- | -------- | ------------------------------------------------------------------------------------------ |
| [ ] T-M4-026 | Install MediaLibrary; configure disks (`local`, `s3`/`r2`), private vs public collections                                     | T-M0-003 | Disk switch is config-only; a test asserts the public URL shape                            |
| [ ] T-M4-027 | Product media conversions (thumb 300, card 600, detail 1200, zoom 2400) in AVIF + WebP + JPEG, generated on the `media` queue | T-M4-026 | Conversions queue and complete; a test asserts all 12 derivatives exist                    |
| [ ] T-M4-028 | Presigned direct-to-storage upload endpoint + client uploader with progress, retry, and cancel                                | T-M4-026 | Files over 2 MB never pass through PHP (test asserts the endpoint returns a presigned URL) |
| [ ] T-M4-029 | Media reorder, cover selection, delete, and **required alt text**                                                             | T-M4-028 | Publishing is blocked when any image lacks alt text (test)                                 |
| [ ] T-M4-030 | `ResponsiveImage` React component (`srcset`, `<picture>`, explicit dimensions, blurhash placeholder, `fetchpriority`)         | T-M4-027 | CLS 0 on the PDP gallery in a Lighthouse run                                               |
| [ ] T-M4-031 | Product video: upload or YouTube/Vimeo embed with a host allow-list                                                           | T-M4-026 | An arbitrary URL is rejected (SSRF guard test)                                             |

### M4.4 Vendor product management UI

| ID           | Task                                                                                                              | Dep                | Done when                                                                     |
| ------------ | ----------------------------------------------------------------------------------------------------------------- | ------------------ | ----------------------------------------------------------------------------- |
| [ ] T-M4-032 | Vendor product list: filters (status, category, stock), sort, bulk selection, quick actions                       | T-M4-025, T-M1-016 | Query count within budget; no N+1 across 100 rows                             |
| [ ] T-M4-033 | Product editor shell: section navigation, autosave drafts, dirty-state guard, keyboard shortcuts                  | T-M4-022           | Navigating away with unsaved changes warns; autosave recovers a draft         |
| [ ] T-M4-034 | Editor — Basics section (title, category picker, type, descriptions, restricted rich text)                        | T-M4-033           | HTML is sanitised server-side against an allow-list (XSS test)                |
| [ ] T-M4-035 | Editor — Media section (drag-drop, reorder, alt text, video)                                                      | T-M4-029, T-M4-033 | Reorder persists; upload failures surface clearly                             |
| [ ] T-M4-036 | Editor — Pricing section (price, compare-at, cost, live margin, tax class)                                        | T-M4-033           | Money inputs never produce floats; a test asserts minor-unit storage          |
| [ ] T-M4-037 | Editor — Variants section: option builder + generated matrix with inline bulk edit                                | T-M4-021, T-M4-033 | Bulk price/stock edit across 60 variants issues a bounded number of queries   |
| [ ] T-M4-038 | Editor — Inventory section (SKU, barcode, quantity, threshold, backorder, one-of-a-kind mode)                     | T-M4-037           | One-of-a-kind forces quantity 1 and auto-unpublish on sale                    |
| [ ] T-M4-039 | Editor — Personalisation builder with live preview and price-delta summary                                        | T-M4-018, T-M4-033 | Field reorder persists; preview matches what the PDP will render              |
| [ ] T-M4-040 | Editor — Shipping section (weight, dimensions, lead time, profile override)                                       | T-M4-033           | Physical products cannot publish without weight (test)                        |
| [ ] T-M4-041 | Editor — Attributes, Organisation (tags, collections, occasions), SEO (slug, meta, preview)                       | T-M4-014, T-M4-033 | Slug is immutable after publish unless explicitly changed with a redirect     |
| [ ] T-M4-042 | Editor — Publish section (status, schedule, live PDP preview in a new tab)                                        | T-M4-023, T-M4-033 | Preview renders the unpublished product only for authorised users             |
| [ ] T-M4-043 | Admin: product moderation queue with side-by-side preview, approve / reject (templated reasons) / request changes | T-M4-023           | Rejection notifies the vendor with the reason; auto-trust threshold respected |

### M4.5 Storefront

| ID           | Task                                                                                                                                                                          | Dep                | Done when                                                                         |
| ------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------ | --------------------------------------------------------------------------------- |
| [ ] T-M4-044 | `ProductListQuery`: filters (category, price, attributes, rating, vendor, in-stock, on-sale), sorts, cursor pagination, eager loads                                           | T-M4-011…020       | Query count ≤ 6 for a 24-item grid; `preventLazyLoading` passes                   |
| [ ] T-M4-045 | Catalog page `/products`: facet sidebar, mobile filter sheet, sort, grid/list, URL state, infinite scroll, active-filter chips, empty state                                   | T-M4-044, T-M1-020 | Back button restores filters and scroll position                                  |
| [ ] T-M4-046 | Category page `/categories/{path}`: banner, subcategory chips, filtered grid, breadcrumbs, SEO copy                                                                           | T-M4-045, T-M4-002 | Breadcrumb matches the materialised path                                          |
| [ ] T-M4-047 | Collection page `/collections/{slug}`                                                                                                                                         | T-M4-008, T-M4-045 | Manual and rule-based collections both render                                     |
| [ ] T-M4-048 | PDP `/products/{slug}`: gallery (zoom, lightbox, video), title, price, variant selector, quantity, add-to-cart, wishlist, lead time, vendor card, tabs, sticky mobile buy bar | T-M4-030, T-M4-037 | Variant selection updates price, image, stock, and URL without a full reload      |
| [ ] T-M4-049 | PDP personalisation panel with live price updates and client + server validation                                                                                              | T-M4-039, T-M4-048 | Server rejects a payload that bypasses client validation (test)                   |
| [ ] T-M4-050 | Vendor storefront `/@{handle}`: theme applied, banner, story, product grid with in-store search, collections, policies, follow, message CTA                                   | T-M3-004, T-M4-045 | Suspended or vacationing vendors render the correct state; unapproved vendors 404 |
| [ ] T-M4-051 | Vendor directory `/vendors` with search, category and country filters, featured band                                                                                          | T-M4-050           | Only approved vendors appear (test)                                               |
| [ ] T-M4-052 | Product card component (all contexts: grid, rail, compare, chat) with rating, price range, lead time, vendor, wishlist toggle                                                 | T-M4-048           | One component, four contexts, no duplicated markup                                |
| [ ] T-M4-053 | `DemoCatalogSeeder`: 20 vendors, 300 products with real variant/personalisation/media shapes                                                                                  | T-M4-011…050       | `php artisan db:seed --class=DemoCatalogSeeder` produces a browsable marketplace  |
| [ ] T-M4-054 | Migration + model: `product_questions` / `product_answers` schema only (UI deferred to v1.1)                                                                                  | T-M4-011           | Tables exist with factories; marked `[-]` for UI                                  |
| [ ] T-M4-055 | Migration + model: `price_history` + observer writing on every variant price change                                                                                           | T-M4-016           | Price change writes exactly one history row (test)                                |

---

## M5 — Cart & the multi-vendor order model

**Goal:** the financial and structural core. **Highest test coverage in the project.**
**Exit:** a 3-vendor order places with exact money allocation; concurrency tests prove no overselling; illegal state transitions throw.

| ID           | Task                                                                                                                                                       | Dep                | Done when                                                                                                     |
| ------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------ | ------------------------------------------------------------------------------------------------------------- |
| [ ] T-M5-001 | `Money` value object: minor units + currency, add/subtract/multiply, percentage, comparison, immutability, currency-mismatch guard                         | T-M0-010           | 100% unit coverage; adding USD to EUR throws                                                                  |
| [ ] T-M5-002 | `Money::allocate()` — largest-remainder split across N shares with zero lost cents                                                                         | T-M5-001           | Property test: allocating any amount across any split always sums back exactly                                |
| [ ] T-M5-003 | `MoneyCast` Eloquent cast + `MoneyDisplay` payload shape `{amount, currency, formatted}`                                                                   | T-M5-001           | Round-trips through the database unchanged; a float never appears in a payload                                |
| [ ] T-M5-004 | Migrations + models: `carts`, `cart_items` with the `personalization_hash` generated column                                                                | T-M4-016           | Identical configurations merge; different ones stay separate (test)                                           |
| [ ] T-M5-005 | `CartService`: add, update quantity, remove, save-for-later, clear; validates stock, product status, vendor status                                         | T-M5-004           | Adding an unpublished or suspended-vendor product fails with a clear error                                    |
| [ ] T-M5-006 | Guest cart via signed cookie + `MergeCartOnLogin` listener (sums quantities, respects stock caps)                                                          | T-M5-005           | Login merge tested: overlapping items, stock-capped items, personalised items                                 |
| [ ] T-M5-007 | `CartValidator`: revalidates price, stock, availability; returns a structured diff                                                                         | T-M5-005           | A price change since add-time produces an explicit diff, not a silent update                                  |
| [ ] T-M5-008 | Cart drawer + cart page: vendor grouping, personalisation summary, quantity steppers, save-for-later, totals                                               | T-M5-005, T-M1-020 | Optimistic quantity updates roll back on server error                                                         |
| [ ] T-M5-009 | Migration + model: `orders` (full column set, indexes, checks, idempotency key)                                                                            | T-M5-003           | `OrderFactory` with every status state                                                                        |
| [ ] T-M5-010 | Migration + model: `vendor_orders`                                                                                                                         | T-M5-009, T-M3-001 | Factory produces a parent order with N vendor orders                                                          |
| [ ] T-M5-011 | Migration + model: `order_items` with full snapshot columns                                                                                                | T-M5-010           | Snapshot survives product deletion (test)                                                                     |
| [ ] T-M5-012 | Migration + model: `order_addresses` (snapshot, no FK to `addresses`)                                                                                      | T-M5-009           | Deleting the source address leaves the order intact (test)                                                    |
| [ ] T-M5-013 | Migration + model: `order_timeline_events` (append-only) + `RecordsTimeline` trait                                                                         | T-M5-010           | Update/delete on a timeline row throws (test)                                                                 |
| [ ] T-M5-014 | Migration + model: `order_notes` with three visibility levels                                                                                              | T-M5-010           | A customer never sees internal notes (test)                                                                   |
| [ ] T-M5-015 | `OrderNumberGenerator`: `CRQ-{base32}`, non-guessable, collision-safe, vendor suffix                                                                       | T-M5-009           | 100k generated numbers are unique; format asserted                                                            |
| [ ] T-M5-016 | `OrderStatus` / `VendorOrderStatus` enums with labels, colours, customer-visible flags                                                                     | T-M5-010           | Unit-tested                                                                                                   |
| [ ] T-M5-017 | `VendorOrderState` state machine implementing PROJECT_PLAN §18.1 exactly                                                                                   | T-M5-016           | Every legal transition passes; every illegal one throws `IllegalTransitionException` (exhaustive matrix test) |
| [ ] T-M5-018 | Migration + model: `inventory_movements` (append-only) + `RecordInventoryMovement` action                                                                  | T-M4-016           | Variant stock always equals the sum of deltas (property test)                                                 |
| [ ] T-M5-019 | Migration + model: `stock_reservations` + `ReserveStock` / `ReleaseStock` / `CommitReservation` actions with row locks                                     | T-M5-018           | Concurrent reservation of the last unit: exactly one succeeds (parallel test)                                 |
| [ ] T-M5-020 | `ReleaseExpiredReservations` scheduled job                                                                                                                 | T-M5-019           | Expired reservations release and restore availability (test with time travel)                                 |
| [ ] T-M5-021 | `TotalsCalculator`: per-line, per-vendor, and order-level subtotal, discount, shipping, tax, total — pure and framework-free                               | T-M5-002           | Unit tests including 3 vendors, mixed tax classes, and a cart-wide discount                                   |
| [ ] T-M5-022 | Cart-wide discount allocation across vendors (proportional, largest-remainder)                                                                             | T-M5-021, T-M5-002 | A $10 discount across three uneven vendor subtotals allocates to the cent (test)                              |
| [ ] T-M5-023 | `PlaceOrder` action: idempotency, transaction, reservations, order + vendor orders + items + address snapshots, timeline                                   | T-M5-009…022       | Double submission with the same key returns the same order and creates no duplicate (test)                    |
| [ ] T-M5-024 | `OrderPlaced` domain event + after-commit dispatch convention                                                                                              | T-M5-023           | Listeners never observe rolled-back state (test with a forced rollback)                                       |
| [ ] T-M5-025 | `OrderPolicy` / `VendorOrderPolicy` (customer owns, vendor scoped, staff scoped, admin)                                                                    | T-M5-023, T-M3-019 | Vendor A cannot read vendor B's vendor order in the same parent order (test)                                  |
| [ ] T-M5-026 | Guest checkout data model: guest token, email/phone capture, order claim on later registration                                                             | T-M5-023           | Registering with the guest email back-links prior orders (test)                                               |
| [ ] T-M5-027 | **Concurrency test suite**: parallel checkouts on the same one-of-a-kind item, same coupon, same last unit                                                 | T-M5-019, T-M5-023 | No oversell, no duplicate coupon use, no deadlock; documented in `docs/domain/ordering.md`                    |
| [ ] T-M5-028 | **Money invariant test suite**: order total always equals the sum of vendor-order totals, which equal the sum of line totals, across 1000 randomised carts | T-M5-021…023       | Property test passes with zero drift                                                                          |

---

## M6 — Shipping & tax

**Goal:** correct per-vendor shipping quotes and correct tax, computed server-side.
**Exit:** a cart spanning two countries quotes the right shipping and tax for each vendor.

| ID           | Task                                                                                                                 | Dep                          | Done when                                                                              |
| ------------ | -------------------------------------------------------------------------------------------------------------------- | ---------------------------- | -------------------------------------------------------------------------------------- |
| [ ] T-M6-001 | Migrations + models: `shipping_profiles`, `shipping_zones`                                                           | T-M3-001                     | Zone matching by country, state, and postcode range all tested                         |
| [ ] T-M6-002 | Migrations + models: `shipping_methods`, `shipping_rates`                                                            | T-M6-001                     | `CHECK (max_value > min_value)` holds                                                  |
| [ ] T-M6-003 | Migration + model: `product_shipping_overrides`                                                                      | T-M6-001, T-M4-011           | Override beats the vendor default (test)                                               |
| [ ] T-M6-004 | `ShippingCarrier` contract + `ManualCarrier` implementation (quote/label/track with graceful capability degradation) | T-M0-010                     | Unimplemented capabilities return a typed "unsupported" result, never an exception     |
| [ ] T-M6-005 | `ShippingQuoteService`: resolves zone → methods → rates per vendor for a given destination and cart                  | T-M6-002…004                 | Flat, weight-based, price-based, free-over-X, and pickup calculations each unit-tested |
| [ ] T-M6-006 | `DeliveryEstimator`: lead time + transit → a date range, never a single false promise                                | T-M6-005                     | Range respects vendor lead time, method transit, and business days (test)              |
| [ ] T-M6-007 | Vendor UI: shipping profiles list and editor                                                                         | T-M6-001, T-M1-021           | A vendor cannot delete the profile currently in use by open orders                     |
| [ ] T-M6-008 | Vendor UI: zone editor (country/state/postcode picker) with overlap warnings                                         | T-M6-007                     | Overlapping zones warn and resolve deterministically by priority                       |
| [ ] T-M6-009 | Vendor UI: methods and rates editor with a live quote preview                                                        | T-M6-008                     | Preview matches `ShippingQuoteService` output exactly (test)                           |
| [ ] T-M6-010 | `ShippingProfileSeeder` — sensible defaults created automatically for a new vendor                                   | T-M6-002                     | A newly approved vendor can transact without touching shipping settings                |
| [ ] T-M6-011 | Migrations + models: `tax_classes`, `tax_zones`, `tax_rates`                                                         | T-M0-001                     | Compound and priority-ordered rates tested                                             |
| [ ] T-M6-012 | `TaxCalculator`: per-line tax by class and destination, inclusive/exclusive modes, shipping taxability               | T-M6-011, T-M5-001           | Unit tests: exclusive, inclusive, compound, zero-rated, shipping-taxed                 |
| [ ] T-M6-013 | Tax rounding policy (per line, then sum) documented and enforced                                                     | T-M6-012                     | A test proves the sum of line taxes equals the order tax exactly                       |
| [ ] T-M6-014 | Admin: tax classes, zones, and rates management UI                                                                   | T-M6-011, T-M1-021           | Effective-dated rates apply correctly at order time (test)                             |
| [ ] T-M6-015 | Wire shipping and tax into `TotalsCalculator` and the checkout summary endpoint                                      | T-M6-005, T-M6-012, T-M5-021 | Multi-country, multi-vendor cart produces correct per-vendor lines (integration test)  |
| [ ] T-M6-016 | Checkout UI: per-vendor shipping method selection with prices and delivery ranges                                    | T-M6-015, T-M1-021           | Changing an address re-quotes without losing other checkout input                      |
| [ ] T-M6-017 | Free-shipping threshold messaging ("$12 away from free shipping") per vendor                                         | T-M6-005                     | Message updates live as the cart changes                                               |
| [ ] T-M6-018 | `TaxAndShippingSeeder` for the launch market                                                                         | T-M6-011, T-M6-002           | Seeded rates produce correct totals for the demo catalog                               |

---

## M7 — Payments, commission & ledger

> **Blocked until Risk R1 (payment-aggregation licensing) is resolved.** Do not start T-M7-001 before that decision is recorded in `docs/adr/0006-payments.md`.

**Goal:** money moves correctly, provably, and only once.
**Exit:** a real Stripe test payment completes via webhook, the ledger balances to zero, and a partial refund reverses exactly the right commission.

| ID           | Task                                                                                                                                         | Dep                    | Done when                                                                              |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------- | -------------------------------------------------------------------------------------- |
| [ ] T-M7-001 | `PaymentGateway` contract + `PaymentIntentData` / `PaymentResult` / `RefundResult` DTOs                                                      | T-M5-001               | Contract documented in `docs/domain/payment.md`; no gateway type leaks into the domain |
| [ ] T-M7-002 | `FakeGateway` driver for tests and local development (configurable success, failure, delay, webhook simulation)                              | T-M7-001               | The full checkout suite runs without network access                                    |
| [ ] T-M7-003 | Migrations + models: `payments`, `payment_transactions`                                                                                      | T-M5-009               | Checks hold: captured ≤ amount, refunded ≤ captured                                    |
| [ ] T-M7-004 | Migration + model: `webhook_events` with `(provider, event_id)` unique replay protection                                                     | T-M0-001               | Replaying the same event twice processes once (test)                                   |
| [ ] T-M7-005 | Migration + model: `payment_methods` (tokenised, no PAN)                                                                                     | T-M2-002               | A test asserts no card-number-shaped data can be persisted                             |
| [ ] T-M7-006 | `StripeGateway` driver: payment intents, confirmation, capture, idempotency keys                                                             | T-M7-001               | Integration test against Stripe test mode passes                                       |
| [ ] T-M7-007 | Stripe webhook controller: signature verification → persist raw → 200 immediately → queue                                                    | T-M7-004, T-M7-006     | An invalid signature is rejected with 400 and logged (test)                            |
| [ ] T-M7-008 | `ProcessStripeWebhook` job handling `payment_intent.succeeded/failed`, `charge.refunded`, `charge.dispute.created`                           | T-M7-007               | Each event type has a feature test using a recorded fixture                            |
| [ ] T-M7-009 | `CashOnDeliveryGateway` driver with per-vendor and per-zone enablement plus an order-value ceiling                                           | T-M7-001, T-M3-001     | COD unavailable above the ceiling or outside enabled zones (test)                      |
| [ ] T-M7-010 | `PayPalGateway` driver + webhook handler                                                                                                     | T-M7-001               | Sandbox order completes end-to-end                                                     |
| [ ] T-M7-011 | `LocalGateway` driver skeleton with a documented extension guide                                                                             | T-M7-001               | A stub driver registers via config without core changes                                |
| [ ] T-M7-012 | `InitiatePayment` action wired into `PlaceOrder` (after commit), with idempotency                                                            | T-M7-003, T-M5-023     | Retried submission does not create a second payment (test)                             |
| [ ] T-M7-013 | Migrations + models: `commission_rules`, `commissions`                                                                                       | T-M5-010               | `CHECK (rate BETWEEN 0 AND 100)` holds                                                 |
| [ ] T-M7-014 | `CommissionResolver`: vendor override → plan → category → global, with effective dates                                                       | T-M7-013               | Resolution order unit-tested for all 16 combinations                                   |
| [ ] T-M7-015 | `FreezeCommission` action — writes rate and amount onto the vendor order at capture, never recomputed later                                  | T-M7-014               | Changing the rule afterwards does not alter historical orders (test)                   |
| [ ] T-M7-016 | Migrations + models: `ledger_accounts`, `ledger_entries` (append-only, transaction groups)                                                   | T-M5-001               | Update/delete on an entry throws (test)                                                |
| [ ] T-M7-017 | `LedgerService::record()` — writes balanced multi-leg movements atomically                                                                   | T-M7-016               | An unbalanced movement is rejected before any write (test)                             |
| [ ] T-M7-018 | Ledger postings for a sale: customer payment → platform clearing → vendor payable + commission income                                        | T-M7-017, T-M7-015     | A 3-vendor order produces balanced groups per vendor (test)                            |
| [ ] T-M7-019 | `CapturePayment` action: commit reservations, decrement stock, freeze commission, post ledger, advance vendor orders, fire `PaymentCaptured` | T-M7-012…018, T-M5-019 | Full integration test from webhook to `awaiting_acceptance`                            |
| [ ] T-M7-020 | `VerifyLedgerBalance` nightly command with a P1 alert on any imbalance                                                                       | T-M7-017               | Deliberately corrupting a row makes the command fail loudly (test)                     |
| [ ] T-M7-021 | Migrations + models: `refunds`, `refund_items`                                                                                               | T-M7-003               | Factories cover full, partial, per-item, and shipping-only                             |
| [ ] T-M7-022 | `RefundPayment` action: gateway refund, ledger reversal, commission reversal, restock option, timeline, notifications                        | T-M7-019, T-M7-021     | Partial refund reverses commission proportionally to the cent (test)                   |
| [ ] T-M7-023 | Refund authorisation thresholds (support ≤ threshold, finance unlimited) enforced by policy                                                  | T-M7-022, T-M2-006     | Support agent exceeding the threshold gets 403 (test)                                  |
| [ ] T-M7-024 | Checkout page: contact → address → per-vendor shipping → gift options → payment → review, with a sticky summary                              | T-M6-016, T-M7-012     | Full keyboard journey completes; validation errors never clear input                   |
| [ ] T-M7-025 | Stripe Elements integration (cards + wallets) with 3DS handling                                                                              | T-M7-006, T-M7-024     | A 3DS-required test card completes successfully                                        |
| [ ] T-M7-026 | Payment result page: optimistic pending state polling `checkout/{uuid}/status` until the webhook lands                                       | T-M7-019, T-M7-024     | Closing the tab mid-flow still results in a correct order (test)                       |
| [ ] T-M7-027 | Order confirmation page: per-vendor summaries, timelines, "message the maker", guest account claim                                           | T-M7-026, T-M5-026     | Guest sees a working tracking link; claim creates an account with the order attached   |
| [ ] T-M7-028 | `AutoCancelUnpaidOrders` scheduled job (60 minutes) releasing reservations                                                                   | T-M5-020, T-M7-012     | Time-travel test confirms cancellation and release                                     |
| [ ] T-M7-029 | Admin: payment and refund detail views with gateway response inspection (redacted)                                                           | T-M7-022, T-M1-021     | No secrets rendered; access is audited                                                 |
| [ ] T-M7-030 | **Payment integration test suite**: success, decline, 3DS, timeout, duplicate webhook, out-of-order webhook, refund after partial refund     | T-M7-001…029           | All scenarios covered; suite runs against `FakeGateway` in CI                          |

---

## M8 — Fulfilment & customer orders

**Goal:** the complete post-purchase lifecycle for both sides. **A sellable marketplace exists at the end of this milestone.**
**Exit:** buy → accept → partial ship → deliver → complete → funds available, demoed end to end.

| ID           | Task                                                                                                                                          | Dep                    | Done when                                                                                             |
| ------------ | --------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------- | ----------------------------------------------------------------------------------------------------- |
| [ ] T-M8-001 | `AcceptVendorOrder` / `RejectVendorOrder` actions with SLA timestamps and reason capture                                                      | T-M5-017, T-M7-019     | Rejection refunds automatically and restocks (test)                                                   |
| [ ] T-M8-002 | `AutoDeclineStaleOrders` job (48h unaccepted) with vendor warning at 24h                                                                      | T-M8-001               | Time-travel test covers warning and auto-decline                                                      |
| [ ] T-M8-003 | `StartProduction` / `MarkReadyToShip` actions with timeline events                                                                            | T-M8-001               | Illegal transitions rejected by the state machine (test)                                              |
| [ ] T-M8-004 | Migrations + models: `shipments`, `shipment_items`, `tracking_events`                                                                         | T-M5-011, T-M6-004     | Shipped quantity can never exceed ordered quantity (database + action test)                           |
| [ ] T-M8-005 | `CreateShipment` action with partial-quantity support and row locking                                                                         | T-M8-004               | Two concurrent shipments of the same item cannot over-ship (test)                                     |
| [ ] T-M8-006 | Fulfilment status derivation (`unfulfilled` / `partially_fulfilled` / `fulfilled`) and vendor-order status sync                               | T-M8-005               | Covering all quantities flips the order to `shipped` (test)                                           |
| [ ] T-M8-007 | `PollTrackingUpdates` scheduled job + carrier webhook endpoint, appending to the timeline                                                     | T-M8-004, T-M6-004     | Duplicate tracking events are idempotent (unique constraint test)                                     |
| [ ] T-M8-008 | `MarkDelivered` (carrier event or customer confirmation) + review-request scheduling                                                          | T-M8-007               | Review request queues 2 days after delivery (test)                                                    |
| [ ] T-M8-009 | `AutoCompleteDeliveredOrders` job (+7 days, no dispute)                                                                                       | T-M8-008               | Disputed orders are skipped (test)                                                                    |
| [ ] T-M8-010 | `ReleaseVendorFunds` job (delivery + vendor hold days) posting ledger entries from pending to available                                       | T-M8-009, T-M7-018     | Balance moves exactly once; re-running is a no-op (test)                                              |
| [ ] T-M8-011 | `CancelOrder` action covering all four initiators with the correct refund and restock rules                                                   | T-M7-022, T-M5-017     | Customer cancel before production is free and automatic; after production it creates a request (test) |
| [ ] T-M8-012 | Cancellation request flow (vendor accept/decline with reason)                                                                                 | T-M8-011               | Declined request notifies the customer and leaves the order intact                                    |
| [ ] T-M8-013 | Vendor order queue UI: tabs, urgency colouring, bulk print, needs-action default                                                              | T-M8-001…006, T-M1-016 | Query budget met with 200 orders; no N+1                                                              |
| [ ] T-M8-014 | Vendor order detail UI: items with personalisation and downloadable customer files, timeline, actions, notes                                  | T-M8-013               | Personalisation files download via signed URLs only (test)                                            |
| [ ] T-M8-015 | Vendor shipment creation UI (item picker for partial, carrier, tracking, "no tracking" path)                                                  | T-M8-005, T-M8-014     | Partial shipment of 1 of 3 units works and reflects on the customer side                              |
| [ ] T-M8-016 | Packing slip PDF (with gift-mode price hiding)                                                                                                | T-M8-014               | Generated on the queue, cached in storage, regenerable                                                |
| [ ] T-M8-017 | Invoice PDF with a gapless per-year numbering sequence                                                                                        | T-M7-019               | Concurrent generation cannot create a duplicate number (locking test)                                 |
| [ ] T-M8-018 | Customer order list UI with filters and status pills                                                                                          | T-M5-025, T-M1-021     | Guest cannot reach another customer's order (test)                                                    |
| [ ] T-M8-019 | Customer order detail UI: per-vendor timelines, tracking steps, item cards, invoice download, actions                                         | T-M8-018               | Timeline shows only customer-visible events (test)                                                    |
| [ ] T-M8-020 | Guest order tracking page (`number` + email, rate-limited)                                                                                    | T-M5-026               | 5 attempts per minute per IP; no enumeration possible (test)                                          |
| [ ] T-M8-021 | Migrations + models: `return_requests`, `return_items` + `RequestReturn` action with eligibility rules                                        | T-M5-011               | Requests outside the return window are rejected (test)                                                |
| [ ] T-M8-022 | Return review flow (approve/reject, return address, received, inspected) → refund                                                             | T-M8-021, T-M7-022     | Approved return with restock returns stock exactly once (test)                                        |
| [ ] T-M8-023 | Return UI: customer wizard (item picker, reason, photo evidence) and vendor review screen                                                     | T-M8-022               | Photo evidence required for `damaged` (test)                                                          |
| [ ] T-M8-024 | **End-to-end order journey test (Playwright)**: browse → configure → multi-vendor checkout → pay → accept → partial ship → deliver → complete | T-M8-001…023           | Runs green in CI against `FakeGateway`                                                                |

---

## M9 — Chat

**Goal:** real-time, order-aware conversation as a first-class part of the product.
**Exit:** two browsers exchange messages live with correct read state; reconnection loses nothing; attachments stay private.

| ID           | Task                                                                                                          | Dep                | Done when                                                                        |
| ------------ | ------------------------------------------------------------------------------------------------------------- | ------------------ | -------------------------------------------------------------------------------- |
| [ ] T-M9-001 | Install and configure Laravel Reverb; Echo client wiring; TLS and process supervision documented              | T-M0-003           | A test event round-trips browser → server → browser locally                      |
| [ ] T-M9-002 | Migrations + models: `conversations`, `conversation_participants`                                             | T-M2-002, T-M3-001 | Polymorphic context resolves to order, vendor order, product, or custom request  |
| [ ] T-M9-003 | Migrations + models: `messages`, `message_attachments`, `message_reads`, `message_reactions`                  | T-M9-002           | `uniq_messages_client` prevents duplicate optimistic sends (test)                |
| [ ] T-M9-004 | `ConversationPolicy` + `MessagePolicy`; channel authorisation reuses them                                     | T-M9-002           | A non-participant cannot subscribe to the private channel (test)                 |
| [ ] T-M9-005 | `StartConversation` action with the one-thread-per-(customer, vendor, context) rule                           | T-M9-002           | Second attempt returns the existing conversation (test)                          |
| [ ] T-M9-006 | `SendMessage` action: policy, rate limit (30/min), sanitisation, persistence, counters, broadcast             | T-M9-004           | Rate-limit test; XSS payload is neutralised (test)                               |
| [ ] T-M9-007 | `MessageSent` / `MessageRead` / `MessageDeleted` broadcast events on `private-conversation.{uuid}`            | T-M9-006, T-M9-001 | Events carry no data the recipient is not authorised to see                      |
| [ ] T-M9-008 | `MarkConversationRead` action + unread counter maintenance (per participant and per user)                     | T-M9-003           | Counters never drift; a reconciliation test proves it after 100 mixed operations |
| [ ] T-M9-009 | Typing indicator via presence-channel whispers (debounced, 3s expiry, never persisted)                        | T-M9-001           | No database writes occur while typing (test asserts query count 0)               |
| [ ] T-M9-010 | Presence and `last_seen_at` in Redis with a 5-minute TTL                                                      | T-M9-009           | Online state clears after disconnect                                             |
| [ ] T-M9-011 | Message cursor pagination endpoint (newest-first, stable across inserts)                                      | T-M9-003           | Paging through 500 messages returns each exactly once (test)                     |
| [ ] T-M9-012 | Attachment upload: presigned URL, MIME/size validation, private disk, queued thumbnail, `scan_status`         | T-M4-028, T-M9-003 | An executable disguised as an image is rejected (test)                           |
| [ ] T-M9-013 | Signed attachment download endpoint authorised by conversation participation                                  | T-M9-012, T-M9-004 | A non-participant with a URL still gets 403 after expiry (test)                  |
| [ ] T-M9-014 | Rich message cards: `product_card`, `order_card`, `quotation_card`, `system` with snapshotted payloads        | T-M9-006           | A later price change does not alter an old card (test)                           |
| [ ] T-M9-015 | Contact-detail detection (phone, email, external links) with `allow`/`warn`/`block` policy                    | T-M9-006           | Each policy mode tested; false-positive rate documented                          |
| [ ] T-M9-016 | Chat UI shell: three-pane layout (list, thread, context sidebar), responsive collapse to a single pane        | T-M1-021, T-M9-011 | Usable at 375px with the keyboard open; composer never covered                   |
| [ ] T-M9-017 | Message list: virtualised, date separators, grouped consecutive messages, read ticks, retry on failure        | T-M9-016           | 5,000-message thread scrolls at 60fps                                            |
| [ ] T-M9-018 | Composer: optimistic send with `client_id`, attachments, emoji picker, product/order attach, offline queue    | T-M9-006, T-M9-014 | Messages sent offline flush on reconnect without duplicates (test)               |
| [ ] T-M9-019 | Reconnection and resync: fetch-after-last-id, merge by uuid, unread re-sync, backoff                          | T-M9-011           | Killing the socket for 60s loses nothing (test)                                  |
| [ ] T-M9-020 | Conversation list: search, filters (unread, order-linked, custom), sorting, unread badges                     | T-M9-016           | Search is scoped to the user's own conversations only (test)                     |
| [ ] T-M9-021 | Message search (fulltext, scoped) with highlighted results and jump-to-message                                | T-M9-020           | Cross-user leakage test passes                                                   |
| [ ] T-M9-022 | Pinned messages (max 5) and reactions                                                                         | T-M9-003           | Sixth pin is rejected with a clear message                                       |
| [ ] T-M9-023 | Auto-created conversations: on order placed, on custom request, on dispute (admin joins with a system notice) | T-M9-005, T-M8-001 | System messages render correctly and are non-interactive                         |
| [ ] T-M9-024 | Unread notification job: email/push after 10 minutes unread, throttled to 1/hour/conversation                 | T-M9-008           | Throttling verified with time travel                                             |
| [ ] T-M9-025 | Block, report, archive, and mute per conversation                                                             | T-M9-004           | A blocked user cannot open a new thread (test)                                   |
| [ ] T-M9-026 | Floating chat launcher available on every page with a live unread badge                                       | T-M9-016, T-M1-020 | Socket survives Inertia navigation (persistent layout test)                      |

---

## M10 — Custom orders

**Goal:** the differentiator — a structured brief-to-quotation-to-order pipeline that reuses the ordinary fulfilment pipeline.
**Exit:** brief → clarification → quotation v2 → accept → paid order, with a complete audit trail.

| ID            | Task                                                                                                                       | Dep                  | Done when                                                                      |
| ------------- | -------------------------------------------------------------------------------------------------------------------------- | -------------------- | ------------------------------------------------------------------------------ |
| [ ] T-M10-001 | Migration + model: `custom_requests` with all brief fields and status enum                                                 | T-M3-001, T-M2-002   | Factory covers every state                                                     |
| [ ] T-M10-002 | `CustomRequestState` machine implementing PROJECT_PLAN §20.2                                                               | T-M10-001            | Exhaustive legal/illegal transition matrix test                                |
| [ ] T-M10-003 | Migrations + models: `quotations`, `quotation_items` with versioning                                                       | T-M10-001            | Creating v2 supersedes v1 and preserves it (test)                              |
| [ ] T-M10-004 | MediaLibrary collections for the brief (inspiration, logo, handwriting, references) on a private disk                      | T-M4-026, T-M10-001  | 10-file / 10 MB limits enforced; files served only to the two parties (test)   |
| [ ] T-M10-005 | `SubmitCustomRequest` action: validation, media confirmation, conversation binding, vendor notification, expiry scheduling | T-M10-001, T-M9-005  | Submission creates exactly one conversation with a system message              |
| [ ] T-M10-006 | `RequestClarification` action posting into the bound thread and moving state                                               | T-M10-002, T-M9-006  | Customer reply returns the request to `under_review` (test)                    |
| [ ] T-M10-007 | `SendQuotation` action: totals from line items, deposit calculation, validity window, notification                         | T-M10-003, T-M5-001  | Quotation totals are money-exact including deposit split (test)                |
| [ ] T-M10-008 | `AcceptQuotation` action → creates `Order` + `VendorOrder` + synthetic line item carrying the full brief and media         | T-M10-007, T-M5-023  | Resulting order flows through the _standard_ pipeline (integration test)       |
| [ ] T-M10-009 | Deposit payments: partial capture at acceptance, balance invoice before shipping                                           | T-M10-008, T-M7-019  | Shipping is blocked until the balance is paid (test)                           |
| [ ] T-M10-010 | `RejectQuotation` / `RequestRevision` actions with reason capture                                                          | T-M10-007            | Revision creates v2 in `draft` for the vendor                                  |
| [ ] T-M10-011 | `ExpireQuotations` and `ExpireStaleRequests` scheduled jobs                                                                | T-M10-002            | An expired quotation cannot be accepted (test); one-click reissue works        |
| [ ] T-M10-012 | `CustomRequestPolicy` / `QuotationPolicy`                                                                                  | T-M10-001, T-M3-019  | Only the requesting customer and the target vendor have access (test)          |
| [ ] T-M10-013 | Customer wizard step 1 — what (title, description, base product, quantity)                                                 | T-M10-005, T-M1-021  | Draft saves per step and is resumable                                          |
| [ ] T-M10-014 | Wizard step 2 — details (colours, materials, size, finishing, packaging, custom text)                                      | T-M10-013            | Vendor-declared options drive the choices offered                              |
| [ ] T-M10-015 | Wizard step 3 — references (multi-upload with previews, reorder, remove)                                                   | T-M10-004, T-M10-013 | Upload failures are recoverable without losing the brief                       |
| [ ] T-M10-016 | Wizard step 4 — budget and date (range, needed-by, flexibility)                                                            | T-M10-013            | Budget below the vendor's minimum warns before submission                      |
| [ ] T-M10-017 | Wizard step 5 — review and submit with a full summary                                                                      | T-M10-016            | Submission is idempotent against double-click                                  |
| [ ] T-M10-018 | Vendor Kanban board (New / Reviewing / Quoted / Accepted / Rejected) with SLA clocks                                       | T-M10-012, T-M1-021  | Drag between columns triggers the correct state action, not a raw status write |
| [ ] T-M10-019 | Vendor request detail: full brief, attachment gallery with download, bound chat, actions                                   | T-M10-018            | Rejecting requires a reason; clarification posts to chat                       |
| [ ] T-M10-020 | Quotation builder UI: line items, types, deposit, validity, estimated completion, attachments, preview                     | T-M10-007, T-M10-019 | Live totals match the server calculation exactly (test)                        |
| [ ] T-M10-021 | Customer quotation viewer: version history, itemised breakdown, accept / reject / request revision                         | T-M10-020            | Accept goes straight into checkout with the deposit or full amount             |
| [ ] T-M10-022 | Customer custom-request list and detail in the account area                                                                | T-M10-021            | Status timeline mirrors the state machine exactly                              |

---

## M11 — Reviews, wishlists & discovery

**Goal:** the trust and re-engagement layer.
**Exit:** a delivered order can be reviewed once, aggregates update, homepage rails populate from real data.

| ID            | Task                                                                                                            | Dep                  | Done when                                                                                               |
| ------------- | --------------------------------------------------------------------------------------------------------------- | -------------------- | ------------------------------------------------------------------------------------------------------- |
| [ ] T-M11-001 | Migration + model: `reviews` (polymorphic, sub-scores, verified purchase)                                       | T-M5-011             | `uniq_reviews_order_item` prevents a second review (test)                                               |
| [ ] T-M11-002 | Migrations + models: `review_replies`, `review_votes`, `content_reports`                                        | T-M11-001            | One vendor reply per review enforced by unique index                                                    |
| [ ] T-M11-003 | `CreateReview` action: delivery-required eligibility, media upload, moderation routing                          | T-M11-001, T-M8-008  | Reviewing an undelivered item is rejected (test)                                                        |
| [ ] T-M11-004 | Rating aggregation listeners: product and vendor `rating_avg`, `rating_count`, `rating_breakdown`               | T-M11-003            | Aggregates match a recomputation from scratch after 100 mixed operations (test)                         |
| [ ] T-M11-005 | 30-day review edit window with an edited marker                                                                 | T-M11-003            | Editing on day 31 is rejected (test)                                                                    |
| [ ] T-M11-006 | Review moderation: profanity filter, report threshold auto-hide, admin queue                                    | T-M11-002            | N reports auto-hide pending review (test)                                                               |
| [ ] T-M11-007 | `ReviewPolicy`                                                                                                  | T-M11-003            | Allow/deny tests for author, vendor, moderator, admin                                                   |
| [ ] T-M11-008 | PDP reviews section: distribution histogram, photo grid, sorting, filtering, helpful votes, vendor replies      | T-M11-004, T-M4-048  | Paginated without N+1; photos lazy-loaded                                                               |
| [ ] T-M11-009 | Review composer UI (stars, sub-scores, text, photo upload with crop)                                            | T-M11-003            | Draft survives an accidental navigation                                                                 |
| [ ] T-M11-010 | Vendor reviews screen: list, filters, reply composer, rating trend chart, sub-score breakdown                   | T-M11-002, T-M1-021  | Reply publishes immediately and is reportable                                                           |
| [ ] T-M11-011 | Customer "awaiting review" queue in the account area                                                            | T-M11-003            | Only delivered, unreviewed items appear                                                                 |
| [ ] T-M11-012 | Migrations + models: `wishlists`, `wishlist_items` (multiple named lists)                                       | T-M2-002, T-M4-011   | Default list auto-created on first save                                                                 |
| [ ] T-M11-013 | Wishlist actions: add, remove, move between lists, create list, share token, public/private                     | T-M11-012            | A private list is 404 for a stranger even with the token (test)                                         |
| [ ] T-M11-014 | Wishlist UI: list management, drag between lists, move to cart, badges                                          | T-M11-013, T-M1-021  | Add-to-wishlist from any product card works while logged out via a sign-in prompt that preserves intent |
| [ ] T-M11-015 | Migration + model: `recently_viewed` with a Redis hot path and durable copy for logged-in users                 | T-M4-048             | Guest→user login merges recently viewed (test)                                                          |
| [ ] T-M11-016 | `TrackRecentlyViewed` middleware and the storefront rail                                                        | T-M11-015            | Adds zero queries to the PDP for guests (test)                                                          |
| [ ] T-M11-017 | Compare tray (up to 4) with an attribute matrix page                                                            | T-M4-052             | State persists across navigation; fifth item prompts a swap                                             |
| [ ] T-M11-018 | `RelatedProductsQuery` (same category, shared tags, same vendor, price band) with caching                       | T-M4-044             | Returns 8 relevant items in under the query budget                                                      |
| [ ] T-M11-019 | `TrendingScoreJob` (hourly velocity-weighted views and sales)                                                   | T-M4-011             | Score decays over time; unit-tested formula                                                             |
| [ ] T-M11-020 | Best sellers, new arrivals, featured queries with caching and tag invalidation                                  | T-M11-019            | Publishing a product purges the right cache tags (test)                                                 |
| [ ] T-M11-021 | Migration + model: `homepage_sections` + rendering engine                                                       | T-M4-008             | Section reorder and scheduling take effect without a deploy                                             |
| [ ] T-M11-022 | Homepage: hero, occasion rail, featured vendors, new arrivals, trending, flash strip, maker stories, newsletter | T-M11-021, T-M11-020 | LCP ≤ 2.0s on throttled mobile in Lighthouse CI                                                         |
| [ ] T-M11-023 | Occasion landing pages `/occasions/{slug}`                                                                      | T-M11-021            | Driven by tags and attributes, no bespoke code per occasion                                             |
| [ ] T-M11-024 | Migration + model: `back_in_stock_subscriptions` + notification job                                             | T-M5-018             | Restocking notifies the waitlist once, in order (test)                                                  |

---

## M12 — Search

**Goal:** typo-tolerant, faceted, fast search that degrades gracefully.
**Exit:** p95 autocomplete under 150 ms; killing Meilisearch degrades to MySQL fulltext instead of erroring.

| ID            | Task                                                                                                                   | Dep                 | Done when                                                                                    |
| ------------- | ---------------------------------------------------------------------------------------------------------------------- | ------------------- | -------------------------------------------------------------------------------------------- |
| [ ] T-M12-001 | Install Scout + Meilisearch; `SearchEngine` contract wrapping Scout so the engine stays replaceable                    | T-M4-011            | Local uses the database driver, CI uses `collection`, staging uses Meilisearch — config only |
| [ ] T-M12-002 | Product index transformer (flat document per PROJECT_PLAN §23.1)                                                       | T-M12-001           | Snapshot test of the document shape for a variable, personalisable product                   |
| [ ] T-M12-003 | Index settings: searchable attribute ranking, filterable and sortable attributes, custom ranking rules, typo tolerance | T-M12-002           | Settings applied by an artisan command and asserted in a test                                |
| [ ] T-M12-004 | Queued, debounced index sync on product save/publish/unpublish/delete                                                  | T-M12-002           | Unpublishing removes the document immediately (test)                                         |
| [ ] T-M12-005 | Vendor, collection, and category indexes                                                                               | T-M12-002           | Each has its own transformer and settings                                                    |
| [ ] T-M12-006 | `ReconcileSearchIndex` nightly command (count check, drift repair)                                                     | T-M12-004           | Deliberately deleting a document is repaired on the next run (test)                          |
| [ ] T-M12-007 | `ProductSearchQuery`: query + facet filters + sorts + pagination, returning a typed result object                      | T-M12-003           | Same result contract regardless of engine (test against two drivers)                         |
| [ ] T-M12-008 | MySQL `FULLTEXT` fallback path with a circuit breaker on engine failure                                                | T-M12-007           | Killing Meilisearch mid-test returns results with a degraded flag, never a 500               |
| [ ] T-M12-009 | Search results page `/search`: grouped results, facets with live counts, sorting, URL state                            | T-M12-007, T-M4-045 | Shareable URL reproduces the exact result set                                                |
| [ ] T-M12-010 | Autocomplete endpoint + UI: grouped products/vendors/categories, recent and trending, keyboard navigation              | T-M12-007, T-M1-018 | p95 under 150 ms with the demo catalog (benchmark test)                                      |
| [ ] T-M12-011 | Zero-result recovery: did-you-mean, filter relaxation with an explanation, trending fallback                           | T-M12-009           | Never renders an empty page without a next action                                            |
| [ ] T-M12-012 | Migration + model: `search_queries` logging (query, results count, click position, filters)                            | T-M12-009           | Logging is queued and adds no latency to the response (test)                                 |
| [ ] T-M12-013 | Migration + model: `search_synonyms` + admin management pushing to the engine on save                                  | T-M12-003           | "epoxy" finds "resin" after saving a synonym (integration test)                              |
| [ ] T-M12-014 | Admin search analytics: top queries, zero-result queries, click-through rate                                           | T-M12-012, T-M1-021 | Zero-result report drives a one-click synonym creation                                       |
| [ ] T-M12-015 | Pinned results and per-category ranking overrides                                                                      | T-M12-013           | A pinned collection appears first for its query (test)                                       |
| [ ] T-M12-016 | In-store search on the vendor storefront (scoped to that vendor)                                                       | T-M12-007, T-M4-050 | Results never leak other vendors' products (test)                                            |

---

## M13 — Notifications

**Goal:** every event reaches the right person on the right channel, always queued, always respecting preferences.
**Exit:** the full catalogue from PROJECT_PLAN §22.2 fires correctly and honours per-channel preferences.

| ID            | Task                                                                                                                              | Dep                  | Done when                                                                    |
| ------------- | --------------------------------------------------------------------------------------------------------------------------------- | -------------------- | ---------------------------------------------------------------------------- |
| [ ] T-M13-001 | Base `CraftiqueNotification` class: preference-aware `via()`, non-disableable transactional flag, queue assignment                | T-M2-003             | A test proves a disabled preference removes only the optional channels       |
| [ ] T-M13-002 | Migration + model: `notification_preferences` with sensible defaults seeded per user                                              | T-M13-001            | New users get defaults automatically (observer test)                         |
| [ ] T-M13-003 | Email layout and component set matching the design system, with plain-text alternatives                                           | T-M1-002             | Renders correctly in Gmail, Outlook, and Apple Mail (documented screenshots) |
| [ ] T-M13-004 | Order notifications: placed, payment failed, accepted, in production, shipped, partially shipped, delivered, completed, cancelled | T-M13-001, T-M8-011  | One feature test per notification asserting recipients and channels          |
| [ ] T-M13-005 | Payment and refund notifications (customer, vendor, finance digest)                                                               | T-M13-004, T-M7-022  | Refund notification includes the exact amount and destination                |
| [ ] T-M13-006 | Vendor operational notifications: new order, low stock digest, product approved/rejected, review received, new follower digest    | T-M13-001            | Digests batch correctly over a 24-hour window (time-travel test)             |
| [ ] T-M13-007 | Custom-order notifications: request received, clarification, quotation sent/accepted/rejected/expiring                            | T-M13-001, T-M10-007 | Expiry warning fires 48 hours before validity ends                           |
| [ ] T-M13-008 | Chat notifications wired to the unread job with throttling                                                                        | T-M9-024, T-M13-001  | Throttle honoured across channels                                            |
| [ ] T-M13-009 | Account and security notifications (non-disableable): new device, password change, 2FA change, payout-method change               | T-M2-015, T-M13-001  | Preference toggles cannot disable these (test)                               |
| [ ] T-M13-010 | Vendor lifecycle notifications: approved, rejected, suspended, verification expiring                                              | T-M3-018, T-M13-001  | Rejection email includes the reason and a resubmit link                      |
| [ ] T-M13-011 | Broadcast channel notifications (`private-user.{uuid}`) driving live bell updates                                                 | T-M9-001, T-M13-001  | Badge updates without a page refresh (browser test)                          |
| [ ] T-M13-012 | Notification centre UI: feed, filters, mark read, mark all read, infinite scroll                                                  | T-M13-011, T-M1-020  | Marking read updates the badge optimistically and reconciles                 |
| [ ] T-M13-013 | Notification preferences UI: per-event × per-channel matrix with bulk toggles                                                     | T-M13-002            | Matrix reflects the real notification registry, not a hardcoded list         |
| [ ] T-M13-014 | Quiet hours (22:00–08:00 local) holding non-urgent push                                                                           | T-M13-011            | Time-zone-aware test across three user time zones                            |
| [ ] T-M13-015 | Migration + model: `notification_templates` + admin editor with variable validation and preview                                   | T-M13-003, T-M1-021  | An unknown variable is rejected before save                                  |
| [ ] T-M13-016 | Failure handling: retries with backoff, dead-letter logging, admin visibility of delivery failures                                | T-M13-001            | A permanently failing notification lands in the failures list, not silence   |

---

## M14 — Vendor analytics, earnings & payouts

**Goal:** vendors can see and trust their numbers, and get their money.
**Exit:** revenue net of commission matches the ledger to the cent; a payout completes end to end.

| ID            | Task                                                                                                      | Dep                 | Done when                                                                        |
| ------------- | --------------------------------------------------------------------------------------------------------- | ------------------- | -------------------------------------------------------------------------------- |
| [ ] T-M14-001 | Migration + model: `analytics_events` (monthly partitioning, retention policy)                            | T-M0-001            | Partition creation is automated; pruning job tested                              |
| [ ] T-M14-002 | `TrackEvent` action + middleware for page views, product views, add-to-cart, checkout steps, purchase     | T-M14-001           | Adds ≤ 1 queued dispatch per request, zero synchronous queries (test)            |
| [ ] T-M14-003 | Migrations + models: `vendor_daily_stats`, `product_daily_stats`, `platform_daily_stats`                  | T-M14-001           | Unique per (entity, date)                                                        |
| [ ] T-M14-004 | Nightly rollup jobs with idempotent re-runs and backfill support                                          | T-M14-003           | Re-running a day produces identical numbers (test)                               |
| [ ] T-M14-005 | `VendorSalesReportQuery` with date range, comparison period, and grouping                                 | T-M14-003           | Query runs on the read replica; under 500 ms for a year of data                  |
| [ ] T-M14-006 | Vendor dashboard KPI row (revenue, orders, AOV, conversion) with sparklines and deltas                    | T-M14-005, T-M3-022 | Numbers reconcile against a manual ledger query (test)                           |
| [ ] T-M14-007 | Vendor "needs action" panel aggregating orders, requests, messages, stock, payouts, moderation            | T-M8-013, T-M10-018 | Single query budget; counts match reality (test)                                 |
| [ ] T-M14-008 | Revenue chart with period toggle and comparison overlay (Recharts, lazy-loaded)                           | T-M14-005           | Chart chunk is not in the storefront bundle (bundle test)                        |
| [ ] T-M14-009 | Vendor analytics: traffic sources, devices, geography                                                     | T-M14-002           | Data respects privacy settings; no raw PII                                       |
| [ ] T-M14-010 | Vendor analytics: conversion funnel (view → cart → checkout → purchase)                                   | T-M14-002           | Funnel numbers match event counts exactly (test)                                 |
| [ ] T-M14-011 | Vendor analytics: product performance table and customer repeat-rate report                               | T-M14-005           | CSV export matches the on-screen data                                            |
| [ ] T-M14-012 | Store health score and onboarding checklist computation                                                   | T-M3-022, T-M14-005 | Score components documented and unit-tested                                      |
| [ ] T-M14-013 | `VendorEarningsQuery`: pending, available, paid balances derived from the ledger                          | T-M7-018            | Balances equal a from-scratch ledger recomputation (property test)               |
| [ ] T-M14-014 | Earnings UI: balance cards, per-order commission breakdown, filters                                       | T-M14-013, T-M1-021 | Every row links to its ledger entries                                            |
| [ ] T-M14-015 | Vendor statement PDF/CSV for a period                                                                     | T-M14-013           | Statement totals reconcile with the ledger (test)                                |
| [ ] T-M14-016 | Migration + model: `payout_methods` with encrypted details and verification status                        | T-M3-013            | Changing details requires re-authentication + 2FA and freezes payouts 24h (test) |
| [ ] T-M14-017 | Migrations + models: `payouts`, `payout_items`                                                            | T-M14-013           | A vendor order can appear in at most one active payout (locking test)            |
| [ ] T-M14-018 | `RequestPayout` action: minimum threshold, available balance, no open disputes, locks the included orders | T-M14-017           | Requesting twice concurrently creates one payout (test)                          |
| [ ] T-M14-019 | `ApprovePayout` / `MarkPayoutPaid` / `FailPayout` actions with ledger settlement                          | T-M14-018, T-M7-017 | Settlement posts balanced entries; failure returns funds to available (test)     |
| [ ] T-M14-020 | Vendor payout UI: request flow, history, status, statement download                                       | T-M14-019, T-M1-021 | Ineligible reasons are explained precisely, never a generic error                |
| [ ] T-M14-021 | Admin finance: payout queue, batch approve, bank-file export, reconciliation view                         | T-M14-019, T-M1-021 | Export format documented in `docs/runbooks/payouts.md`                           |
| [ ] T-M14-022 | Admin ledger explorer: account balances, entry search, transaction-group drill-down, imbalance alarm      | T-M7-020, T-M1-021  | Every displayed balance recomputes live and matches the cached value             |

---

## M15 — Admin & platform operations

**Goal:** the platform is fully operable without touching the database or tinker.
**Exit:** every moderation, support, content, and configuration action has a UI, and each is audited.

| ID            | Task                                                                                                                   | Dep                  | Done when                                                                                        |
| ------------- | ---------------------------------------------------------------------------------------------------------------------- | -------------------- | ------------------------------------------------------------------------------------------------ |
| [ ] T-M15-001 | Admin dashboard: live KPIs, GMV chart, queue counts with SLA ages, system health panel                                 | T-M14-004, T-M1-021  | Loads under 500 ms using rollups only, never raw scans                                           |
| [ ] T-M15-002 | Admin user management: list, detail (orders, reviews, chats, devices, sessions), suspend, reset 2FA                    | T-M2-020             | Suspension terminates active sessions immediately (test)                                         |
| [ ] T-M15-003 | Impersonation: start (reason required), 30-minute limit, persistent banner, payment/payout actions blocked, stop       | T-M15-002            | `impersonation_logs` records start and end; a test proves payout actions 403 while impersonating |
| [ ] T-M15-004 | Admin vendor management: list, detail with full activity, suspend, feature, commission override, subscription override | T-M3-018, T-M14-013  | Suspension blocks new orders but preserves fulfilment of existing ones (test)                    |
| [ ] T-M15-005 | Admin order management: advanced filters, detail with payment/refund/chat/timeline context                             | T-M8-019             | Cross-vendor view is admin-only and audited                                                      |
| [ ] T-M15-006 | Admin force-status and manual refund with mandatory reason                                                             | T-M15-005, T-M7-023  | Both write audit entries and timeline events (test)                                              |
| [ ] T-M15-007 | Migration + model: `moderation_queue` with SLA tracking and assignment                                                 | T-M4-043, T-M11-006  | Queue ages and assignment survive a worker restart                                               |
| [ ] T-M15-008 | Unified moderation UI (products, reviews, vendors, reports) with templated reasons and bulk actions                    | T-M15-007            | Each queue reaches decision in ≤ 3 clicks                                                        |
| [ ] T-M15-009 | Migration + model: `disputes` + dispute workflow actions (open, assign, escalate, resolve)                             | T-M8-019, T-M9-023   | Resolution with a partial refund posts the correct ledger entries (test)                         |
| [ ] T-M15-010 | Dispute resolution UI: order, payment, chat, and evidence on one screen                                                | T-M15-009            | Admin joining the conversation posts a visible system notice                                     |
| [ ] T-M15-011 | Migration + model: `content_reports` handling with actions and outcomes                                                | T-M11-002            | Actioning a report hides content and notifies the owner                                          |
| [ ] T-M15-012 | Migration + model: `settings` (grouped key-value) + typed accessor with caching                                        | T-M0-012             | Setting changes purge the cache and are audited with before/after (test)                         |
| [ ] T-M15-013 | Admin settings UI: general, commerce, payments, shipping, media, email, search, chat, legal                            | T-M15-012            | Secret values are write-only and never rendered back                                             |
| [ ] T-M15-014 | Migration + model: `feature_flags` + `Feature::active()` helper with percentage rollout                                | T-M15-012            | Flag flip takes effect without a deploy (test)                                                   |
| [ ] T-M15-015 | Install `spatie/laravel-activitylog`; `Auditable` trait on all financial and permission-bearing models                 | T-M0-010             | Every model change records causer, IP, and before/after (test)                                   |
| [ ] T-M15-016 | Admin audit log viewer with filters (actor, subject, date, action) and diff rendering                                  | T-M15-015            | Cannot be edited or deleted from the UI                                                          |
| [ ] T-M15-017 | CMS: `pages` migration, model, admin editor, and public rendering with SEO fields                                      | T-M0-001             | Draft/publish/schedule all work; slug collisions rejected                                        |
| [ ] T-M15-018 | CMS: `banners` with placements, scheduling, and impression/click tracking                                              | T-M15-017            | Expired banners disappear without a deploy                                                       |
| [ ] T-M15-019 | CMS: `faqs` with categories and helpful votes                                                                          | T-M15-017            | Public FAQ page with search                                                                      |
| [ ] T-M15-020 | CMS: homepage merchandising UI (reorder sections, schedule, preview)                                                   | T-M11-021, T-M15-017 | Preview renders exactly what will publish                                                        |
| [ ] T-M15-021 | Migration + model: `redirects` + middleware for handle/slug changes                                                    | T-M4-041             | Changing a published slug creates a 301 automatically (test)                                     |
| [ ] T-M15-022 | Admin support inbox: all conversations, filters, assignment, canned responses                                          | T-M9-020             | Admin read access to a private thread is audited (test)                                          |
| [ ] T-M15-023 | Admin analytics: GMV, funnel, cohort retention, vendor leaderboard, category performance                               | T-M14-004            | All served from rollups; each has a CSV export                                                   |
| [ ] T-M15-024 | Platform health page: queue depth, failed jobs, webhook failures, index lag, error rate, backup status                 | T-M0-003, T-M12-006  | Each metric has a documented alert threshold                                                     |
| [ ] T-M15-025 | Install Horizon and Pulse (production-gated), linked from the health page                                              | T-M0-003             | Access restricted to admins via gate (test)                                                      |
| [ ] T-M15-026 | GDPR: data export job (queued, emailed as a signed archive)                                                            | T-M2-021             | Export contains every table holding the user's PII (checklist test)                              |
| [ ] T-M15-027 | GDPR: erasure job with financial-record retention and anonymisation                                                    | T-M2-021             | Orders survive anonymised; reviews become "Deleted user" (test)                                  |
| [ ] T-M15-028 | Cookie consent banner + preference storage, gating analytics scripts                                                   | T-M14-002            | No analytics events fire before consent (test)                                                   |

---

## M16 — Promotions & growth

**Goal:** the levers that grow GMV, with correct multi-vendor money allocation.
**Exit:** a stacked platform + vendor coupon on a multi-vendor cart allocates and settles exactly.

| ID            | Task                                                                                                                    | Dep                  | Done when                                                                         |
| ------------- | ----------------------------------------------------------------------------------------------------------------------- | -------------------- | --------------------------------------------------------------------------------- |
| [ ] T-M16-001 | Migration + model: `coupons` with all types, scopes, and constraints                                                    | T-M5-001             | Checks hold; factory covers every type                                            |
| [ ] T-M16-002 | Migrations: `coupon_products`, `coupon_categories`, `coupon_collections`, `coupon_users` with include/exclude semantics | T-M16-001            | Exclusion beats inclusion (test)                                                  |
| [ ] T-M16-003 | Migration + model: `coupon_usages` with transactional counter increments under lock                                     | T-M16-001            | Concurrent redemption of the last use: exactly one succeeds (test)                |
| [ ] T-M16-004 | `CouponValidator`: scope, dates, min spend, caps, per-user limits, first-order-only, stackability                       | T-M16-002            | Each rule has a dedicated unit test                                               |
| [ ] T-M16-005 | `DiscountEngine`: applies validated coupons to cart lines and allocates across vendors                                  | T-M16-004, T-M5-022  | Platform-funded vs vendor-funded allocation posts different ledger entries (test) |
| [ ] T-M16-006 | Coupon UI: cart/checkout entry with precise error messaging, auto-apply support                                         | T-M16-005, T-M7-024  | Invalid coupon explains exactly why, never "invalid code"                         |
| [ ] T-M16-007 | Vendor coupon management UI (create, restrict, schedule, track usage)                                                   | T-M16-001, T-M1-021  | A vendor coupon cannot target another vendor's products (test)                    |
| [ ] T-M16-008 | Admin platform coupon management with funding source selection                                                          | T-M16-007            | Funding choice is reflected in vendor payouts (test)                              |
| [ ] T-M16-009 | Migrations + models: `flash_sales`, `flash_sale_items` with quantity caps                                               | T-M4-016             | Selling past the cap is impossible under concurrency (test)                       |
| [ ] T-M16-010 | Flash sale pricing resolution in cart and checkout (beats compare-at, respects per-user limits)                         | T-M16-009, T-M5-021  | Price at checkout matches the price shown on the PDP (test)                       |
| [ ] T-M16-011 | Flash sale UI: storefront strip, dedicated page, countdown, stock progress                                              | T-M16-010            | Countdown is server-time-anchored, not client-clock-dependent                     |
| [ ] T-M16-012 | Migrations + models: `gift_cards`, `gift_card_transactions` (hashed codes)                                              | T-M5-001             | Balance can never go negative (database check + test)                             |
| [ ] T-M16-013 | Gift card purchase, delivery scheduling, and redemption at checkout with partial balance                                | T-M16-012, T-M7-019  | Partial redemption plus card payment splits correctly across the ledger (test)    |
| [ ] T-M16-014 | Migrations + models: `loyalty_accounts`, `loyalty_transactions`                                                         | T-M2-002             | Points ledger balances like the money ledger (property test)                      |
| [ ] T-M16-015 | Loyalty earn on delivery, redeem at checkout, expiry job, tier calculation                                              | T-M16-014, T-M8-008  | Redeemed points are refunded when an order is refunded (test)                     |
| [ ] T-M16-016 | Migration + model: `referrals` + referral link, attribution, qualification, and reward issuance                         | T-M2-002             | Self-referral and circular referral are blocked (test)                            |
| [ ] T-M16-017 | Loyalty and referral UI in the customer account                                                                         | T-M16-015, T-M16-016 | Balances and history match the ledger                                             |
| [ ] T-M16-018 | Abandoned cart recovery: detection job and 1h/24h/72h email sequence with a resume link                                 | T-M5-004, T-M13-003  | Sequence stops on purchase or unsubscribe (test)                                  |
| [ ] T-M16-019 | Migration + model: `newsletter_subscribers` with double opt-in and unsubscribe                                          | T-M13-003            | Unsubscribe link works without login (test)                                       |
| [ ] T-M16-020 | Vendor follower broadcast ("new drop") with a 1/vendor/day throttle                                                     | T-M3-004, T-M13-006  | Throttle enforced across products published in a batch (test)                     |
| [ ] T-M16-021 | Featured placement: paid vendor/product slots with a scheduling calendar and inventory control                          | T-M3-001, T-M15-020  | Double-booking a slot is impossible (test)                                        |
| [ ] T-M16-022 | Gift finder quiz (occasion → recipient → budget → style → results)                                                      | T-M12-007, T-M11-023 | Results derive from real attributes; shareable result URL                         |

---

## M17 — Hardening & launch

**Goal:** prove the system is safe, fast, observable, recoverable, and operable — then ship it.
**Exit:** every budget in PROJECT_PLAN §8 met, restore drill passed, launch checklist signed.

| ID            | Task                                                                                                                                      | Dep                 | Done when                                                              |
| ------------- | ----------------------------------------------------------------------------------------------------------------------------------------- | ------------------- | ---------------------------------------------------------------------- |
| [ ] T-M17-001 | Security review against the full PROJECT_PLAN §24 threat model, with a written finding-by-finding response                                | M0–M16              | Every threat row has a linked mitigation test or an accepted-risk note |
| [ ] T-M17-002 | Automated authorization sweep: every route asserted against every role (allow/deny matrix)                                                | T-M3-021            | A newly added unprotected route fails the suite                        |
| [ ] T-M17-003 | Security headers, CSP with nonces, HSTS preload, cookie flags                                                                             | T-M17-001           | securityheaders.com grade A; CSP has no `unsafe-inline` for scripts    |
| [ ] T-M17-004 | Rate limiting audit across all surfaces (web, API, webhooks, broadcast auth)                                                              | T-M17-001           | Each limit is tested and documented                                    |
| [ ] T-M17-005 | Third-party penetration test and remediation of findings                                                                                  | T-M17-001…004       | All high and critical findings closed; report archived                 |
| [ ] T-M17-006 | Load test: catalog browse, search, checkout, chat at 10× expected launch traffic                                                          | T-M12-010, T-M9-017 | p95 budgets met; bottlenecks documented in `docs/runbooks/scaling.md`  |
| [ ] T-M17-007 | Query audit: slow-query log review, missing index sweep, N+1 sweep with `preventLazyLoading` in staging                                   | T-M17-006           | Zero queries above 200 ms on the demo dataset                          |
| [ ] T-M17-008 | Lighthouse CI on home, catalog, PDP, vendor storefront, cart, checkout, with budgets enforced                                             | T-M11-022           | Build fails on regression beyond the budget                            |
| [ ] T-M17-009 | Bundle audit and code-split verification (no dashboard or chart code in the storefront bundle)                                            | T-M14-008           | Storefront first-paint JS ≤ 180 KB gzipped                             |
| [ ] T-M17-010 | Full accessibility pass: axe on every key journey, manual keyboard pass, NVDA/VoiceOver pass, accessibility statement page                | T-M1-024            | Zero critical violations; findings log published                       |
| [ ] T-M17-011 | SSR verification for all public routes + crawler rendering check                                                                          | T-M0-004            | View-source contains full content on every public page                 |
| [ ] T-M17-012 | Structured data for Product, Review, Organization, LocalBusiness, Breadcrumb, FAQ, WebSite                                                | T-M17-011           | Google Rich Results test passes for each type                          |
| [ ] T-M17-013 | Sitemap generation (index + per-type, nightly), `robots.txt`, canonical and `noindex` audit                                               | T-M17-011           | Sitemaps validate; account/checkout/admin excluded                     |
| [ ] T-M17-014 | Auto-generated OG images per product and vendor                                                                                           | T-M4-027            | Social preview renders correctly on Twitter, Facebook, WhatsApp        |
| [ ] T-M17-015 | Observability: Sentry, structured JSON logs with a correlation id spanning HTTP → queue → webhook, uptime monitors on checkout            | T-M15-024           | A test exception appears in Sentry with full request context           |
| [ ] T-M17-016 | Alerting: payment success rate, queue depth, failed jobs, webhook failures, p95 latency, ledger imbalance                                 | T-M17-015, T-M7-020 | Each alert fires in a deliberate failure drill                         |
| [ ] T-M17-017 | Backups: `spatie/laravel-backup` scheduled, encrypted, off-site + **a documented restore drill actually performed**                       | T-M0-001            | Restore into a clean environment succeeds and is timed against the RTO |
| [ ] T-M17-018 | Runbooks: deploy, rollback, incident response, payout reconciliation, restore, scaling, on-call                                           | T-M17-017           | Each is followed once by someone other than the author                 |
| [ ] T-M17-019 | Zero-downtime deploy pipeline: expand/contract migrations, queue draining, asset versioning, health check, rollback                       | T-M0-013            | A deploy runs with no dropped requests under load                      |
| [ ] T-M17-020 | Launch: production provisioning, DNS/TLS, seed production data (roles, settings, categories, tax, plans), smoke tests, go/no-go checklist | T-M17-001…019       | Checklist signed; first real order placed and fulfilled successfully   |

---

## Deferred (v1.1 / v2)

Tracked here so nothing is lost. **Do not start these** until v1 has launched and the roadmap is re-approved.

| ID        | Item                                                                     | From  |
| --------- | ------------------------------------------------------------------------ | ----- |
| [-] D-001 | PWA: manifest, service worker, offline shell, background sync for chat   | §31   |
| [-] D-002 | Web Push notification channel                                            | §31   |
| [-] D-003 | SMS / WhatsApp notification driver                                       | §22   |
| [-] D-004 | Carrier API drivers: FedEx, UPS, DHL (rates, labels, tracking)           | §31   |
| [-] D-005 | Product bulk import/export (CSV/XLSX)                                    | §5.2  |
| [-] D-006 | Product Q&A UI (schema shipped in T-M4-054)                              | §5.2  |
| [-] D-007 | 360° spin viewer                                                         | §5.2  |
| [-] D-008 | Digital product delivery UI (schema shipped in T-M4-019)                 | §5.2  |
| [-] D-009 | Live personalisation preview rendered on the product photo               | §5.5  |
| [-] D-010 | Production milestones with photo approval gates                          | §5.5  |
| [-] D-011 | Vendor quick replies, auto-reply, business hours                         | §5.8  |
| [-] D-012 | Vendor subscription plans + Cashier billing                              | §5.1  |
| [-] D-013 | Price-drop alerts on wishlisted items                                    | §5.3  |
| [-] D-014 | Recommended products (co-purchase model)                                 | §5.3  |
| [-] D-015 | Exchanges (distinct from returns)                                        | §5.7  |
| [-] D-016 | Custom domains per vendor storefront                                     | §5.1  |
| [-] D-017 | Native mobile apps on `/api/v1`                                          | §31   |
| [-] D-018 | Multi-currency and multi-language storefronts                            | §31   |
| [-] D-019 | Voice notes and video calls                                              | §31   |
| [-] D-020 | AI assistant (reply drafts, brief summarisation, description generation) | §31   |
| [-] D-021 | Visual / image search                                                    | §31   |
| [-] D-022 | Laravel Octane evaluation                                                | §26.2 |

---

## Critical path

If schedule pressure appears, this is the chain that cannot be shortened:

```
T-M0-001 → T-M1-002 → T-M2-002 → T-M3-001 → T-M4-011 → T-M4-016
   → T-M5-001 → T-M5-002 → T-M5-023 → T-M6-015
   → T-M7-019 → T-M7-022 → T-M8-005 → T-M8-024
```

**Never cut:** T-M5-017 (state machine), T-M5-019 (stock locking), T-M5-027/028 (concurrency + money invariants), T-M7-017/020 (ledger + balance verification), T-M17-017 (restore drill).
**Cut first if needed:** M16 entirely, then M12 advanced tuning (T-M12-013…015), then M14 analytics depth (T-M14-009…012).

---

## Next action

**Start with `T-M0-001` — switch the database connection to MySQL 8.**

Per the working agreement: I will explain what and why, implement, test, fix, mark it `[x]`, leave it commit-ready, then stop for approval.

_End of TASKS.md_
