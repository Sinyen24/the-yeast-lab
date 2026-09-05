<?php

// Start the session only if it has not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// User must be logged in
if (!isset($_SESSION["user_id"])) {

    $_SESSION["access_denied"] =
        "Please log in with an administrator account.";

    header("Location: ../login.php");
    exit;
}


// User must have the Admin role
if (
    !isset($_SESSION["user_role"]) ||
    $_SESSION["user_role"] !== "Admin"
) {
    http_response_code(403);

    die(
        "Access denied. Administrator permission is required."
    );
}

?>