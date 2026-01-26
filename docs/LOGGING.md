# 📝 Guia de Logging - Cardápio SaaS

**Versão:** 1.0  
**Data:** 26/01/2026

---

## 📋 Visão Geral

Este documento descreve como usar o sistema de logging do projeto de forma padronizada.

**IMPORTANTE:** Use sempre `Logger` em vez de `error_log()` ou `file_put_contents()`.

---

## 🎯 Níveis de Log

### `Logger::error()`
**Quando usar:** Erros críticos que impedem operação ou indicam falha grave.

**Exemplos:**
- Falha de conexão com banco
- Erro ao processar pagamento
- Exceções não tratadas
- Falhas de validação crítica

```php
Logger::error('Falha ao processar pagamento', [
    'order_id' => $orderId,
    'payment_method' => $method,
    'error' => $e->getMessage()
]);
```

---

### `Logger::warning()`
**Quando usar:** Situações inesperadas mas não críticas, ou problemas que podem ser recuperados.

**Exemplos:**
- Cache inválido (mas pode ser recriado)
- Dados faltando mas com fallback
- Validações que falharam mas não quebram o fluxo

```php
Logger::warning('Cache inválido, recriando', [
    'restaurant_id' => $restaurantId,
    'cache_key' => $key
]);
```

---

### `Logger::info()`
**Quando usar:** Ações importantes para auditoria e rastreamento.

**Exemplos:**
- Pedido criado
- Mesa fechada
- Pagamento processado
- Status alterado

```php
Logger::info('Pedido criado com sucesso', [
    'order_id' => $orderId,
    'restaurant_id' => $restaurantId,
    'total' => $total,
    'order_type' => $type
]);
```

---

### `Logger::debug()`
**Quando usar:** Informações detalhadas apenas em desenvolvimento.

**IMPORTANTE:** Este nível **NÃO** loga em produção (verifica `APP_ENV`).

**Exemplos:**
- Valores de variáveis durante processamento
- Estados intermediários
- Informações de depuração

```php
Logger::debug('Processando pedido', [
    'restaurant_id' => $restaurantId,
    'cart_count' => count($cart),
    'order_type' => $orderType
]);
```

---

## 📝 Formato Padrão

### Estrutura de Contexto

Sempre inclua contexto relevante:

```php
Logger::info('Mensagem descritiva', [
    'restaurant_id' => $restaurantId,  // Sempre incluir se disponível
    'order_id' => $orderId,             // Se aplicável
    'user_id' => $userId,               // Se aplicável
    // ... outros campos relevantes
]);
```

### Convenções de Mensagem

- **Use prefixos:** `[MESA]`, `[COMANDA]`, `[DELIVERY]`, `[BALCAO]`
- **Seja descritivo:** "Pedido criado" em vez de "OK"
- **Inclua IDs:** Sempre mencione IDs relevantes na mensagem

**Bom:**
```php
Logger::info('[MESA] Conta aberta: Mesa #5, Pedido #123', [
    'restaurant_id' => 8,
    'order_id' => 123,
    'table_id' => 5
]);
```

**Ruim:**
```php
error_log('OK'); // Sem contexto, sem estrutura
```

---

## 🚫 O Que NÃO Fazer

### ❌ Não use `error_log()` diretamente
```php
// ❌ ERRADO
error_log('Erro ao processar');

// ✅ CORRETO
Logger::error('Erro ao processar', ['context' => 'value']);
```

### ❌ Não use `file_put_contents()` para logs
```php
// ❌ ERRADO
file_put_contents('debug.log', $data);

// ✅ CORRETO
Logger::debug('Debug info', $data);
```

### ❌ Não logue informações sensíveis
```php
// ❌ ERRADO
Logger::info('Login', ['password' => $password]);

// ✅ CORRETO
Logger::info('Login realizado', ['user_id' => $userId]);
```

### ❌ Não logue em loops sem controle
```php
// ❌ ERRADO (pode gerar milhares de logs)
foreach ($items as $item) {
    Logger::debug('Processando item', ['item_id' => $item['id']]);
}

// ✅ CORRETO (log resumido)
Logger::debug('Processando itens', [
    'count' => count($items),
    'item_ids' => array_column($items, 'id')
]);
```

---

## 📂 Localização dos Logs

Os logs são salvos em:
```
logs/YYYY-MM-DD.log
```

**Exemplo:** `logs/2026-01-26.log`

### Formato do Log

```
[2026-01-26 14:30:45] [INFO] [RID:8] [MESA] Conta aberta: Mesa #5, Pedido #123 {"order_id":123,"table_id":5}
```

**Estrutura:**
- `[TIMESTAMP]` - Data e hora
- `[LEVEL]` - Nível do log (ERROR, WARNING, INFO, DEBUG)
- `[RID:X]` - Restaurant ID (se disponível)
- `Mensagem` - Mensagem descritiva
- `{JSON}` - Contexto adicional em JSON

---

## 🔧 Uso em Actions

### Exemplo: Abrir Mesa

```php
use App\Core\Logger;
use App\Traits\OrderCreationTrait;

class OpenMesaAction
{
    use OrderCreationTrait;

    public function execute(int $restaurantId, array $data): array
    {
        try {
            // ... lógica ...
            
            $this->logOrderCreated('MESA', $orderId, [
                'restaurant_id' => $restaurantId,
                'table_id' => $tableId,
                'table_number' => $mesa['number'],
                'total' => $total
            ]);

            return ['order_id' => $orderId, ...];
            
        } catch (\Throwable $e) {
            $this->logOrderError('MESA', 'abrir', $e, [
                'restaurant_id' => $restaurantId,
                'table_id' => $tableId
            ]);
            throw new RuntimeException('Erro ao abrir mesa: ' . $e->getMessage());
        }
    }
}
```

---

## 🧹 Limpeza de Logs

Os logs são mantidos por **30 dias** por padrão.

Para limpar manualmente:
```php
Logger::cleanup(30); // Remove logs com mais de 30 dias
```

**Recomendação:** Configure um cron job para limpeza automática:
```bash
# Executar diariamente às 2h da manhã
0 2 * * * php /path/to/cleanup_logs.php
```

---

## 📊 Monitoramento

### Logs Importantes para Monitorar

1. **ERROR** - Todos os erros devem ser investigados
2. **WARNING** - Revisar periodicamente
3. **INFO** - Para auditoria e rastreamento

### Ferramentas Recomendadas

- **Desenvolvimento:** Leia diretamente os arquivos `.log`
- **Produção:** Use ferramentas como:
  - `tail -f logs/$(date +%Y-%m-%d).log`
  - Log aggregators (ELK, Graylog, etc)

---

## ✅ Checklist de Boas Práticas

- [ ] Use `Logger` em vez de `error_log()`
- [ ] Inclua `restaurant_id` sempre que disponível
- [ ] Use níveis apropriados (ERROR, WARNING, INFO, DEBUG)
- [ ] Adicione contexto relevante
- [ ] Use prefixos para identificar módulos (`[MESA]`, `[COMANDA]`)
- [ ] Não logue informações sensíveis
- [ ] DEBUG apenas em desenvolvimento
- [ ] Mensagens descritivas e claras

---

**Última atualização:** 26/01/2026
