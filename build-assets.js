const esbuild = require('esbuild');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

// Entrypoints (ajuste conforme necessário)
const jsEntries = [
  'public/js/cardapio/utils.js',
  'public/js/cardapio/modules/cart-state.js',
  'public/js/cardapio/modules/cart-view.js',
  'public/js/cardapio/cart.js',
  'public/js/cardapio/modals.js',
  'public/js/cardapio/modals-product.js',
  'public/js/cardapio/modules/combo-validator.js',
  'public/js/cardapio/modules/combo-view.js',
  'public/js/cardapio/modules/combo-controller.js',
  'public/js/cardapio/modals-combo.js',
  'public/js/cardapio/checkout-order.js',
  'public/js/cardapio/checkout-fields.js',
  'public/js/cardapio/checkout-modals.js',
  'public/js/cardapio/checkout.js',
  'public/js/cardapio/modules/catalog-renderer.js',
  'public/js/cardapio/modules/catalog-virtualizer.js',
  'public/js/cardapio.js'
];

const cssEntries = [
  'public/css/base.css',
  'public/css/cards.css',
  'public/css/modals/index.css',
  'public/css/form.css',
  'public/css/publico/index.css',
  'public/css/checkout.css',
  'public/css/cardapio-layout.css',
  'public/css/cardapio-badges.css'
];

const outDir = path.resolve(__dirname, 'public', 'dist');
if (!fs.existsSync(outDir)) fs.mkdirSync(outDir, { recursive: true });

function hashContent(content) {
  return crypto.createHash('md5').update(content).digest('hex').slice(0, 10);
}

(async () => {
  try {
    // Bundle JS
    const jsBundle = await esbuild.build({
      entryPoints: jsEntries.reduce((acc, cur, idx) => ({ ...acc, ['entry' + idx]: cur }), {}),
      bundle: true,
      minify: true,
      sourcemap: false,
      write: false,
      outdir: outDir,
      format: 'iife',
      target: ['chrome58', 'firefox57', 'safari11']
    });

    const jsOutputs = jsBundle.outputFiles
      .filter(f => f.path.endsWith('.js'))
      .sort((a, b) => {
        const aName = path.basename(a.path);
        const bName = path.basename(b.path);
        const aMatch = aName.match(/^entry(\d+)\.js$/);
        const bMatch = bName.match(/^entry(\d+)\.js$/);
        const aIndex = aMatch ? parseInt(aMatch[1], 10) : Number.MAX_SAFE_INTEGER;
        const bIndex = bMatch ? parseInt(bMatch[1], 10) : Number.MAX_SAFE_INTEGER;
        return aIndex - bIndex;
      });
    const combinedJs = jsOutputs.map(f => f.text).join('\n');
    const jsHash = hashContent(combinedJs);
    const jsFileName = `cardapio.${jsHash}.js`;
    fs.writeFileSync(path.join(outDir, jsFileName), combinedJs);

    // Bundle CSS - simples concat + minify using clean-css
    const CleanCSS = require('clean-css');
    let combinedCss = '';
    cssEntries.forEach(p => {
      const fp = path.resolve(__dirname, p);
      if (fs.existsSync(fp)) {
        combinedCss += fs.readFileSync(fp, 'utf8') + '\n';
      }
    });
    const minifiedCss = new CleanCSS().minify(combinedCss).styles;
    const cssHash = hashContent(minifiedCss);
    const cssFileName = `cardapio.${cssHash}.css`;
    fs.writeFileSync(path.join(outDir, cssFileName), minifiedCss);

    // Manifest
    const manifest = {
      'cardapio.js': '/dist/' + jsFileName,
      'cardapio.css': '/dist/' + cssFileName
    };
    fs.writeFileSync(path.join(outDir, 'assets-manifest.json'), JSON.stringify(manifest, null, 2));

    console.log('Build OK:', manifest);
  } catch (err) {
    console.error('Build error', err);
    process.exit(1);
  }
})();
