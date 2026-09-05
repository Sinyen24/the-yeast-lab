<?php

session_start();
require_once __DIR__ . "/db.php";

$errors = [];

$email = "";


// Redirect users who are already logged in
if (isset($_SESSION["user_id"])) {
    header("Location: profile.php");
    exit;
}


// Process login form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    // Validate email
    if ($email === "") {
        $errors["email"] =
            "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] =
            "Please enter a valid email address.";
    }


    // Validate password
    if ($password === "") {
        $errors["password"] =
            "Please enter your password.";
    }


    // Check login details
    if (empty($errors)) {

        $loginStmt = $conn->prepare("
            SELECT
                user_id,
                full_name,
                email,
                phone,
                password_hash,
                user_role,
                account_status
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $loginStmt->bind_param("s", $email);
        $loginStmt->execute();

        $loginResult = $loginStmt->get_result();
        $user = $loginResult->fetch_assoc();


        if (
            $user &&
            $user["account_status"] === "Active" &&
            password_verify(
                $password,
                $user["password_hash"]
            )
        ) {
            // Protect the login session
            session_regenerate_id(true);

            $_SESSION["user_id"] =
                $user["user_id"];

            $_SESSION["user_name"] =
                $user["full_name"];

            $_SESSION["user_email"] =
                $user["email"];

            $_SESSION["user_role"] =
                $user["user_role"];


            // Send administrators to the admin area
if ($user["user_role"] === "Admin") {

    unset($_SESSION["redirect_after_login"]);

    header("Location: admin/dashboard.php");

} else {

    $redirectAfterLogin =
        $_SESSION["redirect_after_login"] ??
        "profile.php";

    unset($_SESSION["redirect_after_login"]);

    header("Location: " . $redirectAfterLogin);
}

exit;

        } else {
            $errors["general"] =
                "The email address or password is incorrect.";
        }
    }
}


// Registration success message
$registrationSuccess =
    $_SESSION["registration_success"] ?? "";

unset($_SESSION["registration_success"]);


// Logout success message
$logoutSuccess =
    $_SESSION["logout_success"] ?? "";

unset($_SESSION["logout_success"]);


// Login-required message
$loginRequired =
    $_SESSION["login_required"] ?? "";

unset($_SESSION["login_required"]);


// Calculate cart quantity
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

    <title>Log In | The Yeast Lab</title>

    <link rel="stylesheet" href="style.css">
</head>

<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>


    <main class="auth-page login-page">

        <section class="auth-introduction login-introduction">

            <p class="section-label">
                Welcome Back
            </p>

            <h1>
                Return to<br>
                the Lab.
            </h1>

            <p>
                Sign in to manage your profile, review your orders
                and continue exploring your favourite bakes.
            </p>

            <div class="auth-benefits">

                <div>
                    <span>01</span>
                    <p>Access your saved account information</p>
                </div>

                <div>
                    <span>02</span>
                    <p>Review your current and previous orders</p>
                </div>

                <div>
                    <span>03</span>
                    <p>Enjoy a faster checkout experience</p>
                </div>

            </div>

        </section>


        <section class="auth-form-section">

            <div class="auth-form-heading">

                <p class="section-label">
                    Customer Account
                </p>

                <h2>Log In</h2>

                <p>
                    New to The Yeast Lab?
                    <a href="register.php">
                        Create an account
                    </a>
                </p>

            </div>


            <?php if ($registrationSuccess !== ""): ?>

                <div class="form-message success-message">
                    <?php echo htmlspecialchars(
                        $registrationSuccess
                    ); ?>
                </div>

            <?php endif; ?>
			
			<?php if ($logoutSuccess !== ""): ?>

				<div class="form-message success-message">
					<?php echo htmlspecialchars(
					$logoutSuccess
				); ?>
				</div>

			<?php endif; ?>
			
			<?php if ($loginRequired !== ""): ?>

				<div class="form-message error-message">
					<?php echo htmlspecialchars(
					$loginRequired
				); ?>
				</div>

			<?php endif; ?>


            <?php if (isset($errors["general"])): ?>

                <div class="form-message error-message">
                    <?php echo htmlspecialchars(
                        $errors["general"]
                    ); ?>
                </div>

            <?php endif; ?>


            <form action="login.php"
                  method="POST"
                  class="auth-form login-form"
                  novalidate>


                <!-- Email -->
                <div class="auth-field">

                    <label for="login-email">
                        Email Address
                    </label>

                    <input type="email"
                           id="login-email"
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


                <!-- Password -->
                <div class="auth-field">

                    <label for="login-password">
                        Password
                    </label>

                    <div class="password-wrapper">

                        <input type="password"
                               id="login-password"
                               name="password"
                               autocomplete="current-password"
                               required>

                        <button type="button"
                                class="password-toggle"
                                data-password-target="login-password">
                            Show
                        </button>

                    </div>

                    <?php if (isset($errors["password"])): ?>
                        <span class="field-error">
                            <?php echo htmlspecialchars(
                                $errors["password"]
                            ); ?>
                        </span>
                    <?php endif; ?>

                </div>


                <button type="submit"
                        class="auth-submit-button">
                    Log In
                </button>

            </form>

        </section>

    </main>


    <?php require_once __DIR__ . "/includes/footer.php"; ?>

</body>
</html>