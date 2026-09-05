<?php

session_start();

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

    <title>Our Story | The Yeast Lab</title>

    <link rel="stylesheet" href="style.css">
</head>

<body id="top">

    <?php require_once __DIR__ . "/includes/header.php"; ?>


    <main class="about-page">

        <!-- About hero -->
        <section class="about-hero">

            <div class="about-hero-image">
                <img src="includes/hero-the-yeast-lab.png"
                     alt="Fresh breads and pastries at The Yeast Lab">
            </div>

            <div class="about-hero-content">

                <p class="section-label">
                    Our Story
                </p>

                <h1>
                    Curiosity<br>
                    Made Edible.
                </h1>

                <p>
                    The Yeast Lab is an artisan bakery inspired by
                    experimentation, thoughtful technique and the
                    simple joy of sharing something freshly baked.
                </p>

            </div>

        </section>


        <!-- Brand introduction -->
        <section class="about-introduction">

            <div class="about-introduction-heading">

                <p class="section-label">
                    How It Began
                </p>

                <h2>
                    Every great bake begins with a question.
                </h2>

            </div>

            <div class="about-introduction-text">

                <p class="about-large-text">
                    What happens when traditional bakery craft meets
                    the curiosity of a laboratory?
                </p>

                <p>
                    The Yeast Lab began with a love for slow-fermented
                    bread, delicate pastries and the endless possibilities
                    hidden inside simple ingredients.
                </p>

                <p>
                    We approach each recipe as an experiment worth
                    perfecting. Temperature, timing, texture and flavour
                    are carefully studied—but the final result should
                    always feel warm, generous and made for sharing.
                </p>

            </div>

        </section>


        <!-- Baking process -->
        <section class="process-section">

            <div class="section-heading">

                <p class="section-label">
                    Our Process
                </p>

                <h2>The Baking Method</h2>

                <p>
                    Good baking cannot be rushed. Every stage is given
                    the attention and time it deserves.
                </p>

            </div>


            <div class="process-grid">

                <article class="process-card">

                    <span>01</span>

                    <h3>Question</h3>

                    <p>
                        Every creation begins with an idea, flavour or
                        texture that makes us curious.
                    </p>

                </article>

                <article class="process-card">

                    <span>02</span>

                    <h3>Experiment</h3>

                    <p>
                        We test ingredient combinations, fermentation
                        times and techniques with careful precision.
                    </p>

                </article>

                <article class="process-card">

                    <span>03</span>

                    <h3>Perfect</h3>

                    <p>
                        Each recipe is refined until its flavour,
                        structure and texture reach the right balance.
                    </p>

                </article>

                <article class="process-card">

                    <span>04</span>

                    <h3>Share</h3>

                    <p>
                        The finished bake leaves our laboratory and
                        becomes part of your everyday moments.
                    </p>

                </article>

            </div>

        </section>


        <!-- Brand philosophy -->
        <section class="philosophy-section">

            <div class="philosophy-image">

                <img src="includes/pistachio.jpg"
                     alt="Pistachio pastry made at The Yeast Lab">

                <span>
                    Crafted with curiosity
                </span>

            </div>


            <div class="philosophy-content">

                <p class="section-label">
                    Our Philosophy
                </p>

                <h2>
                    Precise in method.<br>
                    Playful in spirit.
                </h2>

                <p>
                    We respect classic techniques while leaving room for
                    new ideas. Our menu combines familiar comfort with
                    unexpected flavours, textures and seasonal ingredients.
                </p>

                <div class="philosophy-list">

                    <div>
                        <span>01</span>
                        <strong>Quality ingredients</strong>
                    </div>

                    <div>
                        <span>02</span>
                        <strong>Patient fermentation</strong>
                    </div>

                    <div>
                        <span>03</span>
                        <strong>Small-batch production</strong>
                    </div>

                    <div>
                        <span>04</span>
                        <strong>Fresh daily baking</strong>
                    </div>

                </div>

                <a href="listing.php">
                    Explore Our Bakes →
                </a>

            </div>

        </section>


        <!-- Final quotation -->
        <section class="about-quote">

            <p>
                “The best experiments are the ones
                you can share around a table.”
            </p>

            <span>The Yeast Lab</span>

        </section>

    </main>


    <?php require_once __DIR__ . "/includes/footer.php"; ?>

</body>
</html>