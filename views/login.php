<main class="container" style="display:flex;justify-content:center;align-items:center;min-height:calc(100vh - 180px)">
  <div style="width:100%;max-width:400px">
    <div style="text-align:center;margin-bottom:32px">
      <h1 style="font-size:28px;margin-bottom:8px">Bienvenue sur TrustPick</h1>
      <p style="color:#6c757d;margin:0">Connectez-vous pour accéder à votre compte</p>
    </div>

    <div
      style="background:linear-gradient(135deg,#e6f0ff,#fff);padding:32px;border-radius:12px;border:1px solid #c7e9ff;box-shadow:0 4px 12px rgba(0,102,204,0.08)">
      <form action="<?= url('actions/login.php') ?>" method="post" class="auth-form">
        <label class="input-enhanced">Adresse email<br>
          <input type="email" name="email" placeholder="jean@exemple.com" required style="width:100%;margin-top:4px">
        </label>

        <label class="input-enhanced">Mot de passe<br>
          <input type="password" name="password" placeholder="••••••••" required style="width:100%;margin-top:4px">
        </label>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;font-size:13px">
          <label style="display:flex;align-items:center;gap:6px;margin:0">
            <input type="checkbox" name="remember" style="cursor:pointer"> Se souvenir de moi
          </label>
          <a href="#" style="color:#0066cc">Mot de passe oublié ?</a>
        </div>

        <button class="btn btn-animated ripple" type="submit" style="width:100%;padding:12px;font-size:15px">Se
          connecter</button>
      </form>

      <div style="border-top:1px solid #e0e4e8;padding-top:20px;margin-top:20px">
        <p style="text-align:center;color:#6c757d;margin-bottom:16px">Ou continuez avec</p>
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
      Vous n'avez pas de compte ? <a class="btn-animated"
        style="color:#0066cc;font-weight:600;text-decoration:none;padding:6px 8px;border-radius:6px"
        href="<?= url('index.php?page=register') ?>">S'inscrire gratuitement</a>
    </div>
  </div>
</main>