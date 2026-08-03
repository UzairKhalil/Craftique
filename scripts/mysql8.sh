#!/usr/bin/env bash
# Craftique - MySQL 8.4 LTS dev instance control (port 3307)
# Usage: scripts/mysql8.sh [start|stop|status|cli]
# See docs/runbooks/environment.md
set -euo pipefail

MYSQL_HOME="/c/mysql8"
MYSQL_BIN="${MYSQL_HOME}/mysql-8.4.10-winx64/bin"
DEFAULTS="--defaults-file=C:/mysql8/my.ini"
PORT=3307
CONN=(-u root -P "${PORT}" -h 127.0.0.1 --default-character-set=utf8mb4)

is_up() { "${MYSQL_BIN}/mysqladmin.exe" "${CONN[@]}" ping >/dev/null 2>&1; }

case "${1:-status}" in
  start)
    if is_up; then echo "MySQL 8 already running on port ${PORT}."; exit 0; fi
    echo "Starting MySQL 8.4 on port ${PORT} ..."
    "${MYSQL_BIN}/mysqld.exe" ${DEFAULTS} &
    for _ in $(seq 1 15); do is_up && break; sleep 1; done
    is_up && echo "MySQL 8 is UP on port ${PORT}." || { echo "Failed to start; see C:/mysql8/mysql-error.log"; exit 1; }
    ;;
  stop)
    echo "Stopping MySQL 8 on port ${PORT} ..."
    "${MYSQL_BIN}/mysqladmin.exe" "${CONN[@]}" shutdown
    ;;
  status)
    if is_up; then
      "${MYSQL_BIN}/mysql.exe" "${CONN[@]}" -e "SELECT VERSION() AS version, @@port AS port;"
      echo "MySQL 8 is UP on port ${PORT}."
    else
      echo "MySQL 8 is DOWN on port ${PORT}.  Run: scripts/mysql8.sh start"
      exit 1
    fi
    ;;
  cli)
    "${MYSQL_BIN}/mysql.exe" "${CONN[@]}" craftique
    ;;
  *)
    echo "Usage: scripts/mysql8.sh [start|stop|status|cli]" >&2
    exit 1
    ;;
esac
