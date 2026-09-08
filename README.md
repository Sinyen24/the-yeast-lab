# The Yeast Lab

The Yeast Lab is a responsive, database-driven pastry bakery web application developed for the UECS2194 Web Application Development group assignment.

The project allows customers to explore bakery products, create an account, manage a shopping cart, place pickup or delivery orders, view previous orders, update their profile, and submit inquiries. Administrators can manage products, orders, and customer inquiries.

## Project Preview

### Homepage

![The Yeast Lab homepage](screenshots/homescreen%20%281%29.png)

<details>
<summary><strong>View the complete homepage</strong></summary>

<br>

<img src="screenshots/homescreen%20%282%29.png" alt="Homepage categories" width="49%">
<img src="screenshots/homescreen%20%283%29.png" alt="Homepage product section" width="49%">
<img src="screenshots/homescreen%20%284%29.png" alt="Homepage featured products" width="49%">
<img src="screenshots/homescreen%20%285%29.png" alt="Homepage bakery content" width="49%">
<img src="screenshots/homescreen%20%286%29.png" alt="Homepage information section" width="49%">
<img src="screenshots/homescreen%20%287%29.png" alt="Homepage additional content" width="49%">
<img src="screenshots/homescreen%20%288%29.png" alt="Homepage footer" width="49%">

</details>

### Product Catalogue

The product catalogue supports category filtering, keyword search, sorting and database-driven product cards.

<details>
<summary><strong>View the product catalogue</strong></summary>

<br>

<img src="screenshots/product_listing_page%20%20%281%29.png" alt="Product listing filters" width="49%">
<img src="screenshots/product_listing_page%20%20%282%29.png" alt="Product listing cards" width="49%">
<img src="screenshots/product_listing_page%20%20%283%29.png" alt="Additional products" width="49%">
<img src="screenshots/product_listing_page%20%20%284%29.png" alt="Product listing footer" width="49%">

</details>

### Product Details

<details>
<summary><strong>View the product details page</strong></summary>

<br>

<img src="screenshots/product_detail_page%20%281%29.png" alt="Product information and quantity selector" width="49%">
<img src="screenshots/product_detail_page%20%282%29.png" alt="Additional product details" width="49%">

</details>

### Cart and Checkout

![Customer cart and checkout interface](screenshots/cart-checkout.png)

### Administrator Product Management

The protected administrator area provides product creation, editing, availability management and deletion controls.

<details>
<summary><strong>View administrator product management</strong></summary>

<br>

<img src="screenshots/admin-products%20%281%29.png" alt="Administrator product list" width="49%">
<img src="screenshots/admin-products%20%282%29.png" alt="Administrator product controls" width="49%">
<img src="screenshots/admin-products%20%283%29.png" alt="Administrator product form" width="49%">

</details>

### Administrator Order Management

<details>
<summary><strong>View administrator order management</strong></summary>

<br>

<img src="screenshots/admin-orders%20%281%29.png" alt="Administrator order list" width="49%">
<img src="screenshots/admin-orders%20%282%29.png" alt="Order products, remarks and status controls" width="49%">

</details>

### Contact and Inquiry Management

![Contact form and inquiry-management interface](screenshots/contact_inquiries.png)

## Technologies Used

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- WampServer
- phpMyAdmin

No external frameworks, website templates, UI kits, CSS libraries, JavaScript libraries, or PHP frameworks are used.

## Main Features

### Customer Features

- Responsive homepage and navigation menu
- Database-driven product listing
- Category filtering, product search, and sorting
- Individual product details page
- Customer registration, login, and logout
- Secure password hashing and PHP sessions
- Login-protected shopping cart
- Add, increase, decrease, remove, and reset cart items
- Stock validation during cart and checkout operations
- Pickup and delivery checkout options
- Order creation with database transactions
- Customer profile editing
- Customer order history
- Contact form stored in MySQL

### Administrator Features

- Role-protected administrator area
- Dashboard statistics
- Product Create, Read, Update, and Delete operations
- Product availability and featured-product management
- Order search and status management
- Payment-status management
- Inquiry search, status management, and deletion

### JavaScript Features

- Responsive mobile navigation toggle
- Product quantity selector
- Password show/hide controls
- Dynamic pickup and delivery selection
- Dynamic delivery-address requirement
- Dynamic delivery-fee and grand-total display

## Project Structure

```text
The_Yeast_Lab/
|-- admin/
|   |-- admin-header.php
|   |-- auth-check.php
|   |-- dashboard.php
|   |-- inquiries.php
|   |-- orders.php
|   |-- product-form.php
|   `-- products.php
|-- includes/
|   |-- footer.php
|   |-- header.php
|   |-- the-yeast-lab-logo.svg
|   `-- product and website images
|-- about.php
|-- cart.php
|-- checkout.php
|-- contact.php
|-- database.sql
|-- db.php
|-- details.php
|-- edit-profile.php
|-- index.php
|-- listing.php
|-- login.php
|-- logout.php
|-- order-success.php
|-- profile.php
|-- register.php
|-- script.js
|-- style.css
`-- README.md
```

## System Requirements

- Windows computer
- WampServer with Apache, PHP, MySQL, and phpMyAdmin
- A modern web browser such as Google Chrome, Microsoft Edge, or Firefox

## Installation Instructions

### 1. Install and Start WampServer

Install WampServer and start it. Wait until the WampServer icon becomes green, indicating that Apache and MySQL are running.

### 2. Copy the Project Folder

Copy the complete `The_Yeast_Lab` folder into:

```text
C:\wamp64\www\
```

The resulting path should be:

```text
C:\wamp64\www\The_Yeast_Lab\
```

### 3. Create and Import the Database

1. Open a browser and visit `http://localhost/phpmyadmin/`.
2. Select the **Import** tab.
3. Choose `database.sql` from the project folder.
4. Click **Go**.
5. Confirm that the `yeast_lab_db` database is created.
6. Confirm that it contains these tables:
   - `categories`
   - `products`
   - `users`
   - `orders`
   - `order_items`
   - `inquiries`

The SQL file creates the database structure and inserts the initial categories and products.

### 4. Check the Database Configuration

The default settings in `db.php` are:

```php
$databaseHost = "localhost";
$databaseUsername = "root";
$databasePassword = "";
$databaseName = "yeast_lab_db";
```

Update these values only if the local MySQL configuration is different.

### 5. Run the Website

Open this address in a browser:

```text
http://localhost/The_Yeast_Lab/
```

Alternatively, open the homepage directly:

```text
http://localhost/The_Yeast_Lab/index.php
```

## Creating a Customer Account

1. Open `http://localhost/The_Yeast_Lab/register.php`.
2. Enter a full name, email address, phone number, and password.
3. Submit the registration form.
4. Log in using the newly created account.

Customer accounts can access the shopping cart, checkout, profile management, and order history.


## Demo Administrator Account

- Email: `admin@theyeastlab.com`
- Password: `admin123`

Administrator dashboard:

`http://localhost/The_Yeast_Lab/admin/dashboard.php`

This account is provided only for local assignment demonstration purposes.


## Creating an Additional Administrator Account
A demonstration administrator account is already included. To create an additional administrator account:

1. Register a new customer account through `register.php`.
2. Open phpMyAdmin.
3. Select the `yeast_lab_db` database.
4. Open the **SQL** tab.
5. Run the following query, replacing the email address with the registered account:
```sql
UPDATE users
SET user_role = 'Admin'
WHERE email = 'your-email@example.com';
```
6. Log out if the account is currently signed in.
7. Log in again to refresh the user role stored in the PHP session.
The administrator dashboard is available at:

```text
http://localhost/The_Yeast_Lab/admin/dashboard.php
```


## Product Image Management

Product image files are stored in the `includes` folder. When adding or editing a product in the administrator area, enter the exact image filename, including its extension.

Example:

```text
strawberrydanish.jpeg
```

The image must already exist inside:

```text
The_Yeast_Lab/includes/
```

## Suggested Functional Test

Perform the following test after installation:

1. Register a customer account.
2. Log in as the customer.
3. Browse, search, filter, and sort products.
4. Open a product details page.
5. Add products to the cart.
6. Increase and decrease product quantities.
7. Complete one pickup order.
8. Complete one delivery order.
9. View the orders from the customer profile.
10. Submit a contact inquiry.
11. Log out and log in using an administrator account.
12. Add, edit, deactivate, and delete a temporary product.
13. Update an order and payment status.
14. Update and delete a temporary inquiry.
15. Test the layout on desktop, tablet, and mobile screen sizes.

## Video Demonstration

A recorded demonstration of The Yeast Lab is available through the Google Drive link below. The video presents the customer and administrator features, responsive interface, JavaScript interactions, PHP processing, MySQL database integration, CRUD operations, authentication and session management.

[Watch The Yeast Lab Video Demonstration](https://utarict-my.sharepoint.com/:v:/r/personal/sinyen24_1utar_my/Documents/UECS2194_Presentation_P6_G4.mp4?d=wd4d678dbc4d74a5da58a4e17ce8b3558&csf=1&web=1&nav=eyJyZWZlcnJhbEluZm8iOnsicmVmZXJyYWxBcHAiOiJPbmVEcml2ZUZvckJ1c2luZXNzIiwicmVmZXJyYWxBcHBQbGF0Zm9ybSI6IldlYiIsInJlZmVycmFsTW9kZSI6InZpZXciLCJyZWZlcnJhbFZpZXciOiJNeUZpbGVzTGlua0NvcHkifX0&e=0lgRHx)

The video is submitted as part of the UECS2194 Web Application Development group assignment.

## Security and Validation

The project implements:

- Prepared SQL statements
- Password hashing with `password_hash()`
- Password verification with `password_verify()`
- Session regeneration after login
- Customer and administrator role checks
- Server-side form validation
- Output escaping with `htmlspecialchars()`
- Stock validation during cart and checkout operations
- MySQL transactions for order creation and stock updates
- Order ownership checks

## Troubleshooting

### WampServer Icon Is Not Green

Confirm that Apache and MySQL are running. Check whether another application is using Apache or MySQL ports.

### Database Connection Failed

Confirm that MySQL is running and verify the username, password, and database name in `db.php`.

### CSS Changes Do Not Appear

Save `style.css`, then perform a hard refresh using `Ctrl + F5`.

### Product Image Does Not Appear

Confirm that the image filename stored in MySQL exactly matches the filename inside the `includes` folder.

### Administrator Access Is Denied

Confirm that the account has `user_role = 'Admin'` in the `users` table. Log out and log in again after changing the role.

## Academic Notes

This project was developed specifically for the Web Application Development group assignment. Team members should understand and be able to explain every submitted component during the recorded demonstration.

All externally sourced text, photographs, icons, design references, and other borrowed content must be acknowledged in the project report using Harvard referencing. The project source code must remain original and must not include prohibited frameworks or templates.

## Authors

- Chew Sin Yen — 2305593
- Ong Huai Zhou — 2303180
- Hue Wei Sheng — 2305332

Practical Group: P6  
Assignment Group: G4

## Licence

This project is intended for educational use as part of the UTAR Web Application Development assignment.
