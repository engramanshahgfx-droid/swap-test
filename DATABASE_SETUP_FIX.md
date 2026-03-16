# 🔧 Local Database Setup Fix Guide

## Problem
Your MySQL database is not connecting properly to the Laravel application running locally on XAMPP.

**Error**: `SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it`

---

## ✅ Solution Steps

### Step 1: Verify XAMPP MySQL is Running
1. Open **XAMPP Control Panel**
2. Check that **MySQL** shows `Running` in green
3. If not, click the **Start** button next to MySQL

### Step 2: Test MySQL Connection
Run this command in Command Prompt:
```bash
cd C:\xampp\mysql\bin
mysql -u root
```

**Expected**: Should show `mysql>` prompt without errors

If you get a connection error, MySQL might need to be restarted or the service might not be starting properly.

---

### Step 3: Create the Database

**Option A: Using phpMyAdmin (Easiest)**
1. Open `http://localhost/phpmyadmin`
2. Click **New** → Type database name: `crewswap_dev`
3. Click **Create**

**Option B: Using Command Prompt**
1. Open Command Prompt as Administrator
2. Run:
```bash
cd C:\xampp\mysql\bin
mysql -u root -e "CREATE DATABASE IF NOT EXISTS crewswap_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

**Option C: Using PowerShell (if Option A/B don't work)**
```powershell
$conn = New-Object System.Data.SqlClient.SqlConnection("Server=localhost;User ID=root;Password=")
$conn.ConnectionString = "Driver={MySQL ODBC 8.0 Driver};Server=localhost;Uid=root;Pwd=;"
$conn.Open()
$cmd = $conn.CreateCommand()
$cmd.CommandText = "CREATE DATABASE IF NOT EXISTS crewswap_dev CHARACTER SET utf8mb4"
$cmd.ExecuteNonQuery()
$conn.Close()
```

---

### Step 4: Verify Database Creation
Run this command:
```bash
cd C:\xampp\mysql\bin
mysql -u root -e "SHOW DATABASES LIKE 'crewswap_dev';"
```

**Expected output**:
```
Database
crewswap_dev
```

---

### Step 5: Clear Laravel Cache & Update Config

From your project folder (`c:\Users\win\Documents\Github\swap-test`):

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

### Step 6: Verify .env Configuration

Check these values in your `.env` file:

```
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crewswap_dev
DB_USERNAME=root
DB_PASSWORD=
```

**If `.env` doesn't match, update it:**
```
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:kJL2juMhZg09Q1DrXb1Op+U9Kh1q64eRE1I1BHSB5tg=
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crewswap_dev
DB_USERNAME=root
DB_PASSWORD=
```

---

### Step 7: Run Migrations

From your project folder:

```bash
# Check migration status
php artisan migrate:status

# Run fresh migrations with seeding
php artisan migrate:fresh --seed
```

**Expected output**:
```
Creating migration table ...
Migrating: 2024_01_01_000001_create_users_table.php
Migrated:  2024_01_01_000001_create_users_table.php (xxx ms)
...
```

---

### Step 8: Verify Everything Works

Run this test command:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

**Expected**: Should show `=> null` without errors

Then type `exit` to quit tinker.

---

## 🧪 Final Verification

### Test 1: Check Database Connection
```bash
php artisan db:show
```

**Expected**: Shows database info without errors

### Test 2: Check Tables Created
```bash
php artisan tinker
>>> Schema::getTableListing();
```

Should show tables like: `users`, `migrations`, `airlines`, etc.

### Test 3: Test API Connection
```bash
php artisan serve
```

Then in another terminal:
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"employee_id":"EMP001","full_name":"Test","phone":"+1234567890","email":"test@example.com","country_base":"US","airline_id":1,"plane_type_id":1,"position_id":1,"password":"password123"}'
```

---

## ❌ Troubleshooting

### Issue: MySQL won't start in XAMPP

**Solution:**
1. Check if port 3306 is in use: `netstat -ano | findstr :3306`
2. Kill conflicting process if needed
3. Try restarting XAMPP Control Panel as Administrator
4. Check MySQL Error Log: `C:\xampp\mysql\data\*.err`

### Issue: "Access denied for user 'root'@'localhost'"

**Solution:**
- MySQL root has NO password by default in XAMPP
- In `.env`, make sure `DB_PASSWORD=` (leave empty)
- NOT `DB_PASSWORD=root` or any other value

### Issue: "No connection could be made"

**Solution:**
- MySQL service might not be running
- Try manually starting MySQL: `C:\xampp\mysql\bin\mysqld.exe`
- Check if another application is using port 3306
- Restart XAMPP completely (stop all, then start all)

### Issue: Migrations won't run

**Solution:**
- Clear caches: `php artisan config:clear && php artisan cache:clear`
- Check .env is correct
- Verify MySQL is actually running
- Try `php artisan migrate:reset` then `php artisan migrate`

---

## 📝 Summary

After completing these steps, you should have:

✅ XAMPP MySQL running and accessible  
✅ `crewswap_dev` database created  
✅ Laravel configuration updated  
✅ All migrations completed  
✅ Tables created and ready  
✅ API ready to use with test data  

---

## 🚀 Start Using the API

Once everything is set up:

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start testing
# Open Postman and import: CrewSwap_Mobile_API_Collection.json
# Or use curl to test endpoints
```
---
**Last Updated**: March 16, 2026  
**Database**: crewswap_dev  
**User**: root  
**Password**: (empty)
