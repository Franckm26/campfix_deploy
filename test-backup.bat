@echo off
REM Database Backup Test Script for Windows
REM This script tests the backup system locally

echo ================================
echo CampFix Database Backup Test
echo ================================
echo.

echo [1/4] Testing if PHP is available...
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP is not in PATH
    echo Please add PHP to your system PATH
    pause
    exit /b 1
)
echo OK: PHP is available
echo.

echo [2/4] Testing if pg_dump is available...
pg_dump --version >nul 2>&1
if errorlevel 1 (
    echo WARNING: pg_dump is not in PATH
    echo Please install PostgreSQL client tools
    echo Download from: https://www.postgresql.org/download/windows/
    echo.
    echo Continuing anyway (backup will fail)...
) else (
    echo OK: pg_dump is available
)
echo.

echo [3/4] Creating backup directory...
if not exist "storage\app\backups" (
    mkdir storage\app\backups
    echo Created: storage\app\backups
) else (
    echo OK: Backup directory exists
)
echo.

echo [4/4] Running backup command...
echo php artisan db:backup --compress
echo.
php artisan db:backup --compress
echo.

if errorlevel 1 (
    echo ================================
    echo ERROR: Backup failed!
    echo ================================
    echo.
    echo Possible reasons:
    echo - pg_dump not installed
    echo - Database credentials incorrect
    echo - Database not accessible
    echo.
    echo Check the error message above
    echo Check logs: storage\logs\laravel.log
) else (
    echo ================================
    echo SUCCESS: Backup completed!
    echo ================================
    echo.
    echo Listing backups:
    dir storage\app\backups /O-D
    echo.
    echo To test restoration, see: DATABASE_BACKUP_SETUP.md
)

echo.
pause
