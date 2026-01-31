/* ============================================================
   TRUSTPICK V2 - CORE JAVASCRIPT
   Fonctions globales et utilitaires
   ============================================================ */

const TrustPick = {
  // Configuration API
  API_BASE: '/TrustPick/api/v2',

  // Session utilisateur
  currentUser: null,

  /**
   * Faire un appel API
   */
  async api(endpoint, options = {}) {
    const url = `${this.API_BASE}/${endpoint}`;
    const config = {
      method: options.method || 'GET',
      headers: {
        'Content-Type': 'application/json',
        ...options.headers
      }
    };

    if (options.body) {
      config.body = JSON.stringify(options.body);
    }

    try {
      const response = await fetch(url, config);
      const data = await response.json();

      if (!data.success && data.error) {
        this.showToast(data.error, 'error');
        return null;
      }

      return data;
    } catch (error) {
      console.error('API Error:', error);
      this.showToast('Erreur de connexion au serveur', 'error');
      return null;
    }
  },

  /**
   * Afficher un toast
   */
  showToast(message, type = 'info', title = '') {
    const container = document.getElementById('toast-container') || this.createToastContainer();

    const icons = {
      success: '✅',
      error: '❌',
      warning: '⚠️',
      info: 'ℹ️'
    };

    const titles = {
      success: title || 'Succès',
      error: title || 'Erreur',
      warning: title || 'Attention',
      info: title || 'Information'
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
      <div class="toast-icon">${icons[type]}</div>
      <div class="toast-content">
        <div class="toast-title">${titles[type]}</div>
        <div class="toast-message">${message}</div>
      </div>
      <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;

    container.appendChild(toast);

    // Auto-remove après 5 secondes
    setTimeout(() => {
      toast.style.opacity = '0';
      setTimeout(() => toast.remove(), 300);
    }, 5000);
  },

  /**
   * Créer le conteneur de toasts
   */
  createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
    return container;
  },

  /**
   * Formater un montant FCFA
   */
  formatFCFA(amount) {
    return new Intl.NumberFormat('fr-FR').format(amount) + ' FCFA';
  },

  /**
   * Formater une date relative
   */
  formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;

    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (seconds < 60) return "À l'instant";
    if (minutes < 60) return `Il y a ${minutes} minute${minutes > 1 ? 's' : ''}`;
    if (hours < 24) return `Il y a ${hours} heure${hours > 1 ? 's' : ''}`;
    if (days < 7) return `Il y a ${days} jour${days > 1 ? 's' : ''}`;

    return date.toLocaleDateString('fr-FR');
  },

  /**
   * Afficher un modal
   */
  showModal(content, title = '') {
    const existing = document.getElementById('tp-modal');
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.id = 'tp-modal';
    modal.className = 'modal-overlay';
    modal.innerHTML = `
      <div class="modal-content">
        <div class="modal-header">
          <h3 class="modal-title">${title}</h3>
          <button class="modal-close" onclick="TrustPick.closeModal()">×</button>
        </div>
        <div class="modal-body">
          ${content}
        </div>
      </div>
    `;

    document.body.appendChild(modal);

    setTimeout(() => modal.classList.add('active'), 10);

    // Fermer au clic sur l'overlay
    modal.addEventListener('click', e => {
      if (e.target === modal) this.closeModal();
    });
  },

  /**
   * Fermer le modal
   */
  closeModal() {
    const modal = document.getElementById('tp-modal');
    if (modal) {
      modal.classList.remove('active');
      setTimeout(() => modal.remove(), 300);
    }
  },

  /**
   * Confirmer une action
   */
  async confirm(message, title = 'Confirmation') {
    return new Promise(resolve => {
      const content = `
        <p style="margin-bottom: 1.5rem;">${message}</p>
        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
          <button class="btn btn-secondary" onclick="TrustPick.closeModal(); window.confirmResolve(false)">Annuler</button>
          <button class="btn-tp-primary" onclick="TrustPick.closeModal(); window.confirmResolve(true)">Confirmer</button>
        </div>
      `;

      window.confirmResolve = resolve;
      this.showModal(content, title);
    });
  },

  /**
   * Copier dans le presse-papiers
   */
  async copyToClipboard(text) {
    try {
      await navigator.clipboard.writeText(text);
      this.showToast('Copié dans le presse-papiers !', 'success');
    } catch (error) {
      // Fallback pour anciens navigateurs
      const input = document.createElement('textarea');
      input.value = text;
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      document.body.removeChild(input);
      this.showToast('Copié dans le presse-papiers !', 'success');
    }
  },

  /**
   * Générer des étoiles de notation
   */
  renderStars(rating, maxStars = 5) {
    let stars = '';
    for (let i = 1; i <= maxStars; i++) {
      stars += i <= rating ? '⭐' : '☆';
    }
    return stars;
  },

  /**
   * Valider un formulaire
   */
  validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;
    const inputs = form.querySelectorAll('input[required], textarea[required]');

    inputs.forEach(input => {
      if (!input.value.trim()) {
        input.classList.add('error');
        isValid = false;
      } else {
        input.classList.remove('error');
      }
    });

    return isValid;
  },

  /**
   * Débounce une fonction
   */
  debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  /**
   * Initialisation globale
   */
  init() {
    console.log('🚀 TrustPick V2 chargé');

    // Charger l'utilisateur depuis la session si existe
    const userDataElement = document.getElementById('user-data');
    if (userDataElement) {
      try {
        this.currentUser = JSON.parse(userDataElement.textContent);
      } catch (e) {
        console.error('Erreur parsing user data');
      }
    }

    // Créer le conteneur de toasts
    this.createToastContainer();
  }
};

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', () => {
  TrustPick.init();
});
