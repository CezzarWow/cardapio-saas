-- ============================================
-- FASE 5: Tabelas de Adicionais
-- Executar uma única vez no banco de dados
-- ============================================

-- Tabela de Grupos de Adicionais
CREATE TABLE IF NOT EXISTS additional_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    required TINYINT(1) DEFAULT 0,    -- 0 = opcional, 1 = obrigatório (usado na Fase 6)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_restaurant (restaurant_id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabela de Itens de Adicionais
CREATE TABLE IF NOT EXISTS additional_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,  -- 0 = grátis
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_group (group_id),
    FOREIGN KEY (group_id) REFERENCES additional_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Estrutura:
-- additional_groups (restaurant_id direto)
--   └── additional_items (herda via group_id)
-- ============================================
