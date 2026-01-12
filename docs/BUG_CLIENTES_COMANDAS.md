# Levantamento Técnico: Pedidos de Clientes Não Aparecem em CLIENTES/COMANDAS

## Problema Reportado
Quando o usuário:
1. Vai no PDV (Balcão)
2. Seleciona um cliente
3. Adiciona itens ao carrinho
4. Clica em **Salvar**

O pedido NÃO aparece na seção **CLIENTES/COMANDAS** na página de Mesas (`/admin/loja/mesas`).

---

## Diagnóstico Realizado

### 1. Verificação no Banco de Dados
Executamos a query:
```sql
SELECT id, order_type, status, client_id FROM orders ORDER BY id DESC LIMIT 5;
```

**Resultado:** Os pedidos estão sendo criados com:
- `status`: `aberto` ✓ (correto)
- `order_type`: **VAZIO/NULL** ✗ (deveria ser `'comanda'`)
- `client_id`: Preenchido ✓

### 2. Query de Listagem
A query que busca pedidos para CLIENTES/COMANDAS filtra por:
```sql
WHERE o.order_type IN ('delivery', 'balcao', 'comanda')
```

Como `order_type` está vazio, os pedidos não são retornados.

---

## Rastreamento do Fluxo

### Frontend (JavaScript)
1. **Arquivo:** `public/js/pdv/checkout/submit.js` → função `saveClientOrder()`
2. Envia payload para `/admin/loja/venda/finalizar` com:
   - `order_type: 'comanda'` ✓
   - `save_account: true` ✓

### Backend (PHP)
1. **Rota:** `/admin/loja/venda/finalizar` 
2. **Controller:** `App\Controllers\Admin\OrderController::store()`
3. **Service:** `App\Services\OrderOrchestratorService::createOrder()`
4. **Action:** `App\Services\Order\CreateOrderAction::execute()`

### Ponto de Falha Identificado
No arquivo `app/Repositories/Order/OrderRepository.php`, linha 75:
```php
$stmt->execute([
    'rid' => $data['restaurant_id'],
    'cid' => $data['client_id'],
    'total' => $data['total'],
    'otype' => $data['order_type'],  // <-- VALOR ESTÁ CHEGANDO?
    'payment' => $data['payment_method'],
    // ...
]);
```

**Hipótese 1:** O `$data['order_type']` está chegando vazio ou NULL no momento do INSERT.

**Hipótese 2:** Há um problema de cache do PHP (OPcache) que não está recarregando os arquivos alterados.

---

## Correções Aplicadas (Não Resolveram)

1. **Query de listagem:** Adicionado `'comanda'` aos tipos buscados
   - Arquivo: `app/Repositories/Order/OrderRepository.php` linha 190

2. **Forçar atualização de order_type:** Adicionado método `updateOrderType()` e chamada após criar pedido
   - Arquivo: `app/Repositories/Order/OrderRepository.php` linhas 168-176
   - Arquivo: `app/Services/Order/CreateOrderAction.php` linhas 148-149

---

## Ações Pendentes para o Técnico

### 1. Verificar se o `order_type` está chegando no Controller
Adicionar log temporário em `app/Controllers/Admin/OrderController.php`:
```php
public function store(): void
{
    $data = $this->getJsonBody();
    error_log("DEBUG order_type: " . ($data['order_type'] ?? 'VAZIO'));
    // ... resto do código
}
```

### 2. Verificar OPcache
Se estiver habilitado, desabilitar temporariamente em `php.ini`:
```ini
opcache.enable=0
```
Ou reiniciar o Apache após cada alteração.

### 3. Verificar se há múltiplos arquivos de configuração
Confirmar que não há outro `OrderRepository.php` ou `CreateOrderAction.php` sendo carregado de outra pasta.

### 4. Testar com query manual
Após criar um pedido, executar:
```sql
SELECT * FROM orders ORDER BY id DESC LIMIT 1;
```
Para verificar todos os campos do último pedido criado.

---

## Arquivos Relevantes para Análise

| Arquivo | Função |
|---------|--------|
| `public/js/pdv/checkout/submit.js` | Frontend - Envia dados |
| `app/Controllers/Admin/OrderController.php` | Controller - Recebe request |
| `app/Services/OrderOrchestratorService.php` | Orquestra criação |
| `app/Services/Order/CreateOrderAction.php` | Lógica de criação |
| `app/Repositories/Order/OrderRepository.php` | Acesso ao banco |
| `views/admin/tables/partials/grid_comandas.php` | View de listagem |

---

## Resumo
O pedido está sendo criado com sucesso (ID gerado, itens salvos, status correto), porém o campo `order_type` não está sendo persistido no banco de dados, fazendo com que a query de listagem não retorne esses pedidos.
