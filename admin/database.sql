CREATE DATABASE IF NOT EXISTS grocery_admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE grocery_admin;

-- ADMIN USERS
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin','admin') DEFAULT 'admin',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (name, email, password, role) VALUES
('Super Admin', 'admin@grocery.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- CATEGORIES
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories (name, slug, description, status) VALUES
('Fruits & Vegetables', 'fruits-vegetables', 'Fresh farm produce', 'active'),
('Dairy & Eggs', 'dairy-eggs', 'Milk, cheese, butter and eggs', 'active'),
('Meat & Seafood', 'meat-seafood', 'Fresh and frozen meats', 'active'),
('Bakery', 'bakery', 'Bread, cakes and pastries', 'active'),
('Beverages', 'beverages', 'Drinks and juices', 'active'),
('Snacks', 'snacks', 'Chips, biscuits and sweets', 'active');

-- PRODUCTS
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) DEFAULT NULL,
    stock INT DEFAULT 0,
    unit VARCHAR(50) DEFAULT 'piece',
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

INSERT INTO products (category_id, name, slug, description, price, sale_price, stock, unit, status, featured) VALUES
(1, 'Organic Bananas', 'organic-bananas', 'Fresh organic bananas from local farms', 4.99, 3.99, 150, 'bunch', 'active', 1),
(1, 'Red Apples', 'red-apples', 'Crispy red apples', 6.50, NULL, 200, 'kg', 'active', 0),
(1, 'Broccoli', 'broccoli', 'Fresh green broccoli', 3.20, NULL, 80, 'head', 'active', 0),
(2, 'Whole Milk', 'whole-milk', 'Fresh whole milk 1L', 2.99, NULL, 100, 'liter', 'active', 1),
(2, 'Farm Eggs', 'farm-eggs', 'Free range farm eggs', 5.50, 4.99, 60, 'dozen', 'active', 0),
(3, 'Chicken Breast', 'chicken-breast', 'Boneless skinless chicken breast', 12.99, 10.99, 40, 'kg', 'active', 1),
(4, 'Sourdough Bread', 'sourdough-bread', 'Artisan sourdough loaf', 5.99, NULL, 30, 'loaf', 'active', 0),
(5, 'Orange Juice', 'orange-juice', '100% fresh orange juice', 4.49, NULL, 75, 'liter', 'active', 0),
(6, 'Potato Chips', 'potato-chips', 'Crispy salted chips', 2.99, 2.50, 120, 'pack', 'active', 0);

-- CUSTOMERS
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    status ENUM('active','blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO customers (name, email, phone, address, city, status) VALUES
('Ahmad bin Hassan', 'ahmad@gmail.com', '012-3456789', '12 Jalan Putra', 'Kuala Lumpur', 'active'),
('Siti Rahmah', 'siti@yahoo.com', '016-8765432', '45 Taman Bahagia', 'Petaling Jaya', 'active'),
('Lee Chong Wei', 'lee@hotmail.com', '011-2233445', '7 Lorong Damai', 'Seremban', 'active'),
('Priya Devi', 'priya@gmail.com', '019-9988776', '23 Jalan Bunga', 'Johor Bahru', 'active'),
('Raj Kumar', 'raj@gmail.com', '013-1122334', '88 Jalan Merdeka', 'Ipoh', 'blocked');

-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount DECIMAL(10,2) DEFAULT 0,
    delivery_fee DECIMAL(10,2) DEFAULT 5.00,
    grand_total DECIMAL(10,2) NOT NULL,
    status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    payment_method ENUM('cod','online','card') DEFAULT 'cod',
    payment_status ENUM('unpaid','paid','refunded') DEFAULT 'unpaid',
    delivery_address TEXT,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- ORDER ITEMS
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

INSERT INTO orders (order_number, customer_id, total_amount, discount, delivery_fee, grand_total, status, payment_method, payment_status, delivery_address) VALUES
('ORD-2024-001', 1, 25.47, 0, 5.00, 30.47, 'delivered', 'online', 'paid', '12 Jalan Putra, Kuala Lumpur'),
('ORD-2024-002', 2, 18.98, 2.00, 5.00, 21.98, 'shipped', 'card', 'paid', '45 Taman Bahagia, Petaling Jaya'),
('ORD-2024-003', 3, 32.50, 0, 5.00, 37.50, 'processing', 'cod', 'unpaid', '7 Lorong Damai, Seremban'),
('ORD-2024-004', 4, 15.99, 0, 5.00, 20.99, 'pending', 'online', 'paid', '23 Jalan Bunga, Johor Bahru'),
('ORD-2024-005', 1, 42.48, 5.00, 0, 37.48, 'confirmed', 'card', 'paid', '12 Jalan Putra, Kuala Lumpur');

INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES
(1, 1, 2, 3.99, 7.98), (1, 4, 1, 2.99, 2.99), (1, 6, 1, 10.99, 10.99), (1, 8, 1, 4.49, 4.49),
(2, 2, 1, 6.50, 6.50), (2, 5, 1, 4.99, 4.99), (2, 9, 2, 2.50, 5.00),
(3, 6, 2, 10.99, 21.98), (3, 7, 1, 5.99, 5.99), (3, 3, 1, 3.20, 3.20),
(4, 1, 1, 3.99, 3.99), (4, 4, 2, 2.99, 5.98), (4, 8, 1, 4.49, 4.49),
(5, 6, 3, 10.99, 32.97), (5, 5, 1, 4.99, 4.99), (5, 7, 1, 5.99, 5.99);
