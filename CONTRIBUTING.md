# Guia de Contribuição

Obrigado por considerar contribuir com o Cardápio SaaS. Este documento descreve padrões e fluxos para manter o código consistente.

---

## 1. Ambiente de desenvolvimento

- **PHP** 8.1+ (recomendado 8.2)
- **MySQL** 8.0
- **Composer** para dependências PHP
- **Node.js** (opcional) para build de assets (Tailwind, bundles)

```bash
composer install
cp .env.example .env
# Edite .env com credenciais do banco e APP_ENV=development
```

---

## 2. Padrões de código

- **PHP**: PSR-4 autoload, PSR-12–style. O projeto usa **PHP-CS-Fixer**:
  ```bash
  composer run cs:fix
  ```
- **Nomes**: camelCase para métodos/variáveis, PascalCase para classes.
- **Controllers**: devem permanecer finos; lógica em Services, validação em Validators.
- **Repositories**: apenas acesso a dados (SQL, QueryBuilder, cache). Sem regras de negócio.
- **Logs**: use `App\Core\Logger::error()`, `Logger::info()`, etc. Evite `error_log()` e `file_put_contents()` para logs.

---

## 3. Testes

- Escreva testes em `tests/Unit/` ou `tests/Integration/`.
- Rode a suíte antes de commitar:
  ```bash
  composer run test
  # ou
  vendor/bin/phpunit
  ```
- Nomenclatura: `*Test.php` para classes, métodos `test*` ou anotações `@test`.

---

## 4. Commits e fluxo

- Mensagens claras e objetivas (ex.: `feat: paginação em vendas`, `fix: CSRF na rota X`).
- Antes de abrir PR/merge:
  - `composer run test`
  - `composer run cs:fix` (ou `composer run lint` se existir)

---

## 5. Onde colocar cada tipo de alteração

| Alteração | Onde |
|-----------|------|
| Nova rota | `public/index.php` (Router::add ou pattern) |
| Nova regra de negócio | `app/Services/` |
| Novo acesso a dados | `app/Repositories/` |
| Nova validação de entrada | `app/Validators/` |
| Reação a evento (ex.: cache) | `app/Events/Listeners/` e registro em `app/Config/dependencies.php` |
| Novo endpoint de API | Controller em `app/Controllers/Api/`, rota em `index.php` |

---

## 6. Documentação

- **Arquitetura**: `docs/ARQUITETURA.md`
- **Status do plano de melhorias**: `docs/PLANO_MELHORIAS_STATUS.md`
- **API**: `docs/openapi.yaml` (OpenAPI 3)
- **CSRF**: `docs/CSRF_EXCEPTIONS.md`
- **Logging**: `docs/LOGGING.md`

Ao adicionar comportamentos relevantes (ex.: nova exceção CSRF, novo evento), atualize a documentação correspondente.

---

## 7. Dúvidas

Abra uma issue ou entre em contato com a equipe mantenedora para alinhar mudanças maiores (novas dependências, quebra de contrato de API, etc.).
