# 📦 Documentação Técnica - Módulo de Estoque

## Visão Geral

O módulo de **Estoque** é composto por 5 sub-módulos interligados:

| Sub-módulo | Função | Controller | View |
|------------|--------|------------|------|
| **Produtos** | CRUD de produtos com estoque | `StockController.php` | `/views/admin/stock/` |
| **Categorias** | CRUD de categorias | `CategoryController.php` | `/views/admin/categories/` |
| **Adicionais** | Grupos + Itens globais | `AdditionalController.php` | `/views/admin/additionals/` |
| **Reposição** | Ajuste de estoque | `StockRepositionController.php` | `/views/admin/reposition/` |
| **Movimentações** | Histórico de entradas/saídas | `StockMovementController.php` | `/views/admin/movements/` |

---

## 🗂️ Estrutura de Pastas

```
cardapio-saas/
├── app/
│   ├── Controllers/Admin/
│   │   ├── StockController.php         # Produtos
│   │   ├── CategoryController.php      # Categorias
│   │   ├── AdditionalController.php    # Adicionais
│   │   ├── StockRepositionController.php
│   │   └── StockMovementController.php
│   └── Core/
│       ├── Database.php                # Conexão PDO
│       └── ViewHelper.php              # Helpers de view
├── views/admin/
│   ├── stock/
│   │   ├── index.php                   # Lista de produtos
│   │   ├── create.php                  # Criar produto
│   │   └── edit.php                    # Editar produto
│   ├── categories/
│   │   ├── index.php                   # Lista de categorias
│   │   └── edit.php                    # Editar categoria
│   ├── additionals/
│   │   ├── index.php                   # Grupos + vínculos
│   │   ├── items.php                   # Catálogo de itens
│   │   └── item_form.php               # Criar/editar item
│   ├── reposition/
│   │   └── index.php                   # Ajuste de estoque
│   └── movements/
│       └── index.php                   # Histórico
└── public/
    └── index.php                       # Router principal
```

---

## 🗄️ Banco de Dados

### Tabelas do Módulo de Estoque

```sql
-- CATEGORIAS
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    ordem INT DEFAULT 0,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- PRODUTOS
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) DEFAULT 0,
    stock_qty INT DEFAULT 0,
    image VARCHAR(255),
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- MOVIMENTAÇÕES DE ESTOQUE
CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    type ENUM('entrada','saida') NOT NULL,
    quantity INT NOT NULL,
    reason VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- GRUPOS DE ADICIONAIS
CREATE TABLE additional_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    required TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- ITENS DE ADICIONAIS (Globais por loja)
CREATE TABLE additional_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- PIVOT: Vínculo Grupo <-> Item
CREATE TABLE additional_group_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    item_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_group_item (group_id, item_id),
    FOREIGN KEY (group_id) REFERENCES additional_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES additional_items(id) ON DELETE CASCADE
);
```

---

## 🔀 Rotas (public/index.php)

### Produtos
| Rota | Método | Controller | Função |
|------|--------|------------|--------|
| `/admin/loja/produtos` | GET | `StockController->index()` | Lista produtos |
| `/admin/loja/produtos/novo` | GET | `StockController->create()` | Form criar |
| `/admin/loja/produtos/salvar` | POST | `StockController->store()` | Salvar novo |
| `/admin/loja/produtos/editar?id=X` | GET | `StockController->edit()` | Form editar |
| `/admin/loja/produtos/atualizar` | POST | `StockController->update()` | Atualizar |
| `/admin/loja/produtos/deletar?id=X` | GET | `StockController->delete()` | Excluir |

### Categorias
| Rota | Método | Controller | Função |
|------|--------|------------|--------|
| `/admin/loja/categorias` | GET | `CategoryController->index()` | Lista |
| `/admin/loja/categorias/salvar` | POST | `CategoryController->store()` | Criar |
| `/admin/loja/categorias/editar?id=X` | GET | `CategoryController->edit()` | Form editar |
| `/admin/loja/categorias/atualizar` | POST | `CategoryController->update()` | Atualizar |
| `/admin/loja/categorias/deletar?id=X` | GET | `CategoryController->delete()` | Excluir |

### Adicionais
| Rota | Método | Controller | Função |
|------|--------|------------|--------|
| `/admin/loja/adicionais` | GET | `AdditionalController->index()` | Grupos + itens vinculados |
| `/admin/loja/adicionais/itens` | GET | `AdditionalController->listItems()` | Catálogo global |
| `/admin/loja/adicionais/grupo/salvar` | POST | `AdditionalController->storeGroup()` | Criar grupo |
| `/admin/loja/adicionais/grupo/deletar?id=X` | GET | `AdditionalController->deleteGroup()` | Excluir grupo |
| `/admin/loja/adicionais/item/novo` | GET | `AdditionalController->createItem()` | Form item |
| `/admin/loja/adicionais/item/salvar` | POST | `AdditionalController->storeItem()` | Criar item |
| `/admin/loja/adicionais/item/editar?id=X` | GET | `AdditionalController->editItem()` | Form editar |
| `/admin/loja/adicionais/item/atualizar` | POST | `AdditionalController->updateItem()` | Atualizar |
| `/admin/loja/adicionais/item/deletar?id=X` | GET | `AdditionalController->deleteItem()` | Excluir |
| `/admin/loja/adicionais/vincular` | POST | `AdditionalController->linkItem()` | Vincular item a grupo |
| `/admin/loja/adicionais/desvincular?grupo=X&item=Y` | GET | `AdditionalController->unlinkItem()` | Desvincular |

### Reposição
| Rota | Método | Controller | Função |
|------|--------|------------|--------|
| `/admin/loja/reposicao` | GET | `StockRepositionController->index()` | Lista produtos |
| `/admin/loja/reposicao/ajustar` | POST | `StockRepositionController->adjust()` | Ajustar estoque |

### Movimentações
| Rota | Método | Controller | Função |
|------|--------|------------|--------|
| `/admin/loja/movimentacoes` | GET | `StockMovementController->index()` | Histórico |

---

## 🎛️ Controllers - Detalhamento

### 1. StockController.php (Produtos)

**Arquivo:** `/app/Controllers/Admin/StockController.php`

**Métodos:**
- `index()` - Lista produtos com categoria e estoque
- `create()` - Formulário de criação
- `store()` - Salva produto (com upload de imagem)
- `edit()` - Formulário de edição
- `update()` - Atualiza produto
- `delete()` - Remove produto

**Fluxo:**
```
POST /produtos/salvar
    ↓
$_POST['name'], $_POST['price'], $_POST['category_id'], $_FILES['image']
    ↓
INSERT INTO products (...) VALUES (...)
    ↓
header('Location: /admin/loja/produtos')
```

---

### 2. CategoryController.php

**Arquivo:** `/app/Controllers/Admin/CategoryController.php`

**Métodos:**
- `index()` - Lista categorias
- `store()` - Cria categoria
- `edit()` - Form editar
- `update()` - Atualiza
- `delete()` - Remove

**Segurança:** Todas as queries filtram por `restaurant_id = $_SESSION['loja_ativa_id']`

---

### 3. AdditionalController.php

**Arquivo:** `/app/Controllers/Admin/AdditionalController.php`

**Arquitetura de Adicionais:**
- Itens são **globais por loja** (não pertencem a um grupo específico)
- Grupos são containers que **vinculam** itens via tabela pivot
- Um item pode estar em **múltiplos grupos**
- Alterar preço do item reflete em todos os grupos

**Métodos privados:**
- `getGroupsWithItems($conn, $restaurantId)` - Busca grupos com seus itens
- `getGlobalItems($conn, $restaurantId)` - Busca todos os itens da loja

**Métodos públicos:**
- `index()` - Mostra grupos com itens vinculados
- `listItems()` - Catálogo global de itens
- `storeGroup()` / `deleteGroup()` - CRUD grupos
- `createItem()` / `storeItem()` / `editItem()` / `updateItem()` / `deleteItem()` - CRUD itens
- `linkItem()` / `unlinkItem()` - Vincular/desvincular via pivot

---

### 4. StockRepositionController.php

**Arquivo:** `/app/Controllers/Admin/StockRepositionController.php`

**Função:** Permite ajustar quantidade de estoque de forma operacional

**Métodos:**
- `index()` - Lista produtos com estoque atual
- `adjust()` - Ajusta quantidade (+ ou -)

**Ao ajustar estoque:**
1. Atualiza `products.stock_qty`
2. Registra movimentação em `stock_movements`

---

### 5. StockMovementController.php

**Arquivo:** `/app/Controllers/Admin/StockMovementController.php`

**Função:** Exibe histórico de todas as movimentações de estoque

**Filtros disponíveis:**
- Por produto
- Por tipo (entrada/saída)
- Por período

---

## 🖼️ Views - Padrão de Layout

Todas as views seguem o padrão:

```php
<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php';
?>

<main class="main-content">
    <!-- Conteúdo -->
</main>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
```

### Sub-abas do Estoque

Ordem nas views: **Produtos | Categorias | Adicionais | Reposição | Movimentações**

A aba ativa tem `background: #2563eb; color: white;`

---

## 🔐 Sessão

Todo controller verifica:
```php
$_SESSION['loja_ativa_id']  // ID do restaurante logado
$_SESSION['loja_ativa_nome'] // Nome do restaurante
```

Se não existir, redireciona para `/admin`

---

## 📁 Arquivos Importantes para Análise

| Arquivo | Descrição |
|---------|-----------|
| `public/index.php` | Router principal (switch/case) |
| `app/Core/Database.php` | Conexão PDO |
| `app/Controllers/Admin/*.php` | Controllers |
| `views/admin/panel/layout/` | Header, sidebar, footer |
| `public/css/pdv.css` | Estilos do painel |

---

## 🚀 Como Testar

1. Acesse: `http://localhost/cardapio-saas/public/admin`
2. Faça login em uma loja
3. No menu lateral, clique em **Estoque** (ícone de caixa)
4. Navegue pelas sub-abas

---

## ⚠️ Pontos de Atenção

1. **Referência em foreach:** Sempre usar `unset($var)` após `foreach ($array as &$var)`
2. **Upload de imagens:** Produtos salvam em `/public/uploads/`
3. **Multi-tenant:** Todos os dados são filtrados por `restaurant_id`
4. **Padrão POST/Redirect:** Após salvar, sempre `header('Location:...'); exit;`
