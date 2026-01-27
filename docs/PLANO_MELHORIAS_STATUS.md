# Status do plano de melhorias

**Atualizado:** 27/01/2026

---

## ETAPA 4: Arquitetura e estrutura

| Item | Status | Observação |
|------|--------|------------|
| **Implementar DTOs (em vez de arrays)** | 🟡 Parcial | OrderDTO e OrderItemDTO criados; `findAsDto()` no OrderRepository. Migração gradual: ainda há muito retorno em array. Falta usar DTOs em mais serviços/repos. |
| **Criar sistema de eventos** | 🟡 Parcial | EventDispatcher + EventContract + OrderCreatedEvent implementados; dispatch no CreateOrderAction. Falta: mais eventos (OrderPaid, OrderDelivered), listeners de exemplo (ex.: invalidação de cache). |
| **Query Builder simples** | ✅ Feito | `App\Core\QueryBuilder` (select/from/join/where/groupBy/orderBy/limit/offset/get); uso piloto em `OrderRepository::findAllWithDetailsPaginated`. |
| **Padronizar versionamento de API** | ✅ Feito | Rota `/api/v1/order/create` registrada; frontend (checkout-order.js) atualizado. `/api/order/create` mantido como legado. |

---

## ETAPA 5: Performance e otimização

| Item | Status | Observação |
|------|--------|------------|
| **Cache com invalidação automática** | 🟢 Feito | ProductRepository, CategoryRepository e ComboRepository disparam `CardapioChangedEvent`; o listener invalida todas as chaves de cardápio. Repos de Config e Adicionais ainda usam forget manual (opcional migrar depois). |
| **Code splitting no frontend** | 🔴 Pendente | Carregar bundles por rota/SPA em vez de um bundle único onde fizer sentido. |
| **Otimizar queries do banco** | 🔴 Pendente | Revisar N+1, índices, consultas pesadas em listagens. |
| **Paginação em listagens** | 🟢 Feito | Vendas paginadas: `OrderRepository::findAllWithDetailsPaginated`, `SalesService::listOrdersPaginated`, `SalesController` com ?page= e ?per_page=; view com links Anterior/Próxima. |

---

## ETAPA 6: Documentação e padrões

| Item | Status | Observação |
|------|--------|------------|
| **Documentar API (Swagger)** | ✅ Feito | `docs/openapi.yaml` (OpenAPI 3.0) com todos os endpoints `/api/v1/`. |
| **Documentar arquitetura** | ✅ Feito | `docs/ARQUITETURA.md` (camadas, fluxo, DTOs, eventos, cache, segurança). |
| **Implementar migrations de banco** | 🟡 Estrutura | `database/migrations/` com README e convenção; `001_example_placeholder.sql` de exemplo. Falta runner automático. |
| **Guias de contribuição** | ✅ Feito | `CONTRIBUTING.md` (ambiente, padrões, testes, commits, onde alterar). |

---

## Resumo visual

```
ETAPA 4  ██████████  100%   (DTOs, eventos, API v1, Query Builder)
ETAPA 5  ██████░░░░  ~50%   (cache invalidation, paginação em vendas; falta code split, otimizar queries)
ETAPA 6  ████████░░  ~80%   (Swagger, ARQUITETURA, CONTRIBUTING, migrations dir; falta runner de migrations)
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
