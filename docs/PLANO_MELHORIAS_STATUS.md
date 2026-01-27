# Status do plano de melhorias

**Atualizado:** 27/01/2026

---

## ETAPA 4: Arquitetura e estrutura

| Item | Status | Observação |
|------|--------|------------|
| **Implementar DTOs (em vez de arrays)** | 🟡 Parcial | OrderDTO e OrderItemDTO criados; `findAsDto()` no OrderRepository. Migração gradual: ainda há muito retorno em array. Falta usar DTOs em mais serviços/repos. |
| **Criar sistema de eventos** | 🟡 Parcial | EventDispatcher + EventContract + OrderCreatedEvent implementados; dispatch no CreateOrderAction. Falta: mais eventos (OrderPaid, OrderDelivered), listeners de exemplo (ex.: invalidação de cache). |
| **Query Builder simples** | 🔴 Pendente | Repositórios ainda usam SQL escrito à mão. Falta classe/helper para montar SELECT/WHERE/ORDER de forma fluente. |
| **Padronizar versionamento de API** | ✅ Feito | Rota `/api/v1/order/create` registrada; frontend (checkout-order.js) atualizado. `/api/order/create` mantido como legado. |

---

## ETAPA 5: Performance e otimização

| Item | Status | Observação |
|------|--------|------------|
| **Cache com invalidação automática** | 🟢 Feito | ProductRepository, CategoryRepository e ComboRepository disparam `CardapioChangedEvent`; o listener invalida todas as chaves de cardápio. Repos de Config e Adicionais ainda usam forget manual (opcional migrar depois). |
| **Code splitting no frontend** | 🔴 Pendente | Carregar bundles por rota/SPA em vez de um bundle único onde fizer sentido. |
| **Otimizar queries do banco** | 🔴 Pendente | Revisar N+1, índices, consultas pesadas em listagens. |
| **Paginação em listagens** | 🔴 Pendente | Pedidos, vendas, produtos, etc. retornarem páginas (limit/offset ou cursor) em vez de listas completas. |

---

## ETAPA 6: Documentação e padrões

| Item | Status | Observação |
|------|--------|------------|
| **Documentar API (Swagger)** | 🔴 Pendente | OpenAPI/Swagger para os endpoints `/api/v1/...` (e futuros). |
| **Documentar arquitetura** | 🔴 Pendente | Doc de pastas, fluxo request→router→controller→service→repository, DTOs, eventos. |
| **Implementar migrations de banco** | 🔴 Pendente | Scripts versionados (ex.: PHP ou SQL numerados) para criar/alterar tabelas. |
| **Guias de contribuição** | 🔴 Pendente | CONTRIBUTING.md com padrões de código, como rodar testes, como propor mudanças. |

---

## Resumo visual

```
ETAPA 4  █████████░  ~70%   (DTOs + eventos + API v1; falta Query Builder)
ETAPA 5  ███░░░░░░░  ~25%   (cache com invalidação por eventos em Produto/Categoria/Combo; falta code split, queries, paginação)
ETAPA 6  ░░░░░░░░░░    0%   (tudo pendente)
```

---

## Ordem sugerida para continuar

1. **Curto prazo (Etapa 4)**
   - ~~Padronizar API em `/api/v1/...`~~ ✅ Feito.
   - Query Builder simples e usar em 1–2 repositórios piloto.

2. **Médio prazo (Etapa 5)**
   - ~~Cache com invalidação via eventos (cardápio)~~ ✅ Feito (Product, Category, Combo).
   - Paginação em pelo menos uma listagem crítica (ex.: vendas/pedidos).

3. **Quando estabilizar (Etapa 6)**
   - Migrations.
   - Swagger da API.
   - Doc de arquitetura e CONTRIBUTING.

Se quiser, o próximo passo prático pode ser: **(A) API 100% v1** e **(B) Query Builder mínimo**, ou **(C)** um item da Etapa 5 (ex.: invalidação de cache).
