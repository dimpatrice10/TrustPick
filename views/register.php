<main class="container" style="display:flex;justify-content:center;align-items:center;min-height:calc(100vh - 180px)">
  <div style="width:100%;max-width:400px">
    <div style="text-align:center;margin-bottom:32px">
      <h1 style="font-size:28px;margin-bottom:8px">Rejoignez TrustPick</h1>
      <p style="color:#6c757d;margin:0">Créez un compte gratuit et commencez à gagner</p>
    </div>

    <div
      style="background:linear-gradient(135deg,#e6f9f2,#fff);padding:32px;border-radius:12px;border:1px solid #a7f3d0;box-shadow:0 4px 12px rgba(26,153,145,0.08)">
      <form action="<?= url('actions/register.php') ?>" method="post" class="auth-form">
        <label class="input-enhanced">Nom complet<br>
          <input type="text" name="name" placeholder="Jean Dupont" required style="width:100%;margin-top:4px">
        </label>

        <label class="input-enhanced">Adresse email<br>
          <input type="email" name="email" placeholder="jean@exemple.com" required style="width:100%;margin-top:4px">
        </label>

        <label class="input-enhanced">Mot de passe<br>
          <input type="password" name="password" placeholder="Minimum 8 caractères" required
            style="width:100%;margin-top:4px">
        </label>

        <label class="input-enhanced">Confirmer le mot de passe<br>
          <input type="password" name="password_confirm" placeholder="••••••••" required
            style="width:100%;margin-top:4px">
        </label>

        <label style="display:flex;align-items:flex-start;gap:8px;margin-bottom:20px;margin-top:16px">
          <input type="checkbox" name="terms" required style="margin-top:4px;cursor:pointer">
          <span style="font-size:13px">
            J'accepte les <a href="#" style="color:#0066cc">conditions d'utilisation</a> et la <a href="#"
              style="color:#0066cc">politique de confidentialité</a>
          </span>
        </label>

        <button class="btn btn-animated ripple" type="submit" style="width:100%;padding:12px;font-size:15px">Créer un
          compte gratuit</button>
      </form>

      <div style="border-top:1px solid #e0e4e8;padding-top:20px;margin-top:20px">
        <p style="text-align:center;color:#6c757d;margin-bottom:16px;font-size:13px">Ou s'inscrire avec</p>
        <div style="display:flex;gap:12px">
          <button class="btn btn-secondary btn-animated"
            style="flex:1;padding:10px;border:1px solid #e0e4e8;border-radius:10px;background:white;cursor:pointer;transition:all .2s">
            ◆ Google
          </button>
          <button class="btn btn-secondary btn-animated"
            style="flex:1;padding:10px;border:1px solid #e0e4e8;border-radius:10px;background:white;cursor:pointer;transition:all .2s">
            ◉ Facebook
          </button>
        </div>
      </div>
    </div>

    <div style="text-align:center;margin-top:24px;font-size:14px">
      Déjà inscrit ? <a class="btn-animated"
        style="color:#0066cc;font-weight:600;text-decoration:none;padding:6px 8px;border-radius:6px"
        href="<?= url('index.php?page=login') ?>">Se connecter</a>
    </div>

    <div
      style="background:linear-gradient(135deg,#e6f0ff,#e6f9f2);padding:16px;border-radius:12px;margin-top:24px;font-size:13px;border-left:4px solid #0066cc">
      <p style="margin:0;margin-bottom:8px"><strong>◈ Bonus bienvenue</strong></p>
      <ul style="margin:0;padding-left:20px;color:#6c757d">
        <li>Crédit de démarrage: 10€</li>
        <li>100 points bonus</li>
        <li>Accès à tous les produits</li>
      </ul>
    </div>
  </div>
</main>