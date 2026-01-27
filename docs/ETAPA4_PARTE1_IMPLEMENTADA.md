# ETAPA 4 (Arquitetura e estrutura) – Parte 1 implementada

**Data:** 27/01/2026

## Resumo

Foram implementados itens iniciais da ETAPA 4 do plano de melhorias: DTOs, sistema de eventos e mais uso de Logger no lugar de `error_log`.

---

## 1. Correção de bug (CreateOrderAction)

- **Problema:** `$saveAccount` era usada no `Logger::debug()` antes de ser definida.
- **Solução:** No contexto do log, passou a ser usado  
  `isset($data['save_account']) && $data['save_account'] == true` em vez da variável.

---

## 2. DTOs (Data Transfer Objects)

### Novos arquivos

- **`app/DTO/OrderDTO.php`**
  - Propriedades readonly: `id`, `restaurantId`, `clientId`, `tableId`, `total`, `status`, `orderType`, `paymentMethod`, `observation`, `changeFor`, `source`, `createdAt`, `isPaid`.
  - `OrderDTO::fromArray(array): self`
  - `toArray(): array` para compatibilidade com código que ainda usa array.

- **`app/DTO/OrderItemDTO.php`**
  - Propriedades: `id`, `productId`, `name`, `quantity`, `price`, `extras`, `observation`.
  - `OrderItemDTO::fromArray(array): self`
  - `toArray(): array`

### Uso nos repositórios

- **`OrderRepository`**
  - `find(int $id, ?int $restaurantId): ?array` — mantido para não quebrar callers.
  - **`findAsDto(int $id, ?int $restaurantId): ?OrderDTO`** — novo; preferir em código novo.

Migração gradual: novos fluxos podem usar `findAsDto()` e `OrderDTO`; o restante continua com `find()` e array.

---

## 3. Sistema de eventos

### Novos arquivos

- **`app/Events/EventContract.php`**  
  Interface com `eventName(): string`.

- **`app/Events/EventDispatcher.php`**
  - `EventDispatcher::listen(string $eventName, callable $listener): void`
  - `EventDispatcher::dispatch(object $event): void`
  - Se o objeto implementar `EventContract`, o nome do evento vem de `eventName()`.
  - Exceções em listeners são logadas via `Logger::error` e não interrompem outros listeners.

- **`app/Events/OrderCreatedEvent.php`**
  - Implementa `EventContract`, `eventName() = 'order.created'`.
  - Propriedades: `orderId`, `restaurantId`, `orderType`, `status`.

### Uso em CreateOrderAction

Após `$conn->commit()` no fluxo de **novo pedido**, é disparado:

```php
EventDispatcher::dispatch(new OrderCreatedEvent(
    $orderId,
    $restaurantId,
    $orderType,
    $orderStatus
));
```

### Como reagir ao evento

Em `app/Config/dependencies.php` ou em um bootstrap de eventos (quando existir), registrar listeners, por exemplo:

```php
use App\Events\EventDispatcher;
use App\Events\OrderCreatedEvent;

EventDispatcher::listen('order.created', function (OrderCreatedEvent $e) {
    // Ex.: invalidar cache do cardápio, notificar cozinha, métricas, etc.
});
```

---

## 4. Logging padronizado (Logger em vez de error_log)

- **OrderRepository::updateStatus**
  - Transição inválida: `Logger::warning('Transição de status bloqueada', [...])`
  - Transição ok: `Logger::info('Status do pedido atualizado', [...])`

- **BaseController**
  - Três pontos que usavam `error_log` passaram a usar `Logger::error(..., ['message' => $e->getMessage()])`.

- **Controllers da API**
  - BalcaoController, MesaController, ComandaController, DeliveryController: `error_log` trocado por `Logger::error(...)`.

Os demais `error_log` (CashierController, ClientController, AdditionalController, CardapioController, CategoryController, RestaurantController, ConfigController, etc.) podem ser migrados na continuação da ETAPA 4 ou na etapa de logging.

---

## 5. Próximos passos sugeridos (ETAPA 4)

- [ ] Query Builder simples para queries complexas nos repositórios.
- [ ] Padronizar versionamento de API (ex.: `/api/v1/...` em todos os endpoints).
- [ ] Substituir os `error_log` restantes por Logger.
- [ ] Aumentar o uso de DTOs (ex.: retorno de serviços, parâmetros de ações).
- [ ] Novos eventos: `OrderPaid`, `OrderDelivered`, etc., e listeners (cache, notificações).

---

## 6. Testes

Os testes existentes foram executados após as alterações; o comportamento de `OrderRepository::find()` (retorno `?array`) foi preservado.
