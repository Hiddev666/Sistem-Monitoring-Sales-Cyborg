<?php
// Create test database
$mysqli = new mysqli('127.0.0.1', 'root', '', '');

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Create test database
$sql = "CREATE DATABASE IF NOT EXISTS monitoring_sales_test";
if ($mysqli->query($sql) === TRUE) {
    echo "Test database created successfully\n";
} else {
    echo "Error creating test database: " . $mysqli->error . "\n";
}

$mysqli->close();
?>
