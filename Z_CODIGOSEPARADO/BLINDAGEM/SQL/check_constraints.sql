-- ============================================
-- BLINDAGEM: CHECK CONSTRAINTS
-- Executar no banco cardapio_saas
-- ============================================

-- 1. ORDERS - Total não pode ser negativo
ALTER TABLE orders 
ADD CONSTRAINT chk_orders_total CHECK (total >= 0);

-- 2. PRODUCTS - Preço não pode ser negativo
ALTER TABLE products 
ADD CONSTRAINT chk_products_price CHECK (price >= 0);

-- 3. PRODUCTS - Estoque não pode ser negativo
ALTER TABLE products 
ADD CONSTRAINT chk_products_stock CHECK (stock >= 0);

-- 4. ORDER_PAYMENTS - Valor do pagamento não pode ser negativo
ALTER TABLE order_payments 
ADD CONSTRAINT chk_payments_amount CHECK (amount >= 0);

-- 5. CASH_MOVEMENTS - Valor do movimento não pode ser negativo
ALTER TABLE cash_movements 
ADD CONSTRAINT chk_movements_amount CHECK (amount >= 0);

-- 6. ORDER_ITEMS - Quantidade deve ser positiva
ALTER TABLE order_items 
ADD CONSTRAINT chk_items_quantity CHECK (quantity > 0);

-- 7. ORDER_ITEMS - Preço não pode ser negativo
ALTER TABLE order_items 
ADD CONSTRAINT chk_items_price CHECK (price >= 0);

-- ============================================
-- VERIFICAÇÃO (executar após o script acima)
-- Estes comandos DEVEM FALHAR se as constraints funcionaram
-- ============================================

-- Teste 1: Deve falhar (preço negativo)
-- INSERT INTO products (restaurant_id, category_id, name, price, stock) VALUES (1, 1, 'Teste', -10.00, 5);

-- Teste 2: Deve falhar (estoque negativo)
-- INSERT INTO products (restaurant_id, category_id, name, price, stock) VALUES (1, 1, 'Teste', 10.00, -5);

-- Teste 3: Deve falhar (total negativo)
-- UPDATE orders SET total = -100 WHERE id = 1;
