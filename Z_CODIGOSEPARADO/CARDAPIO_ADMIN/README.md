# 📋 Cardápio Admin - Documentação para Desenvolvimento

## Objetivo
Criar o **painel administrativo** para gerenciar o cardápio web público que já existe em `/c/{restaurant_id}`.

## Contexto do Projeto
O sistema já possui:
- ✅ Cardápio público funcionando (mobile-first)
- ✅ PDV admin completo
- ✅ Gestão de produtos, categorias, adicionais
- ❌ **FALTA**: Admin específico para configurar o cardápio web (cores, horários, delivery, taxa, etc)

---

## Estrutura de Arquivos Relevantes

### Backend (Controllers)
| Arquivo | Descrição |
|---------|-----------|
| `app/Controllers/CardapioPublicoController.php` | Renderiza o cardápio público |
| `app/Controllers/Admin/CardapioController.php` | Controller do admin (a expandir) |
| `app/Controllers/Admin/ConfigController.php` | Configurações gerais da loja |

### Views
| Arquivo | Descrição |
|---------|-----------|
| `views/cardapio_publico.php` | Frontend do cardápio (619 linhas) |
| `views/admin/cardapio/index.php` | Admin do cardápio (a criar/expandir) |

### JavaScript do Cardápio
| Arquivo | Descrição |
|---------|-----------|
| `public/js/cardapio/utils.js` | Utilitários (formatação) |
| `public/js/cardapio/cart.js` | Carrinho de compras |
| `public/js/cardapio/modals.js` | Modais de produto e carrinho |
| `public/js/cardapio/checkout.js` | Checkout e pagamento |
| `public/js/cardapio.js` | Script principal (listeners) |

### CSS do Cardápio
| Arquivo | Descrição |
|---------|-----------|
| `public/css/cardapio.css` | Estilos principais |
| `public/css/cart.css` | Carrinho flutuante |
| `public/css/modals.css` | Modais |
| `public/css/checkout.css` | Checkout |
| `public/css/payment.css` | Tela de pagamento |

---

## Banco de Dados Atual

### Tabelas que o Cardápio USA
```sql
restaurants      -- nome, logo, slug, status
categories       -- categorias de produtos
products         -- produtos com preço e imagem
additional_groups     -- grupos de adicionais
additional_items      -- itens de adicional
product_additional_relations -- vínculo produto→grupo
```

### Tabela que FALTA criar (sugestão)
```sql
CREATE TABLE IF NOT EXISTS cardapio_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL UNIQUE,
    
    -- Identidade Visual
    primary_color VARCHAR(7) DEFAULT '#2563eb',
    secondary_color VARCHAR(7) DEFAULT '#f59e0b',
    
    -- Horário de Funcionamento
    opening_time TIME DEFAULT '08:00',
    closing_time TIME DEFAULT '22:00',
    is_open BOOLEAN DEFAULT TRUE,
    closed_message VARCHAR(255) DEFAULT 'Estamos fechados no momento',
    
    -- Delivery
    delivery_enabled BOOLEAN DEFAULT TRUE,
    delivery_fee DECIMAL(10,2) DEFAULT 5.00,
    min_order_value DECIMAL(10,2) DEFAULT 20.00,
    delivery_time_min INT DEFAULT 30,
    delivery_time_max INT DEFAULT 45,
    
    -- Retirada
    pickup_enabled BOOLEAN DEFAULT TRUE,
    pickup_discount DECIMAL(5,2) DEFAULT 0.00,
    
    -- Local
    dine_in_enabled BOOLEAN DEFAULT TRUE,
    
    -- WhatsApp
    whatsapp_number VARCHAR(20),
    
    -- Pagamento
    accept_cash BOOLEAN DEFAULT TRUE,
    accept_card BOOLEAN DEFAULT TRUE,
    accept_pix BOOLEAN DEFAULT TRUE,
    pix_key VARCHAR(100),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);
```

---

## Fluxo do Cardápio Público (Atual)

```
1. Cliente acessa /c/{id}
2. CardapioPublicoController::show() busca:
   - Dados do restaurante
   - Categorias com produtos
   - Grupos de adicionais
   - Relações produto→adicional
3. Renderiza cardapio_publico.php
4. Cliente navega, adiciona itens, checkout
5. Envia pedido via WhatsApp (hardcoded atualmente)
```

---

## O que o Admin do Cardápio precisa fazer

### 1. Configurações Visuais
- Cor primária e secundária
- Logo (já existe em restaurants)
- Banner/imagem de capa

### 2. Horário de Funcionamento
- Hora de abertura/fechamento
- Status aberto/fechado manual
- Mensagem quando fechado

### 3. Configurações de Entrega
- Habilitar/desabilitar delivery
- Taxa de entrega
- Valor mínimo do pedido
- Tempo estimado (min-max)

### 4. Configurações de Retirada
- Habilitar/desabilitar


### 5. Formas de Pagamento
- Dinheiro (sim/não)
- Cartão (sim/não)
- PIX (sim/não + chave)

### 6. WhatsApp
- Número para receber pedidos

---

## Rota Sugerida
- `/admin/loja/cardapio` → Tela de configuração do cardápio

---

## Próximos Passos

1. [ ] Criar tabela `cardapio_config` no banco
2. [ ] Expandir `CardapioController.php` com CRUD de configurações
3. [ ] Criar view `views/admin/cardapio/index.php` com formulário
4. [ ] Modificar `CardapioPublicoController.php` para ler configs
5. [ ] Aplicar configs dinâmicas no `cardapio_publico.php`

---

## Arquivos de Referência
Consulte os arquivos na pasta `REFERENCIAS/` para ver o código atual completo.

1. Configurações Gerais / Operação

WhatsApp Bot: campo para mensagem automática e toggle ON/OFF

Botão de emergência para fechar a loja imediatamente

Tempo de preparo padrão do pedido (ex.: 40 min)

Status Online/Offline visível no topo

Tabela de horários de funcionamento da loja (Seg-Sab, abertura/fechamento)

2. Logística / Delivery

Taxa de entrega configurável por bairro ou por raio em km

Pedido mínimo para entrega

Cadastro de bairros atendidos com valor de taxa

3. Promoções e Combos

Criar combos: escolher itens, definir preço promocional e validade da promoção

Ícone ou destaque visual para promoções

Prioridade de exibição de combos (aparecem primeiro ou em seção especial)

4. Ajuste de Itens em Destaque

Arrastar e soltar para organizar ordem de produtos no cardápio

Definir itens fixos no topo ou temporariamente em destaque

Seções de categorias (ex.: Burgers, Combos, Bebidas) com prioridade de exibição

5. Visual e UX

Painel limpo, moderno e intuitivo

Preview em tempo real mostrando como os clientes verão o cardápio

Barras laterais ou abas para navegar entre Configurações, Delivery, Promoções & Combos, Destaques

Notificações de alterações salvas com sucesso
