-- ============================================
-- MIGRATION: Adiciona order_type na tabela orders
-- Data: 2026-01-01
-- Objetivo: Diferenciar pedidos local vs delivery
-- ============================================

-- BACKUP RECOMENDADO ANTES DE EXECUTAR:
-- mysqldump -u root cardapio_saas orders > orders_backup_20260101.sql

-- Adiciona coluna order_type
ALTER TABLE orders 
ADD COLUMN order_type ENUM('local','delivery') NOT NULL DEFAULT 'local' 
AFTER status;

-- Verifica se foi criado corretamente
-- SELECT id, status, order_type FROM orders LIMIT 5;

-- Para reverter (se necessário):
-- ALTER TABLE orders DROP COLUMN order_type;
