/**
 * TrustPick V2 - PWA Installation Manager
 * Gère l'installation PWA sur Android, iOS et Desktop
 */

(function () {
  'use strict';

  // Variable pour stocker l'événement d'installation différé
  let deferredPrompt = null;
  let isInstalled = false;

  // Éléments du DOM
  const installBtn = document.getElementById('pwa-install-btn');
  const iosBanner = document.getElementById('ios-install-banner');
  const iosBannerClose = document.getElementById('ios-banner-close');

  /**
   * Détection du type d'appareil
   */
  const DeviceDetect = {
    isIOS: function () {
      return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    },
    isAndroid: function () {
      return /Android/.test(navigator.userAgent);
    },
    isSafari: function () {
      return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    },
    isStandalone: function () {
      return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true ||
        document.referrer.includes('android-app://')
      );
    },
    isMobile: function () {
      return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }
  };

  /**
   * Vérifier si l'app est déjà installée
   */
  function checkIfInstalled() {
    if (DeviceDetect.isStandalone()) {
      isInstalled = true;
      hideInstallButton();
      hideIOSBanner();
      console.log('[PWA] Application déjà installée');
      return true;
    }
    return false;
  }

  /**
   * Afficher le bouton d'installation
   */
  function showInstallButton() {
    if (installBtn && !isInstalled) {
      installBtn.classList.remove('d-none');
      installBtn.style.display = 'inline-flex';
      console.log('[PWA] Bouton installation affiché');
    }
  }

  /**
   * Masquer le bouton d'installation
   */
  function hideInstallButton() {
    if (installBtn) {
      installBtn.classList.add('d-none');
      installBtn.style.display = 'none';
    }
  }

  /**
   * Afficher la bannière iOS
   */
  function showIOSBanner() {
    if (iosBanner && !isInstalled && !sessionStorage.getItem('iosBannerDismissed')) {
      iosBanner.classList.remove('d-none');
      iosBanner.style.display = 'block';
      console.log('[PWA] Bannière iOS affichée');
    }
  }

  /**
   * Masquer la bannière iOS
   */
  function hideIOSBanner() {
    if (iosBanner) {
      iosBanner.classList.add('d-none');
      iosBanner.style.display = 'none';
    }
  }

  /**
   * Gérer l'événement beforeinstallprompt (Android/Chrome)
   */
  function handleBeforeInstallPrompt(e) {
    console.log('[PWA] beforeinstallprompt déclenché');

    // Empêcher l'affichage automatique de Chrome
    e.preventDefault();

    // Stocker l'événement pour l'utiliser plus tard
    deferredPrompt = e;

    // Afficher notre bouton d'installation
    showInstallButton();
  }

  /**
   * Déclencher l'installation (Android/Chrome)
   */
  async function triggerInstall() {
    if (!deferredPrompt) {
      console.log('[PWA] Pas de prompt disponible');
      return;
    }

    console.log("[PWA] Déclenchement de l'installation");

    // Afficher le prompt d'installation
    deferredPrompt.prompt();

    // Attendre le choix de l'utilisateur
    const { outcome } = await deferredPrompt.userChoice;

    console.log('[PWA] Résultat installation:', outcome);

    if (outcome === 'accepted') {
      console.log('[PWA] Installation acceptée');
      isInstalled = true;
      hideInstallButton();

      // Afficher une notification de succès
      showToast('TrustPick a été installé avec succès !', 'success');
    } else {
      console.log('[PWA] Installation refusée');
    }

    // Réinitialiser le prompt
    deferredPrompt = null;
  }

  /**
   * Gérer l'événement appinstalled
   */
  function handleAppInstalled() {
    console.log('[PWA] Application installée');
    isInstalled = true;
    deferredPrompt = null;
    hideInstallButton();
    hideIOSBanner();
  }

  /**
   * Afficher un toast de notification
   */
  function showToast(message, type = 'info') {
    // Créer le toast
    const toast = document.createElement('div');
    toast.className = `pwa-toast pwa-toast-${type}`;
    toast.innerHTML = `
      <i class="bi ${type === 'success' ? 'bi-check-circle-fill' : 'bi-info-circle-fill'}"></i>
      <span>${message}</span>
    `;

    // Ajouter au DOM
    document.body.appendChild(toast);

    // Animation d'entrée
    setTimeout(() => toast.classList.add('show'), 10);

    // Supprimer après 4 secondes
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

  /**
   * Initialisation au chargement du DOM
   */
  function init() {
    console.log('[PWA] Initialisation PWA Install Manager');

    // Vérifier si déjà installé
    if (checkIfInstalled()) {
      return;
    }

    // Écouter l'événement beforeinstallprompt (Android/Chrome/Edge)
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

    // Écouter l'événement appinstalled
    window.addEventListener('appinstalled', handleAppInstalled);

    // Configurer le bouton d'installation
    if (installBtn) {
      installBtn.addEventListener('click', triggerInstall);
    }

    // Gestion spéciale pour iOS
    if (DeviceDetect.isIOS() && DeviceDetect.isSafari()) {
      console.log('[PWA] Appareil iOS détecté');
      showIOSBanner();

      // Bouton de fermeture de la bannière iOS
      if (iosBannerClose) {
        iosBannerClose.addEventListener('click', function () {
          hideIOSBanner();
          sessionStorage.setItem('iosBannerDismissed', 'true');
        });
      }
    }

    // Vérifier si l'utilisateur est sur mobile mais pas iOS
    if (DeviceDetect.isMobile() && !DeviceDetect.isIOS()) {
      // Sur Android, le bouton sera affiché quand beforeinstallprompt se déclenche
      console.log('[PWA] Appareil mobile Android détecté');
    }
  }

  // Attendre que le DOM soit prêt
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Exposer les fonctions globalement pour le débogage
  window.TrustPickPWA = {
    triggerInstall: triggerInstall,
    isInstalled: function () {
      return isInstalled;
    },
    showIOSBanner: showIOSBanner,
    hideIOSBanner: hideIOSBanner
  };
})();
