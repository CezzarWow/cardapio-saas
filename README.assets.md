Build de assets (JS/CSS)

Opções de build:

1) Com Node.js (recomendado, mais potente):

```bash
npm install
npm run build
```

2) Sem Node.js — usando o builder PHP (usa PHP CLI):

```bash
php scripts/build_assets_php.php
```

O comando gerará:
- `public/dist/cardapio.[hash].js`
- `public/dist/cardapio.[hash].css`
- `public/dist/assets-manifest.json`

A aplicação usa `\App\Helpers\ViewHelper::asset('cardapio.js')` e `asset('cardapio.css')` para referenciar os arquivos gerados.
