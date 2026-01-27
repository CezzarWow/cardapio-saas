# Migrations de banco (ETAPA 6)

Este diretório guarda scripts de migração versionados para o schema e alterações de dados.

## Convenção

- **Nomenclatura**: `NNN_descricao_curta.sql` (ex.: `001_create_orders.sql`, `002_add_index_orders_restaurant.sql`).
- **Ordem**: Execute em ordem numérica crescente. Um script deve ser idempotente quando possível (ex.: `CREATE TABLE IF NOT EXISTS`, `ALTER TABLE` com checagem).
- **Rollback**: Não há runner automático de rollback; em mudanças críticas, manter um `NNN_descricao_curta_down.sql` opcional com os reversos.

## Como rodar

Hoje não há CLI de migrations no projeto. Opções:

1. **Manual**: executar cada `.sql` no MySQL/MariaDB na ordem, em ambiente controlado.
2. **Script futuro**: um `scripts/run_migrations.php` pode ler este diretório, comparar com uma tabela `schema_migrations (version)`, e executar apenas os novos.

## Exemplo de migração

Arquivo `001_example_placeholder.sql` (não executa nada; só ilustra o formato):

```sql
-- 001_example_placeholder.sql
-- Descrição: placeholder para convenção de migrations.
-- Executado em: (data)

-- CREATE TABLE IF NOT EXISTS example (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   name VARCHAR(255) NOT NULL,
--   created_at DATETIME DEFAULT CURRENT_TIMESTAMP
-- );
```

## Boas práticas

- Não alterar migrations já aplicadas em produção; criar uma nova que corrige ou estende.
- Em alterações de dados em massa, usar transações e batches se necessário.
- Documentar em comentário no topo do arquivo o que a migração faz e em qual contexto foi criada.
