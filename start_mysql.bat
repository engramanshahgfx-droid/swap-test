@echo off
REM Start MySQL manually
cd /d C:\xampp\mysql\bin
echo Starting MySQL...
mysqld --console --defaults-file=C:\xampp\mysql\bin\my.ini
pause
