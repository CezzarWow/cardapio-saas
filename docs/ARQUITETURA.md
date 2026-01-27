# Arquitetura do Cardápio SaaS

**Última atualização:** Janeiro/2026

---

## 1. Visão geral

O sistema segue um **MVC + DDD Lite**: controllers finos, lógica em services, dados em repositories. Não há framework full‑stack; o núcleo é PHP com Router próprio, Container de DI e camadas bem definidas.

```
Request → Middlewares → Router → Controller → Service → Repository → Database
                ↓           ↓          ↓           ↓
            (CSRF,     (rota →    (orquestra  (SQL, cache,
             auth,      classe     regras)     transações)
             sanitize)   método)
```

---

## 2. Estrutura de pastas (`app/`)

| Pasta | Responsabilidade |
|-------|------------------|
| **Config/** | Container, providers (Repository, Service, Controller, etc.) e `dependencies.php` como bootstrap do DI. |
| **Controllers/** | Recebem request, chamam Service/Validator, devolvem View ou JSON. Devem ser “finos”. |
| **Core/** | Router, Database (PDO singleton), Logger, Cache, View, QueryBuilder. |
| **DTO/** | Objetos de transferência de dados (ex.: OrderDTO, OrderItemDTO) para type safety. |
| **Events/** | EventDispatcher, eventos (OrderCreatedEvent, CardapioChangedEvent) e listeners (ex.: invalidação de cache). |
| **Exceptions/** | Exceções de domínio/infra (ex.: DatabaseConnectionException). |
| **Middleware/** | CsrfMiddleware, RequestSanitizerMiddleware, ThrottleMiddleware, AuthorizationMiddleware. |
| **Repositories/** | Acesso a dados: apenas SQL/QueryBuilder e cache. Sem regras de negócio. |
| **Services/** | Regras de negócio, orquestração, transações. Chamam Repositories e disparam eventos. |
| **Validators/** | Validação de entrada por domínio (OrderValidator, SalesValidator, etc.). |

---

## 3. Fluxo de uma requisição

1. **`public/index.php`**  
   Carrega `.env`, define erros por ambiente, inicia sessão, registra middlewares globais e rotas, e chama `Router::dispatch($path)`.

2. **Middlewares (ordem)**  
   Throttle → RequestSanitizer → Authorization → CSRF. Qualquer um pode interromper a requisição (ex.: 403, redirect).

3. **Router**  
   Faz match do `$path` com rotas registradas (`Router::add()` ou `Router::pattern()`), resolve controller e método no Container e invoca o método.

4. **Controller**  
   Obtém parâmetros (GET/POST/JSON), chama validators e services, e responde com `View::renderFromScope()` ou `$this->json()`.

5. **Service**  
   Contém a lógica de negócio, usa Repositories, dispara eventos (`EventDispatcher::dispatch()`), abre/fecha transações quando necessário.

6. **Repository**  
   Executa queries (SQL direto ou via `QueryBuilder`), trata cache quando fizer sentido. Retorna arrays ou DTOs (ex.: `findAsDto()`).

---

## 4. DTOs (Data Transfer Objects)

- **OrderDTO**, **OrderItemDTO**: imutáveis (readonly), com `fromArray()` e `toArray()`.
- Uso: `OrderRepository::findAsDto()` retorna `?OrderDTO`; novo código pode preferir DTOs em vez de arrays.
- Fica em `app/DTO/`.

---

## 5. Sistema de eventos

- **EventDispatcher**: `listen($eventName, callable)` e `dispatch(object $event)`.
- Eventos implementam **EventContract** (`eventName(): string`).
- Exemplos:
  - **OrderCreatedEvent**: disparado ao criar pedido (CreateOrderAction).
  - **CardapioChangedEvent**: disparado quando mudam produtos/categorias/combos; o **InvalidateCardapioCacheListener** invalida o cache do cardápio.
- Listeners são registrados em `app/Config/dependencies.php` após o registro dos providers.

---

## 6. Cache

- **Cache** (Core): abstração sobre Redis ou SimpleCache (arquivo).
- Chaves usadas no cardápio: `cardapio_index_{restaurantId}_v2`, `categories_{id}`, `config_{id}`, `hours_{id}`, `products_{id}`, `combos_{id}`, `product_additional_relations`.
- Invalidação: feita via **CardapioChangedEvent** nos repositórios de Product, Category e Combo.

---

## 7. Logging

- **Logger** (Core): `Logger::error()`, `Logger::warning()`, `Logger::info()`, `Logger::debug()`.
- Em produção deve-se usar Logger em vez de `error_log()` ou `file_put_contents()` para logs.
- Ver `docs/LOGGING.md` para convenções.

---

## 8. API e versionamento

- Rotas de API públicas estão sob **`/api/v1/`** (ex.: `/api/v1/order/create`, `/api/v1/balcao/venda`, `/api/v1/mesa/abrir`).
- A rota legada `/api/order/create` permanece como alias; o frontend usa `/api/v1/order/create`.

---

## 9. Segurança

- **CSRF**: tokens em formulários e header `X-CSRF-TOKEN`; exceções documentadas em `docs/CSRF_EXCEPTIONS.md`.
- **Sanitização**: RequestSanitizerMiddleware aplicado a todos os inputs.
- **Rate limiting**: ThrottleMiddleware por IP.
- **Sessão**: cookies HttpOnly, SameSite, opção Secure em HTTPS.
- **Erros**: `display_errors` desligado em produção; exceções tratadas por handler em `index.php`.

---

## 10. Testes

- **PHPUnit** em `tests/`: `Unit/` e `Integration/`.
- Execução: `composer run test` ou `vendor/bin/phpunit`.
- Configuração em `phpunit.xml` e bootstrap em `tests/bootstrap.php`.

---

## 11. Diagrama de camadas (resumido)

```
┌─────────────────────────────────────────────────────────┐
│  View (views/*.php)                                      │
├─────────────────────────────────────────────────────────┤
│  Controllers (Admin/*, Api/*)                            │
├─────────────────────────────────────────────────────────┤
│  Validators  │  Services (regras de negócio)             │
├──────────────┴──────────────────────────────────────────┤
│  Repositories (dados)  │  Events + Listeners             │
├────────────────────────┴────────────────────────────────┤
│  Core (Router, Database, Cache, Logger, QueryBuilder)    │
└─────────────────────────────────────────────────────────┘
```

Para mais detalhes por etapa do plano de melhorias, ver `docs/PLANO_MELHORIAS_STATUS.md`.
