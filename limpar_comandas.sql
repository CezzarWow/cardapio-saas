-- ============================================
-- Script para LIMPAR pedidos de Clientes/Comandas
-- Execute no phpMyAdmin ou MySQL
-- ============================================

-- PASSO 1: Ver quais pedidos serão afetados (REVISE ANTES DE DELETAR!)
SELECT id, order_type, status, total, created_at 
FROM orders 
WHERE order_type IN ('delivery', 'balcao') 
AND status NOT IN ('concluido', 'cancelado') 
ORDER BY created_at DESC;

-- PASSO 2: Apagar os itens desses pedidos (execute este PRIMEIRO)
DELETE FROM order_items 
WHERE order_id IN (
    SELECT id FROM orders 
    WHERE order_type IN ('delivery', 'balcao') 
    AND status NOT IN ('concluido', 'cancelado')
);

-- PASSO 3: Apagar os pedidos (execute DEPOIS do passo 2)
DELETE FROM orders 
WHERE order_type IN ('delivery', 'balcao') 
AND status NOT IN ('concluido', 'cancelado');

-- ============================================
-- ATENÇÃO: Esta ação é IRREVERSÍVEL!
-- ============================================
