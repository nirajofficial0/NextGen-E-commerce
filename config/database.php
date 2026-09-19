<?php
/**
 * Database Connection Configuration
 * Supports MySQL (Default XAMPP) with seamless automatic SQLite fallback.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'nextgen_ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private static $pdo = null;
    private static $driver = 'mysql';

    public static function getConnection() {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        // Try MySQL first
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::$driver = 'mysql';
            return self::$pdo;
        } catch (PDOException $e) {
            // MySQL unavailable - Fallback to SQLite automatically
            return self::initSQLiteFallback();
        }
    }

    private static function initSQLiteFallback() {
        $dbPath = __DIR__ . '/../database/ecommerce.db';
        $isNew = !file_exists($dbPath);

        try {
            self::$pdo = new PDO("sqlite:" . $dbPath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$driver = 'sqlite';

            // Auto-provision SQLite schema if brand new database
            if ($isNew || filesize($dbPath) == 0) {
                self::provisionSQLiteDatabase(self::$pdo);
            }

            return self::$pdo;
        } catch (PDOException $ex) {
            die("Database Connection Error: " . $ex->getMessage());
        }
    }

    public static function getDriver() {
        return self::$driver;
    }

    private static function provisionSQLiteDatabase($pdo) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                full_name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                phone TEXT,
                address TEXT,
                city TEXT,
                state TEXT,
                pincode TEXT,
                role TEXT DEFAULT 'customer',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                description TEXT,
                icon_class TEXT DEFAULT 'fa-box',
                image_url TEXT
            );

            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                short_description TEXT,
                description TEXT,
                price REAL NOT NULL,
                original_price REAL,
                stock_quantity INTEGER DEFAULT 10,
                image_url TEXT,
                rating REAL DEFAULT 4.5,
                total_reviews INTEGER DEFAULT 12,
                is_featured INTEGER DEFAULT 0,
                is_trending INTEGER DEFAULT 0,
                tags TEXT,
                specs_json TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                total_amount REAL NOT NULL,
                discount_amount REAL DEFAULT 0.00,
                final_amount REAL NOT NULL,
                payment_method TEXT DEFAULT 'UPI',
                payment_status TEXT DEFAULT 'Paid',
                order_status TEXT DEFAULT 'Processing',
                shipping_name TEXT NOT NULL,
                shipping_phone TEXT NOT NULL,
                shipping_address TEXT NOT NULL,
                city TEXT NOT NULL,
                state TEXT NOT NULL,
                pincode TEXT NOT NULL,
                tracking_number TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                product_name TEXT NOT NULL,
                price REAL NOT NULL,
                quantity INTEGER NOT NULL,
                subtotal REAL NOT NULL
            );

            CREATE TABLE IF NOT EXISTS wishlist (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(user_id, product_id)
            );

            CREATE TABLE IF NOT EXISTS cart (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                session_id TEXT,
                product_id INTEGER NOT NULL,
                quantity INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                user_name TEXT NOT NULL,
                rating INTEGER NOT NULL,
                comment TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS coupons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                discount_percentage INTEGER NOT NULL,
                min_order_amount REAL DEFAULT 0.00,
                max_discount REAL DEFAULT 5000.00,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS ai_requirements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER,
                raw_prompt TEXT NOT NULL,
                parsed_category TEXT,
                parsed_budget REAL,
                parsed_purpose TEXT,
                parsed_features TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Seed users (Passwords: Admin@123, User@123)
        $adminPass = password_hash('Admin@123', PASSWORD_DEFAULT);
        $userPass = password_hash('User@123', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (id, full_name, email, password, phone, address, city, state, pincode, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([1, 'System Administrator', 'admin@nextgen.com', $adminPass, '9876543210', '123 Tech Park', 'Bangalore', 'Karnataka', '560001', 'admin']);
        $stmt->execute([2, 'Alex Johnson', 'alex@example.com', $userPass, '9876543211', '45 Innovation Way', 'Mumbai', 'Maharashtra', '400001', 'customer']);

        // Seed categories
        $pdo->exec("INSERT INTO categories (id, name, slug, description, icon_class, image_url) VALUES
            (1, 'Laptops & Computers', 'laptops-computers', 'High performance laptops for gaming, coding, and productivity.', 'fa-laptop', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=600&q=80'),
            (2, 'Smartphones & Mobile', 'smartphones-mobile', 'Latest flagship smartphones, foldable devices, and accessories.', 'fa-mobile-screen-button', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=600&q=80'),
            (3, 'Audio & Headphones', 'audio-headphones', 'Noise cancelling headphones, wireless earbuds, and spatial sound.', 'fa-headphones', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80'),
            (4, 'Gaming & Consoles', 'gaming-consoles', 'Next-gen gaming gear, mechanical keyboards, monitors, and controllers.', 'fa-gamepad', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?auto=format&fit=crop&w=600&q=80'),
            (5, 'Smart Wearables', 'smart-wearables', 'Smartwatches, fitness bands, and health tracking wearables.', 'fa-clock', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80'),
            (6, 'Cameras & Accessories', 'cameras-accessories', 'Mirrorless cameras, studio lighting, drones, and vlogging equipment.', 'fa-camera', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80')
        ");

        // Seed products
        $pdo->exec("INSERT INTO products (id, category_id, name, slug, short_description, description, price, original_price, stock_quantity, image_url, rating, total_reviews, is_featured, is_trending, tags, specs_json) VALUES
            (1, 1, 'ZenBook Pro 16 AI Ultra', 'zenbook-pro-16-ai-ultra', 'Ultra-thin AI laptop with 16GB RAM, OLED 120Hz display & 14hr battery life.', 'Engineered for developers, creators, and power users. Powered by Intel Core Ultra 7 processor, 16GB LPDDR5X RAM, 1TB NVMe SSD, and dedicated NPU for fast local AI processing.', 58999.00, 69999.00, 15, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80', 4.90, 48, 1, 1, 'laptop,coding,college,16gb,battery,oled,intel,thin', '{\"ram\":\"16GB\",\"storage\":\"1TB SSD\",\"battery\":\"14 Hours\",\"cpu\":\"Intel Core Ultra 7\",\"weight\":\"1.4 kg\",\"screen\":\"16-inch OLED\"}'),
            (2, 1, 'Legion Pro Gaming 5i', 'legion-pro-gaming-5i', 'High performance gaming laptop with RTX 4060, 16GB DDR5, 165Hz Display.', 'Unleash full gaming potential. Features 16GB DDR5 RAM, 512GB SSD, NVIDIA GeForce RTX 4060 8GB GPU, Coldfront 5.0 cooling, and RGB backlit keyboard.', 64999.00, 74999.00, 8, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?auto=format&fit=crop&w=800&q=80', 4.80, 36, 1, 1, 'laptop,gaming,coding,16gb,rtx4060,gpu,performance', '{\"ram\":\"16GB\",\"storage\":\"512GB SSD\",\"battery\":\"6 Hours\",\"cpu\":\"Intel Core i7-13700HX\",\"weight\":\"2.3 kg\",\"gpu\":\"NVIDIA RTX 4060\"}'),
            (3, 1, 'Swift Go 14 Student Edition', 'swift-go-14-student-edition', 'Budget student laptop with 16GB RAM, Ryzen 5, and all-day battery.', 'The ultimate companion for college and daily productivity. Features AMD Ryzen 5 7530U, 16GB RAM, 512GB SSD, anti-glare FHD screen, and lightweight aluminum chassis.', 44999.00, 52999.00, 25, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?auto=format&fit=crop&w=800&q=80', 4.60, 29, 0, 1, 'laptop,student,college,coding,16gb,budget,lightweight,battery', '{\"ram\":\"16GB\",\"storage\":\"512GB SSD\",\"battery\":\"11 Hours\",\"cpu\":\"AMD Ryzen 5 7530U\",\"weight\":\"1.25 kg\",\"screen\":\"14-inch FHD\"}'),
            (4, 2, 'Galaxy S25 Ultra 5G', 'galaxy-s25-ultra-5g', 'Flagship AI smartphone with 200MP camera, Snapdragon 8 Elite & S-Pen.', 'Experience next generation mobile technology with Galaxy AI, titanium frame, 12GB RAM, 256GB storage, and quad camera setup.', 79999.00, 89999.00, 12, 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?auto=format&fit=crop&w=800&q=80', 4.90, 64, 1, 1, 'phone,smartphone,5g,camera,flagship,galaxy,spen,ai', '{\"ram\":\"12GB\",\"storage\":\"256GB\",\"battery\":\"5000 mAh\",\"camera\":\"200MP Quad\",\"screen\":\"6.8-inch AMOLED 120Hz\"}'),
            (5, 2, 'Pixel 9 Pro 5G', 'pixel-9-pro-5g', 'Pure Android AI phone with Tensor G4, pro camera controls & 16GB RAM.', 'Advanced AI studio in your pocket. Features Google Tensor G4, 16GB RAM, 128GB storage, super res zoom, and 7 years of OS updates.', 68999.00, 75999.00, 10, 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=800&q=80', 4.70, 22, 1, 0, 'phone,smartphone,5g,camera,android,pixel,ai,16gb', '{\"ram\":\"16GB\",\"storage\":\"128GB\",\"battery\":\"4700 mAh\",\"camera\":\"50MP Triple Pro\",\"screen\":\"6.3-inch Super Actua\"}'),
            (6, 3, 'SonicPro Wireless ANC Headphones', 'sonicpro-wireless-anc-headphones', 'Hybrid Active Noise Cancelling headphones with 40-hour battery and HD mic.', 'Block out background noise while studying or working. Features 40mm titanium drivers, multipoint Bluetooth 5.4, ultra-clear microphone for calls, and quick charge.', 4499.00, 6999.00, 40, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80', 4.80, 85, 1, 1, 'headphones,audio,anc,wireless,mic,gaming,music,budget,battery', '{\"battery\":\"40 Hours\",\"anc\":\"Hybrid ANC 42dB\",\"mic\":\"AI Dual Mic\",\"driver\":\"40mm Titanium\",\"weight\":\"240g\"}'),
            (7, 3, 'AirSound Pro True Wireless Earbuds', 'airsound-pro-tws-earbuds', 'Low-latency TWS earbuds with ANC, spatial audio, and IPX5 water resistance.', 'Compact audiophile sound. Includes spatial audio, 8ms ultra low latency mode for mobile gaming, touch controls, and 30-hour total playback with USB-C case.', 2999.00, 4999.00, 60, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=800&q=80', 4.60, 53, 0, 1, 'earbuds,audio,tws,gaming,wireless,anc,budget,compact', '{\"battery\":\"30 Hours Total\",\"latency\":\"8ms\",\"waterproof\":\"IPX5\",\"anc\":\"30dB ANC\"}'),
            (8, 4, 'Apex Precision RGB Mechanical Keyboard', 'apex-precision-rgb-mechanical-keyboard', 'Wireless hot-swappable mechanical keyboard with custom linear switches.', 'Designed for fast typing and low-latency gaming. PBT keycaps, per-key RGB lighting, tri-mode connection (Bluetooth/2.4G/USB-C), and sound dampening foam.', 3999.00, 5499.00, 30, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=800&q=80', 4.85, 41, 1, 1, 'keyboard,gaming,mechanical,coding,rgb,wireless,accessories', '{\"switches\":\"Custom Hot-Swap Linear\",\"layout\":\"75% Compact\",\"battery\":\"4000 mAh\",\"connection\":\"Bluetooth/2.4G/USB-C\"}'),
            (9, 4, 'Pulsefire Ultra Wireless Gaming Mouse', 'pulsefire-ultra-wireless-gaming-mouse', 'Ultra-lightweight 49g wireless mouse with 26K DPI sensor.', 'Maximum precision for esports and productivity. 26,000 DPI optical sensor, 80 million click optical switches, PTFE feet, and 90-hour battery life.', 2499.00, 3999.00, 35, 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=800&q=80', 4.75, 38, 0, 1, 'mouse,gaming,wireless,coding,accessories,lightweight', '{\"weight\":\"49g\",\"sensor\":\"26K DPI Optical\",\"battery\":\"90 Hours\",\"switches\":\"Optical 80M Clicks\"}'),
            (10, 5, 'FitPulse Watch 3 Pro', 'fitpulse-watch-3-pro', 'AMOLED smartwatch with GPS, SpO2, Heart Rate, and 12-day battery.', 'Track fitness, sleep, stress, and receive Bluetooth phone calls. IP68 water resistance, customizable watch faces, and stainless steel bezel.', 3499.00, 5999.00, 50, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80', 4.70, 77, 1, 1, 'smartwatch,watch,wearable,fitness,gps,battery,budget', '{\"screen\":\"1.43-inch AMOLED\",\"battery\":\"12 Days\",\"sensors\":\"Heart Rate, SpO2, Sleep, GPS\",\"waterproof\":\"IP68\"}')
        ");

        // Seed sample order
        $pdo->exec("INSERT INTO orders (id, user_id, total_amount, discount_amount, final_amount, payment_method, payment_status, order_status, shipping_name, shipping_phone, shipping_address, city, state, pincode, tracking_number) VALUES
            (1001, 2, 58999.00, 0.00, 58999.00, 'UPI / QR', 'Paid', 'Shipped', 'Alex Johnson', '9876543211', '45 Innovation Way', 'Mumbai', 'Maharashtra', '400001', 'TRACK-NEXTGEN-8942')
        ");
        $pdo->exec("INSERT INTO order_items (id, order_id, product_id, product_name, price, quantity, subtotal) VALUES
            (1, 1001, 1, 'ZenBook Pro 16 AI Ultra', 58999.00, 1, 58999.00)
        ");

        // Seed coupons
        $pdo->exec("INSERT INTO coupons (code, discount_percentage, min_order_amount, max_discount) VALUES
            ('NEXTGEN10', 10, 1000.00, 2000.00),
            ('AI500', 15, 3000.00, 3000.00),
            ('WELCOME20', 20, 5000.00, 5000.00)
        ");

        // Seed reviews
        $pdo->exec("INSERT INTO reviews (product_id, user_id, user_name, rating, comment) VALUES
            (1, 2, 'Alex Johnson', 5, 'Super fast laptop! Handles heavy VS Code sessions and local AI models effortlessly. Battery lasts 12+ hours easily.'),
            (6, 2, 'Alex Johnson', 5, 'Noise cancelling is top notch for studying. The microphone clear sound surprised me.')
        ");
    }
}
