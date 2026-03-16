# 🔧 MySQL Troubleshooting - Complete Fix Guide

## 🚨 Problem
MySQL is crashing immediately after startup in XAMPP:
- Status shows "running"
- Then immediately changes to "stopped"
- Error: "MySQL shutdown unexpectedly"

---

## ✅ Solution: Step-by-Step Fix

### **STEP 1: Clear MySQL Locks (CRITICAL)**

1. **Close all applications** that might use MySQL:
   - Close XAMPP Control Panel
   - Close all browser tabs with phpMyAdmin
   - Close all terminals/command prompts
   - Stop any running MySQL processes

2. **Delete MySQL lock files:**
   ```powershell
   cd C:\xampp\mysql\data
   # Delete these files:
   del /F ib_logfile0
   del /F ib_logfile1
   del /F ibdata1
   del /F mysql-error.log
   ```
   
   Or manually:
   - Navigate to: `C:\xampp\mysql\data\`
   - Delete: `ib_logfile0`, `ib_logfile1`, `ibdata1`, `mysql-error.log`

---

### **STEP 2: Completely Restart XAMPP**

1. **Open XAMPP Control Panel**
2. **Stop All Services:**
   - Click "Stop" next to Apache
   - Click "Stop" next to MySQL
   - Wait 10 seconds
   
3. **Close the Control Panel completely**

4. **Wait 30 seconds**

5. **Restart XAMPP Control Panel**
   - Double-click `C:\xampp\xampp-control.exe`

6. **Start services again:**
   - Click "Start" next to MySQL
   - Wait 5 seconds and check status
   - Click "Start" next to Apache

7. **Verify both show green "Running"**

---

### **STEP 3: Test MySQL Connection**

Once MySQL is running, open Command Prompt and test:

```bash
cd C:\xampp\mysql\bin
mysql -u root -e "SELECT 1;"
```

**Expected output:**
```
+---+
| 1 |
+---+
| 1 |
+---+
```

---

### **STEP 4: Create Database**

Once MySQL is confirmed working:

```bash
cd C:\xampp\mysql\bin
mysql -u root -e "CREATE DATABASE IF NOT EXISTS crewswap_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -e "SHOW DATABASES LIKE 'crewswap_dev';"
```

**Expected output:**
```
Database
crewswap_dev
```

---

### **STEP 5: Run Laravel Migrations**

From your project folder:

```bash
cd c:\Users\win\Documents\Github\swap-test
php artisan cache:clear
php artisan config:clear
php artisan migrate --force
```

---

## 🆘 If MySQL Still Won't Start

### Option A: Rebuild MySQL (Nuclear Option)

**WARNING: This will delete all data**

1. **Delete entire MySQL data folder:**
   ```bash
   rmdir /S /Q C:\xampp\mysql\data
   ```

2. **Restart XAMPP** - it will automatically recreate MySQL

3. **Continue from STEP 4**

### Option B: Use SQLite for Testing

If MySQL continues to fail, temporarily use **SQLite** for testing:

1. **Update `.env`:**
   ```
   DB_CONNECTION=sqlite
   DB_DATABASE=database.sqlite
   ```

2. **Create SQLite database:**
   ```bash
   touch database.sqlite
   php artisan migrate
   ```

3. **This lets you test the API while fixing MySQL**

### Option C: Reinstall XAMPP

1. **Backup your project files**
2. **Uninstall XAMPP** completely
3. **Reinstall fresh XAMPP**
4. **Copy your project back**
5. **Configure and run migrations**

---

## 🔍 MySQL Error Diagnostics

### Check MySQL Error Log

```bash
type C:\xampp\mysql\data\mysql-error.log
```

Look for:
- `Port already in use` → Use Option B (SQLite)
- `Corrupted ibdata1` → Delete the file (STEP 1)
- `Permission denied` → Run as Administrator
- `InnoDB errors` → Rebuild MySQL (Option A)

### Check if Port 3306 is In Use

```bash
netstat -ano | findstr ":3306"
```

If something is using it:
```bash
# Find what's using port 3306
Get-Process | findstr ":3306"

# Kill it (replace PID with the number)
taskkill /PID <PID> /F
```

---

## ✅ Quick Checklist

- [ ] Closed all applications using MySQL
- [ ] Deleted lock files from `C:\xampp\mysql\data\`
- [ ] Restarted XAMPP completely
- [ ] MySQL shows "Running" (green)
- [ ] Tested connection with `mysql -u root -e "SELECT 1;"`
- [ ] Created `crewswap_dev` database
- [ ] Ran `php artisan migrate`
- [ ] Ready to test API

---

## 🚀 After MySQL is Fixed

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear

# Run migrations
php artisan migrate --fresh --seed

# Start server
php artisan serve

# In another terminal, test API:
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{...}'
```

---

## 📞 Need More Help?

If MySQL still won't work:
1. Check MySQL error log: `C:\xampp\mysql\data\mysql-error.log`
2. Try rebuilding MySQL (Option A above)
3. Use SQLite temporarily (Option B)
4. Reinstall XAMPP if needed (Option C)

---

**Last Updated**: March 16, 2026
