/**
 * Admin Categories (Gestão de Categorias)
 */

function openCategoryModal() {
    document.getElementById('categoryModal').style.display = 'flex';
}

function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

// Fechar ao clicar fora
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('categoryModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) closeCategoryModal();
        });
    }
});

function filterCategories(query) {
    const rows = document.querySelectorAll('.category-row');
    const q = query.toLowerCase().trim();
    rows.forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
}
