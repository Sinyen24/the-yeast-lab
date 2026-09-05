<?php

// Detect the current page for the active navigation link
$currentPage = basename($_SERVER["PHP_SELF"]);

// Calculate the number of products in the cart
if (!isset($totalQty)) {
    $totalQty = 0;

    if (
        isset($_SESSION["cart"]) &&
        is_array($_SESSION["cart"])
    ) {
        $totalQty = array_sum($_SESSION["cart"]);
    }
}

// Pages that belong to the Shop menu
$shopPages = [
    "listing.php",
    "details.php"
];

// Pages that belong to the Account menu
$accountPages = [
    "login.php",
    "register.php",
    "profile.php",
    "edit-profile.php"
];

// Send logged-in users to their profile
$accountDestination = isset($_SESSION["user_id"])
    ? "profile.php"
    : "login.php";

$accountLabel = isset($_SESSION["user_id"])
    ? "View my account"
    : "Log in";

?>

<!-- Announcement Bar -->
<div class="top-bar" aria-label="Bakery announcement">
    Freshly baked every morning — free delivery for orders above RM100!
</div>


<!-- Main Website Header -->
<header class="site-header">

    <nav class="main-nav" aria-label="Main navigation">

        <!-- Logo -->
        <a class="brand-logo"
           href="index.php"
           aria-label="The Yeast Lab home">

            <img src="includes/the-yeast-lab-logo.svg"
                 alt="The Yeast Lab — Artisan Bakery">
        </a>


        <!-- Mobile Navigation Button -->
        <button class="menu-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="main-menu">

            <span></span>
            <span></span>
            <span></span>

            <span class="screen-reader-text">
                Open navigation menu
            </span>
        </button>


        <!-- Navigation Links -->
        <ul class="nav-menu" id="main-menu">

            <li>
                <a href="index.php"
                   class="<?php
                       echo $currentPage === "index.php"
                           ? "active"
                           : "";
                   ?>">
                    Home
                </a>
            </li>

            <li class="has-submenu">

                <a href="listing.php"
                   class="<?php
                       echo in_array(
                           $currentPage,
                           $shopPages,
                           true
                       ) ? "active" : "";
                   ?>">
                    Shop
                </a>

                <ul class="submenu">

                    <li>
                        <a href="listing.php?category=cakes">
                            Cakes
                        </a>
                    </li>

                    <li>
                        <a href="listing.php?category=pastries">
                            Pastries
                        </a>
                    </li>

                    <li>
                        <a href="listing.php?category=breads">
                            Breads
                        </a>
                    </li>

                    <li>
                        <a href="listing.php?category=beverages">
                            Beverages
                        </a>
                    </li>

                </ul>

            </li>

            <li>
                <a href="about.php"
                   class="<?php
                       echo $currentPage === "about.php"
                           ? "active"
                           : "";
                   ?>">
                    Our Story
                </a>
            </li>

            <li>
                <a href="contact.php"
                   class="<?php
                       echo $currentPage === "contact.php"
                           ? "active"
                           : "";
                   ?>">
                    Contact
                </a>
            </li>

        </ul>


        <!-- Search, Account and Cart -->
        <div class="nav-actions">

            <a href="listing.php"
               aria-label="Search products"
               title="Search">
                ⌕
            </a>

            <a href="<?php echo $accountDestination; ?>"
               class="<?php
                   echo in_array(
                       $currentPage,
                       $accountPages,
                       true
                   ) ? "active" : "";
               ?>"
               aria-label="<?php echo $accountLabel; ?>"
				title="<?php echo $accountLabel; ?>">
                ♙
            </a>

            <a href="cart.php"
               class="cart-link <?php
                   echo $currentPage === "cart.php"
                       ? "active"
                       : "";
               ?>"
               aria-label="Shopping cart"
               title="Cart">

                ♧

                <?php if ($totalQty > 0): ?>
                    <span><?php echo $totalQty; ?></span>
                <?php endif; ?>

            </a>

        </div>

    </nav>

</header>