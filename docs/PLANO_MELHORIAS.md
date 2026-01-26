# 🚀 Plano de Melhorias - Cardápio SaaS

**Versão:** 1.0  
**Data:** 26/01/2026  
**Status:** Planejamento

---

## 📋 **VISÃO GERAL**

Este documento organiza todas as melhorias identificadas em **etapas práticas e executáveis**, priorizadas por:
- **Impacto** (Segurança > Performance > Manutenibilidade)
- **Esforço** (Rápido < Médio < Longo)
- **Dependências** (O que precisa vir antes)

---

## 🎯 **ETAPA 1: PRODUCTION READINESS (CRÍTICO)**
**Prazo:** 1-2 semanas  
**Prioridade:** 🔴 ALTA  
**Objetivo:** Sistema seguro para produção

### 1.1 Configuração de Ambiente
- [ ] **1.1.1** Criar variável `APP_ENV` no `.env` (development/production)
- [ ] **1.1.2** Ajustar `public/index.php` para desabilitar `display_errors` em produção
- [ ] **1.1.3** Configurar `error_reporting` baseado em ambiente
- [ ] **1.1.4** Criar `.env.example` atualizado com todas as variáveis

**Arquivos:**
- `public/index.php`
- `.env.example`

**Estimativa:** 2 horas

---

### 1.2 Tratamento de Erros
- [ ] **1.2.1** Substituir `die()` em `Database::connect()` por exceção customizada
- [ ] **1.2.2** Criar `App\Exceptions\DatabaseConnectionException`
- [ ] **1.2.3** Implementar handler global de exceções (opcional, mas recomendado)
- [ ] **1.2.4** Atualizar testes para verificar exceções

**Arquivos:**
- `app/Core/Database.php`
- `app/Exceptions/DatabaseConnectionException.php` (novo)
- `tests/Unit/DatabaseTest.php` (novo)

**Estimativa:** 4 horas

---

### 1.3 Segurança CSRF
- [ ] **1.3.1** Auditar todas as exceções CSRF em `CsrfMiddleware`
- [ ] **1.3.2** Remover exceção de `/admin/loja/venda/finalizar` (rota crítica)
- [ ] **1.3.3** Implementar validação alternativa para rotas que realmente precisam (ex: API tokens)
- [ ] **1.3.4** Documentar por que cada exceção existe (se mantida)
- [ ] **1.3.5** Testar todas as rotas protegidas

**Arquivos:**
- `app/Middleware/CsrfMiddleware.php`
- `docs/CSRF_EXCEPTIONS.md` (novo)

**Estimativa:** 6 horas

---

### 1.4 Logging e Debug
- [ ] **1.4.1** Remover `file_put_contents()` hardcoded de `CreateOrderAction`
- [ ] **1.4.2** Substituir por `Logger::debug()` com verificação de ambiente
- [ ] **1.4.3** Auditar código para outros logs hardcoded
- [ ] **1.4.4** Padronizar uso do `Logger` em todo o código
- [ ] **1.4.5** Configurar níveis de log por ambiente (DEBUG em dev, ERROR em prod)

**Arquivos:**
- `app/Services/Order/CreateOrderAction.php`
- `app/Core/Logger.php` (melhorar)
- Buscar por `file_put_contents` e `error_log` em todo o projeto

**Estimativa:** 8 horas

---

### 1.5 Validação de Autorização
- [ ] **1.5.1** Criar middleware `AuthorizationMiddleware`
- [ ] **1.5.2** Verificar se usuário tem acesso ao restaurante em todas as rotas admin
- [ ] **1.5.3** Implementar método `BaseController::assertRestaurantAccess()`
- [ ] **1.5.4** Adicionar validação em controllers críticos (Order, Delivery, etc)

**Arquivos:**
- `app/Middleware/AuthorizationMiddleware.php` (novo)
- `app/Controllers/Admin/BaseController.php`
- Controllers que acessam `restaurant_id`

**Estimativa:** 12 horas

---

**TOTAL ETAPA 1:** ~32 horas (4 dias úteis)

---

## 🔧 **ETAPA 2: REFATORAÇÃO E QUALIDADE (IMPORTANTE)**
**Prazo:** 2-3 semanas  
**Prioridade:** 🟡 MÉDIA  
**Objetivo:** Reduzir dívida técnica e melhorar manutenibilidade

### 2.1 Refatoração de CreateOrderAction
- [ ] **2.1.1** Analisar complexidade de `CreateOrderAction::execute()` (300+ linhas)
- [ ] **2.1.2** Extrair método `normalizeOrderType()`
- [ ] **2.1.3** Extrair método `calculateTotals()`
- [ ] **2.1.4** Extrair método `determineOrderStatus()`
- [ ] **2.1.5** Extrair método `handleExistingOrder()`
- [ ] **2.1.6** Extrair método `createNewOrder()`
- [ ] **2.1.7** Atualizar testes após refatoração

**Arquivos:**
- `app/Services/Order/CreateOrderAction.php`
- `tests/Unit/CreateOrderActionTest.php`

**Estimativa:** 16 horas

---

### 2.2 Eliminação de Duplicação
- [ ] **2.2.1** Identificar código duplicado entre Actions (Mesa, Comanda, Delivery)
- [ ] **2.2.2** Criar trait `OrderCreationTrait` com métodos comuns
- [ ] **2.2.3** Extrair lógica de cálculo de totais para `TotalCalculator` (já existe, melhorar)
- [ ] **2.2.4** Extrair lógica de baixa de estoque para método reutilizável
- [ ] **2.2.5** Refatorar validações similares em Validators

**Arquivos:**
- `app/Services/Order/Flows/*/`
- `app/Services/Order/TotalCalculator.php`
- `app/Traits/OrderCreationTrait.php` (novo)

**Estimativa:** 20 horas

---

### 2.3 Padronização de Logging
- [ ] **2.3.1** Criar guia de uso do Logger (`docs/LOGGING.md`)
- [ ] **2.3.2** Substituir todos os `error_log()` por `Logger::error()`
- [ ] **2.3.3** Substituir todos os `file_put_contents()` de log por `Logger`
- [ ] **2.3.4** Adicionar contexto estruturado aos logs (restaurant_id, order_id, etc)
- [ ] **2.3.5** Implementar níveis de log adequados (DEBUG, INFO, WARNING, ERROR)

**Arquivos:**
- `app/Core/Logger.php`
- `docs/LOGGING.md` (novo)
- Buscar por `error_log` e `file_put_contents` em todo o projeto

**Estimativa:** 12 horas

---

### 2.4 Type Safety e Documentação
- [ ] **2.4.1** Adicionar PHPDoc completo em todos os métodos públicos
- [ ] **2.4.2** Adicionar type hints de retorno onde faltam
- [ ] **2.4.3** Criar tipos de retorno para arrays complexos (ex: `@return array{id: int, name: string}`)
- [ ] **2.4.4** Validar com PHPStan nível 5 (opcional, mas recomendado)

**Arquivos:**
- Todos os Services, Repositories, Controllers

**Estimativa:** 16 horas

---

**TOTAL ETAPA 2:** ~64 horas (8 dias úteis)

---

## 🧪 **ETAPA 3: TESTES E CONFIABILIDADE (IMPORTANTE)**
**Prazo:** 2-3 semanas  
**Prioridade:** 🟡 MÉDIA  
**Objetivo:** Garantir qualidade e prevenir regressões

### 3.1 Expansão de Testes Unitários
- [ ] **3.1.1** Testar todos os Validators (cobertura 100%)
- [ ] **3.1.2** Testar todos os Repositories (métodos críticos)
- [ ] **3.1.3** Testar Services de negócio (Order, Payment, Stock)
- [ ] **3.1.4** Testar Middlewares (CSRF, Sanitizer, Throttle)
- [ ] **3.1.5** Configurar cobertura mínima de 70% (phpunit.xml)

**Arquivos:**
- `tests/Unit/`
- `phpunit.xml`

**Estimativa:** 24 horas

---

### 3.2 Testes de Integração
- [ ] **3.2.1** Testar fluxo completo de criação de pedido (Balcão)
- [ ] **3.2.2** Testar fluxo completo de criação de pedido (Mesa)
- [ ] **3.2.3** Testar fluxo completo de criação de pedido (Comanda)
- [ ] **3.2.4** Testar fluxo completo de criação de pedido (Delivery)
- [ ] **3.2.5** Testar transições de status de pedido
- [ ] **3.2.6** Testar integração com pagamentos
- [ ] **3.2.7** Testar integração com estoque

**Arquivos:**
- `tests/Integration/`

**Estimativa:** 32 horas

---

### 3.3 Testes de API
- [ ] **3.3.1** Criar testes para endpoints `/api/v1/*`
- [ ] **3.3.2** Testar autenticação/autorização
- [ ] **3.3.3** Testar validação de CSRF em APIs
- [ ] **3.3.4** Testar rate limiting
- [ ] **3.3.5** Testar sanitização de inputs

**Arquivos:**
- `tests/Integration/Api/` (novo)

**Estimativa:** 16 horas

---

### 3.4 CI/CD Básico
- [ ] **3.4.1** Configurar GitHub Actions (ou similar)
- [ ] **3.4.2** Rodar testes automaticamente em PRs
- [ ] **3.4.3** Rodar PHP-CS-Fixer automaticamente
- [ ] **3.4.4** Gerar relatório de cobertura

**Arquivos:**
- `.github/workflows/ci.yml` (novo)

**Estimativa:** 8 horas

---

**TOTAL ETAPA 3:** ~80 horas (10 dias úteis)

---

## 🏗️ **ETAPA 4: ARQUITETURA E ESTRUTURA (MELHORIAS)**
**Prazo:** 3-4 semanas  
**Prioridade:** 🟢 BAIXA  
**Objetivo:** Melhorar arquitetura e preparar para escalabilidade

### 4.1 Implementação de DTOs
- [ ] **4.1.1** Criar DTOs para Order (`OrderDTO`, `OrderItemDTO`)
- [ ] **4.1.2** Criar DTOs para Payment (`PaymentDTO`)
- [ ] **4.1.3** Criar DTOs para Client (`ClientDTO`)
- [ ] **4.1.4** Refatorar Services para usar DTOs em vez de arrays
- [ ] **4.1.5** Atualizar testes

**Arquivos:**
- `app/DTOs/` (novo diretório)
- Services e Repositories

**Estimativa:** 24 horas

---

### 4.2 Sistema de Eventos
- [ ] **4.2.1** Criar `EventDispatcher` simples
- [ ] **4.2.2** Definir eventos principais (`OrderCreated`, `OrderPaid`, `OrderCancelled`)
- [ ] **4.2.3** Implementar listeners (ex: enviar email, atualizar cache)
- [ ] **4.2.4** Refatorar código para disparar eventos em vez de chamadas diretas
- [ ] **4.2.5** Documentar sistema de eventos

**Arquivos:**
- `app/Core/EventDispatcher.php` (novo)
- `app/Events/` (novo diretório)
- `app/Listeners/` (novo diretório)

**Estimativa:** 32 horas

---

### 4.3 Query Builder Simples
- [ ] **4.3.1** Criar `QueryBuilder` básico para queries complexas
- [ ] **4.3.2** Refatorar queries mais complexas dos Repositories
- [ ] **4.3.3** Manter prepared statements e segurança
- [ ] **4.3.4** Documentar uso

**Arquivos:**
- `app/Core/QueryBuilder.php` (novo)
- Repositories com queries complexas

**Estimativa:** 20 horas

---

### 4.4 API Versioning
- [ ] **4.4.1** Padronizar todas as rotas API para `/api/v1/`
- [ ] **4.4.2** Migrar `/api/order/create` para `/api/v1/order/create`
- [ ] **4.4.3** Criar estrutura para futuras versões (`/api/v2/`)
- [ ] **4.4.4** Documentar versionamento
- [ ] **4.4.5** Atualizar frontend para usar novas rotas

**Arquivos:**
- `public/index.php` (rotas)
- Controllers de API
- Frontend JS

**Estimativa:** 12 horas

---

**TOTAL ETAPA 4:** ~88 horas (11 dias úteis)

---

## ⚡ **ETAPA 5: PERFORMANCE E OTIMIZAÇÃO (MELHORIAS)**
**Prazo:** 2-3 semanas  
**Prioridade:** 🟢 BAIXA  
**Objetivo:** Melhorar performance e experiência do usuário

### 5.1 Cache e Invalidação
- [ ] **5.1.1** Implementar invalidação automática de cache ao atualizar produtos
- [ ] **5.1.2** Implementar cache para queries frequentes (categorias, configurações)
- [ ] **5.1.3** Adicionar TTL configurável por tipo de cache
- [ ] **5.1.4** Criar sistema de tags de cache para invalidação seletiva

**Arquivos:**
- `app/Core/SimpleCache.php`
- Services que atualizam dados cacheados

**Estimativa:** 16 horas

---

### 5.2 Frontend Optimization
- [ ] **5.2.1** Analisar tamanho dos bundles JS
- [ ] **5.2.2** Implementar code splitting (lazy loading de módulos)
- [ ] **5.2.3** Minificar CSS/JS em produção
- [ ] **5.2.4** Otimizar imagens (lazy loading, WebP)
- [ ] **5.2.5** Implementar service worker para cache offline (PWA)

**Arquivos:**
- `build-bundles.js`
- `public/js/`
- `public/css/`

**Estimativa:** 24 horas

---

### 5.3 Database Optimization
- [ ] **5.3.1** Auditar queries lentas (usar EXPLAIN)
- [ ] **5.3.2** Adicionar índices faltantes
- [ ] **5.3.3** Otimizar queries com JOINs complexos
- [ ] **5.3.4** Implementar paginação em listagens grandes
- [ ] **5.3.5** Considerar read replicas para queries de leitura (futuro)

**Arquivos:**
- Repositories com queries complexas
- Scripts de migração de índices

**Estimativa:** 16 horas

---

**TOTAL ETAPA 5:** ~56 horas (7 dias úteis)

---

## 📚 **ETAPA 6: DOCUMENTAÇÃO E PADRÕES (MELHORIAS)**
**Prazo:** 1-2 semanas  
**Prioridade:** 🟢 BAIXA  
**Objetivo:** Melhorar documentação e padronização

### 6.1 Documentação de API
- [ ] **6.1.1** Criar/atualizar Swagger/OpenAPI spec
- [ ] **6.1.2** Documentar todos os endpoints `/api/v1/*`
- [ ] **6.1.3** Adicionar exemplos de request/response
- [ ] **6.1.4** Documentar autenticação e autorização
- [ ] **6.1.5** Publicar documentação (Swagger UI ou similar)

**Arquivos:**
- `docs/api/swagger.yaml` (criar/atualizar)
- `docs/API.md` (novo)

**Estimativa:** 16 horas

---

### 6.2 Documentação de Código
- [ ] **6.2.1** Documentar arquitetura geral (`docs/ARCHITECTURE.md`)
- [ ] **6.2.2** Documentar fluxos principais (Order, Payment, Delivery)
- [ ] **6.2.3** Criar guia de contribuição (`CONTRIBUTING.md`)
- [ ] **6.2.4** Documentar padrões de código (`docs/CODING_STANDARDS.md`)
- [ ] **6.2.5** Atualizar README com informações completas

**Arquivos:**
- `docs/`
- `README.md`

**Estimativa:** 12 horas

---

### 6.3 Database Migrations
- [ ] **6.3.1** Escolher ferramenta de migrations (Phinx, Doctrine Migrations, ou custom)
- [ ] **6.3.2** Criar migrations para schema atual
- [ ] **6.3.3** Documentar processo de migrations
- [ ] **6.3.4** Criar script de rollback

**Arquivos:**
- `database/migrations/` (novo)
- `docs/MIGRATIONS.md` (novo)

**Estimativa:** 16 horas

---

**TOTAL ETAPA 6:** ~44 horas (5-6 dias úteis)

---

## 📊 **RESUMO GERAL**

| Etapa | Prioridade | Prazo | Horas | Foco |
|-------|-----------|-------|-------|------|
| **1. Production Readiness** | 🔴 ALTA | 1-2 sem | 32h | Segurança e estabilidade |
| **2. Refatoração** | 🟡 MÉDIA | 2-3 sem | 64h | Qualidade de código |
| **3. Testes** | 🟡 MÉDIA | 2-3 sem | 80h | Confiabilidade |
| **4. Arquitetura** | 🟢 BAIXA | 3-4 sem | 88h | Escalabilidade |
| **5. Performance** | 🟢 BAIXA | 2-3 sem | 56h | Otimização |
| **6. Documentação** | 🟢 BAIXA | 1-2 sem | 44h | Manutenibilidade |
| **TOTAL** | | **11-17 sem** | **364h** | ~45 dias úteis |

---

## 🎯 **RECOMENDAÇÃO DE EXECUÇÃO**

### **Fase 1: Crítico (Imediato)**
Execute **ETAPA 1** completa antes de colocar em produção.

### **Fase 2: Estabilização (1-2 meses)**
Execute **ETAPA 2** e **ETAPA 3** em paralelo para melhorar qualidade.

### **Fase 3: Evolução (3-6 meses)**
Execute **ETAPA 4**, **5** e **6** conforme necessidade e recursos.

---

## 📝 **NOTAS**

- **Estimativas** são aproximadas e podem variar conforme complexidade real
- **Prioridades** podem mudar baseado em necessidades do negócio
- **Paralelização**: Algumas tarefas podem ser feitas em paralelo por desenvolvedores diferentes
- **Testes**: Sempre atualizar testes ao fazer mudanças

---

**Última atualização:** 26/01/2026
