<?php

$currentAdminPage =
    basename($_SERVER["PHP_SELF"]);

?>

<header class="admin-header">

    <div class="admin-header-inner">

        <!-- Admin logo -->
        <a href="dashboard.php"
           class="admin-logo">

            <img src="../includes/the-yeast-lab-logo.svg"
                 alt="The Yeast Lab">

            <span>Administration</span>

        </a>


        <!-- Mobile admin menu button -->
        <button type="button"
                class="admin-menu-toggle"
                aria-expanded="false"
                aria-controls="admin-navigation">

            <span></span>
            <span></span>
            <span></span>

            <span class="screen-reader-text">
                Open admin navigation
            </span>

        </button>


        <!-- Admin navigation -->
        <nav class="admin-navigation"
             id="admin-navigation"
             aria-label="Administrator navigation">

            <a href="dashboard.php"
               class="<?php
                   echo $currentAdminPage === "dashboard.php"
                       ? "active"
                       : "";
               ?>">
                Dashboard
            </a>

            <a href="products.php"
               class="<?php
                   echo in_array(
                       $currentAdminPage,
                       [
                           "products.php",
                           "product-form.php"
                       ],
                       true
                   ) ? "active" : "";
               ?>">
                Products
            </a>

            <a href="orders.php"
               class="<?php
                   echo $currentAdminPage === "orders.php"
                       ? "active"
                       : "";
               ?>">
                Orders
            </a>

            <a href="inquiries.php"
               class="<?php
                   echo $currentAdminPage === "inquiries.php"
                       ? "active"
                       : "";
               ?>">
                Enquiries
            </a>

        </nav>


        <!-- Administrator actions -->
        <div class="admin-actions">

            <div class="admin-user">
                <span>Signed in as</span>

                <strong>
                    <?php echo htmlspecialchars(
                        $_SESSION["user_name"] ??
                        "Administrator"
                    ); ?>
                </strong>
            </div>

            <a href="../index.php"
               class="admin-view-site">
                View Website
            </a>

            <a href="../logout.php"
               class="admin-logout">
                Log Out
            </a>

        </div>

    </div>

</header>