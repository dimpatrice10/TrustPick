<?php
// Test complet PWA - Version FINALE
require_once __DIR__ . '/../includes/url.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Test PWA Final - TrustPick</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
    .test-card { background: white; border-radius: 16px; padding: 24px; margin-bottom: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
    .test-result { padding: 12px; border-radius: 8px; margin-top: 12px; font-family: monospace; font-size: 13px; }
    .test-result.success { background: #d1fae5; color: #065f46; border: 2px solid #10b981; }
    .test-result.error { background: #fee2e2; color: #991b1b; border: 2px solid #ef4444; }
    .test-result.warning { background: #fef3c7; color: #92400e; border: 2px solid #f59e0b; }
    .test-result.info { background: #dbeafe; color: #1e40af; border: 2px solid #3b82f6; }
    .status-dot { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 8px; }
    .status-dot.green { background: #10b981; }
    .status-dot.red { background: #ef4444; }
    .status-dot.yellow { background: #f59e0b; }
    .test-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .test-header i { font-size: 24px; color: #667eea; }
    .test-header h5 { margin: 0; color: #1f2937; }
    .url-display { background: #f3f4f6; padding: 8px 12px; border-radius: 6px; word-break: break-all; font-size: 12px; }
    .btn-test { margin-top: 12px; }
    .summary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; margin-bottom: 24px; }
    .summary h2 { font-size: 24px; margin-bottom: 12px; }
    .summary .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 16px; margin-top: 16px; }
    .summary .stat-box { background: rgba(255,255,255,0.2); padding: 12px; border-radius: 8px; text-align: center; }
    .summary .stat-box .number { font-size: 32px; font-weight: bold; }
    .summary .stat-box .label { font-size: 12px; opacity: 0.9; }
  </style>
</head>
<body>
  <div class="container">
    <div class="summary">
      <h2><i class="bi bi-check-circle"></i> Test PWA Complet - TrustPick</h2>
      <p>Cette page teste TOUS les aspects de la PWA pour identifier et résoudre les problèmes de redirection</p>
      <div class="stats">
        <div class="stat-box">
          <div class="number" id="total-tests">0</div>
          <div class="label">Tests Total</div>
        </div>
        <div class="stat-box">
          <div class="number" id="passed-tests">0</div>
          <div class="label">Réussis ✅</div>
        </div>
        <div class="stat-box">
          <div class="number" id="failed-tests">0</div>
          <div class="label">Échoués ❌</div>
        </div>
        <div class="stat-box">
          <div class="number" id="warning-tests">0</div>
          <div class="label">Avertissements ⚠️</div>
        </div>
      </div>
    </div>

    <!-- Test 1: Manifest Racine -->
    <div class="test-card">
      <div class="test-header">
        <i class="bi bi-file-earmark-code"></i>
        <h5>Test 1: Manifest PWA (Racine)</h5>
      </div>
      <p>Test du fichier <code>/pwa-manifest.json</code> à la racine</p>
      <div class="url-display" id="manifest-root-url">URL: <?= url('pwa-manifest.json') ?></div>
      <button class="btn btn-primary btn-sm btn-test" onclick="testManifestRoot()">
        <i class="bi bi-play-fill"></i> Tester
      </button>
      <div id="manifest-root-result"></div>
    </div>

    <!-- Test 2: Manifest Subdirectory -->
    <div class="test-card">
      <div class="test-header">
        <i class="bi bi-folder2-open"></i>
        <h5>Test 2: Manifest PWA (Sous-dossier)</h5>
      </div>
      <p>Test du fichier <code>/pwa/manifest.json</code> dans le sous-dossier</p>
      <div class="url-display" id="manifest-pwa-url">URL: <?= url('pwa/manifest.json') ?></div>
      <button class="btn btn-primary btn-sm btn-test" onclick="testManifestPwa()">
        <i class="bi bi-play-fill"></i> Tester
      </button>
      <div id="manifest-pwa-result"></div>
    </div>

    <!-- Test 3: Service Worker Racine -->
    <div class="test-card">
      <div class="test-header">
        <i class="bi bi-gear"></i>
        <h5>Test 3: Service Worker (Racine)</h5>
      </div>
      <p>Test du fichier <code>/pwa-worker.js</code> à la racine</p>
      <div class="url-display" id="sw-root-url">URL: <?= url('pwa-worker.js') ?></div>
      <button class="btn btn-primary btn-sm btn-test" onclick="testSwRoot()">
        <i class="bi bi-play-fill"></i> Tester
      </button>
      <div id="sw-root-result"></div>
    </div>

    <!-- Test 4: Service Worker Subdirectory -->
    <div class="test-card">
      <div class="test-header">
        <i class="bi bi-layers"></i>
        <h5>Test 4: Service Worker (Sous-dossier)</h5>
      </div>
      <p>Test du fichier <code>/pwa/sw.js</code> dans le sous-dossier</p>
      <div class="url-display" id="sw-pwa-url">URL: <?= url('pwa/sw.js') ?></div>
      <button class="btn btn-primary btn-sm btn-test" onclick="testSwPwa()">
        <i class="bi bi-play-fill"></i> Tester
      </button>
      <div id="sw-pwa-result"></div>
    </div>

    <!-- Test 5: Registration Service Worker -->
    <div class="test-card">
      <div class="test-header">
        <i class="bi bi-download"></i>
        <h5>Test 5: Enregistrement Service Worker</h5>
      </div>
      <p>Test de l'enregistrement réel du Service Worker</p>
      <button class="btn btn-success btn-sm btn-test" onclick="testSwRegistration()">
        <i class="bi bi-play-fill"></i> Enregistrer SW
      </button>
      <button class="btn btn-danger btn-sm btn-test" onclick="unregisterAllSw()">
        <i class="bi bi-trash"></i> Désinscrire tous les SW
      </button>
      <div id="sw-registration-result"></div>
    </div>

    <!-- Test 6: Installation PWA -->
    <div class="test-card">
      <div class="test-header">
        <i class="bi bi-app-indicator"></i>
        <h5>Test 6: Installation PWA</h5>
      </div>
      <p>Test de la disponibilité du prompt d'installation</p>
      <button class="btn btn-success btn-sm btn-test" onclick="testInstallPrompt()">
        <i class="bi bi-download"></i> Tester Installation
      </button>
      <div id="install-result"></div>
    </div>

    <!-- Test 7: Offline Page -->
    <div class="test-card">
      <div class="test-header">
        <i class="bi bi-wifi-off"></i>
        <h5>Test 7: Page Offline</h5>
      </div>
      <p>Test du fichier <code>/offline.html</code></p>
      <div class="url-display" id="offline-url">URL: <?= url('offline.html') ?></div>
      <button class="btn btn-primary btn-sm btn-test" onclick="testOfflinePage()">
        <i class="bi bi-play-fill"></i> Tester
      </button>
      <div id="offline-result"></div>
    </div>

    <!-- Bouton tout tester -->
    <div class="text-center mt-4">
      <button class="btn btn-lg btn-success" onclick="runAllTests()">
        <i class="bi bi-play-circle"></i> Lancer TOUS les Tests
      </button>
    </div>
  </div>

  <script>
    let stats = { total: 0, passed: 0, failed: 0, warning: 0 };
    let installPrompt = null;

    // Capturer le prompt d'installation
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      installPrompt = e;
      console.log('Install prompt capturé');
    });

    function updateStats() {
      document.getElementById('total-tests').textContent = stats.total;
      document.getElementById('passed-tests').textContent = stats.passed;
      document.getElementById('failed-tests').textContent = stats.failed;
      document.getElementById('warning-tests').textContent = stats.warning;
    }

    function showResult(elementId, type, message) {
      const el = document.getElementById(elementId);
      const dot = type === 'success' ? 'green' : type === 'error' ? 'red' : 'yellow';
      const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : '⚠️';
      
      el.innerHTML = `
        <div class="test-result ${type}">
          <span class="status-dot ${dot}"></span>
          ${icon} ${message}
        </div>
      `;

      stats.total++;
      if (type === 'success') stats.passed++;
      else if (type === 'error') stats.failed++;
      else if (type === 'warning') stats.warning++;
      
      updateStats();
    }

    async function testManifestRoot() {
      const url = '<?= url('pwa-manifest.json') ?>';
      try {
        const response = await fetch(url, { method: 'GET', redirect: 'manual' });
        
        if (response.type === 'opaqueredirect') {
          showResult('manifest-root-result', 'error', 
            `REDIRIGÉ ! Le fichier est redirigé par Apache. Status: ${response.type}`);
          return;
        }

        if (response.ok) {
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.includes('json')) {
            const data = await response.json();
            showResult('manifest-root-result', 'success', 
              `OK ! Manifest chargé. Nom: ${data.name || 'N/A'}, ${Object.keys(data).length} propriétés`);
          } else {
            showResult('manifest-root-result', 'error', 
              `Mauvais Content-Type: ${contentType}. Probablement du HTML/PHP`);
          }
        } else {
          showResult('manifest-root-result', 'error', 
            `HTTP ${response.status}: ${response.statusText}`);
        }
      } catch (error) {
        showResult('manifest-root-result', 'error', `Erreur: ${error.message}`);
      }
    }

    async function testManifestPwa() {
      const url = '<?= url('pwa/manifest.json') ?>';
      try {
        const response = await fetch(url, { method: 'GET', redirect: 'manual' });
        
        if (response.type === 'opaqueredirect') {
          showResult('manifest-pwa-result', 'error', 
            `REDIRIGÉ ! Le fichier est redirigé par Apache. Status: ${response.type}`);
          return;
        }

        if (response.ok) {
          const contentType = response.headers.get('content-type');
          if (contentType && contentType.includes('json')) {
            const data = await response.json();
            showResult('manifest-pwa-result', 'success', 
              `OK ! Manifest chargé. Nom: ${data.name || 'N/A'}, ${Object.keys(data).length} propriétés`);
          } else {
            showResult('manifest-pwa-result', 'error', 
              `Mauvais Content-Type: ${contentType}. Probablement du HTML/PHP`);
          }
        } else {
          showResult('manifest-pwa-result', 'error', 
            `HTTP ${response.status}: ${response.statusText}`);
        }
      } catch (error) {
        showResult('manifest-pwa-result', 'error', `Erreur: ${error.message}`);
      }
    }

    async function testSwRoot() {
      const url = '<?= url('pwa-worker.js') ?>';
      try {
        const response = await fetch(url, { method: 'GET', redirect: 'manual' });
        
        if (response.type === 'opaqueredirect') {
          showResult('sw-root-result', 'error', 
            `REDIRIGÉ ! Le fichier est redirigé par Apache. Status: ${response.type}`);
          return;
        }

        if (response.ok) {
          const contentType = response.headers.get('content-type');
          const text = await response.text();
          
          if (text.includes('<!DOCTYPE') || text.includes('<html')) {
            showResult('sw-root-result', 'error', 
              `HTML retourné au lieu de JavaScript ! Fichier probablement redirigé vers index.php`);
          } else if (text.includes('self.addEventListener')) {
            showResult('sw-root-result', 'success', 
              `OK ! Service Worker valide (${text.length} octets)`);
          } else {
            showResult('sw-root-result', 'warning', 
              `JavaScript retourné mais structure inhabituelle`);
          }
        } else {
          showResult('sw-root-result', 'error', 
            `HTTP ${response.status}: ${response.statusText}`);
        }
      } catch (error) {
        showResult('sw-root-result', 'error', `Erreur: ${error.message}`);
      }
    }

    async function testSwPwa() {
      const url = '<?= url('pwa/sw.js') ?>';
      try {
        const response = await fetch(url, { method: 'GET', redirect: 'manual' });
        
        if (response.type === 'opaqueredirect') {
          showResult('sw-pwa-result', 'error', 
            `REDIRIGÉ ! Le fichier est redirigé par Apache. Status: ${response.type}`);
          return;
        }

        if (response.ok) {
          const contentType = response.headers.get('content-type');
          const text = await response.text();
          
          if (text.includes('<!DOCTYPE') || text.includes('<html')) {
            showResult('sw-pwa-result', 'error', 
              `HTML retourné au lieu de JavaScript ! Fichier probablement redirigé vers index.php`);
          } else if (text.includes('self.addEventListener')) {
            showResult('sw-pwa-result', 'success', 
              `OK ! Service Worker valide (${text.length} octets)`);
          } else {
            showResult('sw-pwa-result', 'warning', 
              `JavaScript retourné mais structure inhabituelle`);
          }
        } else {
          showResult('sw-pwa-result', 'error', 
            `HTTP ${response.status}: ${response.statusText}`);
        }
      } catch (error) {
        showResult('sw-pwa-result', 'error', `Erreur: ${error.message}`);
      }
    }

    async function testSwRegistration() {
      if (!('serviceWorker' in navigator)) {
        showResult('sw-registration-result', 'error', 
          'Service Worker non supporté par ce navigateur');
        return;
      }

      try {
        // Essayer le sous-dossier d'abord
        const reg = await navigator.serviceWorker.register('<?= url('pwa/sw.js') ?>', {
          scope: '<?= url('') ?>'
        });
        
        showResult('sw-registration-result', 'success', 
          `Service Worker enregistré ! Scope: ${reg.scope}, État: ${reg.active ? 'actif' : 'en attente'}`);
      } catch (error) {
        // Essayer la racine en fallback
        try {
          const reg = await navigator.serviceWorker.register('<?= url('pwa-worker.js') ?>', {
            scope: '<?= url('') ?>'
          });
          
          showResult('sw-registration-result', 'warning', 
            `Fallback racine utilisé. Scope: ${reg.scope}. Le sous-dossier n'a pas fonctionné: ${error.message}`);
        } catch (error2) {
          showResult('sw-registration-result', 'error', 
            `Échec total: ${error2.message}`);
        }
      }
    }

    async function unregisterAllSw() {
      const registrations = await navigator.serviceWorker.getRegistrations();
      for (let registration of registrations) {
        await registration.unregister();
      }
      showResult('sw-registration-result', 'info', 
        `${registrations.length} Service Worker(s) désinscrit(s)`);
    }

    function testInstallPrompt() {
      if (!installPrompt) {
        showResult('install-result', 'warning', 
          'Prompt non disponible (déjà installé, iOS, ou PWA non détectée)');
        return;
      }

      installPrompt.prompt();
      installPrompt.userChoice.then((choiceResult) => {
        if (choiceResult.outcome === 'accepted') {
          showResult('install-result', 'success', 
            'Installation acceptée par l\'utilisateur');
        } else {
          showResult('install-result', 'info', 
            'Installation refusée par l\'utilisateur');
        }
        installPrompt = null;
      });
    }

    async function testOfflinePage() {
      const url = '<?= url('offline.html') ?>';
      try {
        const response = await fetch(url, { method: 'GET', redirect: 'manual' });
        
        if (response.type === 'opaqueredirect') {
          showResult('offline-result', 'error', 
            `REDIRIGÉ ! Le fichier est redirigé par Apache`);
          return;
        }

        if (response.ok) {
          const text = await response.text();
          if (text.includes('Vous êtes hors ligne') || text.includes('offline')) {
            showResult('offline-result', 'success', 
              `OK ! Page offline valide (${text.length} octets)`);
          } else {
            showResult('offline-result', 'warning', 
              `HTML retourné mais contenu inhabituel`);
          }
        } else {
          showResult('offline-result', 'error', 
            `HTTP ${response.status}: ${response.statusText}`);
        }
      } catch (error) {
        showResult('offline-result', 'error', `Erreur: ${error.message}`);
      }
    }

    async function runAllTests() {
      stats = { total: 0, passed: 0, failed: 0, warning: 0 };
      updateStats();
      
      await testManifestRoot();
      await new Promise(r => setTimeout(r, 500));
      
      await testManifestPwa();
      await new Promise(r => setTimeout(r, 500));
      
      await testSwRoot();
      await new Promise(r => setTimeout(r, 500));
      
      await testSwPwa();
      await new Promise(r => setTimeout(r, 500));
      
      await testOfflinePage();
      await new Promise(r => setTimeout(r, 500));
      
      await testSwRegistration();
      
      // Résumé final
      setTimeout(() => {
        const successRate = Math.round((stats.passed / stats.total) * 100);
        alert(`Tests terminés !\n\n✅ Réussis: ${stats.passed}\n❌ Échoués: ${stats.failed}\n⚠️ Avertissements: ${stats.warning}\n\nTaux de réussite: ${successRate}%`);
      }, 1000);
    }

    // Auto-run all tests on page load
    window.addEventListener('load', () => {
      setTimeout(runAllTests, 1000);
    });
  </script>
</body>
</html>
