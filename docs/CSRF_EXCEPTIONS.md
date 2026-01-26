# CSRF Exceptions - Documentação

**Data:** 26/01/2026  
**Última atualização:** 26/01/2026

## 📋 Visão Geral

Este documento justifica todas as exceções de verificação CSRF no `CsrfMiddleware`.

**IMPORTANTE:** Exceções CSRF são um risco de segurança. Cada exceção deve ser justificada e, idealmente, removida no futuro.

---

## ✅ Rotas SEM Exceção (Protegidas)

Todas as rotas POST/PUT/DELETE devem enviar CSRF token via:
- Header: `X-CSRF-TOKEN`
- Form field: `csrf_token`
- JSON body: `csrf_token` (para requisições JSON)

**Exemplos de rotas protegidas:**
- `/admin/loja/venda/finalizar` ✅ (frontend envia token no payload)
- `/admin/loja/venda/fechar-comanda` ✅ (frontend envia token no payload)

---

## ⚠️ Exceções Atuais

### 1. `/admin/loja/reposicao/ajustar`
**Status:** ⚠️ TEMPORÁRIA  
**Justificativa:** Ajuste de estoque via SPA. Frontend pode não estar enviando token corretamente.  
**Ação:** Verificar se frontend pode enviar CSRF token. Se sim, remover exceção.

### 2. `reposicao/ajustar`
**Status:** ⚠️ TEMPORÁRIA  
**Justificativa:** Variação da rota acima (sem prefixo completo).  
**Ação:** Remover após corrigir rota principal.

### 3. `/api/order/create`
**Status:** ⚠️ LEGADO  
**Justificativa:** API legada. Considerar migrar para `/api/v1/order/create` com autenticação adequada (API keys, JWT, etc).  
**Ação:** Migrar para nova API ou implementar autenticação alternativa (não CSRF).

---

## 🔒 Boas Práticas

1. **Nunca adicionar exceções sem documentar aqui**
2. **Revisar exceções periodicamente** (a cada release)
3. **Remover exceções assim que possível**
4. **Para APIs públicas:** Usar autenticação alternativa (API keys, OAuth, JWT)

---

## 📝 Histórico de Mudanças

### 26/01/2026
- ✅ **REMOVIDO:** `/admin/loja/venda/finalizar` - Frontend já envia CSRF token
- ✅ **REMOVIDO:** `/admin/loja/venda/fechar-comanda` - Frontend já envia CSRF token
- 📝 **DOCUMENTADO:** Exceções restantes com justificativas

---

**Última revisão:** 26/01/2026
