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
    isPrefetching: false,
    cache: {},
    state: {},
    modules: {},
    loadedScripts: new Set(),
    spaContentContainer: null,

    // Mapa de prefetch: seção atual → seções para pré-carregar
    prefetchMap: {
        'balcao': ['mesas', 'delivery'],
        'mesas': ['balcao', 'delivery'],
        'delivery': ['mesas', 'caixa'],
        'caixa': ['delivery', 'estoque'],
        'estoque': ['caixa', 'cardapio'],
        'cardapio': ['estoque', 'caixa']
    },

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
            onEnter: async (params) => { if (window.DeliveryPolling) DeliveryPolling.init(params); },
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

            e.preventDefault(); // Impede navegação tradicional do link

            const section = link.dataset.section;
            if (section) {
                // [FIX] Removemos a verificação (section !== currentSection) aqui
                // Deixamos o navigateTo decidir se deve recarregar (ex: limpar params)
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

        // [FIX] Captura query params da URL principal na inicialização
        const searchParams = new URLSearchParams(window.location.search);
        const queryParams = {};
        for (const [key, value] of searchParams.entries()) {
            queryParams[key] = value;
        }

        // Se tiver params, passa para navigateTo
        const hasParams = Object.keys(queryParams).length > 0;
        this.navigateTo(section, false, false, hasParams ? queryParams : null);
    },

    async navigateTo(sectionName, updateHistory = true, forceReload = false, queryParams = null) {
        if (this.isLoading && !forceReload) return;

        // Verifica se devemos recarregar a mesma seção
        // Permitimos se:
        // 1. ForceReload = true
        // 2. Query params diferentes (ex: clicou 'Balcão' limpo estando em 'Balcão?mesa=1')
        if (sectionName === this.currentSection && !forceReload) {
            const newParamsStr = queryParams ? JSON.stringify(queryParams) : null;
            const oldParamsStr = this.currentQueryParams ? JSON.stringify(this.currentQueryParams) : null;

            // Se params forem iguais, não faz nada
            if (newParamsStr === oldParamsStr) return;
        }

        const config = this.sections[sectionName];
        if (!config) {
            console.error('[AdminSPA] Unknown section:', sectionName);
            return;
        }

        // Armazena params atuais
        this.currentQueryParams = queryParams;

        // 1. Leave Logic
        await this.leaveCurrentSection();

        // [FIX] Se estiver indo para Balcão LIMPO (sem params), limpar o carrinho migrado
        // Isso evita que itens da mesa anterior (salvos no leaveCurrentSection) apareçam no balcão limpo
        if (sectionName === 'balcao' && !queryParams) {
            sessionStorage.removeItem('pdv_migration_cart');
        }

        // 2. State Update
        this.currentSection = sectionName;

        // [UX] Se carregando balcao com mesa_id ou order_id, destaca 'mesas' ao invés de 'balcao'
        let navSection = sectionName;
        if (sectionName === 'balcao' && queryParams && (queryParams.mesa_id || queryParams.order_id)) {
            navSection = 'mesas';
        }
        SpaUI.updateActiveNav(navSection);

        if (updateHistory) {
            // Preserva sub-rota do hash para seções com navegação interna (ex: estoque/reposicao)
            let hashValue = `#${sectionName}`;
            const currentHash = window.location.hash.replace('#', '');
            if (currentHash.startsWith(sectionName + '/')) {
                hashValue = '#' + currentHash; // Mantém sub-rota existente
            }
            history.pushState({ section: sectionName }, '', hashValue);
        }

        // 3. Load Content (sempre forceReload se tiver params)
        await this.loadSectionContent(sectionName, config, forceReload || !!queryParams, queryParams);

        // 4. Enter Logic - agora é chamado após scripts carregarem em initLoadedModules()
        // Não chamar enterSection aqui porque scripts podem não ter carregado ainda
    },

    async loadSectionContent(sectionName, config, forceReload = false, queryParams = null) {
        const container = this.spaContentContainer;
        if (!container) return;

        // [FIX] Seções dinâmicas não devem usar cache simples, pois o conteúdo varia com params (mesa_id)
        // Isso força o carregamento fresco para balcao/mesas, evitando o problema de "Zombie Cart"
        const noCacheSections = ['balcao', 'pdv', 'mesas', 'delivery'];
        const canUseCache = !noCacheSections.includes(sectionName);

        // Cache Hit (Instant Load com transição suave) - Apenas se permitido
        if (canUseCache && this.cache[sectionName] && !forceReload && !queryParams) {
            container.classList.add('fade-out');
            // Espera transição CSS completar (150ms definido no spa.css)
            await new Promise(r => setTimeout(r, 150));
            this.renderSectionSync(this.cache[sectionName]);
            container.classList.remove('fade-out');
            return;
        }

        // Start Transition
        container.classList.add('fade-out');
        this.isLoading = true;

        // Monta URL com Timestamp para evitar cache do navegador (browser cache)
        let partialUrl = `${BASE_URL}${config.partial}`;
        // Detecta se já tem query params
        const separator = partialUrl.includes('?') ? '&' : '?';
        partialUrl += `${separator}_t=${Date.now()}`;

        if (queryParams) {
            const params = new URLSearchParams(queryParams);
            partialUrl += '&' + params.toString();
        }

        // Fetch
        try {
            const response = await fetch(partialUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const html = await response.text();
            this.cache[sectionName] = html;

            // Preload CSS antes de renderizar
            await this.preloadStyles(html);

            // Render (espera fade-out completar)
            if (this.isLoading) {
                await new Promise(r => setTimeout(r, 150));
                this.renderSectionSync(html);
                container.classList.remove('fade-out');
            }

        } catch (error) {
            container.innerHTML = SpaUI.getErrorHtml(sectionName);
            console.error('[AdminSPA]', error);
            container.classList.remove('fade-out');
        } finally {
            this.isLoading = false;
        }
    },

    /**
     * Preload CSS encontrado no HTML da partial
     * Usa método nativo do browser para melhor performance
     */
    async preloadStyles(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const links = doc.querySelectorAll('link[rel="stylesheet"]');

        if (links.length === 0) return;

        const promises = [];

        links.forEach(link => {
            const href = link.getAttribute('href');
            if (!href) return;

            // Se já estiver no head, ignora
            if (document.querySelector(`link[href^="${href.split('?')[0]}"]`)) return;

            const promise = new Promise((resolve) => {
                // Usa <link rel="preload"> nativo para melhor performance
                const preload = document.createElement('link');
                preload.rel = 'preload';
                preload.as = 'style';
                preload.href = href;
                preload.onload = resolve;
                preload.onerror = resolve;
                document.head.appendChild(preload);

                // Timeout curto para não bloquear
                setTimeout(resolve, 200);
            });
            promises.push(promise);
        });

        if (promises.length > 0) {
            await Promise.all(promises);
        }
    },

    /**
     * Renderiza seção de forma síncrona (sem requestAnimationFrame extra)
     */
    renderSectionSync(htmlContent) {
        if (this.spaContentContainer) {
            this.spaContentContainer.innerHTML = htmlContent;
            this.executeScripts(this.spaContentContainer);
        }
    },

    /**
     * Alias para compatibilidade
     */
    renderSection(htmlContent) {
        this.renderSectionSync(htmlContent);
    },

    // =========================================================================
    // HELPERS & LIFECYCLE
    // =========================================================================
    getSectionFromHash() {
        const hash = window.location.hash.replace('#', '');
        const mainSection = hash.split('/')[0];

        // Seção válida direta
        if (this.sections[mainSection]) return mainSection;

        // Fallback: Mapeia sub-abas órfãs de estoque para 'estoque'
        // Útil se o usuário acessar #reposicao diretamente ou se o hash perder o prefixo
        const stockTabs = ['reposicao', 'produtos', 'categorias', 'adicionais', 'movimentacoes'];
        if (stockTabs.includes(mainSection)) {
            return 'estoque';
        }

        return null;
    },

    async leaveCurrentSection() {
        if (!this.currentSection) return;
        const module = this.modules[this.currentSection];
        if (module && module.onLeave) await module.onLeave();
    },

    async enterSection(sectionName) {
        const module = this.modules[sectionName];
        // [FIX] Passa queryParams para o onEnter do módulo (ex: open_order para delivery)
        if (module && module.onEnter) await module.onEnter(this.currentQueryParams);
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

        // Prefetch seções adjacentes em idle time
        this.prefetchAdjacentSections(this.currentSection);
    },

    /**
     * Prefetch silencioso de seções adjacentes
     * Usa requestIdleCallback para não bloquear interação
     */
    prefetchAdjacentSections(currentSection) {
        const adjacentSections = this.prefetchMap[currentSection];
        if (!adjacentSections || adjacentSections.length === 0) return;

        // Filtra seções que já estão em cache
        const sectionsToFetch = adjacentSections.filter(s => !this.cache[s]);
        if (sectionsToFetch.length === 0) return;

        // Usa requestIdleCallback ou setTimeout como fallback
        const scheduleIdle = window.requestIdleCallback || ((cb) => setTimeout(cb, 300));

        scheduleIdle(() => {
            if (this.isLoading || this.isPrefetching) return;
            this.isPrefetching = true;

            // Prefetch uma seção por vez para não sobrecarregar
            this.prefetchSection(sectionsToFetch[0]).finally(() => {
                this.isPrefetching = false;
                // Agenda próxima se houver
                if (sectionsToFetch.length > 1) {
                    scheduleIdle(() => {
                        if (!this.isLoading && !this.cache[sectionsToFetch[1]]) {
                            this.prefetchSection(sectionsToFetch[1]);
                        }
                    }, { timeout: 2000 });
                }
            });
        }, { timeout: 1000 });
    },

    /**
     * Faz fetch silencioso de uma partial (só HTML, sem executar scripts)
     */
    async prefetchSection(sectionName) {
        const config = this.sections[sectionName];
        if (!config || this.cache[sectionName]) return;

        try {
            // Adiciona timestamp para evitar cache
            const url = `${BASE_URL}${config.partial}${config.partial.includes('?') ? '&' : '?'}_t=${Date.now()}`;
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (response.ok) {
                this.cache[sectionName] = await response.text();
            }
        } catch (e) {
            // Silencioso - prefetch falhou, não é crítico
        }
    },

    // Alias para compatibilidade
    reloadCurrentSection() {
        if (this.currentSection) this.navigateTo(this.currentSection, false, true);
    },

    /**
     * Invalida o cache de uma seção específica
     * Útil após salvar dados que alteram a visualização (ex: mesas, pedidos)
     */
    invalidateCache(sectionName) {
        if (this.cache[sectionName]) {
            delete this.cache[sectionName];
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('spa-content')) {
        AdminSPA.init();
    }
});
