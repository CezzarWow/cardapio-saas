-- Migration: Adiciona campo source_type em order_items para agrupar itens por origem
-- Data: 2026-01-28

ALTER TABLE order_items ADD COLUMN source_type VARCHAR(20) NULL DEFAULT NULL;

-- Valores possíveis: 'delivery', 'pickup', 'mesa', 'comanda', 'balcao'
-- NULL significa legado (itens antigos sem classificação)

-- Índice para facilitar agrupamento
CREATE INDEX idx_order_items_source_type ON order_items(source_type);
