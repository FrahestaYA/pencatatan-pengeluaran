<?php
require_once '../conf/db.php';

try {
    // Add columns if they don't exist
    $columns = [
        "ADD COLUMN full_name VARCHAR(100) NULL AFTER password",
        "ADD COLUMN email VARCHAR(100) NULL AFTER full_name",
        "ADD COLUMN bio TEXT NULL AFTER email",
        "ADD COLUMN avatar VARCHAR(255) DEFAULT 'default.png' AFTER bio"
    ];

    foreach ($columns as $col) {
        try {
            $pdo->exec("ALTER TABLE users $col");
            echo "Executed: $col <br>";
        } catch (PDOException $e) {
            // Ignore error if column exists (SQLSTATE 42S21)
            if ($e->getCode() != '42S21') {
                echo "Error: " . $e->getMessage() . "<br>";
            } else {
                echo "Skipped (exists): $col <br>";
            }
        }
    }

    echo "Database updated successfully!";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>