<?php

// Database configuration
$databaseHost = "localhost";
$databaseUsername = "root";
$databasePassword = "";
$databaseName = "yeast_lab_db";

// Create the database connection
$conn = new mysqli(
    $databaseHost,
    $databaseUsername,
    $databasePassword,
    $databaseName
);

// Check the connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Support special characters such as è and apostrophes
$conn->set_charset("utf8mb4");
?>