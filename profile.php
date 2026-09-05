<?php

session_start();
require_once __DIR__ . "/db.php";


// Protect the page from users who are not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];


// Retrieve current account information
$userStmt = $conn->prepare("
    SELECT
        user_id,
        full_name,
        email,
        phone,
        user_role,
        created_at
    FROM users
    WHERE user_id = ?
      AND account_status = 'Active'
    LIMIT 1
");

$userStmt->bind_param("i", $userId);
$userStmt->execute();

$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();


// End the session if the user no longer exists
if (!$user) {
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}
// Retrieve this customer's orders
$orderStmt = $conn->prepare("
    SELECT
        order_id,
        order_number,
        fulfilment_method,
        requested_date,
        grand_total,
        order_status,
        payment_status,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$orderStmt->bind_param("i", $userId);
$orderStmt->execute();

$orderResult = $orderStmt->get_result();


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

    <title>My Account | The Yeast Lab</title>

    <link rel="stylesheet" href="style.css">
</head>

<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>


    <main class="profile-page">

        <!-- Profile heading -->
        <section class="profile-heading">

            <div>
                <p class="section-label">
                    Customer Laboratory
                </p>

                <h1>
                    Hello,
                    <?php echo htmlspecialchars(
                        $user["full_name"]
                    ); ?>.
                </h1>

                <p>
                    Welcome back to your personal corner of
                    The Yeast Lab.
                </p>
            </div>

            <a href="logout.php"
               class="profile-logout-button">
                Log Out
            </a>

        </section>


        <!-- Profile information -->
        <section class="profile-grid">

            <article class="profile-card account-card">

                <div class="profile-card-heading">
                    <span>01</span>
                    <h2>Account Information</h2>
                </div>

                <div class="account-information">

                    <div>
                        <span>Full Name</span>

                        <strong>
                            <?php echo htmlspecialchars(
                                $user["full_name"]
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Email Address</span>

                        <strong>
                            <?php echo htmlspecialchars(
                                $user["email"]
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Phone Number</span>

                        <strong>
                            <?php echo htmlspecialchars(
                                $user["phone"]
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Account Type</span>

                        <strong>
                            <?php echo htmlspecialchars(
                                $user["user_role"]
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Member Since</span>

                        <strong>
                            <?php echo date(
                                "d M Y",
                                strtotime($user["created_at"])
                            ); ?>
                        </strong>
                    </div>

                </div>

                <a href="edit-profile.php"
                   class="profile-action-link">
                    Edit My Information →
                </a>

            </article>


            <article class="profile-card orders-card">

                <div class="profile-card-heading">
                    <span>02</span>
                    <h2>My Orders</h2>
                </div>

                <?php if ($orderResult->num_rows > 0): ?>

    <div class="profile-orders">

        <?php while (
            $customerOrder =
                $orderResult->fetch_assoc()
        ): ?>

            <article class="profile-order">

                <div class="profile-order-top">

                    <div>
                        <span>Order Number</span>

                        <h3>
                            <?php echo htmlspecialchars(
                                $customerOrder[
                                    "order_number"
                                ]
                            ); ?>
                        </h3>
                    </div>

                    <span class="order-status status-<?php
                        echo strtolower(
                            $customerOrder[
                                "order_status"
                            ]
                        );
                    ?>">
                        <?php echo htmlspecialchars(
                            $customerOrder[
                                "order_status"
                            ]
                        ); ?>
                    </span>

                </div>

                <div class="profile-order-information">

                    <div>
                        <span>Placed On</span>

                        <strong>
                            <?php echo date(
                                "d M Y",
                                strtotime(
                                    $customerOrder[
                                        "created_at"
                                    ]
                                )
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Fulfilment</span>

                        <strong>
                            <?php echo htmlspecialchars(
                                $customerOrder[
                                    "fulfilment_method"
                                ]
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Requested Date</span>

                        <strong>
                            <?php echo date(
                                "d M Y",
                                strtotime(
                                    $customerOrder[
                                        "requested_date"
                                    ]
                                )
                            ); ?>
                        </strong>
                    </div>

                    <div>
                        <span>Total</span>

                        <strong>
                            RM <?php echo number_format(
                                $customerOrder[
                                    "grand_total"
                                ],
                                2
                            ); ?>
                        </strong>
                    </div>

                </div>

                <a href="order-success.php?order=<?php
                    echo urlencode(
                        $customerOrder["order_number"]
                    );
                ?>"
                   class="profile-order-link">
                    View Order Details →
                </a>

            </article>

        <?php endwhile; ?>

    </div>

<?php else: ?>

    <div class="empty-orders">

        <span aria-hidden="true">♨</span>

        <h3>No orders yet</h3>

        <p>
            Your completed bakery orders will appear
            here after checkout.
        </p>

        <a href="listing.php">
            Explore Our Bakes
        </a>

    </div>

<?php endif; ?>

            </article>

        </section>

    </main>


    <?php require_once __DIR__ . "/includes/footer.php"; ?>

</body>
</html>