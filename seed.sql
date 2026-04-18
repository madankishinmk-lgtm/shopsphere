-- Database Schema and Seed Data for E-Commerce Shop
-- ==========================================
-- DROP EXISTING TABLES (clean re-seed)
-- ==========================================
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- ==========================================
-- TABLE DEFINITIONS
-- ==========================================

-- 1. users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- 3. products  (extended with is_new, is_sale, original_price)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255),
    is_new TINYINT(1) NOT NULL DEFAULT 0,
    is_sale TINYINT(1) NOT NULL DEFAULT 0,
    original_price DECIMAL(10,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- 4. carts
CREATE TABLE carts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. cart_items
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- 6. orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 7. order_items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ==========================================
-- SEED DATA
-- ==========================================

-- Users (password: admin123 | test123  — bcrypt hashes)
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin User',    'admin@shop.com',    '$2y$10$I9ymkFSiKwmNWqKy0p7TJOqoy2SnigcowP0Ew0uZU3FXAOZajthiK', 'admin'),
('Customer User', 'customer@shop.com', '$2y$10$F2a72g.tSfM2oEgBG7t4KOO74IVeUc.Pj6/oEtPRBcxCC0i1ceVgC', 'customer');

-- Categories
INSERT INTO categories (name, slug, description) VALUES
('Electronics', 'electronics', 'Gadgets, phones, and computers.'),
('Clothing',    'clothing',    'Men and women apparel.'),
('Books',       'books',       'Books across various genres.'),
('Home & Living','home-living','Furniture and home decor.'),
('Sports',      'sports',      'Sporting goods and outdoor equipment.');

-- ==========================================
-- PRODUCTS
-- col order: (category_id, name, slug, description, price, stock, image_url, is_new, is_sale, original_price)
-- ==========================================

-- ELECTRONICS (id=1)
INSERT INTO products (category_id, name, slug, description, price, stock, image_url, is_new, is_sale, original_price) VALUES
(1,'Smartphone X','smartphone-x','The latest high-end smartphone with a 120Hz display, 5G, and triple camera.',999.00,50,'placeholder.jpg',0,0,NULL),
(1,'Noise Cancelling Headphones','noise-cancelling-headphones','Premium over-ear wireless headphones with 30-hr battery and industry-leading ANC.',299.99,30,'placeholder.jpg',0,0,NULL),
(1,'4K Smart TV','4k-smart-tv','65-inch 4K UHD Smart TV with HDR, Dolby Vision, and built-in streaming apps.',799.00,15,'placeholder.jpg',0,0,NULL),
(1,'Gaming Laptop','gaming-laptop','15-inch laptop with RTX 4070 GPU, 32GB RAM, and 144Hz display for pro gaming.',1299.00,10,'placeholder.jpg',0,0,NULL),
(1,'Wireless Mouse','wireless-mouse','Ergonomic wireless mouse with 2.4GHz response, 6 buttons, and 12-month battery.',49.99,100,'placeholder.jpg',0,1,69.99),
-- New Arrivals - Electronics
(1,'Mechanical Keyboard','mechanical-keyboard','Compact TKL mechanical keyboard with RGB backlight and tactile blue switches.',89.99,40,'placeholder.jpg',1,0,NULL),
(1,'Wireless Earbuds','wireless-earbuds','True wireless earbuds with 8-hour playback, IPX4 water resistance, fast charging case.',79.99,60,'placeholder.jpg',1,0,NULL),
(1,'Smartwatch Pro','smartwatch-pro','AMOLED smartwatch with GPS, heart rate, SpO2 sensor, and 14-day battery life.',249.99,25,'placeholder.jpg',1,0,NULL),
(1,'Digital Camera DSLR','digital-camera-dslr','24MP DSLR with 18-55mm kit lens, 4K video, and built-in Wi-Fi sharing.',699.00,8,'placeholder.jpg',1,0,NULL),
-- Sale - Electronics
(1,'Bluetooth Speaker','bluetooth-speaker','360° portable speaker with 24-hr playtime, IPX7 waterproof, and deep bass.',39.99,35,'placeholder.jpg',0,1,79.99);

-- CLOTHING (id=2)
INSERT INTO products (category_id, name, slug, description, price, stock, image_url, is_new, is_sale, original_price) VALUES
(2,'Classic T-Shirt','classic-t-shirt','Comfortable 100% cotton t-shirt in 12 colors. The ultimate wardrobe essential.',19.99,200,'placeholder.jpg',0,0,NULL),
(2,'Denim Jeans','denim-jeans','High-quality slim-fit stretch denim jeans perfect for everyday wear.',59.99,75,'placeholder.jpg',0,0,NULL),
(2,'Running Shoes','running-shoes','Lightweight breathable mesh running shoes with cushioned midsole and grip outsole.',89.99,40,'placeholder.jpg',0,0,NULL),
(2,'Winter Jacket','winter-jacket','Warm windproof and waterproof parka with removable fleece lining for cold weather.',149.00,20,'placeholder.jpg',0,0,NULL),
-- New Arrivals - Clothing
(2,'Graphic Hoodie','graphic-hoodie','Soft fleece hoodie with modern screen print. Relaxed oversized fit with front pocket.',49.99,80,'placeholder.jpg',1,0,NULL),
(2,'Summer Dress','summer-dress','Flowy linen-blend summer dress with floral pattern, adjustable waist, pockets.',39.99,55,'placeholder.jpg',1,0,NULL),
(2,'Baseball Cap','baseball-cap','Adjustable structured 6-panel cap with embroidered logo and breathable mesh back.',24.99,120,'placeholder.jpg',1,0,NULL),
-- Sale - Clothing
(2,'Sports Sneakers','sports-sneakers','Versatile cross-training sneakers with memory foam insole. Was $120 — save 42%!',69.99,30,'placeholder.jpg',0,1,119.99),
(2,'Leather Wallet','leather-wallet','Slim genuine leather bifold wallet with RFID blocking and 6 card slots.',24.99,45,'placeholder.jpg',0,1,44.99);

-- BOOKS (id=3)
INSERT INTO products (category_id, name, slug, description, price, stock, image_url, is_new, is_sale, original_price) VALUES
(3,'The Great Gatsby','the-great-gatsby','A timeless American literature classic by F. Scott Fitzgerald set in the Roaring Twenties.',14.99,150,'placeholder.jpg',0,0,NULL),
(3,'1984','1984','George Orwell''s chilling dystopian masterpiece about surveillance and totalitarianism.',12.99,120,'placeholder.jpg',0,0,NULL),
(3,'Sapiens','sapiens','A bold and thought-provoking brief history of humankind by Yuval Noah Harari.',24.99,80,'placeholder.jpg',0,0,NULL),
-- New Arrivals - Books
(3,'Atomic Habits','atomic-habits','James Clear''s #1 bestseller on tiny changes that lead to remarkable long-term results.',18.99,95,'placeholder.jpg',1,0,NULL),
(3,'The Midnight Library','the-midnight-library','A novel about all the choices that go into a life well lived — enchanting and heartbreaking.',15.99,70,'placeholder.jpg',1,0,NULL),
-- Sale - Books
(3,'Dune','dune','Frank Herbert''s epic sci-fi saga about desert planet Arrakis and hero Paul Atreides.',9.99,200,'placeholder.jpg',0,1,19.99);

-- HOME & LIVING (id=4)
INSERT INTO products (category_id, name, slug, description, price, stock, image_url, is_new, is_sale, original_price) VALUES
(4,'Sofa Set','sofa-set','Modern 3-seater sofa with plush cushions and stain-resistant fabric upholstery.',499.00,5,'placeholder.jpg',0,0,NULL),
(4,'Coffee Table','coffee-table','Scandinavian-style solid wood coffee table with tempered glass top and lower shelf.',129.00,15,'placeholder.jpg',0,0,NULL),
(4,'Table Lamp','table-lamp','Minimalist brushed brass table lamp with dimmable warm white LED bulb included.',39.99,50,'placeholder.jpg',0,0,NULL),
(4,'Ceramic Vase','ceramic-vase','Hand-crafted decorative ceramic vase, perfect for dried flowers and boho interiors.',29.99,35,'placeholder.jpg',0,0,NULL),
-- New Arrivals - Home
(4,'Aromatherapy Diffuser','aromatherapy-diffuser','Ultrasonic essential oil diffuser with 7-color LED mood lighting and 8-hr auto shut-off.',34.99,40,'placeholder.jpg',1,0,NULL),
(4,'Throw Pillow Set','throw-pillow-set','Set of 4 decorative throw pillows with removable machine-wash covers in neutral tones.',44.99,55,'placeholder.jpg',1,0,NULL),
-- Sale - Home
(4,'Electric Kettle','electric-kettle','Gooseneck electric pour-over kettle with 6 temp settings, keep-warm, and stainless interior.',29.99,25,'placeholder.jpg',0,1,59.99);

-- SPORTS (id=5)
INSERT INTO products (category_id, name, slug, description, price, stock, image_url, is_new, is_sale, original_price) VALUES
(5,'Yoga Mat','yoga-mat','Non-slip 6mm thick yoga mat with alignment lines, carrying strap, and sweat-absorbing surface.',24.99,60,'placeholder.jpg',0,0,NULL),
(5,'Dumbbell Set','dumbbell-set','Adjustable 5-25kg dumbbell set with chrome finish and anti-roll rubber end caps.',89.99,25,'placeholder.jpg',0,0,NULL),
(5,'Tennis Racket','tennis-racket','Professional-grade graphite tennis racket with pre-strung 16x19 pattern.',149.00,12,'placeholder.jpg',0,0,NULL),
(5,'Basketball','basketball','Official size 7 indoor/outdoor composite leather basketball for all skill levels.',29.99,45,'placeholder.jpg',0,0,NULL),
-- New Arrivals - Sports
(5,'Resistance Bands Set','resistance-bands-set','Set of 5 resistance bands in varying strengths for full-body training anywhere, anytime.',19.99,90,'placeholder.jpg',1,0,NULL),
(5,'Cycling Bicycle','cycling-bicycle','Lightweight 21-speed aluminum road bike with front suspension fork and hydraulic disc brakes.',389.00,6,'placeholder.jpg',1,0,NULL),
-- Sale - Sports
(5,'Swimming Goggles','swimming-goggles','Anti-fog UV-protection wide-lens swimming goggles with adjustable silicone strap.',12.99,80,'placeholder.jpg',0,1,24.99),
(5,'Jump Rope','jump-rope','Speed jump rope with precision ball-bearing handles and adjustable PVC cable — save 40%!',8.99,100,'placeholder.jpg',0,1,14.99);

-- ==========================================
-- CARTS & ORDERS
-- ==========================================
INSERT INTO carts (user_id) VALUES (2);

INSERT INTO orders (user_id, total_amount, status, created_at) VALUES
(2, 1018.99, 'completed', '2023-10-01 10:00:00'),
(2,   74.98, 'completed', '2023-10-15 14:30:00'),
(2,   24.99, 'shipped',   '2023-11-01 09:15:00');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 999.00),(1, 11, 1, 19.99),
(2, 5, 1,  49.99),(2, 35, 1, 24.99),
(3, 23, 1, 24.99);
