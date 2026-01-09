# Cardápio SaaS

A robust, multi-tenant SaaS application for restaurant management and digital menus.

## 🚀 Features

- **Admin Panel**: Complete management of products, categories, tables, and stock.
- **PDV (Point of Sale)**: Fast and efficient cashier interface.
- **Public Menu**: Responsive web interface for customers to place orders (Delivery/Pickup/Local).
- **Security**: CSRF protection, Input Sanitization, Rate Limiting, and Secure Sessions.
- **Performance**: Caching enabled for menus and optimized database indexes.

## 🛠️ Technology Stack

- **Backend**: PHP 8.x (Vanilla + DI Container + Custom Router)
- **Frontend**: HTML5, CSS3, Vanilla JS (Modular Architecture)
- **Database**: MySQL 8.0
- **Architecture**: MVC + DDD Lite (Services, Repositories, DTOs)

## 📦 Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your/repo.git
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Environment**
   - Copy `.env.example` to `.env`
   - Set database credentials and `BASE_URL`.

4. **Setup Database**
   - Import `database/schema.sql` (if available)
   - Run migrations if any.
   - Run `php apply_indexes.php` to optimize performance.

5. **Permissions**
   - Ensure `cache/` and `logs/` directories are writable.

## 🔒 Security

This application implements several security layers:
- **CSRF**: All POST requests must include `X-CSRF-TOKEN` header or `csrf_token` field.
- **Sanitization**: Global middleware cleans all inputs.
- **Rate Limiting**: Limits API abuse (60 req/min per IP).
- **Sessions**: Secure, HttpOnly, and SameSite cookies enforced.

## 📚 API Documentation

See `docs/api/swagger.yaml` for the Public API specification.

## 🏗️ Architecture Overview

- **App/Core**: Core framework components (Router, Container, Database).
- **App/Controllers**: Request handlers.
- **App/Services**: Business logic.
- **App/Repositories**: Data access layer.
- **App/Middleware**: Request filtering pipeline.

---
*Built by Google Deepmind Agent*
