-- ============================================================
-- FRESHMART ONLINE GROCERY — SAMPLE DATA
-- Run this AFTER your CREATE TABLE statements
-- ============================================================

USE online_grocery;

-- ── ADMIN ──
-- superadmin password: superadmin123
INSERT INTO admin (username, password, admin_role) VALUES
('superadmin', '$2y$13$kbVuAKDN7Bor2XWBxKiLQe9oJja5GhXtZmEsD6lTRDHWyRWcZa//O', 'superadmin');

-- admin password: admin123
INSERT INTO admin (username, password, admin_role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ── CATEGORIES ──
INSERT INTO categories (category_name) VALUES
('Fruits & Vegetables'),
('Dairy & Eggs'),
('Meat & Seafood'),
('Bakery'),
('Beverages'),
('Snacks & Confectionery');

-- ── PRODUCTS ──
INSERT INTO products (category_id, name, description, price, stock_quantity, product_image) VALUES
-- Fruits & Vegetables
(1, 'Organic Bananas',      'Fresh organic bananas from local farms, perfect for smoothies', 4.99,  150, 'https://images.unsplash.com/photo-1528825871115-3581a5387919?w=400&q=80'),
(1, 'Red Apples',           'Crispy and sweet red apples, freshly harvested',                6.50,  200, 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400&q=80'),
(1, 'Broccoli',             'Fresh green broccoli, rich in vitamins and minerals',           3.20,   80, 'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?w=400&q=80'),
(1, 'Mango',                'Sweet Harum Manis mango, tropical and juicy',                   8.50,  100, 'https://images.unsplash.com/photo-1601493700631-2b16ec4b4716?w=400&q=80'),
(1, 'Avocado',              'Creamy ripe Hass avocado, great for salads',                   11.99,   40, 'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=400&q=80'),
(1, 'Cherry Tomatoes',      'Sweet cherry tomatoes on the vine, fresh daily',                5.99,  110, 'https://images.unsplash.com/photo-1592841200221-a6898f307baa?w=400&q=80'),
(1, 'Carrot',               'Crunchy orange carrots, great for cooking or snacking',         4.99,  140, 'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=400&q=80'),
(1, 'Watermelon',           'Fresh whole watermelon, sweet and juicy inside',               15.99,   30, 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=400&q=80'),
-- Dairy & Eggs
(2, 'Fresh Whole Milk 1L',  'Farm fresh full cream milk, pasteurised',                       2.99,  100, 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400&q=80'),
(2, 'Farm Eggs (12pcs)',    'Free range farm eggs, large size',                              5.50,   60, 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=400&q=80'),
(2, 'Greek Yogurt 500g',    'Thick creamy Greek yogurt, high protein',                       8.99,   65, 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&q=80'),
(2, 'Cheddar Cheese 200g',  'Mature cheddar cheese block, great for sandwiches',            12.99,   45, 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400&q=80'),
(2, 'Salted Butter 250g',   'Creamy salted butter, perfect for baking and cooking',          8.50,   75, 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?w=400&q=80'),
-- Meat & Seafood
(3, 'Chicken Breast 1kg',   'Boneless skinless chicken breast, fresh and halal',            12.99,   40, 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=400&q=80'),
(3, 'Beef Ribeye Steak',    'Premium Australian beef ribeye, 300g per piece',               38.99,   20, 'https://images.unsplash.com/photo-1546833998-877b37c2e5c6?w=400&q=80'),
(3, 'Tiger Prawns 500g',    'Fresh large tiger prawns, cleaned and deveined',               24.99,   35, 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=400&q=80'),
(3, 'Salmon Fillet 300g',   'Fresh Norwegian salmon fillet, rich in omega-3',               29.99,   22, 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=400&q=80'),
-- Bakery
(4, 'Sourdough Loaf',       'Artisan sourdough bread, baked fresh daily',                    5.99,   30, 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&q=80'),
(4, 'Croissant',            'Buttery flaky croissant, freshly baked each morning',           4.99,   25, 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400&q=80'),
(4, 'Blueberry Muffin',     'Moist blueberry muffin, topped with sugar crumble',             3.99,   35, 'https://images.unsplash.com/photo-1607958996333-41aef7caefaa?w=400&q=80'),
-- Beverages
(5, 'Orange Juice 1L',      '100% pure squeezed orange juice, no added sugar',               4.49,   75, 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400&q=80'),
(5, 'Coconut Water 320ml',  'Natural isotonic coconut water, hydrating and refreshing',       4.50,  100, 'https://images.unsplash.com/photo-1536939459926-301728717817?w=400&q=80'),
(5, 'Oat Milk 1L',          'Barista oat milk, great for coffee and cooking',                 9.99,   55, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400&q=80'),
-- Snacks
(6, 'Potato Chips 150g',    'Crispy lightly salted potato chips',                             2.99,  120, 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&q=80'),
(6, 'Cashew Nuts 200g',     'Premium roasted whole cashews, lightly salted',                 12.99,   80, 'https://images.unsplash.com/photo-1611689342806-0863700ce1e4?w=400&q=80'),
(6, 'Dark Chocolate 100g',  '70% dark chocolate bar, rich and intense flavour',               7.99,   75, 'https://images.unsplash.com/photo-1511381939415-e44015466834?w=400&q=80');

-- ── CUSTOMERS ──
-- All passwords: password123
INSERT INTO customers (customer_name, customer_email, customer_password, customer_phone) VALUES
('Ahmad bin Hassan',    'ahmad@gmail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '012-3456789'),
('Siti Rahmah',         'siti@gmail.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '016-8765432'),
('Lee Chong Wei',       'lee@gmail.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '011-2233445'),
('Priya Devi',          'priya@gmail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '019-9988776'),
('Raj Kumar',           'raj@gmail.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '013-1122334'),
('Nurul Ain',           'nurul@gmail.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '017-5544332'),
('Tan Wei Ming',        'tanwei@gmail.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '014-6677889');

-- ── ADDRESSES ──
INSERT INTO addresses (customer_id, unit_no, street, city, state, postal_code, country) VALUES
(1, 'No. 12',  'Jalan Putra Damai',        'Kuala Lumpur',  'Wilayah Persekutuan', '50480', 'Malaysia'),
(2, 'Unit 4A', 'Jalan SS2/24 Taman Bahagia','Petaling Jaya', 'Selangor',            '47300', 'Malaysia'),
(3, 'No. 7',   'Lorong Damai 3',           'Seremban',      'Negeri Sembilan',     '70200', 'Malaysia'),
(4, 'No. 23',  'Jalan Bunga Raya',         'Johor Bahru',   'Johor',               '80100', 'Malaysia'),
(5, 'No. 88',  'Jalan Merdeka',            'Ipoh',          'Perak',               '30000', 'Malaysia'),
(6, 'B-12-3',  'Jalan Ampang Hilir',       'Ampang',        'Selangor',            '68000', 'Malaysia'),
(7, 'No. 5',   'Jalan Maharajalela',       'Kuala Lumpur',  'Wilayah Persekutuan', '50150', 'Malaysia');

-- ── ORDERS ──
INSERT INTO orders (order_date, address_id, total_price, delivery_status, customer_id) VALUES
('2025-01-05 10:30:00', 1, 45.47,  'delivered', 1),
('2025-01-08 14:15:00', 2, 31.98,  'delivered', 2),
('2025-01-12 09:00:00', 3, 67.50,  'shipped',   3),
('2025-01-15 16:45:00', 4, 24.99,  'pending',   4),
('2025-01-18 11:20:00', 1, 89.48,  'delivered', 1),
('2025-01-20 13:00:00', 5, 52.97,  'shipped',   5),
('2025-01-22 08:30:00', 6, 18.48,  'pending',   6),
('2025-01-25 15:00:00', 7, 37.98,  'delivered', 7),
('2025-02-01 10:00:00', 2, 76.47,  'delivered', 2),
('2025-02-03 12:30:00', 3, 29.97,  'pending',   3),
('2025-02-10 09:45:00', 1, 55.96,  'shipped',   1),
('2025-02-14 16:00:00', 4, 42.98,  'delivered', 4);

-- ── ORDER DETAILS ──
INSERT INTO order_details (order_id, product_id, quantity, product_price) VALUES
-- Order 1 (Ahmad)
(1, 1, 2, 4.99), (1, 9, 1, 2.99), (1, 14, 1, 12.99), (1, 21, 2, 4.49),
-- Order 2 (Siti)
(2, 2, 1, 6.50), (2, 10, 1, 5.50), (2, 24, 1, 2.99), (2, 18, 2, 5.99),
-- Order 3 (Lee)
(3, 14, 1, 12.99), (3, 16, 1, 24.99), (3, 3, 2, 3.20), (3, 19, 2, 4.99),
-- Order 4 (Priya)
(4, 16, 1, 24.99),
-- Order 5 (Ahmad)
(5, 15, 2, 38.99), (5, 10, 1, 5.50),
-- Order 6 (Raj)
(6, 14, 2, 12.99), (6, 18, 1, 5.99), (6, 21, 3, 4.49),
-- Order 7 (Nurul)
(7, 1, 2, 4.99), (7, 22, 1, 4.50), (7, 25, 1, 7.99),
-- Order 8 (Tan)
(8, 9, 2, 2.99), (8, 13, 2, 8.50), (8, 20, 2, 3.99),
-- Order 9 (Siti)
(9, 15, 1, 38.99), (9, 17, 1, 29.99), (9, 11, 1, 8.99),
-- Order 10 (Lee)
(10, 1, 2, 4.99), (10, 7, 3, 4.99), (10, 22, 1, 4.50),
-- Order 11 (Ahmad)
(11, 4, 2, 8.50), (11, 16, 1, 24.99), (11, 21, 1, 4.49),
-- Order 12 (Priya)
(12, 11, 2, 8.99), (12, 12, 1, 12.99), (12, 13, 1, 8.50);

-- ── PAYMENTS ──
INSERT INTO payments (order_id, payment_date, price, payment_status) VALUES
(1,  '2025-01-05 10:35:00', 45.47, 'completed'),
(2,  '2025-01-08 14:20:00', 31.98, 'completed'),
(3,  '2025-01-12 09:05:00', 67.50, 'completed'),
(4,  '2025-01-15 16:50:00', 24.99, 'pending'),
(5,  '2025-01-18 11:25:00', 89.48, 'completed'),
(6,  '2025-01-20 13:05:00', 52.97, 'completed'),
(7,  '2025-01-22 08:35:00', 18.48, 'pending'),
(8,  '2025-01-25 15:05:00', 37.98, 'completed'),
(9,  '2025-02-01 10:05:00', 76.47, 'completed'),
(10, '2025-02-03 12:35:00', 29.97, 'failed'),
(11, '2025-02-10 09:50:00', 55.96, 'completed'),
(12, '2025-02-14 16:05:00', 42.98, 'completed');

-- ── CART ──
INSERT INTO cart (customer_id, product_id, quantity, active) VALUES
(1, 5,  2, true),
(1, 23, 1, true),
(2, 8,  1, true),
(3, 14, 1, true),
(3, 20, 3, true),
(4, 26, 2, true);
