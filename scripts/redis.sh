#!/usr/bin/env bash
# Craftique - Redis dev instance control (port 6379)
# Usage: scripts/redis.sh [start|stop|status|cli]
# See docs/runbooks/environment.md
set -euo pipefail

REDIS_HOME="/c/redis"
CONF="C:/redis/craftique.conf"
PORT=6379

is_up() { "${REDIS_HOME}/redis-cli.exe" -p "${PORT}" ping >/dev/null 2>&1; }

case "${1:-status}" in
  start)
    if is_up; then echo "Redis already running on port ${PORT}."; exit 0; fi
    echo "Starting Redis on port ${PORT} ..."
    "${REDIS_HOME}/redis-server.exe" "${CONF}" &
    for _ in $(seq 1 10); do is_up && break; sleep 1; done
    is_up && echo "Redis is UP on port ${PORT}." || { echo "Failed to start; see C:/redis/redis.log"; exit 1; }
    ;;
  stop)
    echo "Stopping Redis on port ${PORT} ..."
    "${REDIS_HOME}/redis-cli.exe" -p "${PORT}" shutdown nosave || true
    ;;
  status)
    if is_up; then
      "${REDIS_HOME}/redis-cli.exe" -p "${PORT}" info server | tr -d '\r' | grep -E "redis_version|tcp_port"
      echo "Redis is UP on port ${PORT}."
    else
      echo "Redis is DOWN on port ${PORT}.  Run: scripts/redis.sh start"
      exit 1
    fi
    ;;
  cli)
    "${REDIS_HOME}/redis-cli.exe" -p "${PORT}"
    ;;
  *)
    echo "Usage: scripts/redis.sh [start|stop|status|cli]" >&2
    exit 1
    ;;
esac
