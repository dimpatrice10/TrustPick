/**
 * TrustPick V2 - PWA Install Manager
 * Gère l'installation de l'application sur toutes les plateformes
 * iOS, Android, Windows, macOS, Linux
 */

(function () {
  'use strict';

  // État de l'installation
  let deferredPrompt = null;
  let isInstalled = false;
  let platform = detectPlatform();

  /**
   * Détecter la plateforme de l'utilisateur
   */
  function detectPlatform() {
    const ua = navigator.userAgent || navigator.vendor || window.opera;

    // iOS
    if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) {
      return 'ios';
    }

    // Android
    if (/android/i.test(ua)) {
      return 'android';
    }

    // Windows
    if (/Win/.test(navigator.platform)) {
      return 'windows';
    }

    // macOS
    if (/Mac/.test(navigator.platform)) {
      return 'macos';
    }

    // Linux
    if (/Linux/.test(navigator.platform)) {
      return 'linux';
    }

    return 'other';
  }

  /**
   * Vérifier si l'app est déjà installée
   */
  function checkIfInstalled() {
    // Mode standalone (installé)
    if (window.matchMedia('(display-mode: standalone)').matches) {
      return true;
    }

    // iOS standalone
    if (window.navigator.standalone === true) {
      return true;
    }

    // Vérifier via getInstalledRelatedApps (Chrome)
    if ('getInstalledRelatedApps' in navigator) {
      navigator.getInstalledRelatedApps().then(apps => {
        if (apps.length > 0) {
          isInstalled = true;
          hideInstallButton();
        }
      });
    }

    return false;
  }

  /**
   * Initialiser les écouteurs d'événements
   */
  function init() {
    isInstalled = checkIfInstalled();

    if (isInstalled) {
      console.log('[PWA] Application déjà installée');
      hideInstallButton();
      return;
    }

    // Écouter l'événement beforeinstallprompt (Chrome, Edge, Samsung Internet)
    window.addEventListener('beforeinstallprompt', e => {
      console.log('[PWA] beforeinstallprompt déclenché');
      e.preventDefault();
      deferredPrompt = e;
      showInstallButton();
    });

    // Écouter l'événement appinstalled
    window.addEventListener('appinstalled', () => {
      console.log('[PWA] Application installée avec succès');
      isInstalled = true;
      deferredPrompt = null;
      hideInstallButton();
      showSuccessToast('TrustPick a été installé avec succès !');
    });

    // Afficher le bouton pour iOS (pas de beforeinstallprompt)
    if (platform === 'ios' && !isInstalled) {
      showInstallButton();
    }

    // Attacher les handlers aux boutons d'installation
    document.addEventListener('click', e => {
      if (e.target.closest('.pwa-install-btn')) {
        handleInstallClick();
      }
      if (e.target.closest('.pwa-install-dismiss')) {
        dismissInstallPrompt();
      }
    });

    // Afficher la bannière après un délai si pas installé
    setTimeout(() => {
      if (!isInstalled && (deferredPrompt || platform === 'ios')) {
        showInstallBanner();
      }
    }, 30000); // 30 secondes
  }

  /**
   * Gérer le clic sur le bouton d'installation
   */
  async function handleInstallClick() {
    console.log('[PWA] Clic sur installer, plateforme:', platform);

    // iOS - Afficher les instructions
    if (platform === 'ios') {
      showIOSInstructions();
      return;
    }

    // Autres plateformes - Utiliser le prompt natif
    if (deferredPrompt) {
      try {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log('[PWA] Choix utilisateur:', outcome);

        if (outcome === 'accepted') {
          deferredPrompt = null;
        }
      } catch (err) {
        console.error('[PWA] Erreur prompt:', err);
        showManualInstructions();
      }
    } else {
      // Pas de prompt disponible - afficher instructions manuelles
      showManualInstructions();
    }
  }

  /**
   * Afficher le bouton d'installation
   */
  function showInstallButton() {
    const btn = document.getElementById('pwa-install-btn');
    if (btn) {
      btn.style.display = 'inline-flex';
      btn.classList.add('pwa-btn-visible');
    }

    const floatingBtn = document.getElementById('pwa-install-floating');
    if (floatingBtn) {
      floatingBtn.style.display = 'flex';
    }
  }

  /**
   * Cacher le bouton d'installation
   */
  function hideInstallButton() {
    const btn = document.getElementById('pwa-install-btn');
    if (btn) {
      btn.style.display = 'none';
    }

    const floatingBtn = document.getElementById('pwa-install-floating');
    if (floatingBtn) {
      floatingBtn.style.display = 'none';
    }

    hideInstallBanner();
  }

  /**
   * Afficher la bannière d'installation
   */
  function showInstallBanner() {
    let banner = document.getElementById('pwa-install-banner');

    if (!banner) {
      banner = document.createElement('div');
      banner.id = 'pwa-install-banner';
      banner.className = 'pwa-install-banner';
      banner.innerHTML = `
        <div class="pwa-banner-content">
          <div class="pwa-banner-icon">
            <i class="bi bi-download"></i>
          </div>
          <div class="pwa-banner-text">
            <strong>Installer TrustPick</strong>
            <span>Accédez rapidement à l'app depuis votre écran d'accueil</span>
          </div>
          <div class="pwa-banner-actions">
            <button class="btn btn-primary btn-sm pwa-install-btn">
              <i class="bi bi-download me-1"></i>Installer
            </button>
            <button class="btn btn-link btn-sm pwa-install-dismiss">Plus tard</button>
          </div>
        </div>
      `;
      document.body.appendChild(banner);
    }

    setTimeout(() => banner.classList.add('show'), 100);
  }

  /**
   * Cacher la bannière d'installation
   */
  function hideInstallBanner() {
    const banner = document.getElementById('pwa-install-banner');
    if (banner) {
      banner.classList.remove('show');
      setTimeout(() => banner.remove(), 300);
    }
  }

  /**
   * Rejeter temporairement l'installation
   */
  function dismissInstallPrompt() {
    hideInstallBanner();
    // Ne plus afficher pendant cette session
    sessionStorage.setItem('pwa-dismissed', 'true');
  }

  /**
   * Afficher les instructions pour iOS
   */
  function showIOSInstructions() {
    const modal = createModal(
      'Installer TrustPick sur iOS',
      `
        <div class="pwa-instructions">
          <div class="pwa-step">
            <div class="pwa-step-number">1</div>
            <div class="pwa-step-content">
              <p>Appuyez sur le bouton <strong>Partager</strong></p>
              <div class="pwa-step-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/>
                  <polyline points="16 6 12 2 8 6"/>
                  <line x1="12" y1="2" x2="12" y2="15"/>
                </svg>
                <span>en bas de Safari</span>
              </div>
            </div>
          </div>
          <div class="pwa-step">
            <div class="pwa-step-number">2</div>
            <div class="pwa-step-content">
              <p>Faites défiler et appuyez sur</p>
              <div class="pwa-step-action">
                <i class="bi bi-plus-square"></i>
                <span>Sur l'écran d'accueil</span>
              </div>
            </div>
          </div>
          <div class="pwa-step">
            <div class="pwa-step-number">3</div>
            <div class="pwa-step-content">
              <p>Appuyez sur <strong>Ajouter</strong> en haut à droite</p>
            </div>
          </div>
        </div>
      `
    );
    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('show'), 10);
  }

  /**
   * Afficher les instructions manuelles (autres navigateurs)
   */
  function showManualInstructions() {
    let instructions = '';

    switch (platform) {
      case 'android':
        instructions = `
          <p><strong>Sur Chrome/Edge Android :</strong></p>
          <ol>
            <li>Appuyez sur le menu <i class="bi bi-three-dots-vertical"></i> (3 points)</li>
            <li>Sélectionnez "Ajouter à l'écran d'accueil"</li>
            <li>Confirmez en appuyant sur "Ajouter"</li>
          </ol>
        `;
        break;
      case 'windows':
        instructions = `
          <p><strong>Sur Windows (Chrome/Edge) :</strong></p>
          <ol>
            <li>Cliquez sur l'icône <i class="bi bi-box-arrow-in-down"></i> dans la barre d'adresse</li>
            <li>Ou utilisez le menu → "Installer TrustPick"</li>
            <li>Confirmez l'installation</li>
          </ol>
        `;
        break;
      case 'macos':
        instructions = `
          <p><strong>Sur macOS (Chrome/Edge) :</strong></p>
          <ol>
            <li>Cliquez sur l'icône <i class="bi bi-box-arrow-in-down"></i> dans la barre d'adresse</li>
            <li>Ou allez dans le menu Chrome → "Installer TrustPick"</li>
            <li>Confirmez l'installation</li>
          </ol>
          <p><strong>Sur Safari :</strong></p>
          <ol>
            <li>Fichier → "Ajouter au Dock"</li>
          </ol>
        `;
        break;
      default:
        instructions = `
          <p>Ouvrez le menu de votre navigateur et cherchez l'option :</p>
          <ul>
            <li>"Installer l'application"</li>
            <li>"Ajouter à l'écran d'accueil"</li>
            <li>"Créer un raccourci"</li>
          </ul>
        `;
    }

    const modal = createModal('Installer TrustPick', `<div class="pwa-instructions">${instructions}</div>`);
    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('show'), 10);
  }

  /**
   * Créer une modal
   */
  function createModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'pwa-modal-overlay';
    modal.innerHTML = `
      <div class="pwa-modal">
        <div class="pwa-modal-header">
          <h5 class="pwa-modal-title">${title}</h5>
          <button type="button" class="pwa-modal-close" onclick="this.closest('.pwa-modal-overlay').remove()">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <div class="pwa-modal-body">
          ${content}
        </div>
        <div class="pwa-modal-footer">
          <button type="button" class="btn btn-secondary" onclick="this.closest('.pwa-modal-overlay').remove()">
            Fermer
          </button>
        </div>
      </div>
    `;
    return modal;
  }

  /**
   * Afficher un toast de succès
   */
  function showSuccessToast(message) {
    const toast = document.createElement('div');
    toast.className = 'pwa-toast success';
    toast.innerHTML = `
      <i class="bi bi-check-circle-fill"></i>
      <span>${message}</span>
    `;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
      toast.classList.remove('show');
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  }

  /**
   * API publique
   */
  window.TrustPickPWA = {
    init: init,
    install: handleInstallClick,
    isInstalled: () => isInstalled,
    getPlatform: () => platform,
    canInstall: () => !isInstalled && (deferredPrompt !== null || platform === 'ios'),
    showBanner: showInstallBanner
  };

  // Auto-initialisation au chargement du DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
