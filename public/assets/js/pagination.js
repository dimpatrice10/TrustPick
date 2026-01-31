/* ============================================================
   TRUSTPICK V2 - PAGINATION UNIVERSELLE
   Système "5 éléments + Voir plus"
   ============================================================ */

class TrustPickPagination {
  constructor(config) {
    this.endpoint = config.endpoint;
    this.container = document.getElementById(config.containerId);
    this.itemsPerPage = config.itemsPerPage || 5;
    this.renderItem = config.renderItem;
    this.emptyMessage = config.emptyMessage || 'Aucun élément trouvé';
    this.loadingMessage = config.loadingMessage || 'Chargement...';

    this.currentPage = 1;
    this.hasMore = true;
    this.isLoading = false;
    this.items = [];

    this.init();
  }

  /**
   * Initialisation
   */
  init() {
    if (!this.container) {
      console.error(`Container #${config.containerId} introuvable`);
      return;
    }

    // Créer la structure
    this.container.innerHTML = `
      <div class="pagination-items"></div>
      <div class="pagination-controls"></div>
    `;

    this.itemsContainer = this.container.querySelector('.pagination-items');
    this.controlsContainer = this.container.querySelector('.pagination-controls');

    // Charger la première page
    this.loadPage();
  }

  /**
   * Charger une page
   */
  async loadPage() {
    if (this.isLoading || !this.hasMore) return;

    this.isLoading = true;
    this.showLoading();

    try {
      // Appeler l'API
      const params = new URLSearchParams({
        page: this.currentPage,
        limit: this.itemsPerPage
      });

      const response = await fetch(`${this.endpoint}?${params}`);
      const data = await response.json();

      if (data.success) {
        this.renderItems(data.items || data.data || []);
        this.hasMore = data.has_more || data.hasMore || false;
        this.currentPage++;
      } else {
        TrustPick.showToast(data.error || 'Erreur de chargement', 'error');
      }
    } catch (error) {
      console.error('Erreur pagination:', error);
      TrustPick.showToast('Erreur de chargement', 'error');
    } finally {
      this.isLoading = false;
      this.updateControls();
    }
  }

  /**
   * Afficher les éléments
   */
  renderItems(newItems) {
    if (this.currentPage === 1 && newItems.length === 0) {
      this.showEmpty();
      return;
    }

    // Enlever le message de chargement
    const loading = this.itemsContainer.querySelector('.loading-spinner');
    if (loading) loading.remove();

    // Ajouter les nouveaux éléments
    newItems.forEach(item => {
      const element = this.renderItem(item);
      this.itemsContainer.insertAdjacentHTML('beforeend', element);
    });

    this.items.push(...newItems);
  }

  /**
   * Mettre à jour les contrôles
   */
  updateControls() {
    if (this.hasMore) {
      this.controlsContainer.innerHTML = `
        <button class="btn-load-more" onclick="this.disabled=true; window.pagination_${this.container.id}.loadPage()">
          📄 Voir plus (${this.items.length} éléments chargés)
        </button>
      `;
    } else {
      if (this.items.length > 0) {
        this.controlsContainer.innerHTML = `
          <p class="text-center text-muted" style="margin-top: 1rem;">
            ✅ Tous les éléments sont affichés (${this.items.length} total)
          </p>
        `;
      }
    }
  }

  /**
   * Afficher le loading
   */
  showLoading() {
    if (this.currentPage === 1) {
      this.itemsContainer.innerHTML = `
        <div style="text-align: center; padding: 3rem;">
          <div class="loading-spinner"></div>
          <p class="text-muted" style="margin-top: 1rem;">${this.loadingMessage}</p>
        </div>
      `;
    }

    this.controlsContainer.innerHTML = `
      <div style="text-align: center; padding: 1rem;">
        <div class="loading-spinner"></div>
      </div>
    `;
  }

  /**
   * Afficher le message vide
   */
  showEmpty() {
    this.itemsContainer.innerHTML = `
      <div class="empty-state">
        <div class="empty-state-icon">📭</div>
        <h3 class="empty-state-title">Aucun élément</h3>
        <p class="empty-state-description">${this.emptyMessage}</p>
      </div>
    `;
    this.controlsContainer.innerHTML = '';
  }

  /**
   * Réinitialiser
   */
  reset() {
    this.currentPage = 1;
    this.hasMore = true;
    this.items = [];
    this.itemsContainer.innerHTML = '';
    this.controlsContainer.innerHTML = '';
    this.loadPage();
  }
}

// ============================================================
// RENDERERS PRÉDÉFINIS
// ============================================================

const PaginationRenderers = {
  /**
   * Renderer pour produits
   */
  product(item) {
    return `
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="product-card">
          <img src="${item.image || 'assets/img/placeholder.jpg'}" alt="${item.title}" class="product-image">
          <div class="product-info">
            <h4 class="product-title">${item.title}</h4>
            <p class="product-company">${item.company_name || 'Entreprise'}</p>
            <div class="product-rating">
              ${TrustPick.renderStars(item.avg_rating || 0)} 
              <span>(${item.reviews_count || 0} avis)</span>
            </div>
            <p class="product-price">${TrustPick.formatFCFA(item.price || 0)}</p>
            <a href="product-detail.php?id=${item.id}" class="btn btn-tp-primary w-100">
              Voir le produit
            </a>
          </div>
        </div>
      </div>
    `;
  },

  /**
   * Renderer pour avis
   */
  review(item) {
    return `
      <div class="review-card">
        <div class="review-header">
          <div>
            <strong class="review-author">${item.user_name || 'Utilisateur'}</strong>
            <div class="review-rating">${TrustPick.renderStars(item.rating)}</div>
          </div>
          <small class="review-date">${TrustPick.formatRelativeTime(item.created_at)}</small>
        </div>
        ${item.title ? `<h5 class="review-title">${item.title}</h5>` : ''}
        <p class="review-body">${item.body}</p>
        <div class="review-actions">
          <button class="review-action-btn" onclick="likeReview(${item.id})">
            👍 <span id="likes-${item.id}">${item.likes_count || 0}</span>
          </button>
          <button class="review-action-btn" onclick="dislikeReview(${item.id})">
            👎 <span id="dislikes-${item.id}">${item.dislikes_count || 0}</span>
          </button>
        </div>
      </div>
    `;
  },

  /**
   * Renderer pour notifications
   */
  notification(item) {
    const icons = {
      task_reminder: '✅',
      new_product: '🛍️',
      new_review: '⭐',
      reward: '💰',
      referral: '👥',
      withdrawal: '💵',
      system: 'ℹ️'
    };

    return `
      <div class="notification-card ${!item.is_read ? 'unread' : ''}" 
           onclick="markNotificationRead(${item.id})">
        <div class="notif-icon">${icons[item.type] || 'ℹ️'}</div>
        <div class="notif-content">
          <h6>${item.title}</h6>
          <p>${item.message}</p>
          <small>${TrustPick.formatRelativeTime(item.created_at)}</small>
        </div>
      </div>
    `;
  },

  /**
   * Renderer pour transactions
   */
  transaction(item) {
    const isPositive = parseFloat(item.amount) > 0;
    return `
      <div class="transaction-item">
        <div class="transaction-info">
          <h6>${item.description || 'Transaction'}</h6>
          <small>${TrustPick.formatRelativeTime(item.created_at)}</small>
        </div>
        <div class="transaction-amount ${isPositive ? 'positive' : 'negative'}">
          ${isPositive ? '+' : ''}${TrustPick.formatFCFA(item.amount)}
        </div>
      </div>
    `;
  },

  /**
   * Renderer pour utilisateurs (admin)
   */
  user(item) {
    return `
      <tr>
        <td><strong>${item.cau}</strong></td>
        <td>${item.name}</td>
        <td><span class="badge-tp badge-${item.role}">${item.role}</span></td>
        <td>${TrustPick.formatFCFA(item.balance || 0)}</td>
        <td><span class="badge-tp badge-${item.is_active ? 'active' : 'inactive'}">
          ${item.is_active ? 'Actif' : 'Inactif'}
        </span></td>
        <td>
          <button class="btn btn-sm btn-primary" onclick="editUser(${item.id})">Modifier</button>
        </td>
      </tr>
    `;
  }
};

// ============================================================
// HELPERS D'INITIALISATION
// ============================================================

function initProductsPagination(containerId = 'products-container') {
  return new TrustPickPagination({
    endpoint: TrustPick.API_BASE + '/products-list.php',
    containerId: containerId,
    itemsPerPage: 5,
    renderItem: PaginationRenderers.product,
    emptyMessage: 'Aucun produit disponible pour le moment'
  });
}

function initNotificationsPagination(containerId = 'notifications-container') {
  return new TrustPickPagination({
    endpoint: TrustPick.API_BASE + '/notifications-list.php',
    containerId: containerId,
    itemsPerPage: 5,
    renderItem: PaginationRenderers.notification,
    emptyMessage: 'Aucune notification'
  });
}

function initTransactionsPagination(containerId = 'transactions-container') {
  return new TrustPickPagination({
    endpoint: TrustPick.API_BASE + '/transactions-list.php',
    containerId: containerId,
    itemsPerPage: 5,
    renderItem: PaginationRenderers.transaction,
    emptyMessage: 'Aucune transaction'
  });
}
