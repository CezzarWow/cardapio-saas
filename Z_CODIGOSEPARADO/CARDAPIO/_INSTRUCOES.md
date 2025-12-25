# 📋 ABA CARDÁPIO - Documentação Técnica para Novo Desenvolvedor

## ⚠️ STATUS ATUAL
A aba **CARDÁPIO** está em **CONSTRUÇÃO**. A tela atual exibe apenas um placeholder.
O novo desenvolvedor terá que **criar do zero** a funcionalidade desta aba.

---

## 🎯 O QUE É A ABA CARDÁPIO?

A aba Cardápio é destinada a gerenciar a **exibição pública** do cardápio digital do restaurante.
Diferente da aba **Estoque** (que é interna), o **Cardápio** é o que o **cliente final vê**.

### Funcionalidades Esperadas:
1. Configurar aparência do cardápio digital (cores, logo, layout)
2. Definir quais produtos/categorias aparecem no cardápio público
3. Ordenação visual das categorias e produtos
4. Horário de funcionamento
5. Link/QR Code para compartilhar o cardápio
6. Preview em tempo real

---

## 🏗️ ARQUITETURA DO SISTEMA

### Padrão MVC (Model-View-Controller)
```
cardapio-saas/
├── app/
│   ├── Controllers/Admin/    ← Controllers (lógica)
│   ├── Core/                 ← Classes base (Database, etc)
│   └── Models/               ← Models (dados)
├── public/
│   ├── index.php             ← Router (todas as rotas)
│   ├── css/                  ← Arquivos CSS
│   └── js/                   ← Arquivos JavaScript
├── views/
│   └── admin/                ← Views (telas)
│       ├── cardapio/         ← Views da aba Cardápio
│       └── panel/layout/     ← Header, Sidebar, Footer
├── database/                 ← Scripts SQL
└── uploads/                  ← Imagens enviadas
```

---

## 🔧 COMO CRIAR UMA NOVA FUNCIONALIDADE

### Passo 1: Criar o Controller
Local: `app/Controllers/Admin/CardapioController.php`

```php
<?php
namespace App\Controllers\Admin;
use App\Core\Database;
use PDO;

class CardapioController {
    public function index() {
        $this->checkSession();
        $conn = Database::connect();
        // Buscar dados...
        require __DIR__ . '/../../../views/admin/cardapio/index.php';
    }
    
    private function checkSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['loja_ativa_id'])) {
            header('Location: ' . BASE_URL . '/admin');
            exit;
        }
    }
}
```

### Passo 2: Adicionar Rota no Router
Local: `public/index.php`

```php
case '/admin/loja/cardapio':
    require __DIR__ . '/../app/Controllers/Admin/CardapioController.php';
    (new \App\Controllers\Admin\CardapioController())->index();
    break;
```

### Passo 3: Criar a View
Local: `views/admin/cardapio/index.php`

```php
<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <!-- Conteúdo aqui -->
</main>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
```

---

## 📁 ARQUIVOS DESTE PACOTE

| Arquivo | Descrição | Localização Original |
|---------|-----------|---------------------|
| `CardapioController.php` | Controller atual (placeholder) | app/Controllers/Admin/ |
| `VIEW_cardapio_index.php` | View atual (placeholder) | views/admin/cardapio/ |
| `ROUTER_trecho.php` | Trecho do router | public/index.php |
| `sidebar.php` | Menu lateral (referência) | views/admin/panel/layout/ |
| `header.php` | Header das páginas | views/admin/panel/layout/ |
| `footer.php` | Footer das páginas | views/admin/panel/layout/ |

---

## 🗄️ BANCO DE DADOS

### Conexão
Arquivo: `app/Core/Database.php`

```php
$conn = Database::connect();
$stmt = $conn->prepare("SELECT * FROM tabela WHERE id = :id");
$stmt->execute(['id' => $id]);
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### Tabelas Existentes Relevantes
```sql
-- Produtos
products (id, name, description, price, image, category_id, restaurant_id, stock)

-- Categorias
categories (id, name, restaurant_id)

-- Restaurantes
restaurants (id, name, slug, logo, status)
```

### Variáveis de Sessão Importantes
```php
$_SESSION['loja_ativa_id']    // ID do restaurante logado
$_SESSION['loja_ativa_nome']  // Nome do restaurante
$_SESSION['loja_ativa_slug']  // Slug (URL amigável)
$_SESSION['loja_ativa_logo']  // Arquivo de logo
```

---

## 🎨 CSS E ESTILIZAÇÃO

### Arquivo Principal de Estilos
Local: `public/css/pdv.css`

### Classes CSS Importantes
```css
.main-content       /* Container principal */
.sidebar            /* Menu lateral */
.sticky-tabs        /* Abas fixas no topo */
.stock-table-container /* Container de tabelas */
.btn-stock-action   /* Botões de ação */
```

### Ícones
O sistema usa **Lucide Icons**: https://lucide.dev/
```html
<i data-lucide="nome-do-icone" size="24"></i>
```

---

## 📝 PADRÕES DE CÓDIGO

### Views - Estrutura Padrão
```php
<?php 
require __DIR__ . '/../panel/layout/header.php'; 
require __DIR__ . '/../panel/layout/sidebar.php'; 
?>

<main class="main-content">
    <div style="padding: 2rem; width: 100%; overflow-y: auto;">
        
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>/admin">Painel</a> › 
            <strong>Cardápio</strong>
        </div>

        <!-- Título -->
        <h1>Título da Página</h1>

        <!-- Conteúdo -->

    </div>
</main>

<?php require __DIR__ . '/../panel/layout/footer.php'; ?>
```

### URLs e Links
```php
// URL base do sistema
BASE_URL

// Exemplo de link
<a href="<?= BASE_URL ?>/admin/loja/cardapio">Link</a>

// Imagens
<img src="<?= BASE_URL ?>/uploads/<?= $product['image'] ?>">
```

---

## 🚀 COMO RODAR LOCALMENTE

1. XAMPP com Apache + MySQL rodando
2. Acessar: `http://localhost/cardapio-saas/public/admin`
3. Clicar em "Acessar" em um restaurante
4. Clicar em "Cardápio" no menu lateral

---

## 💡 DICAS IMPORTANTES

1. **Sempre verificar sessão** no início dos métodos do controller
2. **Usar `htmlspecialchars()`** ao exibir dados na view (segurança XSS)
3. **BASE_URL** sempre para links e imagens
4. **Testar em múltiplos restaurantes** para garantir isolamento de dados
5. **O menu lateral** já tem o link para `/admin/loja/cardapio`
6. **A rota NÃO está no router ainda** - precisa adicionar!

---

## 📞 DÚVIDAS?
Consulte o código das outras abas (Estoque, PDV) como referência.
A aba Estoque em `Z_CODIGOSEPARADO/ESTOQUE/` tem estrutura similar.
