<?php

session_start();
require_once "db.php";

// Customers must log in before accessing the cart
if (!isset($_SESSION["user_id"])) {

    $_SESSION["login_required"] =
        "Please log in before adding products to your cart.";

    $_SESSION["redirect_after_login"] =
        $_SERVER["REQUEST_URI"];

    header("Location: login.php");
    exit;
}


// Prevent administrators from using the customer cart
if (
    ($_SESSION["user_role"] ?? "") !== "Customer"
) {
    header("Location: admin/dashboard.php");
    exit;
}


// Create the cart session if it does not exist
if (
    !isset($_SESSION["cart"]) ||
    !is_array($_SESSION["cart"])
) {
    $_SESSION["cart"] = [];
}


// =====================================================
// Handle Cart Actions
// =====================================================

if (isset($_GET["action"])) {

    $action = $_GET["action"];

    $productId = filter_input(
        INPUT_GET,
        "id",
        FILTER_VALIDATE_INT
    );

    // Add or increase a product
    if (
        ($action === "add" || $action === "increase") &&
        $productId &&
        $productId > 0
    ) {
        // Check that the product exists and retrieve its stock
        $stockStmt = $conn->prepare("
            SELECT stock_quantity
            FROM products
            WHERE product_id = ?
              AND product_status = 'Available'
            LIMIT 1
        ");

        $stockStmt->bind_param("i", $productId);
        $stockStmt->execute();

        $stockResult = $stockStmt->get_result();
        $stockProduct = $stockResult->fetch_assoc();

        if ($stockProduct) {

            $availableStock =
                (int) $stockProduct["stock_quantity"];

            $currentQuantity =
                $_SESSION["cart"][$productId] ?? 0;

            // Quantity selected on details.php
            if ($action === "add") {

                $requestedQuantity = filter_input(
                    INPUT_GET,
                    "quantity",
                    FILTER_VALIDATE_INT
                );

                if (
                    !$requestedQuantity ||
                    $requestedQuantity < 1
                ) {
                    $requestedQuantity = 1;
                }

            } else {
                // Increase button on cart page
                $requestedQuantity = 1;
            }

            $newQuantity =
                $currentQuantity + $requestedQuantity;

            // Never allow cart quantity to exceed stock
            $_SESSION["cart"][$productId] = min(
                $newQuantity,
                $availableStock
            );
        }
    }


    // Decrease product quantity
    if (
        $action === "decrease" &&
        $productId &&
        isset($_SESSION["cart"][$productId])
    ) {
        $_SESSION["cart"][$productId]--;

        if ($_SESSION["cart"][$productId] <= 0) {
            unset($_SESSION["cart"][$productId]);
        }
    }


    // Remove one product completely
    if (
        $action === "remove" &&
        $productId
    ) {
        unset($_SESSION["cart"][$productId]);
    }


    // Empty the complete cart
    if ($action === "reset") {
        $_SESSION["cart"] = [];
    }


    header("Location: cart.php");
    exit;
}


// =====================================================
// Retrieve Cart Products from MySQL
// =====================================================

$cartItems = [];
$grandTotal = 0;
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

    $availableStock =
        (int) $product["stock_quantity"];

    // Correct quantity if database stock has changed
    $quantity = min($quantity, $availableStock);

    if ($quantity < 1) {
        unset($_SESSION["cart"][$productId]);
        continue;
    }

    $_SESSION["cart"][$productId] = $quantity;

    $lineTotal =
        (float) $product["price"] * $quantity;

    $cartItems[] = [
        "id" => $product["product_id"],
        "name" => $product["product_name"],
        "price" => $product["price"],
        "image" => "includes/" . $product["image"],
        "quantity" => $quantity,
        "stock" => $availableStock,
        "lineTotal" => $lineTotal
    ];

    $grandTotal += $lineTotal;
    $totalQty += $quantity;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart | The Yeast Lab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>

    <div class="container cart-page">
        <div class="cart-header">
            <a href="index.php" class="back-link" title="Back to home">&larr;</a>
            <h1>Cart Summary</h1>
        </div>

        <div class="pickup-box">
            <div class="pickup-col">
                <span class="pickup-label">Self Pick-Up At</span>
                <strong>The Yeast Lab, Jalan Ampang</strong>
                <p>204A, Jalan Ampang, Kampung Datuk Keramat, 50450 Kuala Lumpur.</p>
            </div>
            <div class="pickup-col">
                <span class="pickup-label">Earliest Self Pick-Up Date &amp; Time</span>
                <strong><?php echo date('d M Y', strtotime('+1 day')); ?>, 01:30 PM</strong>
            </div>
        </div>

        <div class="cart-section-header">
            <h2>You've Ordered</h2>
            <a href="listing.php" class="btn-outline">+ Add Items</a>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <p>You have not ordered anything yet.</p>
                <a href="listing.php" class="btn-contact-footer" style="background:var(--nav-bg); margin-top:20px;">Start ordering now</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($cartItems as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div class="cart-item-info">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p class="unit-price">RM <?php echo number_format($item['price'], 2); ?> each</p>
                    </div>
                    <div class="qty-control">
                        <a href="cart.php?action=decrease&id=<?php echo $item['id']; ?>" class="qty-btn">&minus;</a>
                        <span class="qty-value">
							<?php echo $item["quantity"]; ?>
						</span>
                        <a href="cart.php?action=increase&id=<?php echo $item['id']; ?>" class="qty-btn">+</a>
                    </div>
                    <div class="line-total">RM <?php echo number_format($item['lineTotal'], 2); ?></div>
                    <a href="cart.php?action=remove&id=<?php echo $item['id']; ?>" class="remove-btn" onclick="return confirm('Remove <?php echo htmlspecialchars(addslashes($item['name'])); ?>?');">&times;</a>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="cart.php?action=reset" class="reset-link" onclick="return confirm('Reset your entire order?');">&#8635; Reset order</a>
        <?php endif; ?>
    </div>

		<?php require_once __DIR__ . "/includes/footer.php"; ?>

    <!-- Sticky Checkout Footer -->
    <div class="cart-footer">
        <div class="cart-footer-inner">
            <div class="grand-total">
                <span>Grand total (<?php echo $totalQty; ?> item<?php echo $totalQty === 1 ? '' : 's'; ?>)</span>
                <strong>RM <?php echo number_format($grandTotal, 2); ?></strong>
            </div>
            <?php if (empty($cartItems)): ?>
                <span class="btn-checkout disabled" style="background:#ccc;">Checkout</span>
            <?php else: ?>
                <a href="checkout.php" class="btn-checkout">Checkout</a>
            <?php endif; ?>
        </div>
    </div>


</body>
</html>