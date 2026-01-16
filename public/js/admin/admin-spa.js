/**
 * AdminSPA - Core Logic
 * Responsável por: Router, Loader, State Management
 */
const AdminSPA = {
    // =========================================================================
    // ESTADO
    // =========================================================================
    currentSection: null,
    currentQueryParams: null,
    isLoading: false,
    cache: {},
    state: {},
    modules: {},
    loadedScripts: new Set(),
    spaContentContainer: null,

    // =========================================================================
    // INICIALIZAÇÃO
    // =========================================================================
    init() {
        this.cacheDOM();

        // Bind Config Sections
        this.sections = SpaConfig.sections;

        // Registra módulos padrão
        this.registerDefaultModules();

        this.bindNavigation();
        this.bindPopState();
        this.handleInitialState();
    },

    cacheDOM() {
        this.spaContentContainer = document.getElementById('spa-content');
    },

    registerDefaultModules() {
        // Delivery
        this.registerModule('delivery', {
            onEnter: async () => { if (window.DeliveryPolling) DeliveryPolling.init(); },
            onLeave: async () => { if (window.DeliveryPolling) DeliveryPolling.stop(); }
        });

        // Mesas (Placeholder)
        this.registerModule('mesas', {});

        // PDV/Balcão
        const pdvHandler = {
            onEnter: async () => { if (window.PDV && PDV.init) PDV.init(); },
            onLeave: async () => { if (window.PDV && PDV.saveState) PDV.saveState(); }
        };
        this.registerModule('pdv', pdvHandler);
        this.registerModule('balcao', pdvHandler);

        // Caixa
        this.registerModule('caixa', {
            onEnter: async () => { if (window.CashierSPA && CashierSPA.init) CashierSPA.init(); }
        });

        // Estoque
        this.registerModule('estoque', {
            onEnter: async () => { if (window.StockSPA?.init) StockSPA.init(); }
        });

        // Cardápio
        this.registerModule('cardapio', {
            onEnter: async () => { if (window.CardapioAdmin?.init) CardapioAdmin.init(); }
        });
    },

    // =========================================================================
    // NAVEGAÇÃO & UI
    // =========================================================================
    bindNavigation() {
        const sidebar = document.querySelector('.sidebar-nav');
        if (!sidebar) return;

        sidebar.addEventListener('click', (e) => {
            const link = e.target.closest('.nav-item');
            if (!link || link.classList.contains('center-exit')) return;

            const section = link.dataset.section;
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

    handleInitialState() {
        const section = this.getSectionFromHash() || 'balcao';
        this.navigateTo(section, false);
    },

    async navigateTo(sectionName, updateHistory = true, forceReload = false, queryParams = null) {
        if (this.isLoading && !forceReload) return;
        // Permite recarregar mesma seção se tiver params (ex: order_id diferente)
        if (sectionName === this.currentSection && !forceReload && !queryParams) return;

        const config = this.sections[sectionName];
        if (!config) {
            console.error('[AdminSPA] Unknown section:', sectionName);
            return;
        }

        // Armazena params atuais
        this.currentQueryParams = queryParams;

        // 1. Leave Logic
        await this.leaveCurrentSection();

        // 2. State Update
        this.currentSection = sectionName;

        // [UX] Se carregando balcao com mesa_id ou order_id, destaca 'mesas' ao invés de 'balcao'
        let navSection = sectionName;
        if (sectionName === 'balcao' && queryParams && (queryParams.mesa_id || queryParams.order_id)) {
            navSection = 'mesas';
        }
        SpaUI.updateActiveNav(navSection);

        if (updateHistory) {
            history.pushState({ section: sectionName }, '', `#${sectionName}`);
        }

        // 3. Load Content (sempre forceReload se tiver params)
        await this.loadSectionContent(sectionName, config, forceReload || !!queryParams, queryParams);

        // 4. Enter Logic - agora é chamado após scripts carregarem em initLoadedModules()
        // Não chamar enterSection aqui porque scripts podem não ter carregado ainda
    },

    async loadSectionContent(sectionName, config, forceReload = false, queryParams = null) {
        const container = this.spaContentContainer;
        if (!container) return;

        // Cache Hit (Instant Load) - só usa cache se não tiver queryParams
        if (this.cache[sectionName] && !forceReload && !queryParams) {
            this.renderSection(this.cache[sectionName]);
            return;
        }

        // Start Transition (only for network fetch)
        container.classList.add('fade-out');

        // Skeleton
        this.isLoading = true;

        // Delay skeleton just a bit to avoid flashing on super fast connections
        const skeletonTimeout = setTimeout(() => {
            if (this.isLoading) {
                SpaUI.showSkeleton(container, config.skeleton);
                container.classList.remove('fade-out');
            }
        }, 100);

        // Monta URL com query params se existir
        let partialUrl = `${BASE_URL}${config.partial}`;
        if (queryParams) {
            const params = new URLSearchParams(queryParams);
            partialUrl += '?' + params.toString();
            console.log('[AdminSPA] Loading with params:', queryParams);
        }

        // Fetch
        try {
            const response = await fetch(partialUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const html = await response.text();
            this.cache[sectionName] = html;

            clearTimeout(skeletonTimeout);

            // Render with smooth entry
            if (this.isLoading) { // Ensure we haven't navigated away
                container.classList.add('fade-out');
                requestAnimationFrame(() => {
                    this.renderSection(html);
                    requestAnimationFrame(() => {
                        container.classList.remove('fade-out');
                    });
                });
            }

        } catch (error) {
            clearTimeout(skeletonTimeout);
            container.innerHTML = SpaUI.getErrorHtml(sectionName);
            console.error('[AdminSPA]', error);
            container.classList.remove('fade-out');
        } finally {
            this.isLoading = false;
        }
    },

    renderSection(htmlContent) {
        requestAnimationFrame(() => {
            if (this.spaContentContainer) {
                this.spaContentContainer.innerHTML = htmlContent;
                this.executeScripts(this.spaContentContainer);
            }
        });
    },

    // =========================================================================
    // HELPERS & LIFECYCLE
    // =========================================================================
    getSectionFromHash() {
        const hash = window.location.hash.replace('#', '');
        return this.sections[hash] ? hash : null;
    },

    async leaveCurrentSection() {
        if (!this.currentSection) return;
        const module = this.modules[this.currentSection];
        if (module && module.onLeave) await module.onLeave();
    },

    async enterSection(sectionName) {
        const module = this.modules[sectionName];
        if (module && module.onEnter) await module.onEnter();
        if (window.lucide) lucide.createIcons();
    },

    registerModule(sectionName, module) {
        this.modules[sectionName] = module;
    },

    setState(key, value) { this.state[key] = value; },
    getState(key) { return this.state[key]; },

    // =========================================================================
    // SCRIPT LOADING
    // =========================================================================
    executeScripts(container) {
        const scripts = container.querySelectorAll('script[src]');
        const scriptPromises = [];

        scripts.forEach(oldScript => {
            const src = oldScript.src;
            if (this.loadedScripts.has(src)) return;

            const promise = new Promise((resolve) => {
                const newScript = document.createElement('script');
                newScript.src = src;
                newScript.async = false;
                newScript.onload = () => resolve();
                newScript.onerror = () => resolve();
                document.head.appendChild(newScript);
            });

            scriptPromises.push(promise);
            this.loadedScripts.add(src);
        });

        Promise.all(scriptPromises).then(() => this.initLoadedModules());
        if (scriptPromises.length === 0) this.initLoadedModules();
    },

    initLoadedModules() {
        // IMPORTANTE: Este método é chamado APÓS scripts carregarem.
        // Então é o lugar correto para chamar onEnter dos módulos.

        // Chama onEnter do módulo atual (agora os scripts estão carregados)
        this.enterSection(this.currentSection);
    },

    // Alias para compatibilidade
    reloadCurrentSection() {
        if (this.currentSection) this.navigateTo(this.currentSection, false, true);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('spa-content')) {
        AdminSPA.init();
    }
});
