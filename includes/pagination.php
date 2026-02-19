<?php
/**
 * TrustPick - Système de Pagination Intelligente Universelle
 * Affichage par défaut: 5 éléments
 * Bouton "Voir plus" avec chargement progressif
 * Fonctionne pour TOUTES les listes (produits, avis, notifications, etc.)
 */

class SmartPagination
{
    private $db;
    private $itemsPerPage = 5; // Par défaut

    public function __construct($pdo, $itemsPerPage = 5)
    {
        $this->db = $pdo;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * Paginer n'importe quelle requête SQL
     * 
     * @param string $baseQuery La requête SQL de base (SELECT ... FROM ... WHERE ...)
     * @param array $params Paramètres de la requête préparée
     * @param int $page Numéro de page (commence à 1)
     * @param int $perPage Nombre d'éléments par page (optionnel)
     * @return array Résultats paginés
     */
    public function paginate($baseQuery, $params = [], $page = 1, $perPage = null)
    {
        try {
            $perPage = $perPage ?? $this->itemsPerPage;
            $page = max(1, (int) $page); // S'assurer que page >= 1
            $offset = ($page - 1) * $perPage;

            // Compter le total d'éléments
            $countQuery = $this->convertToCountQuery($baseQuery);
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute($params);
            $totalItems = $stmt->fetchColumn();

            // Calculer les métadonnées
            $totalPages = ceil($totalItems / $perPage);
            $hasMore = $page < $totalPages;

            // Ajouter LIMIT et OFFSET à la requête
            $paginatedQuery = $baseQuery . " LIMIT ? OFFSET ?";
            $paginatedParams = array_merge($params, [$perPage, $offset]);

            // Exécuter la requête paginée
            $stmt = $this->db->prepare($paginatedQuery);
            $stmt->execute($paginatedParams);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => $items,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_items' => $totalItems,
                    'total_pages' => $totalPages,
                    'has_more' => $hasMore,
                    'has_previous' => $page > 1,
                    'next_page' => $hasMore ? $page + 1 : null,
                    'previous_page' => $page > 1 ? $page - 1 : null,
                    'from' => $totalItems > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $perPage, $totalItems)
                ]
            ];

        } catch (Exception $e) {
            error_log("Erreur pagination: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => [],
                'pagination' => []
            ];
        }
    }

    /**
     * Convertir une requête SELECT en requête COUNT
     */
    private function convertToCountQuery($query)
    {
        // Supprimer ORDER BY car non nécessaire pour compter
        $query = preg_replace('/ORDER BY.*$/i', '', $query);

        // Si la requête contient déjà SELECT COUNT
        if (stripos($query, 'SELECT COUNT') !== false) {
            return $query;
        }

        // Remplacer SELECT ... FROM par SELECT COUNT(*) FROM
        $query = preg_replace('/SELECT\s+.*?\s+FROM/is', 'SELECT COUNT(*) FROM', $query, 1);

        return $query;
    }

    /**
     * Paginer des produits
     */
    public function paginateProducts($filters = [], $page = 1)
    {
        $query = "
            SELECT 
                p.*,
                c.name as company_name,
                cat.name as category_name,
                COALESCE(AVG(r.rating), 0) as avg_rating,
                COUNT(DISTINCT r.id) as reviews_count
            FROM products p
            JOIN companies c ON p.company_id = c.id
            LEFT JOIN categories cat ON p.category_id = cat.id
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.is_active = TRUE
        ";

        $params = [];

        // Filtres
        if (!empty($filters['company_id'])) {
            $query .= " AND p.company_id = ?";
            $params[] = $filters['company_id'];
        }

        if (!empty($filters['category_id'])) {
            $query .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (p.title LIKE ? OR p.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " GROUP BY p.id";
        $query .= " ORDER BY p.created_at DESC";

        return $this->paginate($query, $params, $page);
    }

    /**
     * Paginer des avis
     */
    public function paginateReviews($filters = [], $page = 1)
    {
        $query = "
            SELECT 
                r.*,
                u.name as user_name,
                u.cau as user_cau,
                p.title as product_title,
                p.id as product_id,
                TO_CHAR(r.created_at, 'DD/MM/YYYY') as review_date
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN products p ON r.product_id = p.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['product_id'])) {
            $query .= " AND r.product_id = ?";
            $params[] = $filters['product_id'];
        }

        if (!empty($filters['user_id'])) {
            $query .= " AND r.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['min_rating'])) {
            $query .= " AND r.rating >= ?";
            $params[] = $filters['min_rating'];
        }

        $query .= " ORDER BY r.created_at DESC";

        return $this->paginate($query, $params, $page);
    }

    /**
     * Paginer des notifications
     */
    public function paginateNotifications($userId, $page = 1, $unreadOnly = false)
    {
        $query = "
            SELECT 
                id,
                type,
                title,
                message,
                link,
                is_read,
                TO_CHAR(created_at, 'DD/MM/YYYY HH24:MI') as formatted_date
            FROM notifications
            WHERE user_id = ?
        ";

        $params = [$userId];

        if ($unreadOnly) {
            $query .= " AND is_read = FALSE";
        }

        $query .= " ORDER BY created_at DESC";

        return $this->paginate($query, $params, $page);
    }

    /**
     * Paginer des entreprises
     */
    public function paginateCompanies($page = 1, $activeOnly = true)
    {
        $query = "
            SELECT 
                c.*,
                COUNT(DISTINCT p.id) as products_count,
                COUNT(DISTINCT r.id) as reviews_count
            FROM companies c
            LEFT JOIN products p ON c.id = p.company_id
            LEFT JOIN reviews r ON p.id = r.product_id
        ";

        $params = [];

        if ($activeOnly) {
            $query .= " WHERE c.is_active = TRUE";
        }

        $query .= " GROUP BY c.id ORDER BY c.name ASC";

        return $this->paginate($query, $params, $page);
    }

    /**
     * Paginer des utilisateurs
     */
    public function paginateUsers($filters = [], $page = 1)
    {
        $query = "
            SELECT 
                u.id,
                u.cau,
                u.name,
                u.phone,
                u.role,
                u.balance,
                u.is_active,
                c.name as company_name,
                TO_CHAR(u.created_at, 'DD/MM/YYYY') as join_date,
                TO_CHAR(u.last_login, 'DD/MM/YYYY HH24:MI') as last_login_formatted
            FROM users u
            LEFT JOIN companies c ON u.company_id = c.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($filters['role'])) {
            $query .= " AND u.role = ?";
            $params[] = $filters['role'];
        }

        if (!empty($filters['company_id'])) {
            $query .= " AND u.company_id = ?";
            $params[] = $filters['company_id'];
        }

        if (isset($filters['is_active'])) {
            $query .= " AND u.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $query .= " AND (u.name LIKE ? OR u.cau LIKE ? OR u.phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $query .= " ORDER BY u.created_at DESC";

        return $this->paginate($query, $params, $page);
    }

    /**
     * Paginer des transactions
     */
    public function paginateTransactions($userId, $page = 1)
    {
        $query = "
            SELECT 
                id,
                type,
                amount,
                description,
                balance_after,
                TO_CHAR(created_at, 'DD/MM/YYYY HH24:MI') as transaction_date
            FROM transactions
            WHERE user_id = ?
            ORDER BY created_at DESC
        ";

        return $this->paginate($query, [$userId], $page);
    }

    /**
     * Générer le HTML pour la pagination (pour faciliter l'intégration)
     */
    public function renderPaginationHTML($paginationData, $baseUrl = '', $ajaxMode = false)
    {
        if (!$paginationData['total_pages'] || $paginationData['total_pages'] <= 1) {
            return '';
        }

        $html = '<div class="pagination-wrapper">';
        $html .= '<div class="pagination-info">';
        $html .= "Affichage de {$paginationData['from']} à {$paginationData['to']} sur {$paginationData['total_items']} éléments";
        $html .= '</div>';

        if ($ajaxMode) {
            // Mode AJAX: bouton "Voir plus"
            if ($paginationData['has_more']) {
                $nextPage = $paginationData['next_page'];
                $html .= '<div class="pagination-actions">';
                $html .= "<button class='btn btn-primary load-more-btn' data-page='{$nextPage}' data-url='{$baseUrl}'>";
                $html .= '📄 Voir plus</button>';
                $html .= '</div>';
            }
        } else {
            // Mode traditionnel: liens de pagination
            $html .= '<div class="pagination-links">';

            // Page précédente
            if ($paginationData['has_previous']) {
                $prevPage = $paginationData['previous_page'];
                $html .= "<a href='{$baseUrl}?page={$prevPage}' class='pagination-link'>← Précédent</a>";
            }

            // Numéros de pages
            $currentPage = $paginationData['current_page'];
            $totalPages = $paginationData['total_pages'];

            for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
                $activeClass = $i == $currentPage ? 'active' : '';
                $html .= "<a href='{$baseUrl}?page={$i}' class='pagination-link {$activeClass}'>{$i}</a>";
            }

            // Page suivante
            if ($paginationData['has_more']) {
                $nextPage = $paginationData['next_page'];
                $html .= "<a href='{$baseUrl}?page={$nextPage}' class='pagination-link'>Suivant →</a>";
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Générer le JavaScript pour le chargement AJAX (mode "Voir plus")
     */
    public function renderAjaxScript()
    {
        return <<<'JAVASCRIPT'
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du bouton "Voir plus"
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('load-more-btn')) {
            e.preventDefault();
            
            const btn = e.target;
            const page = btn.dataset.page;
            const url = btn.dataset.url;
            const container = btn.closest('.paginated-container');
            const itemsContainer = container.querySelector('.items-list');
            
            // Désactiver le bouton pendant le chargement
            btn.disabled = true;
            btn.textContent = '⏳ Chargement...';
            
            // Requête AJAX
            fetch(url + '?page=' + page + '&ajax=1')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.html) {
                        // Ajouter les nouveaux éléments
                        itemsContainer.insertAdjacentHTML('beforeend', data.html);
                        
                        // Mettre à jour ou supprimer le bouton
                        if (data.pagination.has_more) {
                            btn.dataset.page = data.pagination.next_page;
                            btn.disabled = false;
                            btn.textContent = '📄 Voir plus';
                        } else {
                            btn.remove();
                        }
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    btn.disabled = false;
                    btn.textContent = '❌ Erreur - Réessayer';
                });
        }
    });
});
</script>
JAVASCRIPT;
    }
}
