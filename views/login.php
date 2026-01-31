<main class="container" style="display:flex;justify-content:center;align-items:center;min-height:calc(100vh - 180px)">
  <div style="width:100%;max-width:400px">
    <div style="text-align:center;margin-bottom:32px">
      <h1 style="font-size:28px;margin-bottom:8px">Bienvenue sur TrustPick</h1>
      <p style="color:#6c757d;margin:0">Connectez-vous avec votre Code d'Accès Utilisateur (CAU)</p>
    </div>

    <div
      style="background:linear-gradient(135deg,#e6f0ff,#fff);padding:32px;border-radius:12px;border:1px solid #c7e9ff;box-shadow:0 4px 12px rgba(0,102,204,0.08)">
      <form action="<?= url('actions/login.php') ?>" method="post" class="auth-form">
        <label class="input-enhanced">Code d'Accès Utilisateur (CAU)<br>
          <input type="text" name="cau" placeholder="Ex: USER001" required
            style="width:100%;margin-top:4px;text-transform:uppercase" pattern="[A-Z0-9]+"
            title="Le CAU doit contenir uniquement des lettres majuscules et des chiffres">
        </label>

        <div style="padding:12px;background:#e8f4fd;border-radius:8px;margin-bottom:20px;font-size:13px;color:#0066cc">
          <strong><i class="bi bi-lightbulb me-1"></i>Astuce :</strong> Votre CAU est un code unique fourni lors de
          votre inscription.<br>
          <span style="color:#6c757d">Exemples de CAU : USER001, TECH001, ADMIN001</span>
        </div>

        <button class="btn btn-animated ripple" type="submit" style="width:100%;padding:12px;font-size:15px">Se
          connecter</button>
      </form>
    </div>

    <div style="text-align:center;margin-top:24px;font-size:14px">
      Vous n'avez pas de compte ? <a class="btn-animated"
        style="color:#0066cc;font-weight:600;text-decoration:none;padding:6px 8px;border-radius:6px"
        href="<?= url('index.php?page=register') ?>">S'inscrire gratuitement</a>
    </div>
  </div>
</main>