# Pull Request: Hotfix CloseCommandAction Status Update

## Descrição

Este PR corrige o bug crítico onde comandas não eram finalizadas corretamente - o status permanecia `'aberto'` ao invés de mudar para `'concluido'` após fechamento.

## Mudanças Principais

### 1. `CloseCommandAction.php`
- ✅ Adicionado chamada a `updateStatus($orderId, 'concluido')` dentro da transação
- ✅ Validação de rowCount para garantir que o UPDATE afetou 1 linha
- ✅ Validação adicional: só permite fechar comandas com `status = 'aberto'`
- ✅ Logs estruturados com tags `[CLOSE_COMMAND]`

### 2. `OrderRepository.php`
- ✅ Adicionado `VALID_TRANSITIONS` constant com modelo de estados
- ✅ `updateStatus()` agora valida transição antes de executar UPDATE
- ✅ `updateStatus()` retorna `int` (rowCount) ao invés de `void`
- ✅ `create()` agora aceita parâmetro opcional `$status` (default: 'novo')
- ✅ Transição `novo → aberto` BLOQUEADA (regra crítica)

### 3. `CreateDeliveryLinkedAction.php`
- ✅ Removidos `error_log` de debug
- ✅ Criação de mesa/comanda usa `create(..., 'aberto')` direto
- ✅ Não chama `updateStatus` em comandas existentes (apenas adiciona item)
- ✅ Documentação atualizada com regras críticas

## Transições de Status Válidas

```
PEDIDOS (operacionais):
  novo → aguardando → em_preparo → pronto → em_entrega → entregue → concluido
  
CONTAS (financeiras):
  aberto → concluido

QUALQUER → cancelado (exceto estados finais)
```

## Transições BLOQUEADAS

| Transição | Razão |
|-----------|-------|
| `novo → aberto` | Mistura pedido com conta |
| `aberto → novo` | Conta não vira pedido |
| `concluido → *` | Estado final |
| `cancelado → *` | Estado final |

## Testes Incluídos

### Unit Tests (33 testes, 72 assertions)
- `CloseCommandActionTest` (5 testes)
  - testExecuteCallsUpdatePaymentAndUpdateStatus
  - testExecuteThrowsExceptionWhenOrderNotFound
  - testExecuteThrowsExceptionWhenOrderNotOpen
  - testExecuteThrowsExceptionWhenNoPaymentAndNotPaid
  - testExecuteThrowsRuntimeExceptionWhenUpdateStatusAffectsZeroRows

- `OrderRepositoryStatusTransitionTest` (28 testes via data providers)
  - Testa todas transições válidas
  - Testa todas transições inválidas
  - Verifica estados finais vazios
  - Verifica regra `novo → aberto` bloqueada

### Integration Tests
- `CloseCommandActionIntegrationTest`
  - testCloseCommandUpdatesStatusToConcluido
  - testCloseCommandWithoutPaymentWhenNotPaidThrowsException
  - testCloseAlreadyClosedCommandThrowsException

## Verificação Pós-Deploy

```sql
-- Verificar comanda fechada
SELECT id, status, is_paid FROM orders WHERE id = :orderId;
-- Esperado: status = 'concluido', is_paid = 1
```

```bash
# Teste via cURL
curl -X POST 'https://your.domain/admin/loja/venda/fechar-comanda' \
  -H 'Content-Type: application/json' \
  -d '{"order_id":123,"payments":[{"method":"pix","amount":100.00}]}'
# Esperado: {"success":true}
```

## Backup e Rollback

### Antes do Deploy

```sql
-- Backup das comandas abertas
CREATE TABLE orders_backup_20260112 AS
SELECT * FROM orders WHERE status IN ('aberto', 'novo');
```

### Rollback do Código

```bash
git revert <commit_hash>
```

### Rollback de Dados (último recurso)

```sql
-- Restaurar status de pedidos específicos
UPDATE orders SET status = 'aberto' WHERE id IN (...);
```

## Checklist

- [x] Código implementado conforme especificação
- [x] rowCount check em updateStatus
- [x] Transação única em CloseCommandAction
- [x] Unit tests passando (33/33)
- [x] Integration tests incluídos
- [x] Debug logs removidos
- [x] Documentação atualizada

## Breaking Changes

- `OrderRepository::updateStatus()` agora retorna `int` ao invés de `void`
- Código que depende deste método pode precisar de ajuste

## Arquivos Modificados

| Arquivo | Alteração |
|---------|-----------|
| `app/Repositories/Order/OrderRepository.php` | Validação de transições, status em create() |
| `app/Services/Order/CloseCommandAction.php` | updateStatus + rowCount check |
| `app/Services/Order/CreateDeliveryLinkedAction.php` | Criação direta com 'aberto' |
| `tests/Unit/CloseCommandActionTest.php` | NOVO |
| `tests/Unit/OrderRepositoryStatusTransitionTest.php` | NOVO |
| `tests/Integration/CloseCommandActionIntegrationTest.php` | NOVO |
