/**
 * AdminSPA - Sistema de Navegação SPA para o Painel Admin
 * 
 * Features:
 * - Router client-side (hash-based)
 * - State Manager centralizado
 * - Cache de conteúdo por seção
 * - Skeleton loader automático
 * - Lifecycle hooks (onEnter, onLeave)
 * 
 * @version 1.0.0
 */
const AdminSPA = {
    // =========================================================================
    // ESTADO
    // =========================================================================
    currentSection: null,
    isLoading: false,
    contentCache: {},
    state: {},
    sectionModules: {},

    // =========================================================================
    // CONFIGURAÇÃO DE SEÇÕES
    // =========================================================================
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
    },

    // =========================================================================
    // INICIALIZAÇÃO
    // =========================================================================
    init() {
        console.log('[AdminSPA] Initializing...');

        // Registra módulos com Lifecycle
        this.registerModule('delivery', {
            onEnter: async () => {
                if (window.DeliveryPolling) DeliveryPolling.init();
            },
            onLeave: async () => {
                if (window.DeliveryPolling) DeliveryPolling.stop();
            }
        });

        // Módulo Mesas
        this.registerModule('mesas', {
            onEnter: async () => {
                // Placeholder para init customizado se necessário
                console.log('[AdminSPA] Mesas module entered');
            }
        });

        // Módulo PDV / Balcão
        const pdvHandler = {
            onEnter: async () => {
                if (window.PDV && typeof PDV.init === 'function') {
                    PDV.init();
                }
            },
            onLeave: async () => {
                // Persiste carrinho no SessionStorage ao sair
                if (window.PDVCart && typeof PDVCart.saveForMigration === 'function') {
                    console.log('[AdminSPA] Saving PDV state...');
                    PDVCart.saveForMigration();
                }
            }
        };
        this.registerModule('pdv', pdvHandler);
        this.registerModule('balcao', pdvHandler); // Alias

        this.bindNavigation();
        this.bindPopState();
        this.loadInitialSection();

        console.log('[AdminSPA] Ready');
    },

    bindNavigation() {
        const sidebar = document.querySelector('.sidebar-nav');
        if (!sidebar) return;

        sidebar.addEventListener('click', (e) => {
            const link = e.target.closest('.nav-item');
            if (!link || link.classList.contains('center-exit')) return;

            e.preventDefault();

            const section = this.getSectionFromUrl(link.href);
            if (section && section !== this.currentSection) {
                this.navigateTo(section);
            }
        });
    },

    bindPopState() {
        window.addEventListener('popstate', () => {
            const section = this.getSectionFromHash() || 'balcao';
            if (section !== this.currentSection) {
                this.navigateTo(section, false);
            }
        });
    },

    loadInitialSection() {
        const section = this.getSectionFromHash() || 'balcao';
        this.navigateTo(section, false);
    },

    // =========================================================================
    // HELPERS DE RECARREGAMENTO
    // =========================================================================
    async reloadCurrentSection() {
        if (!this.currentSection) return;

        console.log('[AdminSPA] Reloading section:', this.currentSection);
        await this.loadSectionContent(this.currentSection, this.sections[this.currentSection]);
    },

    // =========================================================================
    // NAVEGAÇÃO
    // =========================================================================
    async navigateTo(sectionName, updateHistory = true) {
        if (this.isLoading || sectionName === this.currentSection) return;

        const config = this.sections[sectionName];
        if (!config) {
            console.error('[AdminSPA] Unknown section:', sectionName);
            return;
        }

        console.log('[AdminSPA] Navigating to:', sectionName);

        // Lifecycle: sair da seção atual
        await this.leaveCurrentSection();

        // Atualiza estado
        const previousSection = this.currentSection;
        this.currentSection = sectionName;
        this.updateActiveNav(sectionName);

        if (updateHistory) {
            history.pushState({ section: sectionName }, '', `#${sectionName}`);
        }

        // Carrega conteúdo
        await this.loadSectionContent(sectionName, config);

        // Lifecycle: entrar na seção
        await this.enterSection(sectionName);
    },

    async leaveCurrentSection() {
        if (!this.currentSection) return;

        const module = this.sectionModules[this.currentSection];
        if (module && typeof module.onLeave === 'function') {
            console.log('[AdminSPA] Leaving section:', this.currentSection);
            await module.onLeave();
        }
    },

    async enterSection(sectionName) {
        const module = this.sectionModules[sectionName];
        if (module && typeof module.onEnter === 'function') {
            console.log('[AdminSPA] Entering section:', sectionName);
            await module.onEnter();
        }

        // Re-init Lucide icons
        if (window.lucide) {
            lucide.createIcons();
        }
    },

    // =========================================================================
    // CARREGAMENTO DE CONTEÚDO
    // =========================================================================
    async loadSectionContent(sectionName, config) {
        const container = document.getElementById('spa-content');
        if (!container) {
            console.error('[AdminSPA] Content container #spa-content not found');
            return;
        }

        // Usa cache se disponível (sem scripts, já foram executados)
        if (this.contentCache[sectionName]) {
            console.log('[AdminSPA] Using cached content for:', sectionName);
            requestAnimationFrame(() => {
                container.innerHTML = this.contentCache[sectionName];
                this.executeScripts(container);
                this.enterSection(sectionName);
            });
            return;
        }

        // Mostra skeleton
        this.isLoading = true;
        this.showSkeleton(container, config.skeleton);

        try {
            const response = await fetch(`${BASE_URL}${config.partial}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const html = await response.text();
            this.contentCache[sectionName] = html;

            requestAnimationFrame(() => {
                container.innerHTML = html;
                this.executeScripts(container);
            });

            console.log('[AdminSPA] Loaded:', sectionName);

        } catch (error) {
            console.error('[AdminSPA] Load error:', error);
            container.innerHTML = this.getErrorHtml(sectionName);
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Executa scripts carregados via innerHTML (que não executam automaticamente)
     */
    executeScripts(container) {
        const scripts = container.querySelectorAll('script[src]');
        const scriptPromises = [];

        scripts.forEach(oldScript => {
            // Pula se já carregado
            const src = oldScript.src;
            if (this.loadedScripts && this.loadedScripts.has(src)) {
                console.log('[AdminSPA] Script already loaded:', src);
                return;
            }

            // Carrega script dinamicamente
            const promise = new Promise((resolve) => {
                const newScript = document.createElement('script');
                newScript.src = src;
                newScript.onload = () => {
                    console.log('[AdminSPA] Script loaded:', src);
                    resolve();
                };
                newScript.onerror = () => {
                    console.error('[AdminSPA] Script failed:', src);
                    resolve();
                };
                document.head.appendChild(newScript);
            });
            scriptPromises.push(promise);

            // Track loaded scripts
            if (!this.loadedScripts) this.loadedScripts = new Set();
            this.loadedScripts.add(src);
        });

        // Após carregar todos os scripts, inicializa módulos conhecidos
        Promise.all(scriptPromises).then(() => {
            this.initLoadedModules();
        });

        // Se não há scripts novos, inicializa módulos existentes
        if (scriptPromises.length === 0) {
            this.initLoadedModules();
        }
    },

    /**
     * Inicializa módulos carregados (StockSPA, CardapioAdmin, etc.)
     */
    initLoadedModules() {
        // StockSPA
        if (window.StockSPA && typeof StockSPA.init === 'function') {
            console.log('[AdminSPA] Initializing StockSPA');
            StockSPA.init();
        }

        // CardapioAdmin
        if (window.CardapioAdmin && typeof CardapioAdmin.init === 'function') {
            console.log('[AdminSPA] Initializing CardapioAdmin');
            CardapioAdmin.init();
        }

        // Delivery Polling
        if (window.DeliveryPolling && typeof DeliveryPolling.init === 'function') {
            const currentSection = this.currentSection;
            // Só inicia se estiver na seção delivery
            if (currentSection === 'delivery') {
                console.log('[AdminSPA] Initializing DeliveryPolling');
                DeliveryPolling.init();
            }
        }

        // PDV
        if (window.PDV && typeof PDV.init === 'function') {
            const currentSection = this.currentSection;
            if (currentSection === 'pdv' || currentSection === 'balcao') {
                console.log('[AdminSPA] Initializing PDV');
                PDV.init();
            }
        }

        // Lucide icons
        if (window.lucide) {
            lucide.createIcons();
        }
    },

    // =========================================================================
    // UI HELPERS
    // =========================================================================
    updateActiveNav(sectionName) {
        document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
            item.classList.remove('active');
            const itemSection = this.getSectionFromUrl(item.href);
            if (itemSection === sectionName) {
                item.classList.add('active');
            }
        });
    },

    showSkeleton(container, type) {
        const skeletons = {
            grid: `
                <div class="skeleton-container">
                    <div class="skeleton-header"></div>
                    <div class="skeleton-grid">
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                    </div>
                </div>`,
            table: `
                <div class="skeleton-container">
                    <div class="skeleton-header"></div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                </div>`,
            kanban: `
                <div class="skeleton-container" style="display: flex; gap: 20px;">
                    <div style="flex: 1;">
                        <div class="skeleton-header"></div>
                        <div class="skeleton-card"></div>
                        <div class="skeleton-card"></div>
                    </div>
                    <div style="flex: 1;">
                        <div class="skeleton-header"></div>
                        <div class="skeleton-card"></div>
                    </div>
                    <div style="flex: 1;">
                        <div class="skeleton-header"></div>
                        <div class="skeleton-card"></div>
                    </div>
                </div>`,
            tabs: `
                <div class="skeleton-container">
                    <div class="skeleton-chips">
                        <div class="skeleton-chip"></div>
                        <div class="skeleton-chip"></div>
                        <div class="skeleton-chip"></div>
                    </div>
                    <div class="skeleton-row"></div>
                    <div class="skeleton-row"></div>
                </div>`
        };

        container.innerHTML = skeletons[type] || skeletons.grid;
    },

    getErrorHtml(sectionName) {
        return `
            <div style="padding: 3rem; text-align: center; color: #dc2626;">
                <i data-lucide="alert-circle" style="width: 48px; height: 48px; margin-bottom: 15px;"></i>
                <h3 style="margin-bottom: 10px;">Erro ao carregar</h3>
                <p style="color: #6b7280; margin-bottom: 20px;">Não foi possível carregar "${sectionName}".</p>
                <button onclick="AdminSPA.navigateTo('${sectionName}')" 
                        style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer;">
                    Tentar Novamente
                </button>
            </div>`;
    },

    // =========================================================================
    // HELPERS
    // =========================================================================
    getSectionFromUrl(url) {
        const urlObj = new URL(url, window.location.origin);
        const path = urlObj.pathname;

        for (const [name, config] of Object.entries(this.sections)) {
            if (path.includes(config.url.split('/').pop())) {
                return name;
            }
        }
        return null;
    },

    getSectionFromHash() {
        const hash = window.location.hash.replace('#', '');
        return this.sections[hash] ? hash : null;
    },

    // =========================================================================
    // STATE MANAGER
    // =========================================================================
    setState(key, value) {
        this.state[key] = value;
        console.log('[AdminSPA] State updated:', key, value);
    },

    getState(key) {
        return this.state[key];
    },

    // =========================================================================
    // CACHE CONTROL
    // =========================================================================
    clearCache(sectionName = null) {
        if (sectionName) {
            delete this.contentCache[sectionName];
        } else {
            this.contentCache = {};
        }
        console.log('[AdminSPA] Cache cleared:', sectionName || 'all');
    },

    // =========================================================================
    // MODULE REGISTRATION
    // =========================================================================
    registerModule(sectionName, module) {
        this.sectionModules[sectionName] = module;
        console.log('[AdminSPA] Module registered:', sectionName);
    }
};

// Auto-init quando DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    // Só inicializa se estiver na página do shell SPA
    if (document.getElementById('spa-content')) {
        AdminSPA.init();
    }
});
