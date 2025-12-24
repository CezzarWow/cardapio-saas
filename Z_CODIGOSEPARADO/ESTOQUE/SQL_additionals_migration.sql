-- LOCALIZACAO ORIGINAL: database/additionals_migration.sql
-- =====================================================
-- MIGRAÇÃO: Itens de Adicionais Globais
-- Fase 5.1 - Arquitetura de Itens Reutilizáveis
-- =====================================================

-- 1. Criar tabela pivot ANTES de modificar additional_items
CREATE TABLE IF NOT EXISTS additional_group_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    item_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_item (group_id, item_id),
    INDEX idx_group_id (group_id),
    INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrar dados existentes para a pivot (se houver)
-- Primeiro migra os vínculos atuais antes de alterar a estrutura
INSERT IGNORE INTO additional_group_items (group_id, item_id)
SELECT group_id, id FROM additional_items WHERE group_id IS NOT NULL;

-- 3. Adicionar restaurant_id na additional_items (usando o restaurant_id do grupo)
ALTER TABLE additional_items 
ADD COLUMN restaurant_id INT NULL AFTER id;

-- 4. Preencher restaurant_id baseado no grupo atual
UPDATE additional_items ai
INNER JOIN additional_groups ag ON ai.group_id = ag.id
SET ai.restaurant_id = ag.restaurant_id;

-- 5. Remover constraint antiga e coluna group_id
ALTER TABLE additional_items DROP FOREIGN KEY additional_items_ibfk_1;
ALTER TABLE additional_items DROP COLUMN group_id;

-- 6. Tornar restaurant_id NOT NULL e adicionar FK
ALTER TABLE additional_items MODIFY restaurant_id INT NOT NULL;
ALTER TABLE additional_items ADD INDEX idx_restaurant_id (restaurant_id);
ALTER TABLE additional_items 
ADD CONSTRAINT fk_items_restaurant 
FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE;

-- 7. Adicionar FKs na pivot
ALTER TABLE additional_group_items 
ADD CONSTRAINT fk_pivot_group 
FOREIGN KEY (group_id) REFERENCES additional_groups(id) ON DELETE CASCADE;

ALTER TABLE additional_group_items 
ADD CONSTRAINT fk_pivot_item 
FOREIGN KEY (item_id) REFERENCES additional_items(id) ON DELETE CASCADE;

