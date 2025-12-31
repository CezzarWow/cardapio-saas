-- ============================================
-- Adicionar coluna display_order à tabela categories
-- Execute este arquivo via phpMyAdmin ou MySQL Workbench
-- ============================================

USE cardapio_saas;

-- Adiciona coluna display_order se não existir
ALTER TABLE categories 
ADD COLUMN IF NOT EXISTS display_order INT DEFAULT 0;

-- (Opcional) Define valores iniciais baseados na ordem alfabética
UPDATE categories 
SET display_order = (@row_number:=@row_number + 1) - 1
WHERE (@row_number:=0) IS NOT NULL OR 1=1
ORDER BY name ASC;
