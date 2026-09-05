<?php

session_start();
require_once __DIR__ . "/db.php";

$errors = [];

$fullName = "";
$email = "";
$phone = "";


// Process the registration form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");

    $password = $_POST["password"] ?? "";
    $confirmPassword =
        $_POST["confirm_password"] ?? "";


    // Validate full name
    if ($fullName === "") {
        $errors["full_name"] =
            "Please enter your full name.";
    } elseif (strlen($fullName) < 2) {
        $errors["full_name"] =
            "Your name must contain at least 2 characters.";
    }


    // Validate email
    if ($email === "") {
        $errors["email"] =
            "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] =
            "Please enter a valid email address.";
    }


    // Validate phone number
    if ($phone === "") {
    $errors["phone"] =
        "Please enter your phone number.";
	} elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
    $errors["phone"] =
        "Please enter a valid phone number.";
	}


    // Validate password
    if ($password === "") {
        $errors["password"] =
            "Please create a password.";
    } elseif (strlen($password) < 8) {
        $errors["password"] =
            "Your password must contain at least 8 characters.";
    }


    // Validate password confirmation
    if ($confirmPassword === "") {
        $errors["confirm_password"] =
            "Please confirm your password.";
    } elseif ($password !== $confirmPassword) {
        $errors["confirm_password"] =
            "The passwords do not match.";
    }


    // Check whether the email is already registered
    if (!isset($errors["email"])) {

        $emailCheckStmt = $conn->prepare("
            SELECT user_id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $emailCheckStmt->bind_param("s", $email);
        $emailCheckStmt->execute();

        $emailCheckResult =
            $emailCheckStmt->get_result();

        if ($emailCheckResult->num_rows > 0) {
            $errors["email"] =
                "An account with this email already exists.";
        }
    }


    // Create the account if validation succeeds
    if (empty($errors)) {

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $insertStmt = $conn->prepare("
            INSERT INTO users
            (
                full_name,
                email,
                phone,
                password_hash
            )
            VALUES (?, ?, ?, ?)
        ");

        $insertStmt->bind_param(
            "ssss",
            $fullName,
            $email,
            $phone,
            $passwordHash
        );

        if ($insertStmt->execute()) {

            $_SESSION["registration_success"] =
                "Your account was created successfully. You can now log in.";

            header("Location: login.php");
            exit;

        } else {
            $errors["general"] =
                "We could not create your account. Please try again.";
        }
    }
}


// Calculate cart quantity for the shared header
$totalQty = 0;

if (
    isset($_SESSION["cart"]) &&
    is_array($_SESSION["cart"])
) {
    $totalQty = array_sum($_SESSION["cart"]);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create an Account | The Yeast Lab</title>

    <link rel="stylesheet" href="style.css">
</head>

<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>


    <main class="auth-page">

        <section class="auth-introduction">

            <p class="section-label">
                Join the Laboratory
            </p>

            <h1>
                Create Your<br>
                Account.
            </h1>

            <p>
                Save your details, manage your orders and enjoy a
                smoother way to discover your favourite bakes.
            </p>

            <div class="auth-benefits">

                <div>
                    <span>01</span>
                    <p>Faster and more convenient checkout</p>
                </div>

                <div>
                    <span>02</span>
                    <p>View your current and previous orders</p>
                </div>

                <div>
                    <span>03</span>
                    <p>Keep your favourite bakes within reach</p>
                </div>

            </div>

        </section>


        <section class="auth-form-section">

            <div class="auth-form-heading">
                <p class="section-label">New Customer</p>
                <h2>Register</h2>
                <p>
                    Already have an account?
                    <a href="login.php">Log in here</a>
                </p>
            </div>


            <?php if (isset($errors["general"])): ?>

                <div class="form-message error-message">
                    <?php echo htmlspecialchars(
                        $errors["general"]
                    ); ?>
                </div>

            <?php endif; ?>


            <form action="register.php"
                  method="POST"
                  class="auth-form"
                  novalidate>


                <!-- Full Name -->
                <div class="auth-field">

                    <label for="full-name">
                        Full Name
                    </label>

                    <input type="text"
                           id="full-name"
                           name="full_name"
                           value="<?php echo htmlspecialchars(
                               $fullName
                           ); ?>"
                           autocomplete="name"
                           required>

                    <?php if (isset($errors["full_name"])): ?>
                        <span class="field-error">
                            <?php echo htmlspecialchars(
                                $errors["full_name"]
                            ); ?>
                        </span>
                    <?php endif; ?>

                </div>


                <!-- Email -->
                <div class="auth-field">

                    <label for="register-email">
                        Email Address
                    </label>

                    <input type="email"
                           id="register-email"
                           name="email"
                           value="<?php echo htmlspecialchars(
                               $email
                           ); ?>"
                           autocomplete="email"
                           required>

                    <?php if (isset($errors["email"])): ?>
                        <span class="field-error">
                            <?php echo htmlspecialchars(
                                $errors["email"]
                            ); ?>
                        </span>
                    <?php endif; ?>

                </div>


                <!-- Phone -->
                <div class="auth-field">

                    <label for="phone">
                        Phone Number
                        
                    </label>

                    <input type="tel"
                           id="phone"
                           name="phone"
                           value="<?php echo htmlspecialchars(
                               $phone
                           ); ?>"
                           autocomplete="tel"
						   required>

                    <?php if (isset($errors["phone"])): ?>
                        <span class="field-error">
                            <?php echo htmlspecialchars(
                                $errors["phone"]
                            ); ?>
                        </span>
                    <?php endif; ?>

                </div>


                <!-- Password -->
                <div class="auth-field">

                    <label for="register-password">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input type="password"
                               id="register-password"
                               name="password"
                               minlength="8"
                               autocomplete="new-password"
                               required>

                        <button type="button"
                                class="password-toggle"
                                data-password-target="register-password">
                            Show
                        </button>

                    </div>

                    <small>
                        Use at least 8 characters.
                    </small>

                    <?php if (isset($errors["password"])): ?>
                        <span class="field-error">
                            <?php echo htmlspecialchars(
                                $errors["password"]
                            ); ?>
                        </span>
                    <?php endif; ?>

                </div>


                <!-- Confirm Password -->
                <div class="auth-field">

                    <label for="confirm-password">
                        Confirm Password
                    </label>

                    <div class="password-wrapper">

                        <input type="password"
                               id="confirm-password"
                               name="confirm_password"
                               minlength="8"
                               autocomplete="new-password"
                               required>

                        <button type="button"
                                class="password-toggle"
                                data-password-target="confirm-password">
                            Show
                        </button>

                    </div>

                    <?php if (
                        isset($errors["confirm_password"])
                    ): ?>
                        <span class="field-error">
                            <?php echo htmlspecialchars(
                                $errors["confirm_password"]
                            ); ?>
                        </span>
                    <?php endif; ?>

                </div>


                <button type="submit"
                        class="auth-submit-button">
                    Create My Account
                </button>

            </form>

        </section>

    </main>


    <?php require_once __DIR__ . "/includes/footer.php"; ?>

</body>
</html>