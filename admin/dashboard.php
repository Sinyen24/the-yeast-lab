<?php

require_once __DIR__ . "/auth-check.php";
require_once __DIR__ . "/../db.php";


// Count available products
$productCountResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
    WHERE product_status = 'Available'
");

$productCount =
    (int) $productCountResult->fetch_assoc()["total"];


// Count pending orders
$pendingOrderResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE order_status = 'Pending'
");

$pendingOrderCount =
    (int) $pendingOrderResult->fetch_assoc()["total"];


// Count new enquiries
$newInquiryResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM inquiries
    WHERE inquiry_status = 'New'
");

$newInquiryCount =
    (int) $newInquiryResult->fetch_assoc()["total"];


// Count customers
$customerCountResult = $conn->query("
    SELECT COUNT(*) AS total
    FROM users
    WHERE user_role = 'Customer'
      AND account_status = 'Active'
");

$customerCount =
    (int) $customerCountResult->fetch_assoc()["total"];


// Calculate total non-cancelled order value
$revenueResult = $conn->query("
    SELECT COALESCE(SUM(grand_total), 0) AS total
    FROM orders
    WHERE order_status != 'Cancelled'
");

$totalRevenue =
    (float) $revenueResult->fetch_assoc()["total"];


// Retrieve recent orders
$recentOrders = $conn->query("
    SELECT
        order_number,
        customer_name,
        grand_total,
        order_status,
        created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 5
");


// Retrieve recent enquiries
$recentInquiries = $conn->query("
    SELECT
        customer_name,
        inquiry_subject,
        inquiry_status,
        created_at
    FROM inquiries
    ORDER BY created_at DESC
    LIMIT 5
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | The Yeast Lab</title>

    <link rel="stylesheet" href="../style.css">
</head>

<body class="admin-body">

    <?php require_once __DIR__ . "/admin-header.php"; ?>


    <main class="admin-main">

        <!-- Dashboard introduction -->
        <section class="admin-page-heading">

            <div>
                <p class="section-label">
                    The Yeast Lab Administration
                </p>

                <h1>Dashboard</h1>

                <p>
                    Welcome back,
                    <?php echo htmlspecialchars(
                        $_SESSION["user_name"]
                    ); ?>.
                </p>
            </div>

            <a href="product-form.php"
               class="admin-primary-button">
                + Add New Product
            </a>

        </section>


        <!-- Dashboard statistics -->
        <section class="admin-stat-grid">

            <article class="admin-stat-card">

                <span>01</span>
                <p>Available Products</p>
                <strong><?php echo $productCount; ?></strong>

                <a href="products.php">
                    Manage Products →
                </a>

            </article>


            <article class="admin-stat-card">

                <span>02</span>
                <p>Pending Orders</p>
                <strong><?php echo $pendingOrderCount; ?></strong>

                <a href="orders.php">
                    Manage Orders →
                </a>

            </article>


            <article class="admin-stat-card">

                <span>03</span>
                <p>New Enquiries</p>
                <strong><?php echo $newInquiryCount; ?></strong>

                <a href="inquiries.php">
                    View Enquiries →
                </a>

            </article>


            <article class="admin-stat-card">

                <span>04</span>
                <p>Active Customers</p>
                <strong><?php echo $customerCount; ?></strong>

                <span class="admin-stat-note">
                    Registered accounts
                </span>

            </article>


            <article class="admin-stat-card revenue-card">

                <span>05</span>
                <p>Total Order Value</p>

                <strong>
                    RM <?php echo number_format(
                        $totalRevenue,
                        2
                    ); ?>
                </strong>

                <span class="admin-stat-note">
                    Excluding cancelled orders
                </span>

            </article>

        </section>


        <!-- Recent activity -->
        <section class="admin-dashboard-grid">

            <!-- Recent orders -->
            <article class="admin-dashboard-panel">

                <div class="admin-panel-heading">
                    <div>
                        <p class="section-label">
                            Latest Activity
                        </p>

                        <h2>Recent Orders</h2>
                    </div>

                    <a href="orders.php">
                        View All →
                    </a>
                </div>


                <?php if ($recentOrders->num_rows > 0): ?>

                    <div class="admin-table-wrapper">

                        <table class="admin-table">

                            <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php while (
                                    $order =
                                        $recentOrders->fetch_assoc()
                                ): ?>

                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars(
                                                $order["order_number"]
                                            ); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars(
                                                $order["customer_name"]
                                            ); ?>
                                        </td>

                                        <td>
                                            RM <?php echo number_format(
                                                $order["grand_total"],
                                                2
                                            ); ?>
                                        </td>

                                        <td>
                                            <span class="admin-status">
                                                <?php echo htmlspecialchars(
                                                    $order["order_status"]
                                                ); ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php echo date(
                                                "d M Y",
                                                strtotime(
                                                    $order["created_at"]
                                                )
                                            ); ?>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                            </tbody>

                        </table>

                    </div>

                <?php else: ?>

                    <div class="admin-empty-state">
                        No orders have been placed yet.
                    </div>

                <?php endif; ?>

            </article>


            <!-- Recent enquiries -->
            <article class="admin-dashboard-panel">

                <div class="admin-panel-heading">
                    <div>
                        <p class="section-label">
                            Customer Messages
                        </p>

                        <h2>Recent Enquiries</h2>
                    </div>

                    <a href="inquiries.php">
                        View All →
                    </a>
                </div>


                <?php if ($recentInquiries->num_rows > 0): ?>

                    <div class="admin-inquiry-list">

                        <?php while (
                            $inquiry =
                                $recentInquiries->fetch_assoc()
                        ): ?>

                            <div class="admin-inquiry-item">

                                <div>
                                    <span>
                                        <?php echo htmlspecialchars(
                                            $inquiry[
                                                "inquiry_status"
                                            ]
                                        ); ?>
                                    </span>

                                    <h3>
                                        <?php echo htmlspecialchars(
                                            $inquiry[
                                                "inquiry_subject"
                                            ]
                                        ); ?>
                                    </h3>

                                    <p>
                                        <?php echo htmlspecialchars(
                                            $inquiry[
                                                "customer_name"
                                            ]
                                        ); ?>
                                    </p>
                                </div>

                                <time>
                                    <?php echo date(
                                        "d M",
                                        strtotime(
                                            $inquiry["created_at"]
                                        )
                                    ); ?>
                                </time>

                            </div>

                        <?php endwhile; ?>

                    </div>

                <?php else: ?>

                    <div class="admin-empty-state">
                        No customer enquiries yet.
                    </div>

                <?php endif; ?>

            </article>

        </section>

    </main>


    <script src="../script.js"></script>

</body>
</html>