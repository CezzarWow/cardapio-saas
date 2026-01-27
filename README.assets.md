Build de assets (JS/CSS)

O build oficial usa Node.js com `esbuild` + `clean-css` e garante um manifesto único em `public/dist/assets-manifest.json`.

1) Instale dependências e rode o build completo:

```bash
npm install
npm run build
```

Esse comando:
- gera `public/dist/cardapio.[hash].js` (bundle principal do cardápio).
- gera `public/dist/cardapio.[hash].css`.
- escreve `public/dist/assets-manifest.json`.
- executa `node build-bundles.js` (pacotes SPA/PDV) e `npm run build:css` (Tailwind).

2) Para rodar apenas os bundles (sem reprocessar o bundle principal):

```bash
npm run bundle
```

A aplicação lê `\App\Helpers\ViewHelper::asset('cardapio.js')` e `asset('cardapio.css')` para incluir os nomes com hash do manifesto e garantir cache-busting harmonizado.

### Verificação de Container

Para garantir que o container registra todos os bindings sem depender de fallback, há um script auxiliar:

```bash
php scripts/check-container-bindings.php
```

Ele verifica controllers/services/repositories/validators e lista apenas classes sem binding definido (atualmente zero com as exceções planejadas). Rode antes de subir mudanças de arquitetura.
