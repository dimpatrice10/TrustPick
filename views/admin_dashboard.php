<main class="container dashboard admin">
  <section style="margin-bottom:32px">
    <h1>Dashboard Administration</h1>
    <p style="color:#6c757d">Surveillez la plateforme, gérez les utilisateurs et modérez les contenus</p>
  </section>

  <div class="dashboard-grid fade-up">
    <div class="dashboard-card">
      <h3>👥 Utilisateurs totaux</h3>
      <strong>4,840</strong>
      <p>+234 ce mois</p>
    </div>

    <div class="dashboard-card">
      <h3><span style="font-size:20px">◉</span> Entreprises</h3>
      <strong>348</strong>
      <p>+12 vérifiées</p>
    </div>

    <div class="dashboard-card">
      <h3>📦 Produits</h3>
      <strong>15,240</strong>
      <p>+892 ce mois</p>
    </div>

    <div class="dashboard-card">
      <h3><span style="font-size:20px">◆</span> Avis</h3>
      <strong>234K</strong>
      <p>+15K cette semaine</p>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin:32px 0">
    <section style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
      <h2 style="margin-top:0">Modération en attente</h2>
      <div style="font-size:13px">
        <div style="padding:12px;border-bottom:1px solid #e0e4e8;display:flex;justify-content:space-between">
          <div>
            <strong style="display:block">Commentaires à valider</strong>
            <span style="color:#6c757d">Signalés pour révision</span>
          </div>
          <strong style="background:#ef4444;color:white;padding:4px 8px;border-radius:4px">3</strong>
        </div>
        <div style="padding:12px;border-bottom:1px solid #e0e4e8;display:flex;justify-content:space-between">
          <div>
            <strong style="display:block">Comptes suspects</strong>
            <span style="color:#6c757d">Comportement anormal</span>
          </div>
          <strong style="background:#ef4444;color:white;padding:4px 8px;border-radius:4px">5</strong>
        </div>
        <div style="padding:12px;display:flex;justify-content:space-between">
          <div>
            <strong style="display:block">Produits non vérifiés</strong>
            <span style="color:#6c757d">Depuis plus de 48h</span>
          </div>
          <strong style="background:#f59e0b;color:white;padding:4px 8px;border-radius:4px">12</strong>
        </div>
      </div>
    </section>

    <section style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
      <h2 style="margin-top:0">Statistiques financières</h2>
      <div style="font-size:13px">
        <div style="padding:12px;border-bottom:1px solid #e0e4e8;display:flex;justify-content:space-between">
          <span>Retraits cette semaine</span>
          <strong>1,240 €</strong>
        </div>
        <div style="padding:12px;border-bottom:1px solid #e0e4e8;display:flex;justify-content:space-between">
          <span>Commissions gagnées</span>
          <strong style="color:#10b981">342 €</strong>
        </div>
        <div style="padding:12px;border-bottom:1px solid #e0e4e8;display:flex;justify-content:space-between">
          <span>Abonnements actifs</span>
          <strong>45</strong>
        </div>
        <div style="padding:12px;display:flex;justify-content:space-between">
          <span>Fraude détectée</span>
          <strong style="color:#ef4444">2 comptes</strong>
        </div>
      </div>
    </section>
  </div>

  <section style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08);margin-bottom:32px">
    <h2>Gestion des utilisateurs</h2>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <thead>
        <tr style="border-bottom:2px solid #e0e4e8">
          <th style="padding:12px;text-align:left;font-weight:600">Utilisateur</th>
          <th style="padding:12px;text-align:center;font-weight:600">Avis donnés</th>
          <th style="padding:12px;text-align:center;font-weight:600">Recommandations</th>
          <th style="padding:12px;text-align:center;font-weight:600">Statut</th>
        </tr>
      </thead>
      <tbody>
        <tr style="border-bottom:1px solid #e0e4e8">
          <td style="padding:12px"><strong>Jean Dupont</strong></td>
          <td style="padding:12px;text-align:center">34</td>
          <td style="padding:12px;text-align:center">18</td>
          <td style="padding:12px;text-align:center"><span style="background:#10b981;color:white;padding:2px 8px;border-radius:4px;font-size:11px">Actif</span></td>
        </tr>
        <tr style="border-bottom:1px solid #e0e4e8">
          <td style="padding:12px"><strong>Marie Martin</strong></td>
          <td style="padding:12px;text-align:center">127</td>
          <td style="padding:12px;text-align:center">52</td>
          <td style="padding:12px;text-align:center"><span style="background:#10b981;color:white;padding:2px 8px;border-radius:4px;font-size:11px">Vérifié</span></td>
        </tr>
        <tr>
          <td style="padding:12px"><strong>Unknown123</strong></td>
          <td style="padding:12px;text-align:center">289</td>
          <td style="padding:12px;text-align:center">145</td>
          <td style="padding:12px;text-align:center"><span style="background:#ef4444;color:white;padding:2px 8px;border-radius:4px;font-size:11px">Suspect</span></td>
        </tr>
      </tbody>
    </table>
  </section>

  <section style="background:linear-gradient(135deg, #0066cc 0%, #1ab991 100%);color:white;padding:32px;border-radius:12px;box-shadow:0 12px 32px rgba(0,102,204,0.25)">
    <h2 style="color:white;margin-bottom:8px">Actions rapides</h2>
    <div style="display:flex;gap:12px;flex-wrap:wrap">
      <button class="btn btn-animated" style="background:white;color:#0066cc;font-weight:700">🔍 Analyser fraude</button>
      <button class="btn btn-animated" style="background:white;color:#0066cc;font-weight:700">▶ Voir rapports</button>
      <button class="btn btn-animated" style="background:white;color:#0066cc;font-weight:700">⚙️ Paramètres</button>
      <button class="btn btn-animated" style="background:white;color:#0066cc;font-weight:700">👥 Gérer utilisateurs</button>
    </div>
  </section>
</main>
