<?php

require_once __DIR__ . "/auth-check.php";
require_once __DIR__ . "/../db.php";


// =====================================================
// Update Inquiry Status
// =====================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "update-status"
) {
    $inquiryId = filter_input(
        INPUT_POST,
        "inquiry_id",
        FILTER_VALIDATE_INT
    );

    $inquiryStatus =
        $_POST["inquiry_status"] ?? "";

    $allowedStatuses = [
        "New",
        "In Progress",
        "Resolved"
    ];


    if (
        $inquiryId &&
        $inquiryId > 0 &&
        in_array(
            $inquiryStatus,
            $allowedStatuses,
            true
        )
    ) {
        $updateStmt = $conn->prepare("
            UPDATE inquiries
            SET inquiry_status = ?
            WHERE inquiry_id = ?
        ");

        $updateStmt->bind_param(
            "si",
            $inquiryStatus,
            $inquiryId
        );

        if ($updateStmt->execute()) {
            $_SESSION["inquiry_message"] =
                "The inquiry status was updated.";
        } else {
            $_SESSION["inquiry_error"] =
                "The inquiry could not be updated.";
        }

    } else {
        $_SESSION["inquiry_error"] =
            "Invalid inquiry information was submitted.";
    }

    header("Location: inquiries.php");
    exit;
}


// =====================================================
// Delete Inquiry
// =====================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    ($_POST["action"] ?? "") === "delete"
) {
    $inquiryId = filter_input(
        INPUT_POST,
        "inquiry_id",
        FILTER_VALIDATE_INT
    );

    if ($inquiryId && $inquiryId > 0) {

        $deleteStmt = $conn->prepare("
            DELETE FROM inquiries
            WHERE inquiry_id = ?
        ");

        $deleteStmt->bind_param(
            "i",
            $inquiryId
        );

        if ($deleteStmt->execute()) {
            $_SESSION["inquiry_message"] =
                "The inquiry was deleted successfully.";
        } else {
            $_SESSION["inquiry_error"] =
                "The inquiry could not be deleted.";
        }

    } else {
        $_SESSION["inquiry_error"] =
            "Invalid inquiry information was submitted.";
    }

    header("Location: inquiries.php");
    exit;
}


// =====================================================
// Retrieve Messages
// =====================================================

$inquiryMessage =
    $_SESSION["inquiry_message"] ?? "";

$inquiryError =
    $_SESSION["inquiry_error"] ?? "";

unset($_SESSION["inquiry_message"]);
unset($_SESSION["inquiry_error"]);


// =====================================================
// Inquiry Filters
// =====================================================

$searchTerm = trim($_GET["search"] ?? "");
$selectedStatus = $_GET["status"] ?? "";

$allowedStatuses = [
    "New",
    "In Progress",
    "Resolved"
];


$sql = "
    SELECT
        inquiry_id,
        customer_name,
        customer_email,
        customer_phone,
        inquiry_subject,
        inquiry_message,
        inquiry_status,
        created_at,
        updated_at
    FROM inquiries
    WHERE 1 = 1
";

$parameters = [];
$parameterTypes = "";


// Search inquiries
if ($searchTerm !== "") {

    $sql .= "
        AND (
            customer_name LIKE ?
            OR customer_email LIKE ?
            OR inquiry_subject LIKE ?
            OR inquiry_message LIKE ?
        )
    ";

    $searchValue = "%" . $searchTerm . "%";

    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
    $parameters[] = $searchValue;
    $parameters[] = $searchValue;

    $parameterTypes .= "ssss";
}


// Filter inquiry status
if (
    in_array(
        $selectedStatus,
        $allowedStatuses,
        true
    )
) {
    $sql .= " AND inquiry_status = ?";

    $parameters[] = $selectedStatus;
    $parameterTypes .= "s";
}


$sql .= "
    ORDER BY
        CASE inquiry_status
            WHEN 'New' THEN 1
            WHEN 'In Progress' THEN 2
            WHEN 'Resolved' THEN 3
        END,
        inquiry_id DESC
";


$inquiryStmt = $conn->prepare($sql);

if (!empty($parameters)) {
    $inquiryStmt->bind_param(
        $parameterTypes,
        ...$parameters
    );
}

$inquiryStmt->execute();

$inquiryResult =
    $inquiryStmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Inquiries | The Yeast Lab</title>

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
                Customer Communication
            </p>

            <h1>Inquiries</h1>

            <p>
                Review customer messages and manage
                their response progress.
            </p>
        </div>

    </section>


    <?php if ($inquiryMessage !== ""): ?>

        <div class="form-message success-message admin-message">
            <?php echo htmlspecialchars(
                $inquiryMessage
            ); ?>
        </div>

    <?php endif; ?>


    <?php if ($inquiryError !== ""): ?>

        <div class="form-message error-message admin-message">
            <?php echo htmlspecialchars(
                $inquiryError
            ); ?>
        </div>

    <?php endif; ?>


    <!-- Inquiry filters -->
    <form action="inquiries.php"
          method="GET"
          class="admin-filter-form admin-inquiry-filter">

        <div class="admin-filter-field">

            <label for="inquiry-search">
                Search Inquiries
            </label>

            <input type="search"
                   id="inquiry-search"
                   name="search"
                   placeholder="Customer, email, subject or message..."
                   value="<?php echo htmlspecialchars(
                       $searchTerm
                   ); ?>">

        </div>


        <div class="admin-filter-field">

            <label for="inquiry-status-filter">
                Inquiry Status
            </label>

            <select id="inquiry-status-filter"
                    name="status">

                <option value="">
                    All Statuses
                </option>

                <?php foreach (
                    $allowedStatuses as $status
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

        <a href="inquiries.php"
           class="admin-clear-filter">
            Clear
        </a>

    </form>


    <div class="admin-result-count">
        Showing
        <strong>
            <?php echo $inquiryResult->num_rows; ?>
        </strong>

        inquir<?php echo
            $inquiryResult->num_rows === 1
                ? "y"
                : "ies";
        ?>
    </div>


    <?php if ($inquiryResult->num_rows > 0): ?>

        <div class="admin-inquiry-list">

            <?php while (
                $inquiry =
                    $inquiryResult->fetch_assoc()
            ): ?>

                <article class="admin-inquiry-card">

                    <div class="admin-inquiry-heading">

                        <div>
                            <p class="admin-inquiry-subject">
                                <?php echo htmlspecialchars(
                                    $inquiry["inquiry_subject"]
                                ); ?>
                            </p>

                            <h2>
                                <?php echo htmlspecialchars(
                                    $inquiry["customer_name"]
                                ); ?>
                            </h2>

                            <span>
                                Received on
                                <?php echo date(
                                    "d M Y, g:i A",
                                    strtotime(
                                        $inquiry["created_at"]
                                    )
                                ); ?>
                            </span>
                        </div>


                        <span class="admin-inquiry-status status-<?php
                            echo strtolower(
                                str_replace(
                                    " ",
                                    "-",
                                    $inquiry["inquiry_status"]
                                )
                            );
                        ?>">
                            <?php echo htmlspecialchars(
                                $inquiry["inquiry_status"]
                            ); ?>
                        </span>

                    </div>


                    <div class="admin-inquiry-contact">

                        <div>
                            <span>Email</span>

                            <a href="mailto:<?php
                                echo htmlspecialchars(
                                    $inquiry["customer_email"]
                                );
                            ?>">
                                <?php echo htmlspecialchars(
                                    $inquiry["customer_email"]
                                ); ?>
                            </a>
                        </div>


                        <div>
                            <span>Phone</span>

                            <?php if (
                                $inquiry["customer_phone"] !== ""
                            ): ?>

                                <a href="tel:<?php
                                    echo htmlspecialchars(
                                        $inquiry["customer_phone"]
                                    );
                                ?>">
                                    <?php echo htmlspecialchars(
                                        $inquiry["customer_phone"]
                                    ); ?>
                                </a>

                            <?php else: ?>

                                <strong>Not provided</strong>

                            <?php endif; ?>

                        </div>

                    </div>


                    <div class="admin-inquiry-message">

                        <span>Customer Message</span>

                        <p>
                            <?php echo nl2br(
                                htmlspecialchars(
                                    $inquiry["inquiry_message"]
                                )
                            ); ?>
                        </p>

                    </div>


                    <div class="admin-inquiry-actions">

                        <form action="inquiries.php"
                              method="POST"
                              class="admin-inquiry-status-form">

                            <input type="hidden"
                                   name="action"
                                   value="update-status">

                            <input type="hidden"
                                   name="inquiry_id"
                                   value="<?php echo
                                       $inquiry["inquiry_id"];
                                   ?>">


                            <div class="admin-filter-field">

                                <label>
                                    Response Status
                                </label>

                                <select name="inquiry_status">

								<?php foreach (
									$allowedStatuses as $status
								): ?>

									<option value="<?php
										echo htmlspecialchars($status);
									?>"
										<?php echo
											$inquiry["inquiry_status"] === $status
												? "selected"
												: "";
										?>>

										<?php echo htmlspecialchars($status); ?>

									</option>

								<?php endforeach; ?>

							</select>

                            </div>

                            <button type="submit"
                                    class="admin-primary-button">
                                Save Status
                            </button>

                        </form>


                        <form action="inquiries.php"
                              method="POST"
                              onsubmit="return confirm(
                                  'Delete this inquiry permanently?'
                              );">

                            <input type="hidden"
                                   name="action"
                                   value="delete">

                            <input type="hidden"
                                   name="inquiry_id"
                                   value="<?php echo
                                       $inquiry["inquiry_id"];
                                   ?>">

                            <button type="submit"
                                    class="admin-delete-button">
                                Delete
                            </button>

                        </form>

                    </div>

                </article>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="no-products admin-product-empty">

            <span>✉</span>

            <h2>No inquiries found</h2>

            <p>
                No customer inquiries match
                the selected filters.
            </p>

            <a href="inquiries.php">
                View All Inquiries
            </a>

        </div>

    <?php endif; ?>

</main>


<script src="../script.js"></script>

</body>
</html>