<?php

require_once __DIR__ . "/auth-check.php";
require_once __DIR__ . "/../db.php";


// =====================================================
// Delete Product
// =====================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "delete"
) {
    $productId = filter_input(
        INPUT_POST,
        "product_id",
        FILTER_VALIDATE_INT
    );

    if ($productId && $productId > 0) {

        $deleteStmt = $conn->prepare("
            DELETE FROM products
            WHERE product_id = ?
        ");

        $deleteStmt->bind_param("i", $productId);

        if ($deleteStmt->execute()) {
            $_SESSION["product_message"] =
                "The product was deleted successfully.";
        } else {
            $_SESSION["product_error"] =
                "The product could not be deleted.";
        }
    }

    header("Location: products.php");
    exit;
}


// =====================================================
// Change Product Availability
// =====================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "change-status"
) {
    $productId = filter_input(
        INPUT_POST,
        "product_id",
        FILTER_VALIDATE_INT
    );

    $newStatus =
        $_POST["product_status"] ?? "";

    if (
        $productId &&
        in_array(
            $newStatus,
            ["Available", "Unavailable"],
            true
        )
    ) {
        $statusStmt = $conn->prepare("
            UPDATE products
            SET product_status = ?
            WHERE product_id = ?
        ");

        $statusStmt->bind_param(
            "si",
            $newStatus,
            $productId
        );

        if ($statusStmt->execute()) {
            $_SESSION["product_message"] =
                "The product status was updated.";
        } else {
            $_SESSION["product_error"] =
                "The product status could not be updated.";
        }
    }

    header("Location: products.php");
    exit;
}


// =====================================================
// Retrieve Messages
// =====================================================

$productMessage =
    $_SESSION["product_message"] ?? "";

$productError =
    $_SESSION["product_error"] ?? "";

unset($_SESSION["product_message"]);
unset($_SESSION["product_error"]);


// =====================================================
// Product Filters
// =====================================================

$searchTerm = trim($_GET["search"] ?? "");

$selectedCategory =
    filter_input(
        INPUT_GET,
        "category",
        FILTER_VALIDATE_INT
    );

$selectedStatus = $_GET["status"] ?? "";


// Base product query
$sql = "
    SELECT
        products.product_id,
        products.product_name,
        products.price,
        products.image,
        products.stock_quantity,
        products.is_featured,
        products.product_status,
        products.created_at,
        categories.category_name
    FROM products
    INNER JOIN categories
        ON products.category_id = categories.category_id
    WHERE 1 = 1
";

$parameters = [];
$parameterTypes = "";


// Search by product name
if ($searchTerm !== "") {
    $sql .= " AND products.product_name LIKE ?";

    $parameters[] = "%" . $searchTerm . "%";
    $parameterTypes .= "s";
}


// Filter by category
if ($selectedCategory) {
    $sql .= " AND products.category_id = ?";

    $parameters[] = $selectedCategory;
    $parameterTypes .= "i";
}


// Filter by status
if (
    in_array(
        $selectedStatus,
        ["Available", "Unavailable"],
        true
    )
) {
    $sql .= " AND products.product_status = ?";

    $parameters[] = $selectedStatus;
    $parameterTypes .= "s";
}


$sql .= " ORDER BY products.product_id DESC";


// Prepare and execute product query
$productStmt = $conn->prepare($sql);

if (!empty($parameters)) {
    $productStmt->bind_param(
        $parameterTypes,
        ...$parameters
    );
}

$productStmt->execute();

$productResult =
    $productStmt->get_result();


// Retrieve categories for the filter
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

    <title>Manage Products | The Yeast Lab</title>

    <link rel="stylesheet"
      href="../style.css?v=<?php echo filemtime(__DIR__ . '/../style.css'); ?>">
</head>

<body class="admin-body">

    <?php require_once __DIR__ . "/admin-header.php"; ?>


    <main class="admin-main">

        <!-- Page heading -->
        <section class="admin-page-heading">

            <div>
                <p class="section-label">
                    Inventory Management
                </p>

                <h1>Products</h1>

                <p>
                    Add, update, deactivate or delete products
                    from The Yeast Lab catalogue.
                </p>
            </div>

            <a href="product-form.php"
               class="admin-primary-button">
                + Add New Product
            </a>

        </section>


        <!-- Success and error messages -->
        <?php if ($productMessage !== ""): ?>

            <div class="form-message success-message admin-message">
                <?php echo htmlspecialchars(
                    $productMessage
                ); ?>
            </div>

        <?php endif; ?>


        <?php if ($productError !== ""): ?>

            <div class="form-message error-message admin-message">
                <?php echo htmlspecialchars(
                    $productError
                ); ?>
            </div>

        <?php endif; ?>


        <!-- Product filters -->
        <form action="products.php"
              method="GET"
              class="admin-filter-form">

            <div class="admin-filter-field">

                <label for="admin-product-search">
                    Search Product
                </label>

                <input type="search"
                       id="admin-product-search"
                       name="search"
                       placeholder="Enter product name..."
                       value="<?php echo htmlspecialchars(
                           $searchTerm
                       ); ?>">

            </div>


            <div class="admin-filter-field">

                <label for="admin-category-filter">
                    Category
                </label>

                <select id="admin-category-filter"
                        name="category">

                    <option value="">
                        All Categories
                    </option>

                    <?php while (
                        $category =
                            $categoryResult->fetch_assoc()
                    ): ?>

                        <option value="<?php
                            echo $category["category_id"];
                        ?>"
                            <?php echo
                                (int) $selectedCategory ===
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


            <div class="admin-filter-field">

                <label for="admin-status-filter">
                    Status
                </label>

                <select id="admin-status-filter"
                        name="status">

                    <option value="">
                        All Statuses
                    </option>

                    <option value="Available"
                        <?php echo $selectedStatus ===
                            "Available"
                                ? "selected"
                                : ""; ?>>
                        Available
                    </option>

                    <option value="Unavailable"
                        <?php echo $selectedStatus ===
                            "Unavailable"
                                ? "selected"
                                : ""; ?>>
                        Unavailable
                    </option>

                </select>

            </div>


            <button type="submit"
                    class="admin-filter-button">
                Apply Filters
            </button>

            <a href="products.php"
               class="admin-clear-filter">
                Clear
            </a>

        </form>


        <!-- Product count -->
        <div class="admin-result-count">
            Showing
            <strong>
                <?php echo $productResult->num_rows; ?>
            </strong>
            product<?php echo
                $productResult->num_rows === 1
                    ? ""
                    : "s";
            ?>
        </div>


        <!-- Product table -->
        <?php if ($productResult->num_rows > 0): ?>

            <div class="admin-product-table-wrapper">

                <table class="admin-table admin-product-table">

                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Featured</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php while (
                            $product =
                                $productResult->fetch_assoc()
                        ): ?>

                            <tr>

                                <!-- Product image and name -->
                                <td>

                                    <div class="admin-product-identity">

                                        <img src="../includes/<?php
                                            echo htmlspecialchars(
                                                $product["image"]
                                            );
                                        ?>"
                                             alt="<?php
                                                echo htmlspecialchars(
                                                    $product[
                                                        "product_name"
                                                    ]
                                                );
                                             ?>">

                                        <div>
                                            <strong>
                                                <?php echo htmlspecialchars(
                                                    $product[
                                                        "product_name"
                                                    ]
                                                ); ?>
                                            </strong>

                                            <span>
                                                ID:
                                                <?php echo
                                                    $product["product_id"];
                                                ?>
                                            </span>
                                        </div>

                                    </div>

                                </td>


                                <td>
                                    <?php echo htmlspecialchars(
                                        $product["category_name"]
                                    ); ?>
                                </td>


                                <td>
                                    RM <?php echo number_format(
                                        $product["price"],
                                        2
                                    ); ?>
                                </td>


                                <td>
                                    <span class="<?php
                                        echo
                                            (int) $product[
                                                "stock_quantity"
                                            ] <= 5
                                                ? "low-stock"
                                                : "";
                                    ?>">
                                        <?php echo
                                            $product["stock_quantity"];
                                        ?>
                                    </span>
                                </td>


                                <td>
                                    <?php echo
                                        (int) $product["is_featured"] === 1
                                            ? "Yes"
                                            : "No";
                                    ?>
                                </td>


                                <!-- Availability update -->
                                <td>

                                    <form action="products.php"
                                          method="POST"
                                          class="admin-inline-form">

                                        <input type="hidden"
                                               name="action"
                                               value="change-status">

                                        <input type="hidden"
                                               name="product_id"
                                               value="<?php
                                                    echo $product[
                                                        "product_id"
                                                    ];
                                               ?>">

                                        <select name="product_status"
                                                onchange="this.form.submit()"
                                                aria-label="Change product status">

                                            <option value="Available"
                                                <?php echo
                                                    $product[
                                                        "product_status"
                                                    ] === "Available"
                                                        ? "selected"
                                                        : "";
                                                ?>>
                                                Available
                                            </option>

                                            <option value="Unavailable"
                                                <?php echo
                                                    $product[
                                                        "product_status"
                                                    ] === "Unavailable"
                                                        ? "selected"
                                                        : "";
                                                ?>>
                                                Unavailable
                                            </option>

                                        </select>

                                    </form>

                                </td>


                                <!-- Edit and delete -->
                                <td>

                                    <div class="admin-table-actions">

                                        <a href="product-form.php?id=<?php
                                            echo $product[
                                                "product_id"
                                            ];
                                        ?>"
                                           class="admin-edit-link">
                                            Edit
                                        </a>


                                        <form action="products.php"
                                              method="POST"
                                              onsubmit="return confirm(
                                                  'Permanently delete this product? This action cannot be undone.'
                                              );">

                                            <input type="hidden"
                                                   name="action"
                                                   value="delete">

                                            <input type="hidden"
                                                   name="product_id"
                                                   value="<?php
                                                        echo $product[
                                                            "product_id"
                                                        ];
                                                   ?>">

                                            <button type="submit"
                                                    class="admin-delete-button">
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="admin-empty-state admin-product-empty">
                No products match the selected filters.
            </div>

        <?php endif; ?>

    </main>


    <script src="../script.js"></script>

</body>
</html>