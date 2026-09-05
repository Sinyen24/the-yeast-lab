<?php

session_start();
require_once "db.php";

// Calculate the number shown beside the cart
$totalQty = 0;

if (isset($_SESSION["cart"]) && is_array($_SESSION["cart"])) {
    $totalQty = array_sum($_SESSION["cart"]);
}


// Get filter values from the URL
$selectedCategory = trim($_GET["category"] ?? "");
$searchTerm = trim($_GET["search"] ?? "");
$sortOption = $_GET["sort"] ?? "newest";


// Basic product query
$sql = "
    SELECT
        products.product_id,
        products.product_name,
        products.product_description,
        products.price,
        products.image,
        products.stock_quantity,
        products.is_featured,
        categories.category_name,
        categories.category_slug
    FROM products
    INNER JOIN categories
        ON products.category_id = categories.category_id
    WHERE products.product_status = 'Available'
";

$parameters = [];
$parameterTypes = "";


// Apply category filter
if ($selectedCategory !== "") {
    $sql .= " AND categories.category_slug = ?";
    $parameters[] = $selectedCategory;
    $parameterTypes .= "s";
}


// Apply search
if ($searchTerm !== "") {
    $sql .= "
        AND (
            products.product_name LIKE ?
            OR products.product_description LIKE ?
        )
    ";

    $searchValue = "%" . $searchTerm . "%";

    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
    $parameterTypes .= "ss";
}


// Apply selected sorting option
switch ($sortOption) {
    case "price-low":
        $sql .= " ORDER BY products.price ASC";
        break;

    case "price-high":
        $sql .= " ORDER BY products.price DESC";
        break;

    case "name":
        $sql .= " ORDER BY products.product_name ASC";
        break;

    default:
        $sql .= " ORDER BY products.product_id DESC";
}


// Prepare and run the query
$stmt = $conn->prepare($sql);

if (!empty($parameters)) {
    $stmt->bind_param($parameterTypes, ...$parameters);
}

$stmt->execute();
$productResult = $stmt->get_result();


// Retrieve categories for the filter buttons
$categoryResult = $conn->query("
    SELECT category_name, category_slug
    FROM categories
    ORDER BY category_id
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Our Bakes | The Yeast Lab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>

    <!-- Shop Page Introduction -->
<section class="shop-hero">
    <p class="section-label">The Yeast Lab Collection</p>
    <h1>Explore Our Bakes</h1>

    <p>
        Discover pastries and breads created through careful technique,
        patient fermentation and a little delicious curiosity.
    </p>
</section>


<!-- Product Listing Section -->
<main class="shop-section">

    <!-- Category filters -->
    <div class="category-filters">

        <a href="listing.php"
           class="<?php echo $selectedCategory === ""
               ? "active"
               : ""; ?>">
            All Products
        </a>

        <?php while ($category = $categoryResult->fetch_assoc()): ?>

            <a href="listing.php?category=<?php
                echo urlencode($category["category_slug"]);
            ?>"
               class="<?php echo $selectedCategory ===
                   $category["category_slug"]
                       ? "active"
                       : ""; ?>">

                <?php echo htmlspecialchars(
                    $category["category_name"]
                ); ?>

            </a>

        <?php endwhile; ?>

    </div>


    <!-- Search and sorting controls -->
    <form action="listing.php"
          method="GET"
          class="shop-controls">

        <?php if ($selectedCategory !== ""): ?>

            <input type="hidden"
                   name="category"
                   value="<?php echo htmlspecialchars(
                       $selectedCategory
                   ); ?>">

        <?php endif; ?>

        <div class="product-search">
            <label for="product-search">
                Search our bakes
            </label>

            <div class="search-box">
                <input type="search"
                       id="product-search"
                       name="search"
                       placeholder="Search by product name..."
                       value="<?php echo htmlspecialchars(
                           $searchTerm
                       ); ?>">

                <button type="submit">
                    Search
                </button>
            </div>
        </div>

        <div class="product-sort">
            <label for="product-sort">
                Sort products
            </label>

            <select id="product-sort"
                    name="sort"
                    onchange="this.form.submit()">

                <option value="newest"
                    <?php echo $sortOption === "newest"
                        ? "selected"
                        : ""; ?>>
                    Newest
                </option>

                <option value="price-low"
                    <?php echo $sortOption === "price-low"
                        ? "selected"
                        : ""; ?>>
                    Price: Low to High
                </option>

                <option value="price-high"
                    <?php echo $sortOption === "price-high"
                        ? "selected"
                        : ""; ?>>
                    Price: High to Low
                </option>

                <option value="name"
                    <?php echo $sortOption === "name"
                        ? "selected"
                        : ""; ?>>
                    Product Name
                </option>

            </select>
        </div>

    </form>


    <!-- Product count -->
    <div class="shop-results-header">
        <p>
            Showing
            <strong><?php echo $productResult->num_rows; ?></strong>
            product<?php echo $productResult->num_rows !== 1
                ? "s"
                : ""; ?>
        </p>

        <?php if (
            $selectedCategory !== "" ||
            $searchTerm !== ""
        ): ?>

            <a href="listing.php">
                Clear Filters ×
            </a>

        <?php endif; ?>
    </div>


    <?php if ($productResult->num_rows > 0): ?>

        <!-- Products from MySQL -->
        <div class="listing-product-grid">

            <?php while (
                $product = $productResult->fetch_assoc()
            ): ?>

                <article class="product-card">

                    <div class="product-image">

                        <a href="details.php?id=<?php
                            echo $product["product_id"];
                        ?>">

                            <img src="includes/<?php
                                echo htmlspecialchars(
                                    $product["image"]
                                );
                            ?>"
                                 alt="<?php
                                    echo htmlspecialchars(
                                        $product["product_name"]
                                    );
                                 ?>">
                        </a>

                        <?php if (
                            (int) $product["is_featured"] === 1
                        ): ?>

                            <span class="product-badge">
                                Best Seller
                            </span>

                        <?php endif; ?>

                        <?php if (
                            (int) $product["stock_quantity"] > 0
                        ): ?>

                            <a href="cart.php?action=add&id=<?php
                                echo $product["product_id"];
                            ?>"
                               class="quick-add">
                                Add to Cart
                            </a>

                        <?php else: ?>

                            <span class="quick-add out-of-stock">
                                Out of Stock
                            </span>

                        <?php endif; ?>

                    </div>

                    <div class="product-information">

                        <p class="product-category">
                            <?php echo htmlspecialchars(
                                $product["category_name"]
                            ); ?>
                        </p>

                        <h3>
                            <a href="details.php?id=<?php
                                echo $product["product_id"];
                            ?>">
                                <?php echo htmlspecialchars(
                                    $product["product_name"]
                                ); ?>
                            </a>
                        </h3>
						
						<p class="product-card-description">
							<?php echo htmlspecialchars(
								$product["product_description"]
							); ?>
							</p>


                        <div class="product-price-row">

                            <p class="product-price">
                                RM <?php echo number_format(
                                    $product["price"],
                                    2
                                ); ?>
                            </p>

                            <span>
                                <?php echo
                                    (int) $product["stock_quantity"] > 0
                                        ? "In Stock"
                                        : "Unavailable";
                                ?>
                            </span>

                        </div>

                    </div>

                </article>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <!-- Displayed when no products match -->
        <div class="no-products">

            <span>⚗</span>

            <h2>No experiments found</h2>

            <p>
                We couldn’t find a product matching your search.
                Try another keyword or explore all our bakes.
            </p>

            <a href="listing.php">
                View All Products
            </a>

        </div>

    <?php endif; ?>

</main>

   <?php require_once __DIR__ . "/includes/footer.php"; ?>

</body>
</html>
