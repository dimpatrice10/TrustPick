/**
 * Système de Notifications Toast TrustPick V2
 * Affiche des notifications élégantes avec animations
 */

class ToastNotification {
  constructor() {
    this.container = null;
    this.init();
  }

  init() {
    // Créer le conteneur de toasts s'il n'existe pas
    if (!document.getElementById('toast-container')) {
      this.container = document.createElement('div');
      this.container.id = 'toast-container';
      this.container.className = 'toast-container position-fixed top-0 end-0 p-3';
      this.container.style.zIndex = '9999';
      document.body.appendChild(this.container);
    } else {
      this.container = document.getElementById('toast-container');
    }
  }

  /**
   * Afficher un toast
   * @param {string} type - success, error, warning, info
   * @param {string} message - Message à afficher
   * @param {number} duration - Durée en ms (défaut: 5000)
   */
  show(type, message, duration = 5000) {
    const toastId = 'toast-' + Date.now();

    const iconMap = {
      success: '✓',
      error: '✗',
      warning: '⚠',
      info: 'ℹ'
    };

    const bgMap = {
      success: 'bg-success',
      error: 'bg-danger',
      warning: 'bg-warning',
      info: 'bg-info'
    };

    const titleMap = {
      success: 'Succès',
      error: 'Erreur',
      warning: 'Attention',
      info: 'Information'
    };

    const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgMap[type] || 'bg-secondary'} border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${iconMap[type] || ''} ${titleMap[type] || 'Notification'}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

    const toastElement = document.createElement('div');
    toastElement.innerHTML = toastHTML;
    this.container.appendChild(toastElement.firstElementChild);

    // Auto-dismiss après durée spécifiée
    setTimeout(() => {
      const toast = document.getElementById(toastId);
      if (toast) {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }
    }, duration);

    // Bouton fermeture manuelle
    const closeBtn = document.getElementById(toastId).querySelector('.btn-close');
    closeBtn.addEventListener('click', () => {
      const toast = document.getElementById(toastId);
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    });
  }

  success(message, duration) {
    this.show('success', message, duration);
  }

  error(message, duration) {
    this.show('error', message, duration);
  }

  warning(message, duration) {
    this.show('warning', message, duration);
  }

  info(message, duration) {
    this.show('info', message, duration);
  }
}

// Initialiser l'instance globale
const toast = new ToastNotification();

// Afficher les toasts depuis PHP au chargement de la page
document.addEventListener('DOMContentLoaded', function () {
  // Les toasts PHP sont injectés via data-toasts
  const toastsData = document.body.getAttribute('data-toasts');
  if (toastsData) {
    try {
      const toasts = JSON.parse(toastsData);
      toasts.forEach(t => {
        toast.show(t.type, t.message, 5000);
      });
    } catch (e) {
      console.error('Erreur chargement toasts:', e);
    }
  }
});

// Exposer globalement
window.toast = toast;
