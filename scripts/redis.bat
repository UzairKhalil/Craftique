@echo off
REM Craftique - Redis dev instance control (port 6379)
REM Usage: scripts\redis.bat [start|stop|status|cli]
REM See docs/runbooks/environment.md

setlocal
set REDIS_HOME=C:\redis
set CONF=%REDIS_HOME%\craftique.conf
set PORT=6379

if "%1"=="" goto status
if /i "%1"=="start"  goto start
if /i "%1"=="stop"   goto stop
if /i "%1"=="status" goto status
if /i "%1"=="cli"    goto cli
echo Unknown command: %1
echo Usage: scripts\redis.bat [start^|stop^|status^|cli]
exit /b 1

:start
"%REDIS_HOME%\redis-cli.exe" -p %PORT% ping >nul 2>&1
if %errorlevel%==0 (
    echo Redis already running on port %PORT%.
    exit /b 0
)
echo Starting Redis on port %PORT% ...
start "craftique-redis" /B "%REDIS_HOME%\redis-server.exe" "%CONF%"
timeout /t 2 /nobreak >nul
goto status

:stop
echo Stopping Redis on port %PORT% ...
"%REDIS_HOME%\redis-cli.exe" -p %PORT% shutdown nosave
exit /b 0

:status
"%REDIS_HOME%\redis-cli.exe" -p %PORT% ping >nul 2>&1
if %errorlevel%==0 (
    echo Redis is UP on port %PORT%.
) else (
    echo Redis is DOWN on port %PORT%.  Run: scripts\redis.bat start
)
exit /b 0

:cli
"%REDIS_HOME%\redis-cli.exe" -p %PORT%
exit /b %errorlevel%
