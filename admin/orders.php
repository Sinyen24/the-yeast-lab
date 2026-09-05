<?php

require_once __DIR__ . "/auth-check.php";
require_once __DIR__ . "/../db.php";


// =====================================================
// Update Order Status
// =====================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "update-order"
) {
    $orderId = filter_input(
        INPUT_POST,
        "order_id",
        FILTER_VALIDATE_INT
    );

    $orderStatus =
        $_POST["order_status"] ?? "";

    $paymentStatus =
        $_POST["payment_status"] ?? "";


    $allowedOrderStatuses = [
        "Pending",
        "Confirmed",
        "Preparing",
        "Ready",
        "Completed",
        "Cancelled"
    ];

    $allowedPaymentStatuses = [
        "Pending",
        "Paid"
    ];


    if (
        $orderId &&
        $orderId > 0 &&
        in_array(
            $orderStatus,
            $allowedOrderStatuses,
            true
        ) &&
        in_array(
            $paymentStatus,
            $allowedPaymentStatuses,
            true
        )
    ) {
        $updateStmt = $conn->prepare("
            UPDATE orders
            SET
                order_status = ?,
                payment_status = ?
            WHERE order_id = ?
        ");

        $updateStmt->bind_param(
            "ssi",
            $orderStatus,
            $paymentStatus,
            $orderId
        );

        if ($updateStmt->execute()) {
            $_SESSION["order_message"] =
                "The order was updated successfully.";
        } else {
            $_SESSION["order_error"] =
                "The order could not be updated.";
        }

    } else {
        $_SESSION["order_error"] =
            "Invalid order information was submitted.";
    }

    header("Location: orders.php");
    exit;
}


// =====================================================
// Retrieve Messages
// =====================================================

$orderMessage =
    $_SESSION["order_message"] ?? "";

$orderError =
    $_SESSION["order_error"] ?? "";

unset($_SESSION["order_message"]);
unset($_SESSION["order_error"]);


// =====================================================
// Order Filters
// =====================================================

$searchTerm = trim($_GET["search"] ?? "");
$selectedStatus = $_GET["status"] ?? "";

$allowedOrderStatuses = [
    "Pending",
    "Confirmed",
    "Preparing",
    "Ready",
    "Completed",
    "Cancelled"
];


$sql = "
    SELECT
        orders.order_id,
        orders.order_number,
        orders.customer_name,
        orders.customer_email,
        orders.customer_phone,
        orders.fulfilment_method,
        orders.requested_date,
        orders.requested_time,
        orders.grand_total,
        orders.order_status,
        orders.payment_method,
        orders.payment_status,
		orders.order_notes,
		orders.created_at,

        (
            SELECT COALESCE(
                SUM(order_items.quantity),
                0
            )
            FROM order_items
            WHERE order_items.order_id =
                orders.order_id
        ) AS total_items

    FROM orders
    WHERE 1 = 1
";

$parameters = [];
$parameterTypes = "";


// Search order
if ($searchTerm !== "") {
    $sql .= "
        AND (
            orders.order_number LIKE ?
            OR orders.customer_name LIKE ?
            OR orders.customer_email LIKE ?
        )
    ";

    $searchValue = "%" . $searchTerm . "%";

    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
    $parameters[] = $searchValue;

    $parameterTypes .= "sss";
}


// Filter order status
if (
    in_array(
        $selectedStatus,
        $allowedOrderStatuses,
        true
    )
) {
    $sql .= " AND orders.order_status = ?";

    $parameters[] = $selectedStatus;
    $parameterTypes .= "s";
}


$sql .= " ORDER BY orders.order_id DESC";


$orderStmt = $conn->prepare($sql);

if (!empty($parameters)) {
    $orderStmt->bind_param(
        $parameterTypes,
        ...$parameters
    );
}

$orderStmt->execute();
$orderResult = $orderStmt->get_result();

// Prepare the query used to retrieve products in each order
$orderItemStmt = $conn->prepare("
    SELECT
        product_name,
        product_price,
        quantity,
        line_total
    FROM order_items
    WHERE order_id = ?
    ORDER BY order_item_id ASC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Orders | The Yeast Lab</title>

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
                Customer Purchases
            </p>

            <h1>Orders</h1>

            <p>
                Review customer orders and update their
                preparation and payment status.
            </p>
        </div>

    </section>


    <?php if ($orderMessage !== ""): ?>

        <div class="form-message success-message admin-message">
            <?php echo htmlspecialchars($orderMessage); ?>
        </div>

    <?php endif; ?>


    <?php if ($orderError !== ""): ?>

        <div class="form-message error-message admin-message">
            <?php echo htmlspecialchars($orderError); ?>
        </div>

    <?php endif; ?>


    <!-- Order filters -->
    <form action="orders.php"
          method="GET"
          class="admin-filter-form admin-order-filter">

        <div class="admin-filter-field">

            <label for="admin-order-search">
                Search Order
            </label>

            <input type="search"
                   id="admin-order-search"
                   name="search"
                   placeholder="Order number, customer or email..."
                   value="<?php echo htmlspecialchars(
                       $searchTerm
                   ); ?>">

        </div>


        <div class="admin-filter-field">

            <label for="admin-order-status">
                Order Status
            </label>

            <select id="admin-order-status"
                    name="status">

                <option value="">
                    All Statuses
                </option>

                <?php foreach (
                    $allowedOrderStatuses as $status
                ): ?>

                    <option value="<?php
                        echo htmlspecialchars($status);
                    ?>"
                        <?php echo
                            $selectedStatus === $status
                                ? "selected"
                                : "";
                        ?>>

                        <?php echo htmlspecialchars($status); ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <button type="submit"
                class="admin-filter-button">
            Apply Filters
        </button>

        <a href="orders.php"
           class="admin-clear-filter">
            Clear
        </a>

    </form>


    <div class="admin-result-count">
        Showing
        <strong>
            <?php echo $orderResult->num_rows; ?>
        </strong>

        order<?php echo
            $orderResult->num_rows === 1
                ? ""
                : "s";
        ?>
    </div>


    <?php if ($orderResult->num_rows > 0): ?>

        <div class="admin-order-list">

            <?php while (
					$order = $orderResult->fetch_assoc()
				): ?>

					<?php
						$currentOrderId = (int) $order["order_id"];

						$orderItemStmt->bind_param(
							"i",
							$currentOrderId
						);

						$orderItemStmt->execute();

						$orderItemResult =
							$orderItemStmt->get_result();
					?>

					<article class="admin-order-card">

                    <div class="admin-order-card-heading">

                        <div>
                            <p class="admin-order-number">
                                <?php echo htmlspecialchars(
                                    $order["order_number"]
                                ); ?>
                            </p>

                            <h2>
                                <?php echo htmlspecialchars(
                                    $order["customer_name"]
                                ); ?>
                            </h2>

                            <span>
                                Ordered on
                                <?php echo date(
                                    "d M Y, g:i A",
                                    strtotime(
                                        $order["created_at"]
                                    )
                                ); ?>
                            </span>
                        </div>


                        <div class="admin-order-total">

                            <span>
                                <?php echo
                                    (int) $order["total_items"];
                                ?>
                                item<?php echo
                                    (int) $order["total_items"] === 1
                                        ? ""
                                        : "s";
                                ?>
                            </span>

                            <strong>
                                RM <?php echo number_format(
                                    $order["grand_total"],
                                    2
                                ); ?>
                            </strong>

                        </div>

                    </div>


                    <div class="admin-order-information">

                        <div>
                            <span>Email</span>

                            <strong>
                                <?php echo htmlspecialchars(
                                    $order["customer_email"]
                                ); ?>
                            </strong>
                        </div>


                        <div>
                            <span>Phone</span>

                            <strong>
                                <?php echo htmlspecialchars(
                                    $order["customer_phone"]
                                ); ?>
                            </strong>
                        </div>


                        <div>
                            <span>Fulfilment</span>

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

                    </div>
					
					<div class="admin-order-remark">

								<span>Customer Remark</span>

								<?php if (
									trim((string) $order["order_notes"]) !== ""
								): ?>

									<p>
										<?php echo nl2br(
											htmlspecialchars($order["order_notes"])
										); ?>
									</p>

								<?php else: ?>

									<p class="no-customer-remark">
										No special remark was provided.
									</p>

								<?php endif; ?>

							</div>

					<div class="admin-ordered-products">

							<h3>Ordered Products</h3>

							<?php if ($orderItemResult->num_rows > 0): ?>

								<div class="admin-order-item-list">

									<?php while (
										$item = $orderItemResult->fetch_assoc()
									): ?>

										<div class="admin-order-item">

											<div class="admin-order-item-name">
												<strong>
													<?php echo htmlspecialchars(
														$item["product_name"]
													); ?>
												</strong>

												<span>
													RM <?php echo number_format(
														$item["product_price"],
														2
													); ?> each
												</span>
											</div>

											<span class="admin-order-item-quantity">
												× <?php echo (int) $item["quantity"]; ?>
											</span>

											<strong class="admin-order-item-total">
												RM <?php echo number_format(
													$item["line_total"],
													2
												); ?>
											</strong>

										</div>

									<?php endwhile; ?>

								</div>

							<?php else: ?>

								<p class="admin-no-order-items">
									No product information was recorded for this order.
								</p>

							<?php endif; ?>

						</div>

                    <!-- Status management -->
                    <form action="orders.php"
                          method="POST"
                          class="admin-order-status-form">

                        <input type="hidden"
                               name="action"
                               value="update-order">

                        <input type="hidden"
                               name="order_id"
                               value="<?php echo
                                   $order["order_id"];
                               ?>">


                        <div class="admin-filter-field">

                            <label>
                                Order Status
                            </label>

                            <select name="order_status">

                                <?php foreach (
                                    $allowedOrderStatuses
                                    as $status
                                ): ?>

                                    <option value="<?php
                                        echo htmlspecialchars(
                                            $status
                                        );
                                    ?>"
                                        <?php echo
                                            $order["order_status"] ===
                                            $status
                                                ? "selected"
                                                : "";
                                        ?>>

                                        <?php echo htmlspecialchars(
                                            $status
                                        ); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="admin-filter-field">

                            <label>
                                Payment Status
                            </label>

                            <select name="payment_status">

                                <option value="Pending"
                                    <?php echo
                                        $order["payment_status"] ===
                                        "Pending"
                                            ? "selected"
                                            : "";
                                    ?>>
                                    Pending
                                </option>

                                <option value="Paid"
                                    <?php echo
                                        $order["payment_status"] ===
                                        "Paid"
                                            ? "selected"
                                            : "";
                                    ?>>
                                    Paid
                                </option>

                            </select>

                        </div>


                        <button type="submit"
                                class="admin-primary-button">
                            Save Status
                        </button>

                    </form>

                </article>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="no-products admin-product-empty">

            <span>↯</span>

            <h2>No orders found</h2>

            <p>
                No customer orders match the selected filters.
            </p>

            <a href="orders.php">
                View All Orders
            </a>

        </div>

    <?php endif; ?>

</main>


<script src="../script.js"></script>

</body>
</html>
