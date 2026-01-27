/**
 * Build Bundles Script
 * Concatena JS files para reduzir requests HTTP
 * 
 * Uso: node build-bundles.js
 */

const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

// Diretório base
const PUBLIC_JS = './public/js';
const BUNDLES_DIR = './public/js/bundles';

// Garante que o diretório de bundles existe
if (!fs.existsSync(BUNDLES_DIR)) {
    fs.mkdirSync(BUNDLES_DIR, { recursive: true });
}

/**
 * PDV Bundle - 23 scripts na ordem correta de dependência
 */
const pdvScripts = [
    // Core: State e Carrinho
    'pdv/state.js',
    'pdv/pdv-extras.js',
    'pdv/pdv-cart.js',

    // Tables: Mesas e Clientes
    'pdv/tables.js',
    'pdv/tables-mesa.js',
    'pdv/tables-cliente.js',
    'pdv/tables-client-modal.js',

    // Ações e Ficha
    'pdv/order-actions.js',
    'pdv/ficha.js',

    // Checkout: Módulos de Pagamento (ordem de dependência)
    'pdv/checkout/helpers.js',
    'pdv/checkout/state.js',
    'pdv/checkout/totals.js',
    'pdv/checkout/ui.js',
    'pdv/checkout/payments.js',
    'pdv/checkout/services/checkout-service.js',
    'pdv/checkout/services/checkout-validator.js',
    'pdv/checkout/adjust.js',
    'pdv/checkout/submit.js',
    'pdv/checkout/orderType.js',
    'pdv/checkout/retirada.js',
    'pdv/checkout/entrega.js',
    'pdv/checkout/flow.js',
    'pdv/checkout/index.js',

    // Orquestrador Principal
    'pdv/pdv-events.js',
    'pdv/pdv-search.js',
    'pdv.js' // Na raiz do public/js
];

/**
 * Delivery Bundle
 */
const deliveryScripts = [
    'delivery/helpers.js',
    'delivery/constants.js',
    'delivery/tabs.js',
    'delivery/actions.js',
    'delivery/ui.js',
    'delivery/polling.js'
];

/**
 * Print Bundle (Shared between Delivery and PDV)
 */
const printScripts = [
    'delivery/print-helpers.js', // Helper functions
    'delivery/print-generators.js', // HTML generators for tickets
    'delivery/print-qz.js', // QZ Tray integration
    'delivery/print-modal.js', // Modal control
    'delivery/print-actions.js', // Print actions (status updates)
    'delivery/print.js' // Main Entry Point for Print Logic
];

/**
 * Mesas Bundle
 */
const mesasScripts = [
    'shared/masks.js',
    'admin/client-validator.js',
    'admin/clientes.js',
    'admin/tables-helpers.js',
    'admin/tables-crud.js',
    'admin/tables-clients.js',
    'admin/tables-paid-orders.js',
    'admin/tables-dossier.js',
    'admin/tables.js'
];

/**
 * Cardápio Admin Bundle
 */
const cardapioScripts = [
    'cardapio-admin/utils.js',
    'cardapio-admin/pix.js',
    'cardapio-admin/whatsapp.js',
    'cardapio-admin/forms.js',
    'cardapio-admin/forms-tabs.js',
    'cardapio-admin/forms-toggles.js',
    'cardapio-admin/forms-validation.js',
    'cardapio-admin/forms-delivery.js',
    'cardapio-admin/forms-cards.js',
    'cardapio-admin/combos.js',
    'cardapio-admin/combos-save.js',
    'cardapio-admin/combos-edit.js',
    'cardapio-admin/combos-helpers.js',
    'cardapio-admin/combos-ui.js',
    'cardapio-admin/featured.js',
    'cardapio-admin/featured-edit.js',
    'cardapio-admin/featured-dragdrop.js',
    'cardapio-admin/featured-tabs.js',
    'cardapio-admin/featured-categories.js',
    'cardapio-admin/index.js'
];

/**
 * Concatena arquivos em um bundle
 */
function createBundle(name, scripts) {
    console.log(`\n📦 Building ${name}...`);

    let bundle = `/* ${name} - Generated ${new Date().toISOString()} */\n\n`;
    let successCount = 0;
    let errors = [];

    for (const script of scripts) {
        const filePath = path.join(PUBLIC_JS, script);

        if (!fs.existsSync(filePath)) {
            errors.push(`  ⚠️  Not found: ${script}`);
            continue;
        }

        try {
            const content = fs.readFileSync(filePath, 'utf8');
            bundle += `\n/* ========== ${script} ========== */\n`;
            bundle += content;
            bundle += '\n';
            successCount++;
        } catch (e) {
            errors.push(`  ❌ Error reading: ${script}`);
        }
    }

    // Escreve o bundle
    const outputPath = path.join(BUNDLES_DIR, `${name}.js`);
    fs.writeFileSync(outputPath, bundle, 'utf8');

    // Estatísticas
    const sizeKB = (Buffer.byteLength(bundle, 'utf8') / 1024).toFixed(1);
    console.log(`  ✅ ${successCount}/${scripts.length} files bundled`);
    console.log(`  📁 ${outputPath} (${sizeKB} KB)`);

    if (errors.length > 0) {
        errors.forEach(e => console.log(e));
    }

    return { success: successCount === scripts.length, size: sizeKB };
}

// ============================================================
// MAIN
// ============================================================
console.log('🚀 Building SPA Bundles...');

const results = {
    pdv: createBundle('pdv-bundle', pdvScripts),
    delivery: createBundle('delivery-bundle', deliveryScripts),
    print: createBundle('print-bundle', printScripts),
    mesas: createBundle('mesas-bundle', mesasScripts),
    cardapio: createBundle('cardapio-bundle', cardapioScripts)
};

console.log('\n✨ Build complete!');
console.log('─'.repeat(40));
Object.entries(results).forEach(([name, r]) => {
    const status = r.success ? '✅' : '⚠️';
    console.log(`${status} ${name}: ${r.size} KB`);
});
