<?php

require_once __DIR__ . "/auth-check.php";
require_once __DIR__ . "/../db.php";


// =====================================================
// Determine Add or Edit Mode
// =====================================================

$productId = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

$isEditing = $productId && $productId > 0;


// Default form values
$productName = "";
$productDescription = "";
$categoryId = "";
$price = "";
$image = "";
$stockQuantity = 0;
$isFeatured = 0;
$productStatus = "Available";

$formError = "";


// =====================================================
// Retrieve Existing Product for Editing
// =====================================================

if ($isEditing) {

    $productStmt = $conn->prepare("
        SELECT
            product_id,
            category_id,
            product_name,
            product_description,
            price,
            image,
            stock_quantity,
            is_featured,
            product_status
        FROM products
        WHERE product_id = ?
    ");

    $productStmt->bind_param("i", $productId);
    $productStmt->execute();

    $productResult = $productStmt->get_result();

    if ($productResult->num_rows !== 1) {
        $_SESSION["product_error"] =
            "The selected product could not be found.";

        header("Location: products.php");
        exit;
    }

    $product = $productResult->fetch_assoc();

    $categoryId = $product["category_id"];
    $productName = $product["product_name"];
    $productDescription =
        $product["product_description"];
    $price = $product["price"];
    $image = $product["image"];
    $stockQuantity = $product["stock_quantity"];
    $isFeatured = $product["is_featured"];
    $productStatus = $product["product_status"];
}


// =====================================================
// Process Product Form
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $postedProductId = filter_input(
        INPUT_POST,
        "product_id",
        FILTER_VALIDATE_INT
    );

    $productName =
        trim($_POST["product_name"] ?? "");

    $productDescription =
        trim($_POST["product_description"] ?? "");

    $categoryId = filter_input(
        INPUT_POST,
        "category_id",
        FILTER_VALIDATE_INT
    );

    $price = trim($_POST["price"] ?? "");

    $image = trim($_POST["image"] ?? "");

    $stockQuantity = filter_input(
        INPUT_POST,
        "stock_quantity",
        FILTER_VALIDATE_INT
    );

    $isFeatured =
        isset($_POST["is_featured"]) ? 1 : 0;

    $productStatus =
        $_POST["product_status"] ?? "Available";


    // =================================================
    // Server-side Validation
    // =================================================

    if ($productName === "") {
        $formError = "Please enter the product name.";

    } elseif (!$categoryId || $categoryId < 1) {
        $formError = "Please select a product category.";

    } elseif ($productDescription === "") {
        $formError =
            "Please enter the product description.";

    } elseif (
        !is_numeric($price) ||
        (float) $price < 0
    ) {
        $formError =
            "Please enter a valid product price.";

    } elseif ($image === "") {
        $formError =
            "Please enter the product image filename.";

    } elseif (
        $stockQuantity === false ||
        $stockQuantity < 0
    ) {
        $formError =
            "Stock quantity cannot be negative.";

    } elseif (
        !in_array(
            $productStatus,
            ["Available", "Unavailable"],
            true
        )
    ) {
        $formError = "Invalid product status.";
    }


    // =================================================
    // Save Product
    // =================================================

    if ($formError === "") {

        $priceValue = (float) $price;


        // Update existing product
        if ($postedProductId && $postedProductId > 0) {

            $updateStmt = $conn->prepare("
                UPDATE products
                SET
                    category_id = ?,
                    product_name = ?,
                    product_description = ?,
                    price = ?,
                    image = ?,
                    stock_quantity = ?,
                    is_featured = ?,
                    product_status = ?
                WHERE product_id = ?
            ");

            $updateStmt->bind_param(
                "issdsiisi",
                $categoryId,
                $productName,
                $productDescription,
                $priceValue,
                $image,
                $stockQuantity,
                $isFeatured,
                $productStatus,
                $postedProductId
            );

            if ($updateStmt->execute()) {
                $_SESSION["product_message"] =
                    "The product was updated successfully.";

                header("Location: products.php");
                exit;
            }

            $formError =
                "The product could not be updated.";
        }


        // Create new product
        else {

            $insertStmt = $conn->prepare("
                INSERT INTO products (
                    category_id,
                    product_name,
                    product_description,
                    price,
                    image,
                    stock_quantity,
                    is_featured,
                    product_status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $insertStmt->bind_param(
                "issdsiis",
                $categoryId,
                $productName,
                $productDescription,
                $priceValue,
                $image,
                $stockQuantity,
                $isFeatured,
                $productStatus
            );

            if ($insertStmt->execute()) {
                $_SESSION["product_message"] =
                    "The new product was added successfully.";

                header("Location: products.php");
                exit;
            }

            $formError =
                "The product could not be added.";
        }
    }
}


// =====================================================
// Retrieve Categories
// =====================================================

$categoryResult = $conn->query("
    SELECT
        category_id,
        category_name
    FROM categories
    ORDER BY category_name
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo $isEditing
            ? "Edit Product"
            : "Add Product"; ?>
        | The Yeast Lab
    </title>

    <link rel="stylesheet"
          href="../style.css?v=<?php
              echo filemtime(
                  __DIR__ . "/../style.css"
              );
          ?>">
</head>

<body class="admin-body">

<?php require_once __DIR__ . "/admin-header.php"; ?>


<main class="admin-main">

    <section class="admin-page-heading">

        <div>
            <p class="section-label">
                Inventory Management
            </p>

            <h1>
                <?php echo $isEditing
                    ? "Edit Product"
                    : "Add New Product"; ?>
            </h1>

            <p>
                <?php echo $isEditing
                    ? "Update the selected product information."
                    : "Create a new product for the bakery catalogue."; ?>
            </p>
        </div>

        <a href="products.php"
           class="admin-clear-filter">
            ← Back to Products
        </a>

    </section>


    <?php if ($formError !== ""): ?>

        <div class="form-message error-message admin-message">
            <?php echo htmlspecialchars($formError); ?>
        </div>

    <?php endif; ?>


    <form action="product-form.php<?php
              echo $isEditing
                  ? "?id=" . $productId
                  : "";
          ?>"
          method="POST"
          class="admin-product-form">

        <input type="hidden"
               name="product_id"
               value="<?php echo $isEditing
                   ? $productId
                   : ""; ?>">


        <div class="admin-form-section">

            <div class="admin-form-section-heading">
                <span>01</span>

                <div>
                    <h2>Basic Information</h2>
                    <p>
                        Enter the product name, category
                        and description.
                    </p>
                </div>
            </div>


            <div class="admin-form-grid">

                <div class="admin-form-field">

                    <label for="product-name">
                        Product Name
                    </label>

                    <input type="text"
                           id="product-name"
                           name="product_name"
                           maxlength="150"
                           required
                           value="<?php
                               echo htmlspecialchars(
                                   $productName
                               );
                           ?>">
                </div>


                <div class="admin-form-field">

                    <label for="category-id">
                        Category
                    </label>

                    <select id="category-id"
                            name="category_id"
                            required>

                        <option value="">
                            Select Category
                        </option>

                        <?php while (
                            $category =
                                $categoryResult->fetch_assoc()
                        ): ?>

                            <option value="<?php
                                echo $category["category_id"];
                            ?>"
                                <?php echo
                                    (int) $categoryId ===
                                    (int) $category["category_id"]
                                        ? "selected"
                                        : "";
                                ?>>

                                <?php echo htmlspecialchars(
                                    $category["category_name"]
                                ); ?>

                            </option>

                        <?php endwhile; ?>

                    </select>
                </div>


                <div class="admin-form-field admin-full-field">

                    <label for="product-description">
                        Product Description
                    </label>

                    <textarea id="product-description"
                              name="product_description"
                              rows="6"
                              required><?php
                        echo htmlspecialchars(
                            $productDescription
                        );
                    ?></textarea>
                </div>

            </div>
        </div>


        <div class="admin-form-section">

            <div class="admin-form-section-heading">
                <span>02</span>

                <div>
                    <h2>Price and Inventory</h2>
                    <p>
                        Set the selling price and available stock.
                    </p>
                </div>
            </div>


            <div class="admin-form-grid">

                <div class="admin-form-field">

                    <label for="price">
                        Price (RM)
                    </label>

                    <input type="number"
                           id="price"
                           name="price"
                           min="0"
                           step="0.01"
                           required
                           value="<?php
                               echo htmlspecialchars($price);
                           ?>">
                </div>


                <div class="admin-form-field">

                    <label for="stock-quantity">
                        Stock Quantity
                    </label>

                    <input type="number"
                           id="stock-quantity"
                           name="stock_quantity"
                           min="0"
                           step="1"
                           required
                           value="<?php
                               echo htmlspecialchars(
                                   (string) $stockQuantity
                               );
                           ?>">
                </div>

            </div>
        </div>


        <div class="admin-form-section">

            <div class="admin-form-section-heading">
                <span>03</span>

                <div>
                    <h2>Display Settings</h2>
                    <p>
                        Choose the product image,
                        visibility and featured status.
                    </p>
                </div>
            </div>


            <div class="admin-form-grid">

                <div class="admin-form-field">

                    <label for="image">
                        Image Filename
                    </label>

                    <input type="text"
                           id="image"
                           name="image"
                           maxlength="255"
                           placeholder="Example: shiopan.jpeg"
                           required
                           value="<?php
                               echo htmlspecialchars($image);
                           ?>">

                    <small>
                        The image must be stored inside
                        the includes folder.
                    </small>
                </div>


                <div class="admin-form-field">

                    <label for="product-status">
                        Product Status
                    </label>

                    <select id="product-status"
                            name="product_status">

                        <option value="Available"
                            <?php echo
                                $productStatus === "Available"
                                    ? "selected"
                                    : "";
                            ?>>
                            Available
                        </option>

                        <option value="Unavailable"
                            <?php echo
                                $productStatus === "Unavailable"
                                    ? "selected"
                                    : "";
                            ?>>
                            Unavailable
                        </option>

                    </select>
                </div>


                <div class="admin-form-checkbox admin-full-field">

                    <input type="checkbox"
                           id="is-featured"
                           name="is_featured"
                           value="1"
                           <?php echo $isFeatured
                               ? "checked"
                               : ""; ?>>

                    <label for="is-featured">
                        Display this product as a featured item
                    </label>
                </div>

            </div>
        </div>


        <div class="admin-form-actions">

            <a href="products.php"
               class="admin-clear-filter">
                Cancel
            </a>

            <button type="submit"
                    class="admin-primary-button">

                <?php echo $isEditing
                    ? "Save Product Changes"
                    : "Add Product"; ?>

            </button>

        </div>

    </form>

</main>

</body>
</html>