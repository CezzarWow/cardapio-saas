# 🛡️ Plano de Blindagem - VERSÃO REVISADA

## ⚠️ IMPORTANTE: Ordem de Execução

**NÃO execute tudo de uma vez.** Siga esta ordem:

1. ✅ Feedback visual (risco zero)
2. ✅ Helper interno getCaixaAberto (risco zero)  
3. ✅ Logger (ajustado, risco zero)
4. ⚠️ CHECK constraints (APENAS após auditoria)

---

## 📋 TAREFA 1: Feedback Visual (PRIMEIRO)

**Risco: ZERO** - Pode executar sem medo.

### Ação
1. Copiar `CODIGO/messages.php` → `views/admin/panel/layout/messages.php`
2. Incluir nas views após o header

### Teste
Acessar qualquer rota com `?success=1` e verificar se aparece a mensagem.

---

## 📋 TAREFA 2: Helper getCaixaAberto (SEGUNDO)

**Risco: ZERO** - Mesmo SQL, apenas remove duplicação.

### No `OrderController.php`, adicionar:

```php
private function getCaixaAberto($conn, $restaurantId) {
    $stmt = $conn->prepare("SELECT id FROM cash_registers WHERE restaurant_id = :rid AND status = 'aberto'");
    $stmt->execute(['rid' => $restaurantId]);
    return $stmt->fetch(\PDO::FETCH_ASSOC);
}
```

### Substituir as 4 ocorrências do código duplicado pelo helper.

---

## 📋 TAREFA 3: Logger (TERCEIRO)

**Risco: ZERO** - Versão corrigida, não depende de $_SESSION.

### Ação
1. Copiar `CODIGO/Logger.php` → `app/Core/Logger.php`
2. Criar pasta `logs/` na raiz
3. Adicionar `logs/` no `.gitignore`

### Uso nos controllers
```php
use App\Core\Logger;

// O restaurant_id vem no context, NÃO da sessão
Logger::info('Caixa aberto', [
    'restaurant_id' => $_SESSION['loja_ativa_id'],
    'saldo' => $saldo
]);

Logger::error('Erro ao salvar', [
    'restaurant_id' => $_SESSION['loja_ativa_id'],
    'error' => $e->getMessage()
]);
```

---

## 📋 TAREFA 4: CHECK Constraints (POR ÚLTIMO)

**⚠️ RISCO SE NÃO AUDITAR ANTES**

### PASSO 1: Executar auditoria
Rodar `SQL/01_auditoria_previa.sql` e verificar se retorna registros.

**Se retornar registros inválidos:**
- Analisar caso a caso
- Corrigir manualmente (UPDATE ou DELETE)
- Documentar o que foi corrigido

### PASSO 2: Só depois, aplicar constraints
Executar `SQL/check_constraints.sql`

### PASSO 3: Testar
```sql
-- Deve FALHAR (isso é bom!)
INSERT INTO products (restaurant_id, category_id, name, price, stock) 
VALUES (1, 1, 'Teste', -10.00, 5);
```

---

## ✅ Checklist Final

- [ ] 1. messages.php copiado e testado
- [ ] 2. Helper getCaixaAberto implementado
- [ ] 3. Logger.php copiado
- [ ] 4. Pasta logs/ criada
- [ ] 5. Auditoria do banco executada
- [ ] 6. Dados inválidos corrigidos (se houver)
- [ ] 7. CHECK constraints aplicados
- [ ] 8. Teste de constraint executado

---

## Após Conclusão

Avisar que blindagem está completa.
Próximo: **Admin do Cardápio**
