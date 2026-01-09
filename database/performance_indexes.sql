-- PERFORMANCE INDEXES MIGRATION
-- Adds indexes to high-traffic columns to optimize standard SaaS queries (Isolation, Filtering, Sorting)

-- 1. ORDERS TABLE
-- Used in Kanban, History, Reports
CREATE INDEX idx_orders_restaurant_id ON orders(restaurant_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created_at ON orders(created_at);
CREATE INDEX idx_orders_client_id ON orders(client_id);
-- Composite for Kanban filtering (Restaurant + Status)
CREATE INDEX idx_orders_rest_status ON orders(restaurant_id, status);

-- 2. PRODUCTS TABLE
-- Used in Menu, POS, Search
CREATE INDEX idx_products_restaurant_id ON products(restaurant_id);
CREATE INDEX idx_products_category_id ON products(category_id);
CREATE INDEX idx_products_is_active ON products(is_active);

-- 3. CLIENTS TABLE
-- Used in Autocomplete, Search, Validation
CREATE INDEX idx_clients_restaurant_id ON clients(restaurant_id);
CREATE INDEX idx_clients_phone ON clients(phone);
CREATE INDEX idx_clients_name ON clients(name);

-- 4. STOCK MOVEMENTS
-- Used in Reports, History
CREATE INDEX idx_stock_movements_product_id ON stock_movements(product_id);
CREATE INDEX idx_stock_movements_created_at ON stock_movements(created_at);

-- 5. TABLES (Mesas)
-- Used in POS map
CREATE INDEX idx_tables_restaurant_id ON tables(restaurant_id);
CREATE INDEX idx_tables_status ON tables(status);

-- 6. ORDER ITEMS
-- Used in kitchen display, reports
CREATE INDEX idx_order_items_order_id ON order_items(order_id);
CREATE INDEX idx_order_items_product_id ON order_items(product_id);
