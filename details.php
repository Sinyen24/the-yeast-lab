<?php

session_start();
require_once "db.php";

// Calculate cart quantity
$totalQty = 0;

if (isset($_SESSION["cart"]) && is_array($_SESSION["cart"])) {
    $totalQty = array_sum($_SESSION["cart"]);
}


// Get product ID from the URL
$productId = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

// Stop if the ID is missing or invalid
if (!$productId || $productId < 1) {
    header("Location: listing.php");
    exit;
}


// Retrieve the selected product
$stmt = $conn->prepare("
    SELECT
        products.product_id,
        products.product_name,
        products.product_description,
        products.price,
        products.image,
        products.stock_quantity,
        products.is_featured,
        products.product_status,
        categories.category_id,
        categories.category_name,
        categories.category_slug
    FROM products
    INNER JOIN categories
        ON products.category_id = categories.category_id
    WHERE products.product_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $productId);
$stmt->execute();

$productResult = $stmt->get_result();
$product = $productResult->fetch_assoc();


// Stop if the product does not exist
if (!$product) {
    http_response_code(404);
    die("Product not found.");
}


// Retrieve three related products
$relatedStmt = $conn->prepare("
    SELECT
        product_id,
        product_name,
        price,
        image
    FROM products
    WHERE category_id = ?
      AND product_id != ?
      AND product_status = 'Available'
    ORDER BY is_featured DESC, product_id DESC
    LIMIT 3
");

$relatedStmt->bind_param(
    "ii",
    $product["category_id"],
    $productId
);

$relatedStmt->execute();
$relatedResult = $relatedStmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
    <?php echo htmlspecialchars($product["product_name"]); ?>
    | The Yeast Lab
</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>

    <main class="product-details-page">

    <!-- Breadcrumb navigation -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Home</a>
        <span>→</span>

        <a href="listing.php">Shop</a>
        <span>→</span>

        <a href="listing.php?category=<?php
            echo urlencode($product["category_slug"]);
        ?>">
            <?php echo htmlspecialchars(
                $product["category_name"]
            ); ?>
        </a>
        <span>→</span>

        <strong>
            <?php echo htmlspecialchars(
                $product["product_name"]
            ); ?>
        </strong>
    </nav>


    <!-- Main product information -->
    <section class="product-details">

        <div class="details-image">

            <img src="includes/<?php
                echo htmlspecialchars($product["image"]);
            ?>"
                 alt="<?php
                    echo htmlspecialchars(
                        $product["product_name"]
                    );
                 ?>">

            <?php if (
                (int) $product["is_featured"] === 1
            ): ?>

                <span class="details-badge">
                    Best Seller
                </span>

            <?php endif; ?>

        </div>


        <div class="details-content">

            <p class="section-label">
                <?php echo htmlspecialchars(
                    $product["category_name"]
                ); ?>
            </p>

            <h1>
                <?php echo htmlspecialchars(
                    $product["product_name"]
                ); ?>
            </h1>

            <p class="details-price">
                RM <?php echo number_format(
                    $product["price"],
                    2
                ); ?>
            </p>

            <p class="details-description">
                <?php echo htmlspecialchars(
                    $product["product_description"]
                ); ?>
            </p>


            <!-- Stock availability -->
            <?php if (
                $product["product_status"] === "Available" &&
                (int) $product["stock_quantity"] > 0
            ): ?>

                <p class="stock-status in-stock">
                    <span></span>
                    In Stock · Freshly available
                </p>

                <!-- Add product to cart -->
                <form action="cart.php"
                      method="GET"
                      class="add-to-cart-form">

                    <input type="hidden"
                           name="action"
                           value="add">

                    <input type="hidden"
                           name="id"
                           value="<?php echo $product["product_id"]; ?>">

                    <div class="quantity-selector">

                        <button type="button"
                                class="quantity-minus"
                                aria-label="Decrease quantity">
                            −
                        </button>

                        <input type="number"
                               name="quantity"
                               value="1"
                               min="1"
                               max="<?php
                                    echo $product["stock_quantity"];
                               ?>"
                               aria-label="Product quantity">

                        <button type="button"
                                class="quantity-plus"
                                aria-label="Increase quantity">
                            +
                        </button>

                    </div>

                    <button type="submit"
                            class="details-cart-button">
                        Add to Cart
                    </button>

                </form>

            <?php else: ?>

                <p class="stock-status unavailable">
                    <span></span>
                    Currently unavailable
                </p>

                <button type="button"
                        class="details-cart-button disabled"
                        disabled>
                    Out of Stock
                </button>

            <?php endif; ?>


            <!-- Product details -->
            <div class="product-notes">

                <div>
                    <span>01</span>

                    <p>
                        <strong>Freshness</strong>
                        Baked fresh every morning
                    </p>
                </div>

                <div>
                    <span>02</span>

                    <p>
                        <strong>Storage</strong>
                        Best enjoyed on the day of purchase
                    </p>
                </div>

                <div>
                    <span>03</span>

                    <p>
                        <strong>Collection</strong>
                        Delivery and self-pickup available
                    </p>
                </div>

            </div>

        </div>

    </section>


    <!-- Related products -->
    <?php if ($relatedResult->num_rows > 0): ?>

        <section class="related-products">

            <div class="section-heading">
                <p class="section-label">
                    You May Also Like
                </p>

                <h2>More from the Lab</h2>
            </div>

            <div class="related-products-grid">

                <?php while (
                    $relatedProduct =
                        $relatedResult->fetch_assoc()
                ): ?>

                    <article class="product-card">

                        <div class="product-image">

                            <a href="details.php?id=<?php
                                echo $relatedProduct["product_id"];
                            ?>">

                                <img src="includes/<?php
                                    echo htmlspecialchars(
                                        $relatedProduct["image"]
                                    );
                                ?>"
                                     alt="<?php
                                        echo htmlspecialchars(
                                            $relatedProduct[
                                                "product_name"
                                            ]
                                        );
                                     ?>">
                            </a>

                            <a href="cart.php?action=add&id=<?php
                                echo $relatedProduct["product_id"];
                            ?>"
                               class="quick-add">
                                Add to Cart
                            </a>

                        </div>

                        <div class="product-information">

                            <h3>
                                <a href="details.php?id=<?php
                                    echo $relatedProduct["product_id"];
                                ?>">
                                    <?php echo htmlspecialchars(
                                        $relatedProduct[
                                            "product_name"
                                        ]
                                    ); ?>
                                </a>
                            </h3>

                            <p class="product-price">
                                RM <?php echo number_format(
                                    $relatedProduct["price"],
                                    2
                                ); ?>
                            </p>

                        </div>

                    </article>

                <?php endwhile; ?>

            </div>

        </section>

    <?php endif; ?>

</main>

<?php require_once __DIR__ . "/includes/footer.php"; ?>

</body>
</html>
