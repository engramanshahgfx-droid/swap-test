<?php
// Simple script to create database using PDO
try {
    echo "Attempting to connect to MySQL...\n";
    $conn = new PDO('mysql:host=localhost;port=3306', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    echo "✅ Connected to MySQL\n";
    echo "Creating database 'flightSwap _dev'...\n";

    // Drop existing database if it exists
    $conn->exec('DROP DATABASE IF EXISTS flightSwap _dev');
    echo "✅ Dropped old database\n";

    // Create database
    $conn->exec('CREATE DATABASE flightSwap _dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    echo "✅ Database 'flightSwap _dev' created successfully\n";
    $conn = null;
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
    exit(1);
}
?>
