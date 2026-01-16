# Auditoria Total e Estabilização do SPA ("Pente Fino Supremo")

Este documento define o roteiro para uma **limpeza completa e validação exaustiva** do sistema cardapio-saas pós-migração SPA. O objetivo é eliminar bugs, código morto, duplicatas, funções confusas e inconsistências visuais.

---

## 🏗️ Fase 0: Infraestrutura e Global Assets
*Objetivo: Limpar a base do sistema antes de olhar os módulos.*

### 0.1 Limpeza de CSS (Arquitetura Visual)
- [ ] **Reset & Base**: Auditar `base.css`. Remover variáveis CSS não utilizadas.
- [ ] **CSS Órfão**: Identificar arquivos CSS na pasta `public/css` que não são importados em lugar nenhum (nem no `header.php` nem dinamicamente).
- [ ] **Conflitos de Framework**: Verificar se há conflito entre Tailwind (se usado), Bootstrap (se usado) e CSS Vanilla.
- [ ] **Padronização**: Garantir que as classes `.spa-padded-container` e `.spa-content-container` sejam a ÚNICA fonte de layout macro, eliminando hacks inline.

### 0.2 Limpeza de JavaScript (Core)
- [ ] **Global Pollution**: Mapear todas as variáveis globais (`window.X`) e reduzir ao mínimo.
- [ ] **Duplicação de Libs**: Garantir que bibliotecas como `jQuery`, `SweetAlert`, `Chart.js`, `Lucide` sejam carregadas APENAS UMA VEZ.
- [ ] **Memory Leaks**: Verificar se Event Listeners globais (`document.on...`) estão vazando ou se acumulando ao navegar.

---

## 📦 Fase 1: Módulo Estoque & Adicionais
*Foco: Validação da restauração recente e limpeza profunda.*

- [ ] **Feature Adicionais**: Testar CRUD completo (Criar/Editar/Excluir Grupos e Itens/Vínculos). Confirmar que não há requisições duplas.
- [ ] **Código JS Estoque**:
    - Ler `stock-spa.js` linha a linha: simplificar funções complexas (`executeScripts` pode ser otimizado?).
    - Ler `additionals.js`: Remover lógica legada se houver.
- [ ] **Views PHP**: Remover comentários HTML comentados (`<!-- <div>...</div> -->`) que poluem o código fonte.

---

## 🏪 Fase 2: Módulo Balcão (PDV) - CRÍTICO
*Foco: Performance e Estabilidade no core do negócio.*

- [ ] **Fluxo de Venda**: 
    - Adicionar 50 itens ao carrinho (Teste de Stress visual e lógico).
    - Verificar cálculo de totais (JS vs Backend).
- [ ] **Duplicidade de Funções**: Verificar se `cart.js` e `checkout.js` compartilham lógica de cálculo que deveria estar centralizada.
- [ ] **Modais**:
    - "Observações do Pedido": Verifica se salva e recupera corretamente.
    - "Clientes": Busca Ajax está otimizada (debounce)?
- [ ] **Visual**: Alinhamento de pixels no Header e Sidebar do carrinho.

---

## 🍽️ Fase 3: Módulo Mesas
*Foco: Concorrência e Estado.*

- [ ] **Polling (Atualização)**: O script que busca status das mesas (`polling.js`) está matando o servidor? Está parando ao sair da aba?
- [ ] **Estado Visual**: Diferença clara entre Mesa Livre, Ocupada, Pagamento.
- [ ] **Bugs de CSS**: Verificar se os cards das mesas quebram em telas médias (Tablets).

---

## 🛵 Fase 4: Módulo Delivery
*Foco: Gestão de Estado Complexo (Kanban).*

- [ ] **Kanban Board**:
    - Drag & Drop funciona suavemente?
    - Ao soltar um card, a atualização de status (AJAX) tem tratamento de erro visual?
    - **Bug Crítico**: Verificar erro de "Token CSRF" relatado anteriormente.
- [ ] **Código JS**: O arquivo `delivery.js` (ou `kanban.js`) costuma ser monolítico. Verificar se precisa quebrar em módulos.

---

## ⚙️ Fase 5: Módulo Configurações (Cardápio Admin)
*Foco: Formulários e Validação.*

- [ ] **Forms**: Inputs de texto, toggles e selects estão estilizados consistentemente?
- [ ] **Validação**: Tentar salvar dados inválidos. O feedback visual é claro?
- [ ] **Upload de Imagens**: Testar upload de logo/banner. Verificar se limpa input file após upload.

---

## 🧹 Fase 6: Varredura Final (Lixo e Logs)
- [ ] **Console.log**: Remover todos os `console.log` de debug deixados para trás.
- [ ] **Arquivos Temporários**: Buscar e deletar arquivos `.bak`, `.tmp`, `.old` no projeto.
- [ ] **Comentários TODO**: Listar todos os `// TODO` e verificar se algum é crítico.

---
**Status**: Pronto para iniciar Fase 0.1 (Auditoria de CSS Global).
**Comando**: Posso começar verificando CSS órfão e a integridade do `base.css`?
