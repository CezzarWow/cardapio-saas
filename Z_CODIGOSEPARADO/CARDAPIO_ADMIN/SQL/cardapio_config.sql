📘 CORREÇÕES / MELHORIAS FUTURAS DO SISTEMA

(para anotações – NÃO prioridade imediata)

🟢 NÍVEL A — QUANDO O PRODUTO CRESCER (USO INTENSO)
A1. Centralizar regras críticas de negócio

Problema futuro:
Regras como:

Caixa aberto

Pedido finalizável

Mesa disponível

continuam espalhadas.

Correção futura:
Criar helpers ou classes simples de domínio, por exemplo:

PedidoRules

CaixaRules

📌 Não é refatorar tudo.
📌 É não duplicar regra crítica.

A2. ENUMs ou tabelas de domínio para status

Hoje:

status como string funciona

Futuro:

Relatórios

Filtros

Integrações

Correção futura:

ENUM no banco ou

Tabelas de domínio (order_statuses, etc.)

📌 Só quando o domínio estabilizar.

🟡 NÍVEL B — QUANDO TIVER MAIS OPERADORES / ERROS HUMANOS
B1. Confirmações fortes para ações críticas

Hoje:

Cancelar pedido

Fechar caixa

Estornar valores

Correção futura:

Confirmação em duas etapas

Campo “motivo” obrigatório

Log detalhado da ação

📌 Protege contra erro humano e fraude.

B2. Auditoria de ações administrativas

Correção futura:
Criar tabela tipo:

admin_actions_log

Gravar:

Quem fez

O quê

Quando

Antes/depois (se aplicável)

📌 Fundamental quando houver disputa ou erro grave.

🟠 NÍVEL C — QUANDO O SISTEMA FICAR GRANDE
C1. Separar leitura de escrita (conceitual)

Hoje:

Controllers fazem tudo

Futuro:

Fluxos mais complexos

Correção futura:

Métodos de leitura (queries)

Métodos de ação (commands)

📌 Pode ser feito dentro do mesmo controller inicialmente.

C2. Padronizar respostas JSON

Hoje:

Cada controller responde do seu jeito

Correção futura:

Padrão único:

{
  "success": true,
  "message": "",
  "data": {}
}


📌 Facilita frontend e integrações.

🔵 NÍVEL D — QUANDO VIRAR SAAS “DE VERDADE”
D1. Isolar contexto do tenant

Hoje:

$_SESSION['loja_ativa_id']

Futuro:

Subdomínios

APIs

Webhooks

Correção futura:

Classe TenantContext

Validação única de escopo

📌 Só quando precisar.

D2. Logs estruturados (JSON)

Hoje:

Log em texto está perfeito

Futuro:

Volume alto

Análise automática

Correção futura:

Logs em JSON

Separar INFO / ERROR

📌 Não agora.

🔴 NÍVEL E — SOMENTE SE NECESSÁRIO (CUIDADO)
E1. Refatoração em Services / Repositories

⚠️ Alto risco se feito cedo demais

Só faz sentido se:

Muitos devs

Muitos módulos

Código difícil de entender

📌 Hoje: não fazer
📌 Amanhã: talvez

E2. Filas / Processamento assíncrono

Só quando:

Integração externa pesada

Volume alto

Gargalo real

📌 Antes disso = over-engineering.

🧠 RESUMO PARA SUAS ANOTAÇÕES
Fazer agora?

❌ Não.

Esquecer?

❌ Também não.

O correto é:

✔ Ter consciência
✔ Saber quando aplicar
✔ Não antecipar complexidade

📌 FRASE-CHAVE PARA GUIAR O FUTURO DO SISTEMA

“Só introduzir complexidade quando o problema for real, recorrente e mensurável.”