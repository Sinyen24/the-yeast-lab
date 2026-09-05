<?php

session_start();
require_once __DIR__ . "/db.php";


// Customer must be logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$orderNumber = trim($_GET["order"] ?? "");


// Validate the order number
if (
    $orderNumber === "" ||
    !preg_match('/^YL[0-9]{10}$/', $orderNumber)
) {
    header("Location: profile.php");
    exit;
}


// Retrieve only an order belonging to this customer
$orderStmt = $conn->prepare("
    SELECT
        order_id,
        order_number,
        customer_name,
        customer_email,
        customer_phone,
        fulfilment_method,
        delivery_address,
        requested_date,
        requested_time,
        subtotal,
        delivery_fee,
        grand_total,
        order_status,
        payment_method,
        payment_status,
        created_at
    FROM orders
    WHERE order_number = ?
      AND user_id = ?
    LIMIT 1
");

$orderStmt->bind_param(
    "si",
    $orderNumber,
    $userId
);

$orderStmt->execute();

$orderResult = $orderStmt->get_result();
$order = $orderResult->fetch_assoc();


// Stop customers from viewing another person's order
if (!$order) {
    header("Location: profile.php");
    exit;
}


// Retrieve products in the order
$itemStmt = $conn->prepare("
    SELECT
        product_name,
        product_price,
        quantity,
        line_total
    FROM order_items
    WHERE order_id = ?
    ORDER BY order_item_id
");

$itemStmt->bind_param(
    "i",
    $order["order_id"]
);

$itemStmt->execute();
$itemResult = $itemStmt->get_result();


// Cart is empty after successful checkout
$totalQty = 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Order Confirmed | The Yeast Lab</title>

    <link rel="stylesheet" href="style.css">
</head>

<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>


    <main class="order-success-page">

        <!-- Confirmation heading -->
        <section class="success-hero">

            <div class="success-symbol" aria-hidden="true">
                ✓
            </div>

            <p class="section-label">
                Experiment Confirmed
            </p>

            <h1>Your order is<br>in the oven.</h1>

            <p>
                Thank you,
                <?php echo htmlspecialchars(
                    $order["customer_name"]
                ); ?>!

                We have received your order and will begin
                preparing your bakes.
            </p>

            <div class="order-number">
                <span>Order Number</span>

                <strong>
                    <?php echo htmlspecialchars(
                        $order["order_number"]
                    ); ?>
                </strong>
            </div>

        </section>


        <!-- Order information -->
        <section class="confirmation-layout">

            <div class="confirmation-details">

                <div class="confirmation-heading">
                    <span>01</span>
                    <h2>Order Information</h2>
                </div>

                <div class="confirmation-information">

                    <div>
                        <span>Order Status</span>

                        <strong>
                            <?php echo htmlspecialchars(
                                $order["order_status"]
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Fulfilment Method</span>

                        <strong>
                            <?php echo htmlspecialchars(
                                $order["fulfilment_method"]
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Requested Date</span>

                        <strong>
                            <?php echo date(
                                "d M Y",
                                strtotime(
                                    $order["requested_date"]
                                )
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Requested Time</span>

                        <strong>
                            <?php echo date(
                                "g:i A",
                                strtotime(
                                    $order["requested_time"]
                                )
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Payment Method</span>

                        <strong>
                            <?php echo htmlspecialchars(
                                $order["payment_method"]
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Payment Status</span>

                        <strong>
                            <?php echo htmlspecialchars(
                                $order["payment_status"]
                            ); ?>
                        </strong>
                    </div>

                </div>


                <?php if (
                    $order["fulfilment_method"] === "Delivery"
                ): ?>

                    <div class="confirmation-address">
                        <span>Delivery Address</span>

                        <p>
                            <?php echo nl2br(
                                htmlspecialchars(
                                    $order["delivery_address"]
                                )
                            ); ?>
                        </p>
                    </div>

                <?php else: ?>

                    <div class="confirmation-address">
                        <span>Pickup Location</span>

                        <p>
                            The Yeast Lab, 204A Jalan Ampang,
                            Kampung Datuk Keramat,
                            50450 Kuala Lumpur.
                        </p>
                    </div>

                <?php endif; ?>

            </div>


            <!-- Purchased products -->
            <aside class="confirmation-summary">

                <div class="confirmation-heading">
                    <span>02</span>
                    <h2>Your Bakes</h2>
                </div>

                <div class="confirmation-items">

                    <?php while (
                        $item = $itemResult->fetch_assoc()
                    ): ?>

                        <div class="confirmation-item">

                            <div>
                                <h3>
                                    <?php echo htmlspecialchars(
                                        $item["product_name"]
                                    ); ?>
                                </h3>

                                <p>
                                    <?php echo $item["quantity"]; ?>
                                    × RM <?php echo number_format(
                                        $item["product_price"],
                                        2
                                    ); ?>
                                </p>
                            </div>

                            <strong>
                                RM <?php echo number_format(
                                    $item["line_total"],
                                    2
                                ); ?>
                            </strong>

                        </div>

                    <?php endwhile; ?>

                </div>


                <div class="confirmation-totals">

                    <div>
                        <span>Subtotal</span>

                        <strong>
                            RM <?php echo number_format(
                                $order["subtotal"],
                                2
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Delivery Fee</span>

                        <strong>
                            RM <?php echo number_format(
                                $order["delivery_fee"],
                                2
                            ); ?>
                        </strong>
                    </div>

                    <div class="confirmation-grand-total">
                        <span>Grand Total</span>

                        <strong>
                            RM <?php echo number_format(
                                $order["grand_total"],
                                2
                            ); ?>
                        </strong>
                    </div>

                </div>

            </aside>

        </section>


        <!-- Next actions -->
        <section class="success-actions">

            <a href="profile.php"
               class="success-primary-button">
                View My Orders
            </a>

            <a href="listing.php"
               class="success-secondary-button">
                Continue Shopping
            </a>

        </section>

    </main>


    <?php require_once __DIR__ . "/includes/footer.php"; ?>

</body>
</html>