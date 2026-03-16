# MySQL System Tables Corruption Fix

## Problem Identified
```
[ERROR] Index for table '.\mysql\proxies_priv' is corrupt
[ERROR] Fatal error: Can't open and lock privilege tables
```

The MySQL system tables database is corrupted, causing immediate shutdown after initialization.

## Root Cause
The mysql system database (containing user privileges, roles, etc.) has a corrupted index. This is typically caused by:
- Unclean MySQL shutdown
- Disk I/O errors
- InnoDB recovery failure

## Solution: Rebuild System Tables

### Option 1: Quick Fix (Easy)
Delete the corrupted mysql database folder and let MySQL recreate it:

```powershell
# Stop any running MySQL
taskkill /IM mysqld.exe /F

# Backup corrupted database
Rename-Item "C:\xampp\mysql\data\mysql" "C:\xampp\mysql\data\mysql_corrupt_backup"

# Start MySQL via XAMPP (it will recreate system tables automatically)
# Or run this to initialize:
cd "C:\xampp\mysql\bin"
mysqld.exe --console --initialize
```

### Option 2: Using Script
Run this in PowerShell as Administrator:

```powershell
$dataPath = "C:\xampp\mysql\data"

# Stop MySQL
taskkill /IM mysqld.exe /F -ErrorAction SilentlyContinue

# Backup
if (Test-Path "$dataPath\mysql") {
    Rename-Item "$dataPath\mysql" "$dataPath\mysql_corrupt_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')" -Force
}

# Start MySQL to auto-initialize
& "C:\xampp\mysql\bin\mysqld.exe" --console 2>&1 &
Start-Sleep -Seconds 5
taskkill /IM mysqld.exe /F
```

### Option 3: Repair (If you want to keep existing data)
```
mysqlcheck --repair --all-databases
```
This requires MySQL to be running first though.

## After Fix: Verification

Once system tables are recreated, verify by:

```powershell
# Check if MySQL stays running
& "C:\xampp\mysql\bin\mysqld.exe" --console 2>&1 &
Start-Sleep -Seconds 3
tasklist | find /i "mysqld.exe"
```

If mysqld is still running after 3 seconds, the fix worked!

## Important Notes

- **SQLite Development**: Your current SQLite setup is fully functional. You can continue dev there while fixing MySQL.
- **Backups**: The script backs up the corrupt database. You can restore it if needed via the `mysql_corrupt_backup` folder.
- **Administrator**: Some operations may require running PowerShell as Administrator.
- **XAMPP Control Panel Issue**: Since XAMPP's control panel seems unreliable for starting MySQL, consider the independent start script: `C:\xampp\mysql\start_mysql_service.bat`

## If Issues Persist

1. Check error log: `Get-Content "C:\xampp\mysql\data\mysql_error.log" -Tail 100`
2. Verify disk space: `(Get-Volume C).SizeRemaining`
3. Check InnoDB files exist: `Get-ChildItem "C:\xampp\mysql\data" -Filter "ib*"`
4. Consider fresh XAMPP install if corruption is severe

## For Production Deployment

Once you're ready to deploy to production:
- Switch `.env` from SQLite to MySQL credentials
- Run: `php artisan migrate --force`
- Verify data integrity with: `php artisan db:show`
