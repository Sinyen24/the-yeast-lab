<!-- Site Footer -->
<footer class="site-footer">

    <div class="footer-main">

        <!-- Bakery Identity -->
        <div class="footer-brand">

            <a href="index.php">
                <img src="includes/the-yeast-lab-logo.svg"
                     alt="The Yeast Lab">
            </a>

            <p>
                An artisan bakery where careful technique,
                quality ingredients and delicious curiosity meet.
            </p>

            <p class="footer-tagline">
                Mix. Ferment. Bake. Repeat.
            </p>

        </div>


        <!-- Explore Links -->
        <div class="footer-column">

            <h3>Explore</h3>

            <ul>
                <li>
                    <a href="index.php">Home</a>
                </li>

                <li>
                    <a href="listing.php">Shop All</a>
                </li>

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
                        Artisan Breads
                    </a>
                </li>
            </ul>

        </div>


        <!-- Customer Links -->
        <div class="footer-column">

            <h3>Customer Care</h3>

            <ul>
                <li>
                    <a href="about.php">Our Story</a>
                </li>

                <li>
                    <a href="contact.php">Contact Us</a>
                </li>

                <li>
                    <a href="login.php">My Account</a>
                </li>

                <li>
                    <a href="cart.php">
                        Shopping Cart

                        <?php if ($totalQty > 0): ?>
                            (<?php echo $totalQty; ?>)
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

        </div>


        <!-- Contact Information -->
        <div class="footer-column footer-contact">

            <h3>Visit the Lab</h3>

            <p>
                204A, Jalan Ampang,<br>
                Kampung Datuk Keramat,<br>
                50450 Kuala Lumpur.
            </p>

            <p>
                <a href="tel:+60312345678">
                    +603 1234 5678
                </a>
            </p>

            <p>
                <a href="mailto:hello@theyeastlab.com">
                    hello@theyeastlab.com
                </a>
            </p>

            <p class="footer-hours">
                Daily · 8:00 AM onwards
            </p>

        </div>

    </div>


    <!-- Copyright Area -->
    <div class="footer-bottom">

        <p>
            © 2026 The Yeast Lab. All rights reserved.
        </p>

        <p>
            Artisan Bakery · Kuala Lumpur, Malaysia
        </p>

        <a href="#top" class="back-to-top">
            Back to Top ↑
        </a>

    </div>

</footer>


<!-- Shared Website JavaScript -->
<script src="script.js"></script>