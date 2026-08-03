# Runbook — Local development environment

Craftique targets **MySQL 8.4 LTS** in every environment ([ADR-0009](../adr/0009-mysql-8-everywhere.md)).
SQLite and MariaDB are unsupported.

## Services and ports

| Service | Port | Where | Notes |
|---|---|---|---|
| **MySQL 8.4.10** (Craftique) | **3307** | `C:\mysql8` | ZIP install, no Windows service, no admin rights |
| MariaDB 10.4 (XAMPP) | 3306 | `C:\xampp\mysql` | **Not used by Craftique.** Left running for other projects on this machine |
| Apache / PHP 8.2 (XAMPP) | 80 | `C:\xampp` | Or use `php artisan serve` on 8000 |
| Redis | 6379 | — | Cache, session, queue, locks (T-M0-003) |

MySQL 8's X protocol is disabled (`mysqlx = OFF`) so nothing contends on 33060.

## Starting and stopping MySQL 8

MySQL 8 is **not** registered as a Windows service — that requires administrator rights. Start it
manually at the beginning of a work session. `artisan`, the test suite, and the dev server all fail
without it.

```bash
scripts/mysql8.sh start     # Git Bash
scripts/mysql8.sh status
scripts/mysql8.sh stop
scripts/mysql8.sh cli       # opens a client on the craftique database
```

```bat
scripts\mysql8.bat start    REM cmd.exe / double-click
scripts\mysql8.bat status
```

Error log: `C:\mysql8\mysql-error.log`

### Optional: run it as a Windows service

Removes the need to start it manually. Requires an **elevated** shell:

```bat
C:\mysql8\mysql-8.4.10-winx64\bin\mysqld.exe --install craftique-mysql8 --defaults-file=C:\mysql8\my.ini
net start craftique-mysql8
```

To undo: `net stop craftique-mysql8` then `mysqld.exe --remove craftique-mysql8`.

## Databases

| Database | Purpose | Collation |
|---|---|---|
| `craftique` | Development | `utf8mb4_0900_ai_ci` |
| `craftique_test` | Test suite (`phpunit.xml`) | `utf8mb4_0900_ai_ci` |

Credentials for local development are `root` with **no password**, matching the XAMPP convention.
The instance binds to `127.0.0.1` only and is never reachable off the machine.

## First-time setup on a new machine

```bash
git clone <repo> && cd craftique
composer install
cp .env.example .env
php artisan key:generate
# install MySQL 8.4 per ADR-0009, then:
scripts/mysql8.sh start
mysql -u root -P 3307 -h 127.0.0.1 -e "CREATE DATABASE craftique CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci; CREATE DATABASE craftique_test CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;"
php artisan migrate
npm install && npm run build
```

## Verifying the environment

```bash
php artisan tinker --execute="\$c=DB::connection(); echo \$c->select('select version() v')[0]->v.' on '.\$c->select('select @@port p')[0]->p.PHP_EOL;"
# expected: 8.4.10 on 3307
php artisan test
```

## Troubleshooting

| Symptom | Cause | Fix |
|---|---|---|
| `SQLSTATE[HY000] [2002]` connection refused | MySQL 8 not running | `scripts/mysql8.sh start` |
| Connects but tables are missing / schema looks old | Pointed at MariaDB on 3306 | Check `DB_PORT=3307` in `.env`, then `php artisan config:clear` |
| `Unknown collation: 'utf8mb4_0900_ai_ci'` | Connected to MariaDB | Same as above — MariaDB has no such collation |
| Config changes ignored | Cached config | `php artisan config:clear` |
| Port 3307 already in use | Stale `mysqld.exe` | `scripts/mysql8.sh stop`, or end the process in Task Manager |

## Removing the MySQL 8 install

Stop it, then delete `C:\mysql8`. Nothing else on the machine is affected — XAMPP is untouched.
