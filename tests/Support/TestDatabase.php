<?php

namespace Tests\Support;

use App\Core\Database;
use PDO;

final class TestDatabase
{
    public static function setup(): void
    {
        $conn = Database::connect();

        $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $conn->exec('PRAGMA foreign_keys = ON;');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY,
                    name TEXT NOT NULL,
                    email TEXT NOT NULL,
                    password TEXT NOT NULL,
                    created_at TEXT
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS restaurants (
                    id INTEGER PRIMARY KEY,
                    user_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    slug TEXT NOT NULL,
                    phone TEXT,
                    address TEXT,
                    address_number TEXT,
                    zip_code TEXT,
                    primary_color TEXT,
                    logo TEXT,
                    is_active INTEGER DEFAULT 1,
                    created_at TEXT
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS cash_registers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    status TEXT NOT NULL,
                    opening_balance REAL NOT NULL,
                    opened_at TEXT,
                    closed_at TEXT
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS cash_movements (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    cash_register_id INTEGER NOT NULL,
                    type TEXT NOT NULL,
                    amount REAL NOT NULL,
                    description TEXT NOT NULL,
                    order_id INTEGER,
                    created_at TEXT
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS orders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    client_id INTEGER,
                    table_id INTEGER,
                    total REAL NOT NULL,
                    status TEXT NOT NULL,
                    order_type TEXT NOT NULL,
                    payment_method TEXT,
                    observation TEXT,
                    change_for REAL,
                    source TEXT,
                    is_paid INTEGER DEFAULT 0,
                    created_at TEXT
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS order_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    product_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    quantity INTEGER NOT NULL,
                    price REAL NOT NULL,
                    extras TEXT,
                    observation TEXT
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS order_payments (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    order_id INTEGER NOT NULL,
                    method TEXT NOT NULL,
                    amount REAL NOT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    category_type TEXT,
                    sort_order INTEGER,
                    is_active INTEGER DEFAULT 1
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    category_id INTEGER,
                    name TEXT NOT NULL,
                    description TEXT,
                    stock INTEGER NOT NULL DEFAULT 0,
                    image TEXT,
                    price REAL DEFAULT 0,
                    icon TEXT,
                    icon_as_photo INTEGER DEFAULT 0,
                    item_number INTEGER,
                    is_active INTEGER DEFAULT 1,
                    display_order INTEGER DEFAULT 0,
                    is_featured INTEGER DEFAULT 0,
                    promotional_price REAL,
                    promo_expires_at TEXT,
                    is_on_promotion INTEGER DEFAULT 0
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS stock_movements (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    product_id INTEGER NOT NULL,
                    type TEXT NOT NULL,
                    quantity INTEGER NOT NULL,
                    stock_before INTEGER NOT NULL,
                    stock_after INTEGER NOT NULL,
                    source TEXT,
                    created_at TEXT
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS clients (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    type TEXT,
                    document TEXT,
                    phone TEXT,
                    zip_code TEXT,
                    address TEXT,
                    address_number TEXT,
                    neighborhood TEXT,
                    city TEXT,
                    credit_limit REAL,
                    due_day INTEGER
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS tables (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    number TEXT NOT NULL,
                    status TEXT NOT NULL,
                    current_order_id INTEGER
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS additional_groups (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    required INTEGER DEFAULT 0
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS additional_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    price REAL NOT NULL
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS additional_group_items (
                    group_id INTEGER NOT NULL,
                    item_id INTEGER NOT NULL
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS additional_categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    name TEXT NOT NULL
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS additional_group_categories (
                    group_id INTEGER NOT NULL,
                    category_id INTEGER NOT NULL
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS product_additional_relations (
                    product_id INTEGER NOT NULL,
                    group_id INTEGER NOT NULL
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS combos (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    name TEXT NOT NULL,
                    description TEXT,
                    price REAL NOT NULL,
                    display_order INTEGER DEFAULT 0,
                    is_active INTEGER DEFAULT 1
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS combo_items (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    combo_id INTEGER NOT NULL,
                    product_id INTEGER NOT NULL,
                    allow_additionals INTEGER DEFAULT 0
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS cardapio_config (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    whatsapp_enabled INTEGER DEFAULT 0,
                    whatsapp_number TEXT,
                    whatsapp_message TEXT,
                    is_open INTEGER DEFAULT 1,
                    opening_time TEXT,
                    closing_time TEXT,
                    closed_message TEXT,
                    delivery_enabled INTEGER DEFAULT 1,
                    delivery_fee REAL,
                    min_order_value REAL,
                    delivery_time_min INTEGER,
                    delivery_time_max INTEGER,
                    pickup_enabled INTEGER DEFAULT 1,
                    dine_in_enabled INTEGER DEFAULT 1,
                    accept_cash INTEGER DEFAULT 1,
                    accept_credit INTEGER DEFAULT 1,
                    accept_debit INTEGER DEFAULT 1,
                    accept_pix INTEGER DEFAULT 1,
                    pix_key TEXT,
                    pix_key_type TEXT
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS business_hours (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    day_of_week INTEGER NOT NULL,
                    is_open INTEGER DEFAULT 1,
                    open_time TEXT,
                    close_time TEXT
                );
            ');
            return;
        }

        if ($driver !== 'mysql') {
            return;
        }

        $conn->exec('
            CREATE TABLE IF NOT EXISTS users (
                id INT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS restaurants (
                id INT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NULL,
                address VARCHAR(255) NULL,
                address_number VARCHAR(50) NULL,
                zip_code VARCHAR(20) NULL,
                primary_color VARCHAR(50) NULL,
                logo VARCHAR(255) NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS cash_registers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                status VARCHAR(50) NOT NULL,
                opening_balance DECIMAL(10,2) NOT NULL,
                opened_at DATETIME NULL,
                closed_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS cash_movements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cash_register_id INT NOT NULL,
                type VARCHAR(50) NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                description VARCHAR(255) NOT NULL,
                order_id INT NULL,
                created_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                client_id INT NULL,
                table_id INT NULL,
                total DECIMAL(10,2) NOT NULL,
                status VARCHAR(50) NOT NULL,
                order_type VARCHAR(50) NOT NULL,
                payment_method VARCHAR(50) NULL,
                observation TEXT NULL,
                change_for DECIMAL(10,2) NULL,
                source VARCHAR(50) NULL,
                is_paid TINYINT(1) DEFAULT 0,
                created_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                quantity INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                extras TEXT NULL,
                observation TEXT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS order_payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                method VARCHAR(50) NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                category_type VARCHAR(50) NULL,
                sort_order INT NULL,
                is_active TINYINT(1) DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                category_id INT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT NULL,
                stock INT NOT NULL DEFAULT 0,
                image VARCHAR(255) NULL,
                price DECIMAL(10,2) DEFAULT 0,
                icon VARCHAR(255) NULL,
                icon_as_photo TINYINT(1) DEFAULT 0,
                item_number INT NULL,
                is_active TINYINT(1) DEFAULT 1,
                display_order INT DEFAULT 0,
                is_featured TINYINT(1) DEFAULT 0,
                promotional_price DECIMAL(10,2) NULL,
                promo_expires_at DATETIME NULL,
                is_on_promotion TINYINT(1) DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS stock_movements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                product_id INT NOT NULL,
                type VARCHAR(50) NOT NULL,
                quantity INT NOT NULL,
                stock_before INT NOT NULL,
                stock_after INT NOT NULL,
                source VARCHAR(255) NULL,
                created_at DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS clients (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                type VARCHAR(50) NULL,
                document VARCHAR(50) NULL,
                phone VARCHAR(50) NULL,
                zip_code VARCHAR(20) NULL,
                address VARCHAR(255) NULL,
                address_number VARCHAR(50) NULL,
                neighborhood VARCHAR(100) NULL,
                city VARCHAR(100) NULL,
                credit_limit DECIMAL(10,2) NULL,
                due_day INT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS tables (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                number VARCHAR(50) NOT NULL,
                status VARCHAR(50) NOT NULL,
                current_order_id INT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS additional_groups (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                required TINYINT(1) DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS additional_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                price DECIMAL(10,2) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS additional_group_items (
                group_id INT NOT NULL,
                item_id INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS additional_categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                name VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS additional_group_categories (
                group_id INT NOT NULL,
                category_id INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS product_additional_relations (
                product_id INT NOT NULL,
                group_id INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS combos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT NULL,
                price DECIMAL(10,2) NOT NULL,
                display_order INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS combo_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                combo_id INT NOT NULL,
                product_id INT NOT NULL,
                allow_additionals TINYINT(1) DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS cardapio_config (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                whatsapp_enabled TINYINT(1) DEFAULT 0,
                whatsapp_number VARCHAR(50) NULL,
                whatsapp_message TEXT NULL,
                is_open TINYINT(1) DEFAULT 1,
                opening_time VARCHAR(10) NULL,
                closing_time VARCHAR(10) NULL,
                closed_message TEXT NULL,
                delivery_enabled TINYINT(1) DEFAULT 1,
                delivery_fee DECIMAL(10,2) NULL,
                min_order_value DECIMAL(10,2) NULL,
                delivery_time_min INT NULL,
                delivery_time_max INT NULL,
                pickup_enabled TINYINT(1) DEFAULT 1,
                dine_in_enabled TINYINT(1) DEFAULT 1,
                accept_cash TINYINT(1) DEFAULT 1,
                accept_credit TINYINT(1) DEFAULT 1,
                accept_debit TINYINT(1) DEFAULT 1,
                accept_pix TINYINT(1) DEFAULT 1,
                pix_key VARCHAR(255) NULL,
                pix_key_type VARCHAR(50) NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS business_hours (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                day_of_week INT NOT NULL,
                is_open TINYINT(1) DEFAULT 1,
                open_time VARCHAR(10) NULL,
                close_time VARCHAR(10) NULL,
                UNIQUE KEY restaurant_day (restaurant_id, day_of_week)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');
    }

    public static function truncateAll(): void
    {
        $conn = Database::connect();

        $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);

        $tables = [
            'additional_group_items',
            'additional_group_categories',
            'product_additional_relations',
            'combo_items',
            'combos',
            'business_hours',
            'cardapio_config',
            'additional_items',
            'additional_groups',
            'additional_categories',
            'cash_movements',
            'order_payments',
            'order_items',
            'orders',
            'cash_registers',
            'stock_movements',
            'products',
            'categories',
            'tables',
            'clients',
            'restaurants',
            'users',
        ];

        if ($driver === 'mysql') {
            $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
            foreach ($tables as $table) {
                $conn->exec("TRUNCATE TABLE {$table}");
            }
            $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
            return;
        }

        if ($driver !== 'sqlite') {
            return;
        }

        foreach ($tables as $table) {
            $conn->exec("DELETE FROM {$table}");
        }
    }
}
