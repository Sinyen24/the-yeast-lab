-- =====================================================
-- The Yeast Lab Database
-- =====================================================

CREATE DATABASE IF NOT EXISTS yeast_lab_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE yeast_lab_db;


-- =====================================================
-- Categories Table
-- =====================================================

CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    category_slug VARCHAR(100) NOT NULL UNIQUE,
    category_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)ENGINE = InnoDB;


-- =====================================================
-- Products Table
-- =====================================================

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    product_description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    is_featured BOOLEAN NOT NULL DEFAULT FALSE,
    product_status ENUM('Available', 'Unavailable')
        NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_category
        FOREIGN KEY (category_id)
        REFERENCES categories(category_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
)ENGINE = InnoDB;


-- =====================================================
-- Insert Product Categories
-- =====================================================

INSERT INTO categories
(category_name, category_slug, category_description)
VALUES
(
    'Cakes',
    'cakes',
    'Celebration cakes and carefully crafted individual cakes.'
),
(
    'Pastries',
    'pastries',
    'Freshly baked laminated pastries, danishes and tarts.'
),
(
    'Breads',
    'breads',
    'Slow-fermented artisan breads and savoury buns.'
),
(
    'Beverages',
    'beverages',
    'Coffee, tea and refreshing drinks made to accompany our bakes.'
);


-- =====================================================
-- Insert Sample Products
-- =====================================================

INSERT INTO `products` (`product_id`, `category_id`, `product_name`, `product_description`, `price`, `image`, `stock_quantity`, `is_featured`, `product_status`, `created_at`, `updated_at`) VALUES

(1, 2, 'Strawberry Danish', 'A crisp and buttery Danish pastry filled with smooth cream and fresh strawberries.', 12.90, 'strawberrydanish.jpeg', 22, 1, 'Available', '2026-09-04 18:29:15', '2026-09-05 10:24:04'),
(2, 2, 'Pistachio Roll', 'A flaky laminated pastry finished with rich pistachio cream and crushed pistachios.', 15.90, 'pistachio.jpg', 15, 1, 'Available', '2026-09-04 18:29:15', '2026-09-05 14:46:48'),
(3, 2, 'Matcha Mochi Royale', 'A buttery pastry combining earthy matcha flavour with a soft and chewy mochi centre.', 14.90, 'matchamochi.jpeg', 18, 0, 'Available', '2026-09-04 18:29:15', '2026-09-04 18:29:15'),
(4, 2, 'Blueberry Danish', 'A golden Danish pastry layered with smooth cream and sweet blueberry filling.', 12.90, 'blueberrydanish.jpeg', 22, 0, 'Available', '2026-09-04 18:29:15', '2026-09-04 18:29:15'),
(5, 2, 'Crème Brûlée Tart', 'A crisp pastry shell filled with creamy vanilla custard and a caramelised sugar top.', 8.90, 'cremebrulee.jpeg', 21, 1, 'Available', '2026-09-04 18:29:15', '2026-09-05 14:46:26'),
(6, 2, 'Exotic Berries Tart', 'A delicate tart filled with silky cream and topped with a colourful selection of berries.', 16.90, 'exoticberries.jpeg', 16, 1, 'Available', '2026-09-04 18:29:15', '2026-09-05 13:50:54'),
(7, 3, 'Roasted Garlic Bread', 'Soft artisan bread filled with roasted garlic butter and fragrant herbs.', 6.90, 'garlicbread.jpeg', 29, 0, 'Available', '2026-09-04 18:29:15', '2026-09-05 10:24:04'),
(8, 3, 'Ori Shio Pan', 'A soft Japanese-style salt bread with a lightly crisp exterior and buttery centre.', 5.90, 'shiopan ori.jpeg', 29, 1, 'Available', '2026-09-04 18:29:15', '2026-09-05 14:45:12'),
(9, 3, 'Coffee Buns', 'A fluffy bun covered with a crisp aromatic coffee topping and a buttery centre.', 5.90, 'coffeebun.jpeg', 30, 0, 'Available', '2026-09-04 18:29:15', '2026-09-05 12:14:33'),
(11, 2, 'Butter Croissant', 'A classic French-style croissant with delicate golden layers, a crisp exterior and a rich buttery centre.', 9.90, 'buttercroissant.jpeg', 10, 0, 'Available', '2026-09-05 12:19:45', '2026-09-05 12:19:45'),
(12, 2, 'Almond Croissant', 'A buttery croissant filled with fragrant almond cream and finished with toasted almond flakes and icing sugar.', 13.90, 'almondcroissant.jpg', 8, 0, 'Available', '2026-09-05 12:33:53', '2026-09-05 12:34:48'),
(13, 2, 'Pistachio Croissant', 'A flaky croissant generously filled with smooth pistachio cream and topped with crushed roasted pistachios.', 16.90, 'pistachiocroissant.jpeg', 8, 0, 'Available', '2026-09-05 12:36:09', '2026-09-05 12:36:09'),
(14, 2, 'Pain au Chocolat', 'Buttery laminated pastry wrapped around rich dark chocolate, baked until crisp and golden.', 11.90, 'PainauChocolat.jpg', 8, 0, 'Available', '2026-09-05 12:37:25', '2026-09-05 12:37:25'),
(15, 2, 'Cheesy Truffle Mushroom Croissant', 'A savoury croissant filled with creamy mushrooms, melted cheese and aromatic truffle seasoning.', 17.90, 'CheesyTruffleMushroomCroissant.jpg', 8, 0, 'Available', '2026-09-05 12:38:08', '2026-09-05 12:38:08'),
(16, 2, 'Ribbon Croissant', 'A beautifully layered ribbon-shaped croissant with a crisp caramelised surface and soft buttery interior.', 12.90, 'RibbonCroissant.jpg', 8, 1, 'Available', '2026-09-05 12:38:52', '2026-09-05 13:48:58'),
(17, 2, 'Cereal Milk Cruffin', 'A flaky croissant-muffin hybrid filled with cereal-infused cream and topped with colourful crunchy cereal pieces.', 13.90, 'CerealMilkCruffin.jpeg', 8, 0, 'Available', '2026-09-05 12:39:45', '2026-09-05 12:39:45'),
(18, 2, 'Crownies with Hazelnut Sauce', 'Bite-sized croissant and brownie hybrids with crisp layers, a chocolate centre and creamy hazelnut dipping sauce.', 16.90, 'CrownieswithHazelnutSauce.jpeg', 5, 0, 'Available', '2026-09-05 12:40:17', '2026-09-05 12:40:17'),
(19, 3, 'Matcha Red Bean Shio Pan', 'Japanese salt bread infused with earthy matcha and filled with lightly sweetened red bean paste.', 8.90, 'Matcha Red Bean Shio Pan.jpeg', 8, 1, 'Available', '2026-09-05 12:54:28', '2026-09-05 13:48:27'),
(20, 3, 'Chocolate Orange Shio Pan', 'Buttery Shio Pan filled with dark chocolate and bright citrus notes from fragrant orange zest.', 8.90, 'chocalate shiopan.jpeg', 4, 0, 'Available', '2026-09-05 12:55:12', '2026-09-05 12:55:12'),
(21, 3, 'Cheesy Shio Pan', 'Buttery Shio Pan filled with melted cheese and baked until golden with crisp savoury edges.', 7.90, 'cheese-shio-pan.jpeg', 8, 0, 'Available', '2026-09-05 12:55:47', '2026-09-05 12:55:47'),
(22, 3, 'Mocha Shio Pan', 'Soft salt bread combining aromatic coffee with rich chocolate for a balanced bittersweet flavour.', 8.90, 'Mocha Shio Pan.jpeg', 8, 0, 'Available', '2026-09-05 12:56:22', '2026-09-05 12:56:22'),
(23, 3, 'Mentai Seaweed Shio Pan', 'Savoury Shio Pan topped with creamy mentai sauce and roasted seaweed flakes.', 9.90, 'mentai seaweed shiopan.jpeg', 3, 0, 'Available', '2026-09-05 12:57:04', '2026-09-05 13:05:03'),
(24, 3, 'Country Sourdough', 'A slow-fermented artisan loaf with a crisp rustic crust, open crumb and naturally tangy flavour.', 18.90, 'sourdough.jpeg', 10, 1, 'Available', '2026-09-05 12:58:51', '2026-09-05 13:48:12'),
(25, 3, 'Butter Corn Cheese Shio Pan', 'Buttery Japanese salt bread filled with sweet corn and creamy melted cheese for a rich sweet-and-savoury combination.', 8.90, 'butter corn cheese shiopan.jpeg', 8, 1, 'Available', '2026-09-05 13:00:38', '2026-09-05 14:46:00'),
(26, 3, 'Red Bean Butter Shio Pan', 'Soft salt bread filled with lightly sweetened red bean paste and a creamy butter centre, inspired by classic Japanese flavours.', 8.90, 'red bean butter shiopan.jpeg', 8, 1, 'Available', '2026-09-05 13:01:15', '2026-09-05 13:48:00'),
(27, 1, 'Burnt Cheesecake', 'A rich and creamy cheesecake with a deeply caramelised top, soft centre and balanced tangy finish.', 16.90, 'Burnt Cheesecake.jpg', 5, 1, 'Available', '2026-09-05 13:17:32', '2026-09-05 13:50:33'),
(28, 1, 'Matcha Burnt Cheesecake', 'A creamy Basque-style burnt cheesecake infused with earthy Japanese matcha and finished with a deeply caramelised top.', 18.90, 'matcha burntcheese cake.jpg', 2, 1, 'Available', '2026-09-05 13:18:03', '2026-09-05 13:50:26'),
(29, 1, 'Lime Avocado Cheesecake', 'A smooth avocado cheesecake brightened with fresh lime, set over a buttery biscuit base.', 18.90, 'Lime Avocado Cheesecake.jpeg', 2, 1, 'Available', '2026-09-05 13:18:41', '2026-09-05 13:50:20'),
(30, 1, 'Seasonal Fruit Tart', 'A crisp pastry shell filled with silky vanilla custard and topped with the freshest available seasonal fruits.', 16.90, 'Seasonal Fruit Tart.jpeg', 1, 0, 'Available', '2026-09-05 13:19:12', '2026-09-05 13:19:12'),
(31, 1, 'Tiramisu in a Cup', 'Layers of coffee-soaked sponge, creamy mascarpone and cocoa powder served in an individual dessert cup. Dine-in only.', 15.90, 'Tiramisu in a Cup.jpeg', 7, 1, 'Available', '2026-09-05 13:19:44', '2026-09-05 13:49:54'),
(32, 1, 'Black Forest Gâteau', 'A rich chocolate sponge layered with light whipped cream, dark cherries and delicate chocolate shavings.', 17.90, 'Black Forest Gateau.jpeg', 5, 0, 'Available', '2026-09-05 13:20:18', '2026-09-05 13:20:25'),
(33, 1, 'Prinsesstårta (Princess Cake)', 'A classic Swedish cake with soft sponge, vanilla custard, raspberry jam and whipped cream beneath a smooth green marzipan layer.', 19.90, 'Prinsesstårta (Princess Cake).jpg', 4, 0, 'Available', '2026-09-05 13:20:57', '2026-09-05 13:20:57'),
(34, 1, 'Red Velvet Cake', 'A soft red cocoa sponge layered with smooth and tangy cream cheese frosting.', 16.90, 'Red Velvet Cake.jpg', 6, 0, 'Available', '2026-09-05 13:21:37', '2026-09-05 13:21:37'),
(35, 1, 'Tres Leches Cake', 'A light sponge soaked in three varieties of sweet milk and finished with a cloud of whipped cream.', 17.90, 'Tres Leches Cake.jpg', 5, 0, 'Available', '2026-09-05 13:22:17', '2026-09-05 13:22:17'),
(36, 1, 'Matcha Mille Crêpe', 'Delicate layers of thin crêpes and silky matcha cream, creating a soft texture with a balanced earthy sweetness.', 18.90, 'Matcha Mille Crêpe.jpeg', 7, 1, 'Available', '2026-09-05 13:23:42', '2026-09-05 13:49:44'),
(37, 4, 'Laboratory Cold Brew', 'Coffee slowly steeped for a smooth, refreshing flavour with gentle chocolate and caramel notes.', 12.90, 'Laboratory Cold Brew.jpeg', 20, 1, 'Available', '2026-09-05 13:35:45', '2026-09-05 13:49:32'),
(38, 4, 'Classic Café Latte', 'Fresh espresso combined with silky steamed milk for a smooth and comforting café classic.', 11.90, 'Classic Café Latte.jpeg', 20, 0, 'Available', '2026-09-05 13:36:14', '2026-09-05 13:36:14'),
(39, 4, 'Iced Matcha Latte', 'Premium matcha whisked with milk and served over ice for an earthy, creamy and refreshing drink.', 14.90, 'Iced Matcha Latte.jpg', 20, 1, 'Available', '2026-09-05 13:36:42', '2026-09-05 13:49:12'),
(40, 4, 'Hojicha Latte', 'Roasted Japanese green tea blended with milk, offering a warm, nutty aroma and naturally mellow flavour.', 14.90, 'Hojicha Latte.jpg', 17, 1, 'Available', '2026-09-05 13:37:16', '2026-09-05 13:49:24'),
(41, 4, 'Belgian Chocolate', 'Rich Belgian-style chocolate blended with creamy milk for a smooth and deeply chocolatey drink.', 13.90, 'Belgian Chocolate.jpeg', 17, 0, 'Available', '2026-09-05 13:37:59', '2026-09-05 13:37:59'),
(42, 4, 'Strawberry Sparkling Tea', 'Fragrant tea combined with strawberry and sparkling water for a fruity and refreshing finish.', 13.90, 'Strawberry Sparkling Tea.jpeg', 10, 0, 'Available', '2026-09-05 13:38:35', '2026-09-05 13:38:35'),
(43, 4, 'Yuzu Honey Soda', 'Bright Japanese yuzu and floral honey mixed with sparkling water for a sweet and citrusy refreshment.', 13.90, 'Yuzu Honey Soda.jpeg', 10, 0, 'Available', '2026-09-05 13:39:05', '2026-09-05 13:39:05'),
(44, 4, 'Earl Grey Peach Tea', 'Aromatic Earl Grey tea infused with juicy peach for a balanced floral and fruity flavour.', 12.90, 'Earl Grey Peach Tea.jpeg', 20, 0, 'Available', '2026-09-05 13:39:30', '2026-09-05 13:39:30'),
(45, 4, 'Sea Salt Caramel Latte', 'Espresso and steamed milk blended with caramel and finished with a delicate sea-salt cream.', 14.90, 'Sea Salt Caramel Latte.jpeg', 8, 0, 'Available', '2026-09-05 13:40:00', '2026-09-05 13:40:00'),
(46, 4, 'Golden Honeycomb Affogato', 'Creamy vanilla gelato topped with a fresh espresso shot and crunchy golden honeycomb pieces. Dine-in only.', 16.90, 'Golden Honeycomb Affogato.jpeg', 4, 0, 'Available', '2026-09-05 13:40:35', '2026-09-05 13:40:35'),
(47, 4, 'Jasmine Osmanthus Tea', 'A refreshing, floral drink made by combining brewed jasmine green tea, sweet osmanthus syrup or dried blossoms, and ice.', 9.90, 'jasmine osmanthus.jpeg', 10, 0, 'Available', '2026-09-05 13:41:49', '2026-09-05 13:41:49'),
(48, 4, 'Brown Sugar Fresh Milk', 'Creamy fresh milk layered with rich caramelised brown sugar for a comforting caffeine-free option.', 14.90, 'Brown Sugar Fresh Milk.jpg', 15, 0, 'Available', '2026-09-05 13:42:35', '2026-09-05 13:42:35'),
(49, 4, 'Strawberry Matche Latte', 'A popular, layered drink made by combining sweet strawberry puree, milk, and earthy green tea matcha.', 17.90, 'matcha strawberry.jpeg', 10, 0, 'Available', '2026-09-05 14:00:14', '2026-09-05 14:00:14'),
(50, 3, 'Plain Bread Loaf', 'A plain bread loaf is a simple, rectangular-shaped baked food made from a basic mixture of flour, water, yeast, salt, and a small amount of sugar or fat.', 6.90, 'plain loaf.jpg', 20, 0, 'Available', '2026-09-05 14:04:59', '2026-09-05 14:04:59'),
(51, 3, 'Wholemeal Multigrain Bread', 'Wholemeal multigrain bread is a dense, nutrient-dense loaf that combines the hearty base of whole wheat flour with a variety of whole seeds and grains. It provides a more robust texture and complex flavor profile compared to a standard white or plain wholemeal loaf.', 8.90, 'wholemeal multigrain bread.jpeg', 20, 0, 'Available', '2026-09-05 14:06:32', '2026-09-05 14:06:32');

-- =====================================================
-- Users Table
-- =====================================================

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30),
    password_hash VARCHAR(255) NOT NULL,

    user_role ENUM('Customer', 'Admin')
        NOT NULL DEFAULT 'Customer',

    account_status ENUM('Active', 'Inactive')
        NOT NULL DEFAULT 'Active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB;

-- =====================================================
-- Demo Administrator Account
-- Email: admin@theyeastlab.com
-- Password: admin123
-- =====================================================

INSERT INTO users
(
    full_name,
    email,
    phone,
    password_hash,
    user_role,
    account_status
)
VALUES
(
    'The Yeast Lab Admin',
    'admin@theyeastlab.com',
    '0123456789',
    '$2y$10$jvjmqy7Wzh7laNsT8rJdIehWd8pGpmx9DWQACW89Lwg7af84XbqeK',
    'Admin',
    'Active'
);

-- =====================================================
-- Orders Table
-- =====================================================

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    order_number VARCHAR(30) NOT NULL UNIQUE,

    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(30) NOT NULL,

    fulfilment_method ENUM('Pickup', 'Delivery')
        NOT NULL DEFAULT 'Pickup',

    delivery_address TEXT NULL,
    requested_date DATE NOT NULL,
    requested_time TIME NOT NULL,
    order_notes TEXT NULL,

    subtotal DECIMAL(10, 2) NOT NULL,
    delivery_fee DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    grand_total DECIMAL(10, 2) NOT NULL,

    order_status ENUM(
        'Pending',
        'Confirmed',
        'Preparing',
        'Ready',
        'Completed',
        'Cancelled'
    ) NOT NULL DEFAULT 'Pending',

    payment_method ENUM(
        'Pay at Counter'
    ) NOT NULL DEFAULT 'Pay at Counter',

    payment_status ENUM(
        'Pending',
        'Paid'
    ) NOT NULL DEFAULT 'Pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_order_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL

) ENGINE = InnoDB;


-- =====================================================
-- Order Items Table
-- =====================================================

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,

    product_name VARCHAR(150) NOT NULL,
    product_price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    line_total DECIMAL(10, 2) NOT NULL,

    CONSTRAINT fk_order_item_order
        FOREIGN KEY (order_id)
        REFERENCES orders(order_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_order_item_product
        FOREIGN KEY (product_id)
        REFERENCES products(product_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL

) ENGINE = InnoDB;

-- =====================================================
-- Customer Inquiries Table
-- =====================================================

CREATE TABLE inquiries (
    inquiry_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,

    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(150) NOT NULL,
    customer_phone VARCHAR(30) NULL,

    inquiry_subject VARCHAR(150) NOT NULL,
    inquiry_message TEXT NOT NULL,

    inquiry_status ENUM(
        'New',
        'In Progress',
        'Resolved'
    ) NOT NULL DEFAULT 'New',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_inquiry_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON UPDATE CASCADE
        ON DELETE SET NULL

) ENGINE = InnoDB;