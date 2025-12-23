-- ============================================
-- FASE 4: Tabela de Movimentações de Estoque
-- Executar uma única vez no banco de dados
-- ============================================

CREATE TABLE IF NOT EXISTS stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    product_id INT NOT NULL,
    type ENUM('entrada', 'saida') NOT NULL,
    quantity INT NOT NULL,          -- Sempre positivo
    stock_before INT NOT NULL,
    stock_after INT NOT NULL,
    source VARCHAR(50) NOT NULL,    -- reposicao, ajuste_manual, venda (futuro)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Índices para performance
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_product (product_id),
    INDEX idx_created (created_at),
    
    -- Foreign keys (opcional, depende do banco)
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Após executar, a tabela estará pronta para
-- registrar movimentações de estoque.
-- ============================================
