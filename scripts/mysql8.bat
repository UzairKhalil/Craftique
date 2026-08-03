@echo off
REM Craftique - MySQL 8.4 LTS dev instance control (port 3307)
REM Usage: scripts\mysql8.bat [start|stop|status|cli]
REM See docs/runbooks/environment.md

setlocal
set MYSQL_HOME=C:\mysql8
set MYSQL_BIN=%MYSQL_HOME%\mysql-8.4.10-winx64\bin
set DEFAULTS=--defaults-file=%MYSQL_HOME%\my.ini
set PORT=3307

if "%1"=="" goto status
if /i "%1"=="start"  goto start
if /i "%1"=="stop"   goto stop
if /i "%1"=="status" goto status
if /i "%1"=="cli"    goto cli
echo Unknown command: %1
echo Usage: scripts\mysql8.bat [start^|stop^|status^|cli]
exit /b 1

:start
"%MYSQL_BIN%\mysqladmin.exe" -u root -P %PORT% -h 127.0.0.1 ping >nul 2>&1
if %errorlevel%==0 (
    echo MySQL 8 already running on port %PORT%.
    exit /b 0
)
echo Starting MySQL 8.4 on port %PORT% ...
start "craftique-mysql8" /B "%MYSQL_BIN%\mysqld.exe" %DEFAULTS%
timeout /t 3 /nobreak >nul
goto status

:stop
echo Stopping MySQL 8 on port %PORT% ...
"%MYSQL_BIN%\mysqladmin.exe" -u root -P %PORT% -h 127.0.0.1 shutdown
exit /b %errorlevel%

:status
"%MYSQL_BIN%\mysql.exe" -u root -P %PORT% -h 127.0.0.1 --default-character-set=utf8mb4 ^
    -e "SELECT VERSION() AS version, @@port AS port;" 2>nul
if %errorlevel%==0 (
    echo MySQL 8 is UP on port %PORT%.
) else (
    echo MySQL 8 is DOWN on port %PORT%.  Run: scripts\mysql8.bat start
)
exit /b 0

:cli
"%MYSQL_BIN%\mysql.exe" -u root -P %PORT% -h 127.0.0.1 --default-character-set=utf8mb4 craftique
exit /b %errorlevel%
