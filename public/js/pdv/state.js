/**
 * PDV STATE - Gerenciamento de Estado Global do PDV
 * Centraliza o estado da aplicação (Balcão, Mesa, Comanda, Retirada)
 */

const PDVState = (() => {
    // Estado Privado
    let state = {
        modo: 'balcao',        // balcao | mesa | comanda | retirada
        pedidoId: null,        // ID do pedido (se existir)
        mesaId: null,          // ID da mesa
        clienteId: null,       // ID do cliente
        status: 'aberto',      // aberto | pago | editando_pago
        fechandoConta: false   // true = processo de checkout iniciado
    };

    // Transições Válidas de Status
    const TRANSICOES = {
        'aberto': ['pago'],
        'pago': ['editando_pago'],
        'editando_pago': ['pago']
    };

    return {
        // Getter (Retorna cópia para imutabilidade superficial)
        getState() {
            return { ...state };
        },

        // Reset completo (Volta ao Balcão)
        reset() {
            state = {
                modo: 'balcao',
                pedidoId: null,
                mesaId: null,
                clienteId: null,
                status: 'aberto',
                fechandoConta: false
            };
            console.log('[PDVState] Resetado para Balcão');
        },

        // Atualizador Genérico (exceto status)
        set(patch) {
            const { status, ...rest } = patch;
            if (status) console.warn('[PDVState] Use mudarStatus() para alterar status.');
            state = { ...state, ...rest };

            // Console Debug (Opcional)
            // console.log('[PDVState] Updated:', state);
        },

        // Inicializador de Status (Bypass de validação - apenas no Load)
        initStatus(novoStatus) {
            if (['aberto', 'pago', 'editando_pago'].includes(novoStatus)) {
                state.status = novoStatus;
                return true;
            }
            console.error(`[PDVState] Status inicial inválido: ${novoStatus}`);
            return false;
        },

        // Transição de Status (Com validação)
        mudarStatus(novoStatus) {
            if (!TRANSICOES[state.status]?.includes(novoStatus)) {
                console.error(`[PDVState] Transição inválida: ${state.status} → ${novoStatus}`);
                return false;
            }
            state.status = novoStatus;
            console.log(`[PDVState] Status alterado: ${novoStatus}`);
            return true;
        }
    };
})();

// Expor Globalmente
window.PDVState = PDVState;
