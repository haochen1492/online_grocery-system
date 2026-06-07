CREATE DATABASE online_grocery;

USE online_grocery;

CREATE TABLE IF NOT EXISTS admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email_verified INT(1) DEFAULT 0,
    admin_role enum('superadmin', 'admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    verification_token VARCHAR(64) NULL
);

INSERT INTO admin(username, email, password, email_verified, admin_role) VALUES ('superadmin', 'hcwmmt0114@gmail.com' , '$2y$13$kbVuAKDN7Bor2XWBxKiLQe9oJja5GhXtZmEsD6lTRDHWyRWcZa//O', 1, 'superadmin');

CREATE TABLE IF NOT EXISTS categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(255) NOT NULL UNIQUE
);
INSERT INTO `categories`( `category_name`) VALUES ('Vegetables');
INSERT INTO `categories`( `category_name`) VALUES ('Fruits');
INSERT INTO `categories`( `category_name`) VALUES ('Snacks');
INSERT INTO `categories`( `category_name`) VALUES ('Food Essentials');
INSERT INTO `categories`( `category_name`) VALUES ('Beverages');

CREATE TABLE IF NOT EXISTS products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id int NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity int NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    product_image VARCHAR(255),
    active BOOLEAN NOT NULL DEFAULT TRUE
);
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `stock_quantity`, `product_image`, `active`) VALUES
(1, 'Beijing Cabbage (China) 600g', 'Beijing Cabbage (China) 600g', 4.90, 48,  'beijingcabbage600g.jpg', 1),
(2, 'Dole Banana (Philippines) 1pack', 'Ripe bananas from tropical plantations, known for their creamy texture and natural sweetness. The hand of fruits arrives at perfect eating ripeness. Required 3-days advance for large order.', 8.20, 49, 'dolebanana1pack.jpg', 1),
(3, 'Oreo Vanilla Slug Sandwich Cookies 110.4g', 'Oreo Vanilla Slug Sandwich Cookies 110.4g', 3.20, 49, 'oreovanillaslugsandwich110.4g.jpg', 1),
(4, 'San Remo No.5 Spaghetti 500g', 'San Remo No.5 Spaghetti 500g', 5.00, 4,  'sanremospaghetti500g.jpg', 1),
(1, 'Bok Choy (Sawi Putih) 350g', 'Bok Choy (Sawi Putih) 350g', 4.70, 49, 'bokchoy(sawiputih)350g.webp', 1),
(3, 'Oriental Super Ring Family Pack 14g x 8', 'Oriental Super Ring Family Pack 14g x 8', 5.00, 99,  'oriental_super_ring_familypack14gx8.jpg', 1),
(5, 'Yarra Farm Australian Pasteurized Cows Milk 1L', 'Yarra Farm Australian Pasteurized Cows Milk 1L', 8.20, 0,  'yarra_farm_cows_milk1L.jpg', 1);



CREATE TABLE IF NOT EXISTS customers (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL UNIQUE,
    customer_password VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS addresses (
    address_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id int NOT NULL,
    unit_no VARCHAR(255) NOT NULL,
    street VARCHAR(255) NOT NULL,
    city VARCHAR(255) NOT NULL,
    state VARCHAR(255) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(255) NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    order_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    address_id int NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    delivery_status enum('pending','shipped', 'delivered') NOT NULL DEFAULT 'pending',
    customer_id int NOT NULL,
    FOREIGN KEY (address_id) REFERENCES addresses(address_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_details (
    order_detail_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id int NOT NULL,
    product_id int NOT NULL,
    quantity int NOT NULL,
    product_price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id int NOT NULL,
    payment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    price DECIMAL(10, 2) NOT NULL,
    payment_status enum('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cart (
    cart_id int primary key AUTO_INCREMENT,
    customer_id int not null,
    product_id int not null,
    quantity int not null,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    active boolean not null default true,
    selected boolean not null default true
);

CREATE TABLE IF NOT EXISTS `pass_reset` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE customers ADD COLUMN is_verified TINYINT(1) DEFAULT 0;

CREATE TABLE IF NOT EXISTS register_verify (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    otp_code VARCHAR(6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) AFTER total_price;

ALTER TABLE cart ADD COLUMN selected boolean NOT NULL DEFAULT true AFTER active;

ALTER TABLE addresses ADD COLUMN active boolean NOT NULL DEFAULT true AFTER country;

ALTER TABLE products ADD COLUMN active boolean NOT NULL DEFAULT true AFTER product_image;

ALTER TABLE admin ADD COLUMN email VARCHAR(255) NOT NULL UNIQUE AFTER username;

alter table admin add column email_verified INT(1) DEFAULT 0 after password;

ALTER TABLE admin ADD COLUMN verification_token VARCHAR(64) NULL;
