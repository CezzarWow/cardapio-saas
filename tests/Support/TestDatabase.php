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
                    name TEXT NOT NULL
                );
            ');

            $conn->exec('
                CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    restaurant_id INTEGER NOT NULL,
                    category_id INTEGER,
                    name TEXT NOT NULL,
                    stock INTEGER NOT NULL DEFAULT 0,
                    image TEXT,
                    price REAL DEFAULT 0
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
                name VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $conn->exec('
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                restaurant_id INT NOT NULL,
                category_id INT NULL,
                name VARCHAR(255) NOT NULL,
                stock INT NOT NULL DEFAULT 0,
                image VARCHAR(255) NULL,
                price DECIMAL(10,2) DEFAULT 0
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
    }

    public static function truncateAll(): void
    {
        $conn = Database::connect();

        $driver = $conn->getAttribute(PDO::ATTR_DRIVER_NAME);

        $tables = [
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
