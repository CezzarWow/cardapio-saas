/**
 * SPA Configuration
 * Definições estáticas de rotas e seções.
 */

// Garantir que window.BASE_URL está definido para uso em outros scripts
if (typeof BASE_URL !== 'undefined' && typeof window.BASE_URL === 'undefined') {
    window.BASE_URL = BASE_URL;
}

const SpaConfig = {
    sections: {
        'balcao': {
            url: '/admin/loja/pdv',
            partial: '/admin/spa/partial/balcao',
            skeleton: 'grid',
            hasPolling: false
        },
        'mesas': {
            url: '/admin/loja/mesas',
            partial: '/admin/spa/partial/mesas',
            skeleton: 'grid',
            hasPolling: false
        },
        'delivery': {
            url: '/admin/loja/delivery',
            partial: '/admin/spa/partial/delivery',
            skeleton: 'kanban',
            hasPolling: true
        },
        'cardapio': {
            url: '/admin/loja/cardapio',
            partial: '/admin/spa/partial/cardapio',
            skeleton: 'tabs',
            hasPolling: false
        },
        'estoque': {
            url: '/admin/loja/catalogo',
            partial: '/admin/spa/partial/estoque',
            skeleton: 'grid',
            hasPolling: false
        },
        'caixa': {
            url: '/admin/loja/caixa',
            partial: '/admin/spa/partial/caixa',
            skeleton: 'table',
            hasPolling: false
        }
    }
};
