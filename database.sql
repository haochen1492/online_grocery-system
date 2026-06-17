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

INSERT INTO `admin` (`username`, `email`, `password`, `email_verified`, `admin_role`, `created_at`, `verification_token`) VALUES ('superadmin', 'hcwmmt0114@gmail.com', '$2y$13$kbVuAKDN7Bor2XWBxKiLQe9oJja5GhXtZmEsD6lTRDHWyRWcZa//O', 1, 'superadmin', '2026-06-15 07:11:18', NULL);

CREATE TABLE IF NOT EXISTS categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(255) NOT NULL UNIQUE
);
INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(5, 'Beverages'),
(9, 'Eggs'),
(4, 'Food Essentials'),
(7, 'Frozen'),
(2, 'Fruits'),
(3, 'Snacks'),
(1, 'Vegetables');

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
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `stock_quantity`, `created_at`, `product_image`, `active`) VALUES
(1, 'Beijing Cabbage (China) 600g', 'Beijing Cabbage (China) 600g', 4.90, 47, '2026-06-15 07:11:18', 'beijingcabbage600g.jpg', 1),
(2, 'Dole Banana (Philippines) 1pack', 'Ripe bananas from tropical plantations, known for their creamy texture and natural sweetness. The hand of fruits arrives at perfect eating ripeness. Required 3-days advance for large order.', 8.20, 48, '2026-06-15 07:11:18', 'dolebanana1pack.jpg', 1),
(3, 'Oreo Vanilla Slug Sandwich Cookies 110.4g', 'Oreo Vanilla Slug Sandwich Cookies 110.4g', 3.20, 48, '2026-06-15 07:11:18', 'oreovanillaslugsandwich110.4g.jpg', 1),
(4, 'San Remo No.5 Spaghetti 500g', 'San Remo No.5 Spaghetti 500g', 5.00, 3, '2026-06-15 07:11:18', 'sanremospaghetti500g.jpg', 1),
(1, 'Bok Choy (Sawi Putih) 350g', 'Bok Choy (Sawi Putih) 350g', 4.70, 48, '2026-06-15 07:11:18', 'bokchoy(sawiputih)350g.webp', 1),
(3, 'Oriental Super Ring Family Pack 14g x 8', 'Oriental Super Ring Family Pack 14g x 8', 5.00, 98, '2026-06-15 07:11:18', 'oriental_super_ring_familypack14gx8.jpg', 1),
(5, 'Yarra Farm Australian Pasteurized Cows Milk 1L', 'Yarra Farm Australian Pasteurized Cows Milk 1L', 8.20, 0, '2026-06-15 07:11:18', 'yarra_farm_cows_milk1L.jpg', 1),
(5, 'Coca-Cola Carbonated Drink 1.5L', 'Coca-Cola Carbonated Drink 1.5L', 4.40, 99, '2026-06-15 07:38:27', 'cocacola_ori1.5L.png', 1),
(6, 'Simplot Hashbrown Original 637g', 'Simplot Hashbrown Original 637g', 15.90, 99, '2026-06-15 07:38:27', 'Simplot_Hashbrown_Original_637g.webp', 1),
(6, 'Figo Steamboat Choice 500g', 'Figo Steamboat Choice 500g', 12.40, 100, '2026-06-15 07:38:27', 'Figo_Steamboat_Choice_500g.webp', 1),
(7, 'Quaker Instant Oatmeal 800g', 'These instant oats are a rich source of fiber and energy that can be prepared in minutes. They provide a heart healthy breakfast option that can be customized with various toppings.', 10.50, 100, '2026-06-15 07:38:27', 'Quaker_Instant_Oatmeal_800g.webp', 1),
(7, 'Nestle Koko Krunch Cereal 450g', 'Nestle Koko Krunch Cereal 450g', 17.50, 100, '2026-06-15 07:38:27', 'Nestle_Koko_Krunch_Cereal_450g.webp', 1),
(4, 'Sunwhite Beras Wangi 5kg', 'A high quality fragrant rice known for its soft texture and pleasant aroma after cooking. It is ideal for daily meals and pairs perfectly with a wide variety of Asian cuisines.', 36.90, 99, '2026-06-15 07:38:27', 'SunwhiteBerasWangi5kg.webp', 1),
(1, 'Lushious Tomato Value Pack (Malaysia) 600g', 'Lushious Tomato Value Pack (Malaysia) 600g', 7.50, 99, '2026-06-15 07:38:27', 'LushiousTomatoValuePack600g.webp', 1),
(2, 'Red Seedless Watermelon 4.5kg', 'Red Seedless Watermelon 4.5kg', 28.00, 99, '2026-06-15 07:38:27', 'RedSeedlessWatermelon4.5kg.webp', 1),
(5, 'F&N Ice Mountain Mineral Water 1.5L', '', 1.50, 97, '2026-06-15 07:38:27', 'F&NIceMountainMineralWater1.5L.webp', 1),
(9, 'NutriPlus Omega-3 Chicken Eggs 15pcs/pack', 'NutriPlus Omega-3 Chicken Eggs 15pcs/pack', 11.50, 100, '2026-06-15 07:45:26', 'NutriPlusOmega-3ChickenEggs15pcsperpack.jpg', 1),
(9, 'Nutriplus Kampung Eggs with Omega-3 10pcs/pack', 'Nutriplus Kampung Eggs with Omega-3 10pcs/pack', 8.60, 100, '2026-06-15 07:46:45', 'NutriplusKampungEggswithOmega-3_10pcsperpack.jpg', 1);

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

CREATE TABLE IF NOT EXISTS password_resets (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    username   VARCHAR(255) NOT NULL,
    token      VARCHAR(64)  NOT NULL UNIQUE,
    expires_at DATETIME     NOT NULL,
    used       TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
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
