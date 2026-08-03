# ADR-0009 — MySQL 8 in every environment

- **Status:** Accepted
- **Date:** 2026-08-03
- **Deciders:** Project owner
- **Supersedes:** the Laravel skeleton default (SQLite)
- **Related tasks:** T-M0-001, T-M0-001a

## Context

The Laravel 12 skeleton shipped with `DB_CONNECTION=sqlite`. The schema designed in
[PROJECT_PLAN.md §11](../PROJECT_PLAN.md#11-database-design) depends on database behaviour that
SQLite either does not have or silently fakes:

- `CHECK` constraints (money non-negativity, `reserved_quantity <= stock_quantity`, rating bounds)
- Generated columns with indexes (`cart_items.personalization_hash`, the one-owner-per-vendor constraint)
- InnoDB `FULLTEXT` (search fallback when Meilisearch is unavailable)
- Window functions (analytics rollups and reports)
- Row-level locking with `SELECT … FOR UPDATE SKIP LOCKED` (stock reservation, queue workers)

Building on SQLite means a broken migration passes CI and fails in production.

The development machine runs XAMPP, which ships **MariaDB 10.4.32** rather than MySQL. A capability
probe was run against it:

| Requirement | MariaDB 10.4.32 | MySQL 8.4.10 |
|---|---|---|
| `CHECK` constraints enforced | ✅ | ✅ |
| Generated columns + index | ✅ | ✅ |
| Window functions | ✅ | ✅ |
| InnoDB `FULLTEXT` | ✅ | ✅ |
| `utf8mb4_0900_ai_ci` collation | ❌ absent | ✅ |
| `FOR UPDATE SKIP LOCKED` | ❌ (MariaDB 10.6+) | ✅ |

MariaDB 10.4 also reached **end of life in June 2024** and receives no security updates.

## Decision

**MySQL 8.4 LTS in every environment** — local, CI, staging, production. SQLite and MariaDB are
both unsupported.

Locally, MySQL 8.4.10 runs from a ZIP install at `C:\mysql8` on **port 3307**, alongside XAMPP's
MariaDB on 3306. XAMPP's instance is left untouched so unrelated projects on this machine keep
working.

Connection defaults: `utf8mb4` / `utf8mb4_0900_ai_ci` / `InnoDB`, all env-driven
(`DB_CHARSET`, `DB_COLLATION`, `DB_ENGINE`).

## Alternatives considered

**Upgrade XAMPP to MariaDB 11.4 LTS.** Would close the `SKIP LOCKED` and EOL gaps and avoid running
two servers, but forfeits MySQL-8-specific collation and JSON behaviour, and makes production
MariaDB too. Rejected: no advantage over MySQL 8 for this workload, and it would mean editing the
shared XAMPP install that other projects on this machine depend on.

**Develop on MariaDB 10.4, deploy to MySQL 8.** Fastest today, but reintroduces exactly the dev/prod
divergence this ADR exists to eliminate, on an EOL server. Rejected.

**Docker Desktop with a MySQL 8 container.** Clean and reproducible, but Docker is not installed on
this machine and it is a much heavier dependency than a 268 MB ZIP.

## Consequences

**Positive**

- Dev, CI, and production run identical database semantics.
- The full locking toolkit is available, which the stock-reservation and payout work (M5, M7) depends on.
- The ZIP install needs no administrator rights and is removed by deleting `C:\mysql8`.

**Negative**

- The test suite now runs against a real server rather than SQLite `:memory:`. Suite time went from
  ~8.8s to ~26.9s on the empty skeleton. This is the accepted cost of catching schema errors before
  production; CI uses a MySQL service container.
- MySQL 8 must be running before `artisan`, tests, or the dev server will work. It is not registered
  as a Windows service (that needs admin), so it is started via `scripts/mysql8.bat start`.
  See [docs/runbooks/environment.md](../runbooks/environment.md).
- Two database servers run on the machine. Mitigated by distinct ports and a documented runbook.

## Compliance

`PROJECT_PLAN.md §11.0` specified `utf8mb4_0900_ai_ci`; this is now satisfied. No amendment to the
schema conventions is required.
