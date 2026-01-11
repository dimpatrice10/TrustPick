<main class="container dashboard">
  <section style="margin-bottom:32px">
    <h1>Tableau de bord • Acme Corp</h1>
    <p style="color:#6c757d">Gérez vos produits, analysez vos performances et améliorez votre réputation</p>
  </section>

  <div class="dashboard-grid fade-up">
    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">★</span> Cote globale</h3>
      <strong>4.7/5.0</strong>
      <p>Excellente réputation</p>
      <div style="background:linear-gradient(90deg,#f59e0b,#f97316);height:6px;border-radius:3px;margin-top:8px;width:94%"></div>
    </div>

    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">◆</span> Produits publiés</h3>
      <strong>12</strong>
      <p>Tous actifs et vérifiés</p>
    </div>

    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">◉</span> Avis reçus</h3>
      <strong>1,240</strong>
      <p>Moyenne 4.7★</p>
    </div>

    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">▲</span> Recommandations</h3>
      <strong>890</strong>
      <p>+45% ce mois</p>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin:32px 0">
    <section style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
      <h2 style="margin-top:0">Avis récents</h2>
      <div style="font-size:13px">
        <div style="padding:12px;border-bottom:1px solid #e0e4e8">
          <div style="display:flex;justify-content:space-between;margin-bottom:4px">
            <strong>Client A</strong>
            <span style="color:#f59e0b">★★★★★</span>
          </div>
          <p style="margin:0;color:#6c757d">Très satisfait, livraison rapide</p>
        </div>
        <div style="padding:12px;border-bottom:1px solid #e0e4e8">
          <div style="display:flex;justify-content:space-between;margin-bottom:4px">
            <strong>Client B</strong>
            <span style="color:#f59e0b">★★☆☆☆</span>
          </div>
          <p style="margin:0;color:#6c757d">Problème de livraison, service client lent</p>
        </div>
        <div style="padding:12px">
          <div style="display:flex;justify-content:space-between;margin-bottom:4px">
            <strong>Client C</strong>
            <span style="color:#f59e0b">★★★★☆</span>
          </div>
          <p style="margin:0;color:#6c757d">Bonne qualité, délai un peu long</p>
        </div>
      </div>
    </section>

    <section style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
      <h2 style="margin-top:0">Top produits</h2>
      <div style="font-size:13px">
        <div style="padding:12px;border-bottom:1px solid #e0e4e8;display:flex;justify-content:space-between">
          <div>
            <strong style="display:block">Casque X</strong>
            <span style="color:#6c757d">341 avis</span>
          </div>
          <strong style="color:#f59e0b">4.8★</strong>
        </div>
        <div style="padding:12px;border-bottom:1px solid #e0e4e8;display:flex;justify-content:space-between">
          <div>
            <strong style="display:block">Chargeur Z</strong>
            <span style="color:#6c757d">289 avis</span>
          </div>
          <strong style="color:#f59e0b">4.7★</strong>
        </div>
        <div style="padding:12px;display:flex;justify-content:space-between">
          <div>
            <strong style="display:block">Adaptateur USB</strong>
            <span style="color:#6c757d">156 avis</span>
          </div>
          <strong style="color:#f59e0b">4.5★</strong>
        </div>
      </div>
    </section>
  </div>

  <section style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08);margin-bottom:32px">
    <h2>Gestion des produits</h2>
    <table style="width:100%;border-collapse:collapse;font-size:13px">
      <thead>
        <tr style="border-bottom:2px solid #e0e4e8">
          <th style="padding:12px;text-align:left;font-weight:600">Produit</th>
          <th style="padding:12px;text-align:center;font-weight:600">Cote</th>
          <th style="padding:12px;text-align:center;font-weight:600">Avis</th>
          <th style="padding:12px;text-align:center;font-weight:600">Statut</th>
        </tr>
      </thead>
      <tbody>
        <tr style="border-bottom:1px solid #e0e4e8">
          <td style="padding:12px"><strong>Casque X</strong></td>
          <td style="padding:12px;text-align:center;color:#f59e0b">4.8★</td>
          <td style="padding:12px;text-align:center">341</td>
          <td style="padding:12px;text-align:center"><span style="background:#10b981;color:white;padding:2px 8px;border-radius:4px;font-size:11px">Actif</span></td>
        </tr>
        <tr style="border-bottom:1px solid #e0e4e8">
          <td style="padding:12px"><strong>Chargeur Z</strong></td>
          <td style="padding:12px;text-align:center;color:#f59e0b">4.7★</td>
          <td style="padding:12px;text-align:center">289</td>
          <td style="padding:12px;text-align:center"><span style="background:#10b981;color:white;padding:2px 8px;border-radius:4px;font-size:11px">Actif</span></td>
        </tr>
      </tbody>
    </table>
  </section>

  <section style="background:linear-gradient(135deg, #0066cc 0%, #1ab991 100%);color:white;padding:32px;border-radius:12px;text-align:center;box-shadow:0 12px 32px rgba(0,102,204,0.25)">
    <h2 style="color:white;margin-bottom:8px">Ajouter un nouveau produit</h2>
    <p>Augmentez votre visibilité en ajoutant des produits à votre catalogue</p>
    <button class="btn btn-animated" style="background:white;color:#0066cc;margin-top:16px;font-weight:700">+ Ajouter un produit</button>
  </section>
</main>
