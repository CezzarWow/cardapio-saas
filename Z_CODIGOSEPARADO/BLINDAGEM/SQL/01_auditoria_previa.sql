-- ============================================
-- BLINDAGEM: AUDITORIA PRÉ-CONSTRAINTS
-- EXECUTAR PRIMEIRO - ANTES DOS CHECKS!
-- ============================================

-- 1. Verificar produtos com preço negativo
SELECT id, name, price, 'PREÇO NEGATIVO' as problema 
FROM products WHERE price < 0;

-- 2. Verificar produtos com estoque negativo
SELECT id, name, stock, 'ESTOQUE NEGATIVO' as problema 
FROM products WHERE stock < 0;

-- 3. Verificar pedidos com total negativo
SELECT id, total, created_at, 'TOTAL NEGATIVO' as problema 
FROM orders WHERE total < 0;

-- 4. Verificar pagamentos com valor negativo
SELECT id, order_id, amount, 'VALOR NEGATIVO' as problema 
FROM order_payments WHERE amount < 0;

-- 5. Verificar movimentos de caixa com valor negativo
SELECT id, type, amount, 'VALOR NEGATIVO' as problema 
FROM cash_movements WHERE amount < 0;

-- 6. Verificar itens com quantidade zero ou negativa
SELECT id, order_id, name, quantity, 'QUANTIDADE INVÁLIDA' as problema 
FROM order_items WHERE quantity <= 0;

-- 7. Verificar itens com preço negativo
SELECT id, order_id, name, price, 'PREÇO NEGATIVO' as problema 
FROM order_items WHERE price < 0;

-- ============================================
-- SE ALGUMA QUERY ACIMA RETORNAR REGISTROS:
-- 1. Analise caso a caso
-- 2. Corrija manualmente (UPDATE ou DELETE)
-- 3. Só depois execute check_constraints.sql
-- ============================================
