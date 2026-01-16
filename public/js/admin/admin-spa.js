/**
 * AdminSPA - Core Logic
 * Responsável por: Router, Loader, State Management
 */
const AdminSPA = {
    // =========================================================================
    // ESTADO
    // =========================================================================
    currentSection: null,
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

    async navigateTo(sectionName, updateHistory = true, forceReload = false) {
        if (this.isLoading && !forceReload) return;
        if (sectionName === this.currentSection && !forceReload) return;

        const config = this.sections[sectionName];
        if (!config) {
            console.error('[AdminSPA] Unknown section:', sectionName);
            return;
        }

        // 1. Leave Logic
        await this.leaveCurrentSection();

        // 2. State Update
        this.currentSection = sectionName;
        SpaUI.updateActiveNav(sectionName); // UI Helper

        if (updateHistory) {
            history.pushState({ section: sectionName }, '', `#${sectionName}`);
        }

        // 3. Load Content
        await this.loadSectionContent(sectionName, config, forceReload);

        // 4. Enter Logic
        await this.enterSection(sectionName);
    },

    async loadSectionContent(sectionName, config, forceReload = false) {
        const container = this.spaContentContainer;
        if (!container) return;

        // Cache Hit (Instant Load)
        if (this.cache[sectionName] && !forceReload) {
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

        // Fetch
        try {
            const response = await fetch(`${BASE_URL}${config.partial}`, {
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
        const section = this.currentSection;

        // Context-aware initialization
        if (section === 'estoque' && window.StockSPA?.init) StockSPA.init();
        if (section === 'cardapio' && window.CardapioAdmin?.init) CardapioAdmin.init();
        if (section === 'delivery' && window.DeliveryPolling?.init) DeliveryPolling.init();
        if ((section === 'pdv' || section === 'balcao') && window.PDV?.init) PDV.init();
        if (section === 'caixa' && window.CashierSPA?.init) CashierSPA.init();

        if (window.lucide) lucide.createIcons();
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
