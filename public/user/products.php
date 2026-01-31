<?php
$page_title = 'Produits';
$current_page = 'products';

include '../layouts/header.php';
include '../layouts/sidebar-user.php';
?>

<!-- Contenu principal -->
<div class="col-md-9 col-lg-10" style="padding: 2rem;">
    <h2 style="margin-bottom: 2rem;">🛍️ Catalogue Produits</h2>

    <!-- Filtres et recherche -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="tp-card">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">🔍 Rechercher</label>
                        <input type="text" class="form-control" id="search-input"
                            placeholder="Nom du produit, entreprise...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">📁 Catégorie</label>
                        <select class="form-control" id="category-filter">
                            <option value="">Toutes les catégories</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">🔀 Trier par</label>
                        <select class="form-control" id="sort-filter">
                            <option value="newest">Plus récents</option>
                            <option value="price_asc">Prix croissant</option>
                            <option value="price_desc">Prix décroissant</option>
                            <option value="rating">Mieux notés</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des produits avec pagination -->
    <div id="products-container">
        <div class="row pagination-items"></div>
        <div class="pagination-controls"></div>
    </div>
</div>

<script>
    let productsPagination;

    // Charger les catégories
    async function loadCategories() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/categories-list.php');
            const data = await response.json();

            if (data.success && data.categories) {
                const select = document.getElementById('category-filter');
                data.categories.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.icon + ' ' + cat.name;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Erreur chargement catégories:', error);
        }
    }

    // Initialiser la pagination des produits
    function initProductsPagination(filters = {}) {
        const params = new URLSearchParams(filters);
        const endpoint = TrustPick.API_BASE + '/products-list.php?' + params.toString();

        productsPagination = new TrustPickPagination({
            endpoint: endpoint,
            containerId: 'products-container',
            itemsPerPage: 5,
            renderItem: PaginationRenderers.product,
            emptyMessage: 'Aucun produit trouvé'
        });

        // Stocker globalement pour le bouton "Voir plus"
        window['pagination_products-container'] = productsPagination;
    }

    // Gestionnaire de recherche
    const searchInput = document.getElementById('search-input');
    const categoryFilter = document.getElementById('category-filter');
    const sortFilter = document.getElementById('sort-filter');

    const handleFiltersChange = TrustPick.debounce(() => {
        const filters = {};

        if (searchInput.value.trim()) {
            filters.search = searchInput.value.trim();
        }

        if (categoryFilter.value) {
            filters.category_id = categoryFilter.value;
        }

        if (sortFilter.value) {
            filters.sort = sortFilter.value;
        }

        // Réinitialiser la pagination avec les nouveaux filtres
        initProductsPagination(filters);
    }, 500);

    searchInput.addEventListener('input', handleFiltersChange);
    categoryFilter.addEventListener('change', handleFiltersChange);
    sortFilter.addEventListener('change', handleFiltersChange);

    // Initialiser au chargement
    window.addEventListener('load', () => {
        loadCategories();
        initProductsPagination();
    });
</script>

<?php include '../layouts/footer.php'; ?>