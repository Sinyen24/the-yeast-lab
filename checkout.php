<?php

session_start();
require_once __DIR__ . "/db.php";


// Customers must log in before checkout
if (!isset($_SESSION["user_id"])) {
    $_SESSION["login_required"] =
        "Please log in before proceeding to checkout.";

    header("Location: login.php");
    exit;
}

// Checkout is only for customer accounts
if (
    ($_SESSION["user_role"] ?? "") !== "Customer"
) {
    header("Location: admin/dashboard.php");
    exit;
}


// The cart cannot be empty
if (
    !isset($_SESSION["cart"]) ||
    !is_array($_SESSION["cart"]) ||
    empty($_SESSION["cart"])
) {
    header("Location: cart.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$errors = [];


// =====================================================
// Retrieve Customer Information
// =====================================================

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


// =====================================================
// Retrieve Cart Products
// =====================================================

$cartItems = [];
$subtotal = 0;
$totalQty = 0;

$productStmt = $conn->prepare("
    SELECT
        product_id,
        product_name,
        price,
        image,
        stock_quantity
    FROM products
    WHERE product_id = ?
      AND product_status = 'Available'
    LIMIT 1
");

foreach (
    $_SESSION["cart"] as $productId => $quantity
) {
    $productId = (int) $productId;
    $quantity = (int) $quantity;

    if ($productId < 1 || $quantity < 1) {
        continue;
    }

    $productStmt->bind_param("i", $productId);
    $productStmt->execute();

    $productResult = $productStmt->get_result();
    $product = $productResult->fetch_assoc();

    if (!$product) {
        unset($_SESSION["cart"][$productId]);
        continue;
    }

    $stockQuantity =
        (int) $product["stock_quantity"];

    // Ensure the order does not exceed available stock
    $quantity = min($quantity, $stockQuantity);

    if ($quantity < 1) {
        unset($_SESSION["cart"][$productId]);
        continue;
    }

    $_SESSION["cart"][$productId] = $quantity;

    $lineTotal =
        (float) $product["price"] * $quantity;

    $cartItems[] = [
        "product_id" =>
            (int) $product["product_id"],

        "product_name" =>
            $product["product_name"],

        "price" =>
            (float) $product["price"],

        "image" =>
            $product["image"],

        "quantity" =>
            $quantity,

        "line_total" =>
            $lineTotal
    ];

    $subtotal += $lineTotal;
    $totalQty += $quantity;
}


// Redirect if every cart product became unavailable
if (empty($cartItems)) {
    header("Location: cart.php");
    exit;
}


// =====================================================
// Default Form Values
// =====================================================

$customerName = $user["full_name"];
$customerEmail = $user["email"];
$customerPhone = $user["phone"];

$fulfilmentMethod = "Pickup";
$deliveryAddress = "";

$requestedDate = date(
    "Y-m-d",
    strtotime("+1 day")
);

$requestedTime = "13:30";
$orderNotes = "";
$paymentMethod = "Pay at Counter";

$deliveryFee = 0.00;
$grandTotal = $subtotal;


// =====================================================
// Process Checkout Form
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $customerName =
        trim($_POST["customer_name"] ?? "");

    $customerEmail =
        trim($_POST["customer_email"] ?? "");

    $customerPhone =
        trim($_POST["customer_phone"] ?? "");

    $fulfilmentMethod =
        $_POST["fulfilment_method"] ?? "Pickup";

    $deliveryAddress =
        trim($_POST["delivery_address"] ?? "");

    $requestedDate =
        $_POST["requested_date"] ?? "";

    $requestedTime =
        $_POST["requested_time"] ?? "";

    $orderNotes =
        trim($_POST["order_notes"] ?? "");

    $paymentMethod =
        $_POST["payment_method"] ??
        "Pay at Counter";


    // Validate customer name
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


    // Validate phone
    if (
        !preg_match(
            '/^[0-9+\-\s]{8,20}$/',
            $customerPhone
        )
    ) {
        $errors["customer_phone"] =
            "Please enter a valid phone number.";
    }


    // Validate fulfilment method
    if (
        !in_array(
            $fulfilmentMethod,
            ["Pickup", "Delivery"],
            true
        )
    ) {
        $errors["fulfilment_method"] =
            "Please select a valid fulfilment method.";
    }


    // Delivery requires an address
    if (
        $fulfilmentMethod === "Delivery" &&
        $deliveryAddress === ""
    ) {
        $errors["delivery_address"] =
            "Please enter your delivery address.";
    }


    // Validate requested date
    $minimumDate = date(
        "Y-m-d",
        strtotime("+1 day")
    );

    if (
        $requestedDate === "" ||
        $requestedDate < $minimumDate
    ) {
        $errors["requested_date"] =
            "Please select a date from tomorrow onwards.";
    }


    // Validate requested time
	if (
		$requestedTime === "" ||
		$requestedTime < "08:00" ||
		$requestedTime > "20:00"
	) {
		$errors["requested_time"] =
			"Please select a time between 8:00 AM and 8:00 PM.";
	}


    // Only payment at collection is currently supported
	if ($paymentMethod !== "Pay at Counter") {
		$errors["payment_method"] =
			"Please select a valid payment method.";
	}


    // Calculate final amount
    $deliveryFee =
        $fulfilmentMethod === "Delivery"
            ? 12.00
            : 0.00;

    $grandTotal =
        $subtotal + $deliveryFee;


    // =================================================
    // Create Order
    // =================================================

    if (empty($errors)) {

        try {
            $conn->begin_transaction();

            $orderNumber =
                "YL" .
                date("ymd") .
                random_int(1000, 9999);


            $orderStmt = $conn->prepare("
                INSERT INTO orders
                (
                    user_id,
                    order_number,
                    customer_name,
                    customer_email,
                    customer_phone,
                    fulfilment_method,
                    delivery_address,
                    requested_date,
                    requested_time,
                    order_notes,
                    subtotal,
                    delivery_fee,
                    grand_total,
                    payment_method
                )
                VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?
                )
            ");

            $orderStmt->bind_param(
                "isssssssssddds",
                $userId,
                $orderNumber,
                $customerName,
                $customerEmail,
                $customerPhone,
                $fulfilmentMethod,
                $deliveryAddress,
                $requestedDate,
                $requestedTime,
                $orderNotes,
                $subtotal,
                $deliveryFee,
                $grandTotal,
                $paymentMethod
            );

            $orderStmt->execute();

            $orderId = $conn->insert_id;


            $itemStmt = $conn->prepare("
                INSERT INTO order_items
                (
                    order_id,
                    product_id,
                    product_name,
                    product_price,
                    quantity,
                    line_total
                )
                VALUES (?, ?, ?, ?, ?, ?)
            ");


            $stockStmt = $conn->prepare("
                UPDATE products
                SET stock_quantity =
                    stock_quantity - ?
                WHERE product_id = ?
                  AND stock_quantity >= ?
            ");


            foreach ($cartItems as $item) {

                $itemStmt->bind_param(
                    "iisdid",
                    $orderId,
                    $item["product_id"],
                    $item["product_name"],
                    $item["price"],
                    $item["quantity"],
                    $item["line_total"]
                );

                $itemStmt->execute();


                $stockStmt->bind_param(
                    "iii",
                    $item["quantity"],
                    $item["product_id"],
                    $item["quantity"]
                );

                $stockStmt->execute();

                if ($stockStmt->affected_rows !== 1) {
                    throw new Exception(
                        "A product no longer has enough stock."
                    );
                }
            }


            $conn->commit();

            // Empty the cart after successful checkout
            $_SESSION["cart"] = [];

            $_SESSION["completed_order_number"] =
                $orderNumber;

            header(
                "Location: order-success.php?order=" .
                urlencode($orderNumber)
            );

            exit;

        } catch (Throwable $error) {

            $conn->rollback();

            $errors["general"] =
                "We could not complete your order. Please review your cart and try again.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Checkout | The Yeast Lab</title>

    <link rel="stylesheet" href="style.css">
</head>

<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>


    <main class="checkout-page">

        <!-- Checkout heading -->
        <section class="checkout-heading">

            <div>
                <p class="section-label">
                    Complete Your Experiment
                </p>

                <h1>Checkout</h1>

                <p>
                    Review your bakes and provide your collection
                    or delivery information.
                </p>
            </div>

            <a href="cart.php">
                ← Return to Cart
            </a>

        </section>


        <?php if (isset($errors["general"])): ?>

            <div class="form-message error-message checkout-message">
                <?php echo htmlspecialchars(
                    $errors["general"]
                ); ?>
            </div>

        <?php endif; ?>


        <form action="checkout.php"
              method="POST"
              class="checkout-layout"
              id="checkout-form"
              novalidate>


            <!-- Customer and fulfilment form -->
            <div class="checkout-form-area">

                <!-- Contact information -->
                <section class="checkout-panel">

                    <div class="checkout-panel-heading">
                        <span>01</span>

                        <div>
                            <h2>Contact Information</h2>
                            <p>Who should we contact about this order?</p>
                        </div>
                    </div>


                    <div class="checkout-fields">

                        <div class="auth-field full-field">

                            <label for="customer-name">
                                Full Name
                            </label>

                            <input type="text"
                                   id="customer-name"
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

                            <label for="customer-email">
                                Email Address
                            </label>

                            <input type="email"
                                   id="customer-email"
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

                            <label for="customer-phone">
                                Phone Number
                            </label>

                            <input type="tel"
                                   id="customer-phone"
                                   name="customer_phone"
                                   value="<?php echo htmlspecialchars(
                                       $customerPhone
                                   ); ?>"
                                   required>

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

                    </div>

                </section>


                <!-- Fulfilment method -->
                <section class="checkout-panel">

                    <div class="checkout-panel-heading">
                        <span>02</span>

                        <div>
                            <h2>Fulfilment Method</h2>
                            <p>Choose how you would like to receive your order.</p>
                        </div>
                    </div>


                    <div class="choice-grid">

                        <label class="choice-card">

                            <input type="radio"
                                   name="fulfilment_method"
                                   value="Pickup"
                                   <?php echo $fulfilmentMethod === "Pickup"
                                       ? "checked"
                                       : ""; ?>>

                            <span class="choice-content">
                                <strong>Self-Pickup</strong>
                                <small>
                                    Collect from The Yeast Lab
                                </small>
                                <b>Free</b>
                            </span>

                        </label>


                        <label class="choice-card">

                            <input type="radio"
                                   name="fulfilment_method"
                                   value="Delivery"
                                   <?php echo $fulfilmentMethod === "Delivery"
                                       ? "checked"
                                       : ""; ?>>

                            <span class="choice-content">
                                <strong>Delivery</strong>
                                <small>
                                    Selected Kuala Lumpur areas
                                </small>
                                <b>RM 12.00</b>
                            </span>

                        </label>

                    </div>


                    <?php if (
                        isset($errors["fulfilment_method"])
                    ): ?>
                        <span class="field-error">
                            <?php echo htmlspecialchars(
                                $errors["fulfilment_method"]
                            ); ?>
                        </span>
                    <?php endif; ?>


                    <!-- Delivery address -->
                    <div class="auth-field full-field delivery-address-field"
                         id="delivery-address-field">

                        <label for="delivery-address">
                            Delivery Address
                        </label>

                        <textarea id="delivery-address"
                                  name="delivery_address"
                                  rows="4"
                                  placeholder="Enter your complete delivery address"><?php
                            echo htmlspecialchars($deliveryAddress);
                        ?></textarea>

                        <?php if (
                            isset($errors["delivery_address"])
                        ): ?>
                            <span class="field-error">
                                <?php echo htmlspecialchars(
                                    $errors["delivery_address"]
                                ); ?>
                            </span>
                        <?php endif; ?>

                    </div>

                </section>


                <!-- Date and payment -->
                <section class="checkout-panel">

                    <div class="checkout-panel-heading">
                        <span>03</span>

                        <div>
                            <h2>Schedule and Payment</h2>
                            <p>Select your preferred date, time and payment method.</p>
                        </div>
                    </div>


                    <div class="checkout-fields">

                        <div class="auth-field">

                            <label for="requested-date">
                                Preferred Date
                            </label>

                            <input type="date"
                                   id="requested-date"
                                   name="requested_date"
                                   min="<?php echo date(
                                       "Y-m-d",
                                       strtotime("+1 day")
                                   ); ?>"
                                   value="<?php echo htmlspecialchars(
                                       $requestedDate
                                   ); ?>"
                                   required>

                            <?php if (
                                isset($errors["requested_date"])
                            ): ?>
                                <span class="field-error">
                                    <?php echo htmlspecialchars(
                                        $errors["requested_date"]
                                    ); ?>
                                </span>
                            <?php endif; ?>

                        </div>


                        <div class="auth-field">

                            <label for="requested-time">
                                Preferred Time
                            </label>

                            <input type="time"
                                   id="requested-time"
                                   name="requested_time"
                                   min="08:00"
                                   max="20:00"
                                   value="<?php echo htmlspecialchars(
                                       $requestedTime
                                   ); ?>"
                                   required>

                            <?php if (
                                isset($errors["requested_time"])
                            ): ?>
                                <span class="field-error">
                                    <?php echo htmlspecialchars(
                                        $errors["requested_time"]
                                    ); ?>
                                </span>
                            <?php endif; ?>

                        </div>


                        <div class="auth-field full-field">

                            <label for="payment-method-display">
                                Payment Method
                            </label>

                            <input type="text"
							   id="payment-method-display"
							   value="Pay at Counter"
							   readonly>

						<input type="hidden"
							   name="payment_method"
							   value="Pay at Counter">

                        </div>


                        <div class="auth-field full-field">

                            <label for="order-notes">
                                Order Notes
                                <span>(Optional)</span>
                            </label>

                            <textarea id="order-notes"
                                      name="order_notes"
                                      rows="4"
                                      placeholder="Allergies, special requests or collection notes"><?php
                                echo htmlspecialchars($orderNotes);
                            ?></textarea>

                        </div>

                    </div>

                </section>

            </div>


            <!-- Order summary -->
            <aside class="checkout-summary">

                <div class="checkout-summary-heading">
                    <p class="section-label">Your Selection</p>
                    <h2>Order Summary</h2>
                </div>


                <div class="checkout-products">

                    <?php foreach ($cartItems as $item): ?>

                        <div class="checkout-product">

                            <div class="checkout-product-image">

                                <img src="includes/<?php
                                    echo htmlspecialchars(
                                        $item["image"]
                                    );
                                ?>"
                                     alt="<?php
                                        echo htmlspecialchars(
                                            $item["product_name"]
                                        );
                                     ?>">

                                <span>
                                    <?php echo $item["quantity"]; ?>
                                </span>

                            </div>

                            <div>
                                <h3>
                                    <?php echo htmlspecialchars(
                                        $item["product_name"]
                                    ); ?>
                                </h3>

                                <p>
                                    RM <?php echo number_format(
                                        $item["price"],
                                        2
                                    ); ?> each
                                </p>
                            </div>

                            <strong>
                                RM <?php echo number_format(
                                    $item["line_total"],
                                    2
                                ); ?>
                            </strong>

                        </div>

                    <?php endforeach; ?>

                </div>


                <div class="checkout-totals">

                    <div>
                        <span>Subtotal</span>

                        <strong>
                            RM <?php echo number_format(
                                $subtotal,
                                2
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Delivery Fee</span>

                        <strong id="delivery-fee-display">
                            RM <?php echo number_format(
                                $deliveryFee,
                                2
                            ); ?>
                        </strong>
                    </div>

                    <div class="checkout-grand-total">
                        <span>Grand Total</span>

                        <strong id="checkout-total-display"
                                data-subtotal="<?php
                                    echo number_format(
                                        $subtotal,
                                        2,
                                        ".",
                                        ""
                                    );
                                ?>">
                            RM <?php echo number_format(
                                $grandTotal,
                                2
                            ); ?>
                        </strong>
                    </div>

                </div>


                <button type="submit"
                        class="place-order-button">
                    Place My Order
                </button>

                <p class="checkout-notice">
                    By placing your order, you confirm that the
                    information provided is correct.
                </p>

            </aside>

        </form>

    </main>


    <?php require_once __DIR__ . "/includes/footer.php"; ?>

</body>
</html>