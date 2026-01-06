-- ============================================
-- ADICIONAR COLUNAS DE ÍCONE E NUMERAÇÃO
-- Executar no banco de dados
-- ============================================

-- 1. Adiciona colunas novas
ALTER TABLE products 
  ADD COLUMN icon VARCHAR(50) DEFAULT 'package' AFTER image,
  ADD COLUMN icon_as_photo TINYINT(1) DEFAULT 0 AFTER icon,
  ADD COLUMN item_number INT UNSIGNED AFTER icon_as_photo;

-- 2. Gera item_number para produtos existentes (por restaurante, ordem de criação)
SET @row_number = 0;
SET @current_restaurant = 0;

UPDATE products p
JOIN (
    SELECT id, restaurant_id,
           @row_number := IF(@current_restaurant = restaurant_id, @row_number + 1, 1) AS new_number,
           @current_restaurant := restaurant_id
    FROM products
    ORDER BY restaurant_id, id
) tmp ON p.id = tmp.id
SET p.item_number = tmp.new_number;

-- 3. Índice para busca rápida
CREATE INDEX idx_products_item_number ON products(restaurant_id, item_number);
