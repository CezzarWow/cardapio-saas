-- Tabela de relacionamento (Muitos-para-Muitos) entre Produtos e Grupos de Adicionais
CREATE TABLE product_additional_relations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    group_id INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES additional_groups(id) ON DELETE CASCADE
);
