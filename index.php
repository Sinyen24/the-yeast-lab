<?php

session_start();
require_once __DIR__ . "/db.php";

$totalQty = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $totalQty = array_sum($_SESSION['cart']);
}

// Retrieve four featured products for the homepage
$featuredResult = $conn->query("
    SELECT
        products.product_id,
        products.product_name,
        products.price,
        products.image,
        products.stock_quantity,
        categories.category_name
    FROM products
    INNER JOIN categories
        ON products.category_id =
           categories.category_id
    WHERE products.is_featured = 1
      AND products.product_status = 'Available'
      AND products.stock_quantity > 0
    ORDER BY products.updated_at DESC
    LIMIT 4
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Yeast Lab | Artisan Bakery</title>
    <link rel="stylesheet" href="style.css">
</head>
<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>

    <section class="home-hero" aria-labelledby="hero-title">
        <div class="hero-image" aria-hidden="true"></div>
        <div class="hero-shade" aria-hidden="true"></div>
        <div class="hero-content">
            <p class="hero-eyebrow">Artisan bakery · Kuala Lumpur</p>
            <h1 id="hero-title">Where Baking<br>Meets Curiosity</h1>
            <p class="hero-description">Thoughtfully crafted pastries, freshly made in our little laboratory of flavour.</p>
            <div class="hero-actions">
                <a href="listing.php" class="btn-primary">Explore Our Bakes</a>
                <a href="about.php" class="btn-secondary">Our Story</a>
            </div>
        </div>
        <a class="hero-scroll" href="#featured-pastries" aria-label="Scroll to featured pastries">
            <span>Discover</span><b>↓</b>
        </a>
    </section>

	<section class="category-section">
    <div class="section-heading">
        <p class="section-label">Find Your Favourite</p>
        <h2>Shop by Category</h2>
        <p>From delicate pastries to freshly baked artisan loaves.</p>
    </div>

    <div class="category-grid">

        <a href="listing.php?category=cakes" class="category-card">
            <img src="includes/category-cake.jpg"
				alt="Selection of cakes and desserts">
            <div class="category-overlay">
                <h3>Cakes</h3>
                <span>View Collection →</span>
            </div>
        </a>

        <a href="listing.php?category=pastries" class="category-card">
            <img src="includes/category-pastries.jpg"
					alt="Selection of artisan pastries">
            <div class="category-overlay">
                <h3>Pastries</h3>
                <span>View Collection →</span>
            </div>
        </a>

        <a href="listing.php?category=breads" class="category-card">
            <img src="includes/category-breads.jpg"
					alt="Selection of artisan breads and Shio Pan">
            <div class="category-overlay">
                <h3>Artisan Breads</h3>
                <span>View Collection →</span>
            </div>
        </a>

        <a href="listing.php?category=beverages" class="category-card">
            <img src="includes/category-beverages.jpg"
					alt="Selection of bakery café beverages">
            <div class="category-overlay">
                <h3>Beverages</h3>
                <span>View Collection →</span>
            </div>
        </a>

    </div>
</section>

		<div class="container" id="featured-pastries">
		
		<div class="section-heading">
			<p class="section-label">Customer Favourites</p>
			<h2>Our Best Sellers</h2>
			<p>Meet the creations our customers return for again and again.</p>
		</div>
        
<div class="bestseller-grid">

    <?php if (
        $featuredResult &&
        $featuredResult->num_rows > 0
    ): ?>

        <?php while (
            $product =
                $featuredResult->fetch_assoc()
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

                    <span class="product-badge">
                        Best Seller
                    </span>

                    <a href="cart.php?action=add&id=<?php
                        echo $product["product_id"];
                    ?>"
                       class="quick-add">
                        Add to Cart
                    </a>

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

                    <p class="product-price">
                        RM <?php echo number_format(
                            $product["price"],
                            2
                        ); ?>
                    </p>

                </div>

            </article>

        <?php endwhile; ?>


    <?php else: ?>

        <div class="no-products">
            <span>⚗</span>

            <h2>No featured products yet</h2>

            <p>
                Select featured products through
                the administrator area.
            </p>
        </div>

    <?php endif; ?>

</div>

<div class="view-all-wrapper">
    <a href="listing.php" class="view-all-products">
        View All Products →
    </a>
</div>
    </div>

<!-- Seasonal Promotion Section -->
<section class="seasonal-section">

    <div class="seasonal-image">
        <img src="includes/exoticberries.jpeg"
             alt="Fresh berry tart collection">

        <span class="seasonal-sticker">
            Limited<br>Edition
        </span>
    </div>

    <div class="seasonal-content">
        <p class="section-label">From Our Seasonal Lab</p>

        <h2>Berry Season,<br>Reimagined.</h2>

        <p class="seasonal-description">
            Crisp pastry shells, silky cream and fresh seasonal berries
            come together in a colourful experiment made for sharing.
        </p>

        <div class="seasonal-details">
            <div>
                <strong>Freshly Made</strong>
                <span>Every morning</span>
            </div>

            <div>
                <strong>Available Until</strong>
                <span>While stocks last</span>
            </div>
        </div>

        <a href="details.php?id=6" class="seasonal-button">
            Discover the Collection →
        </a>
    </div>

</section>

	<!-- Our Story Section -->
<section class="story-section">

    <div class="story-content">
        <p class="section-label">Our Story</p>

        <h2>Part Bakery.<br>Part Laboratory.</h2>

        <p class="story-introduction">
            The Yeast Lab began with one simple curiosity:
            how can traditional baking become something unexpected?
        </p>

        <p class="story-description">
            We combine carefully measured techniques with playful ideas,
            allowing every dough, flavour and texture to become a delicious
            experiment. From slow-fermented breads to delicate pastries,
            everything is freshly made with patience and purpose.
        </p>

        <div class="story-values">

            <div class="story-value">
                <span>01</span>
                <h3>Curious</h3>
                <p>We continuously explore new flavours and creative combinations.</p>
            </div>

            <div class="story-value">
                <span>02</span>
                <h3>Precise</h3>
                <p>Every ingredient is thoughtfully measured and carefully prepared.</p>
            </div>

            <div class="story-value">
                <span>03</span>
                <h3>Fresh</h3>
                <p>Our breads and pastries are baked fresh every morning.</p>
            </div>

        </div>

        <a href="about.php" class="story-button">
            Explore Our Story →
        </a>
    </div>

    <div class="story-image">
        <img src="includes/hero-the-yeast-lab.png"
             alt="Fresh artisan breads and pastries at The Yeast Lab">

        <div class="story-image-caption">
            <span>Est. 2026</span>
            <p>Kuala Lumpur, Malaysia</p>
        </div>
    </div>

</section>

<!-- Bakery Benefits Section -->
<section class="benefits-section">

    <div class="section-heading">
        <p class="section-label">Why The Yeast Lab?</p>
        <h2>The Method Behind Every Bake</h2>
        <p>
            From our ingredients to your doorstep, every detail is
            carefully considered.
        </p>
    </div>

    <div class="benefits-grid">

        <article class="benefit-card">
            <span class="benefit-number">01</span>

            <div class="benefit-icon" aria-hidden="true">
                ♨
            </div>

            <h3>Baked Fresh Daily</h3>

            <p>
                Our ovens begin early every morning, so every order
                reaches you at its freshest.
            </p>
        </article>

        <article class="benefit-card">
            <span class="benefit-number">02</span>

            <div class="benefit-icon" aria-hidden="true">
                ✦
            </div>

            <h3>Quality Ingredients</h3>

            <p>
                We select quality butter, flour, chocolate and seasonal
                produce for every creation.
            </p>
        </article>

        <article class="benefit-card">
            <span class="benefit-number">03</span>

            <div class="benefit-icon" aria-hidden="true">
                ⚗
            </div>

            <h3>Made with Precision</h3>

            <p>
                Every recipe is carefully tested and measured to create
                consistent flavour and texture.
            </p>
        </article>

        <article class="benefit-card">
            <span class="benefit-number">04</span>

            <div class="benefit-icon" aria-hidden="true">
                ♡
            </div>

            <h3>Ready for Sharing</h3>

            <p>
                Choose convenient self-pickup or delivery for everyday
                treats and meaningful celebrations.
            </p>
        </article>

    </div>

</section>

	<!-- Visit and Contact Section -->
<section class="visit-section">

    <div class="visit-heading">
        <p class="section-label">Visit Our Laboratory</p>
        <h2>Come for the bread.<br>Stay for the experiment.</h2>
    </div>

    <div class="visit-grid">

        <div class="visit-detail">
            <span>01</span>
            <h3>Find Us</h3>

            <p>
                204A, Jalan Ampang,<br>
                Kampung Datuk Keramat,<br>
                50450 Kuala Lumpur.
            </p>

            <a href="contact.php">
                View Location →
            </a>
        </div>

        <div class="visit-detail">
            <span>02</span>
            <h3>Opening Hours</h3>

            <div class="opening-row">
                <p>Monday – Friday</p>
                <strong>8:00 AM – 8:00 PM</strong>
            </div>

            <div class="opening-row">
                <p>Saturday – Sunday</p>
                <strong>8:00 AM – 10:00 PM</strong>
            </div>
        </div>

        <div class="visit-detail">
            <span>03</span>
            <h3>Talk to Us</h3>

            <p>
                Planning a celebration, corporate event or simply
                craving something special?
            </p>

            <a href="contact.php">
                Send an Enquiry →
            </a>
        </div>

    </div>

    <div class="visit-cta">
        <p>Fresh ideas. Freshly baked.</p>

        <a href="listing.php">
            Order Online
        </a>
    </div>

</section>

    <?php require_once __DIR__ . "/includes/footer.php"; ?>
</body>
</html>
