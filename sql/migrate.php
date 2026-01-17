<?php
require_once '../conf/db.php';

try {
    $sql = file_get_contents('schema.sql');
    $pdo->exec($sql);
    echo "Database updated successfully successfully!";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>
