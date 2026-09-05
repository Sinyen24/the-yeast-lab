<?php

session_start();

// Remove only the logged-in user information
unset($_SESSION["user_id"]);
unset($_SESSION["user_name"]);
unset($_SESSION["user_email"]);
unset($_SESSION["user_role"]);

// Remove the previous customer's cart
unset($_SESSION["cart"]);
unset($_SESSION["redirect_after_login"]);
unset($_SESSION["login_required"]);

// Create a new secure session ID
session_regenerate_id(true);

// Display confirmation and return to login
$_SESSION["logout_success"] =
    "You have logged out successfully.";

header("Location: login.php");
exit;

?>