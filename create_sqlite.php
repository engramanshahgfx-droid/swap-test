<?php
// Create SQLite database
$db_path = 'C:/Users/win/Documents/Github/swap-test/database.sqlite';

// Create database by connecting to it
try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ SQLite database created successfully\n";
} catch (PDOException $e) {
    echo "❌ Error creating database: " . $e->getMessage() . "\n";
    exit(1);
}
?>
