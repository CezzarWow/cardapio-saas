/**
 * SPA UI Helpers
 * Manipulação de DOM, Skeletons e Feedback visual.
 */
const SpaUI = {
    updateActiveNav(sectionName) {
        document.querySelectorAll('.sidebar-nav .nav-item').forEach(item => {
            item.classList.remove('active');
            // Use data-section for robust matching
            if (item.dataset.section === sectionName) {
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
    }
};
