<?php

session_start();
require_once __DIR__ . "/db.php";


// Protect the page
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$errors = [];


// Retrieve the current customer information
$userStmt = $conn->prepare("
    SELECT
        full_name,
        email,
        phone
    FROM users
    WHERE user_id = ?
      AND account_status = 'Active'
    LIMIT 1
");

$userStmt->bind_param("i", $userId);
$userStmt->execute();

$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

if (!$user) {
    header("Location: logout.php");
    exit;
}


$fullName = $user["full_name"];
$email = $user["email"];
$phone = $user["phone"];


// Process the update form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim($_POST["full_name"] ?? "");
    $phone = trim($_POST["phone"] ?? "");


    // Validate full name
    if ($fullName === "") {
        $errors["full_name"] =
            "Please enter your full name.";
    } elseif (strlen($fullName) < 2) {
        $errors["full_name"] =
            "Your name must contain at least 2 characters.";
    }


    // Validate phone number
    if ($phone === "") {
        $errors["phone"] =
            "Please enter your phone number.";
    } elseif (
        !preg_match('/^[0-9+\-\s]{8,20}$/', $phone)
    ) {
        $errors["phone"] =
            "Please enter a valid phone number.";
    }


    // Update the database
    if (empty($errors)) {

        $updateStmt = $conn->prepare("
            UPDATE users
            SET
                full_name = ?,
                phone = ?
            WHERE user_id = ?
        ");

        $updateStmt->bind_param(
            "ssi",
            $fullName,
            $phone,
            $userId
        );

        if ($updateStmt->execute()) {

            // Update the name stored in the login session
            $_SESSION["user_name"] = $fullName;

            $_SESSION["profile_success"] =
                "Your account information was updated successfully.";

            header("Location: edit-profile.php");
            exit;

        } else {
            $errors["general"] =
                "We could not update your information. Please try again.";
        }
    }
}


// Retrieve and remove the success message
$profileSuccess =
    $_SESSION["profile_success"] ?? "";

unset($_SESSION["profile_success"]);


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

    <title>Edit My Profile | The Yeast Lab</title>

    <link rel="stylesheet" href="style.css">
</head>

<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>


    <main class="auth-page">

        <section class="auth-introduction">

            <p class="section-label">
                My Account
            </p>

            <h1>
                Update Your<br>
                Details.
            </h1>

            <p>
                Keep your contact information accurate so we can
                prepare and coordinate your future orders smoothly.
            </p>

            <div class="auth-benefits">

                <div>
                    <span>01</span>
                    <p>Your email address identifies your account</p>
                </div>

                <div>
                    <span>02</span>
                    <p>Your phone number helps with order collection</p>
                </div>

                <div>
                    <span>03</span>
                    <p>Your information remains connected to your account</p>
                </div>

            </div>

        </section>


        <section class="auth-form-section">

            <div class="auth-form-heading">

                <p class="section-label">
                    Account Settings
                </p>

                <h2>Edit Profile</h2>

                <p>
                    <a href="profile.php">
                        ← Return to My Account
                    </a>
                </p>

            </div>


            <?php if ($profileSuccess !== ""): ?>

                <div class="form-message success-message">
                    <?php echo htmlspecialchars(
                        $profileSuccess
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


            <form action="edit-profile.php"
                  method="POST"
                  class="auth-form login-form"
                  novalidate>


                <!-- Full Name -->
                <div class="auth-field">

                    <label for="edit-full-name">
                        Full Name
                    </label>

                    <input type="text"
                           id="edit-full-name"
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

                    <label for="edit-email">
                        Email Address
                    </label>

                    <input type="email"
                           id="edit-email"
                           value="<?php echo htmlspecialchars(
                               $email
                           ); ?>"
                           readonly>

                    <small>
                        The login email cannot be changed here.
                    </small>

                </div>


                <!-- Phone -->
                <div class="auth-field">

                    <label for="edit-phone">
                        Phone Number
                    </label>

                    <input type="tel"
                           id="edit-phone"
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


                <button type="submit"
                        class="auth-submit-button">
                    Save Changes
                </button>

            </form>

        </section>

    </main>


    <?php require_once __DIR__ . "/includes/footer.php"; ?>

</body>
</html>