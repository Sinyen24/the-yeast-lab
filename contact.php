<?php

session_start();
require_once __DIR__ . "/db.php";

$errors = [];

$userId = isset($_SESSION["user_id"])
    ? (int) $_SESSION["user_id"]
    : null;

$customerName = "";
$customerEmail = "";
$customerPhone = "";
$inquirySubject = "";
$inquiryMessage = "";


// Prefill contact information for logged-in customers
if ($userId !== null) {

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
    $contactUser = $userResult->fetch_assoc();

    if ($contactUser) {
        $customerName = $contactUser["full_name"];
        $customerEmail = $contactUser["email"];
        $customerPhone = $contactUser["phone"];
    }
}


// Process the contact form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customerName =
        trim($_POST["customer_name"] ?? "");

    $customerEmail =
        trim($_POST["customer_email"] ?? "");

    $customerPhone =
        trim($_POST["customer_phone"] ?? "");

    $inquirySubject =
        trim($_POST["inquiry_subject"] ?? "");

    $inquiryMessage =
        trim($_POST["inquiry_message"] ?? "");


    // Validate name
    if ($customerName === "") {
        $errors["customer_name"] =
            "Please enter your full name.";
    }


    // Validate email
    if (
        !filter_var(
            $customerEmail,
            FILTER_VALIDATE_EMAIL
        )
    ) {
        $errors["customer_email"] =
            "Please enter a valid email address.";
    }


    // Validate optional phone number
    if (
        $customerPhone !== "" &&
        !preg_match(
            '/^[0-9+\-\s]{8,20}$/',
            $customerPhone
        )
    ) {
        $errors["customer_phone"] =
            "Please enter a valid phone number.";
    }


    // Validate subject
    $allowedSubjects = [
        "General Enquiry",
        "Product Enquiry",
        "Order Assistance",
        "Celebration Order",
        "Corporate Event"
    ];

    if (
        !in_array(
            $inquirySubject,
            $allowedSubjects,
            true
        )
    ) {
        $errors["inquiry_subject"] =
            "Please select an enquiry subject.";
    }


    // Validate message
    if ($inquiryMessage === "") {
        $errors["inquiry_message"] =
            "Please enter your message.";
    } elseif (strlen($inquiryMessage) < 10) {
        $errors["inquiry_message"] =
            "Your message must contain at least 10 characters.";
    }


    // Save the enquiry
    if (empty($errors)) {

        $inquiryStmt = $conn->prepare("
            INSERT INTO inquiries
            (
                user_id,
                customer_name,
                customer_email,
                customer_phone,
                inquiry_subject,
                inquiry_message
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $inquiryStmt->bind_param(
            "isssss",
            $userId,
            $customerName,
            $customerEmail,
            $customerPhone,
            $inquirySubject,
            $inquiryMessage
        );

        if ($inquiryStmt->execute()) {

            $_SESSION["contact_success"] =
                "Thank you! Your enquiry has been sent to The Yeast Lab.";

            header("Location: contact.php");
            exit;

        } else {
            $errors["general"] =
                "We could not send your enquiry. Please try again.";
        }
    }
}


// Retrieve the success message
$contactSuccess =
    $_SESSION["contact_success"] ?? "";

unset($_SESSION["contact_success"]);


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

    <title>Contact Us | The Yeast Lab</title>

    <link rel="stylesheet" href="style.css">
</head>

<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>


    <main class="contact-page">

        <!-- Contact introduction -->
        <section class="contact-hero">

            <p class="section-label">
                Contact The Yeast Lab
            </p>

            <h1>Let’s create something<br>delicious together.</h1>

            <p>
                Questions, celebrations or special requests?
                Send us a message and our bakery team will assist you.
            </p>

        </section>


        <section class="contact-layout">

            <!-- Bakery information -->
            <div class="contact-information-panel">

                <div class="contact-panel-heading">
                    <span>01</span>
                    <h2>Visit the Lab</h2>
                </div>


                <div class="contact-information-list">

                    <div>
                        <span>Address</span>

                        <p>
                            204A, Jalan Ampang,<br>
                            Kampung Datuk Keramat,<br>
                            50450 Kuala Lumpur.
                        </p>
                    </div>

                    <div>
                        <span>Telephone</span>

                        <p>
                            <a href="tel:+60312345678">
                                +603 1234 5678
                            </a>
                        </p>
                    </div>

                    <div>
                        <span>Email</span>

                        <p>
                            <a href="mailto:hello@theyeastlab.com">
                                hello@theyeastlab.com
                            </a>
                        </p>
                    </div>

                    <div>
                        <span>Opening Hours</span>

                        <p>
                            Monday – Friday<br>
                            8:00 AM – 8:00 PM
                        </p>

                        <p>
                            Saturday – Sunday<br>
                            8:00 AM – 10:00 PM
                        </p>
                    </div>

                </div>


                <a href="https://maps.google.com/"
                   target="_blank"
                   rel="noopener"
                   class="contact-location-button">
                    Open in Google Maps →
                </a>

            </div>


            <!-- Contact form -->
            <div class="contact-form-panel">

                <div class="contact-panel-heading">
                    <span>02</span>
                    <h2>Send an Enquiry</h2>
                </div>


                <?php if ($contactSuccess !== ""): ?>

                    <div class="form-message success-message">
                        <?php echo htmlspecialchars(
                            $contactSuccess
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


                <form action="contact.php"
                      method="POST"
                      class="contact-enquiry-form"
                      novalidate>


                    <div class="auth-field">

                        <label for="contact-name">
                            Full Name
                        </label>

                        <input type="text"
                               id="contact-name"
                               name="customer_name"
                               value="<?php echo htmlspecialchars(
                                   $customerName
                               ); ?>"
                               required>

                        <?php if (
                            isset($errors["customer_name"])
                        ): ?>
                            <span class="field-error">
                                <?php echo htmlspecialchars(
                                    $errors["customer_name"]
                                ); ?>
                            </span>
                        <?php endif; ?>

                    </div>


                    <div class="auth-field">

                        <label for="contact-email">
                            Email Address
                        </label>

                        <input type="email"
                               id="contact-email"
                               name="customer_email"
                               value="<?php echo htmlspecialchars(
                                   $customerEmail
                               ); ?>"
                               required>

                        <?php if (
                            isset($errors["customer_email"])
                        ): ?>
                            <span class="field-error">
                                <?php echo htmlspecialchars(
                                    $errors["customer_email"]
                                ); ?>
                            </span>
                        <?php endif; ?>

                    </div>


                    <div class="auth-field">

                        <label for="contact-phone">
                            Phone Number
                            <span>(Optional)</span>
                        </label>

                        <input type="tel"
                               id="contact-phone"
                               name="customer_phone"
                               value="<?php echo htmlspecialchars(
                                   $customerPhone
                               ); ?>">

                        <?php if (
                            isset($errors["customer_phone"])
                        ): ?>
                            <span class="field-error">
                                <?php echo htmlspecialchars(
                                    $errors["customer_phone"]
                                ); ?>
                            </span>
                        <?php endif; ?>

                    </div>


                    <div class="auth-field">

                        <label for="inquiry-subject">
                            Enquiry Subject
                        </label>

                        <select id="inquiry-subject"
                                name="inquiry_subject"
                                required>

                            <option value="">
                                Select a subject
                            </option>

                            <?php foreach (
                                $allowedSubjects as $subject
                            ): ?>

                                <option value="<?php
                                    echo htmlspecialchars($subject);
                                ?>"
                                    <?php echo $inquirySubject ===
                                        $subject
                                            ? "selected"
                                            : ""; ?>>

                                    <?php echo htmlspecialchars(
                                        $subject
                                    ); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <?php if (
                            isset($errors["inquiry_subject"])
                        ): ?>
                            <span class="field-error">
                                <?php echo htmlspecialchars(
                                    $errors["inquiry_subject"]
                                ); ?>
                            </span>
                        <?php endif; ?>

                    </div>


                    <div class="auth-field contact-message-field">

                        <label for="inquiry-message">
                            Your Message
                        </label>

                        <textarea id="inquiry-message"
                                  name="inquiry_message"
                                  rows="7"
                                  placeholder="Tell us how we can help..."
                                  required><?php
                            echo htmlspecialchars($inquiryMessage);
                        ?></textarea>

                        <?php if (
                            isset($errors["inquiry_message"])
                        ): ?>
                            <span class="field-error">
                                <?php echo htmlspecialchars(
                                    $errors["inquiry_message"]
                                ); ?>
                            </span>
                        <?php endif; ?>

                    </div>


                    <button type="submit"
                            class="contact-submit-button">
                        Send My Enquiry
                    </button>

                </form>

            </div>

        </section>

    </main>


    <?php require_once __DIR__ . "/includes/footer.php"; ?>

</body>
</html>