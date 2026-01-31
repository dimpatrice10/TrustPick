<main class="container" style="display:flex;justify-content:center;align-items:center;min-height:calc(100vh - 180px)">
    <div style="width:100%;max-width:500px">
        <div style="text-align:center;margin-bottom:32px">
            <h1 style="font-size:28px;margin-bottom:8px">Rejoignez TrustPick</h1>
            <p style="color:#6c757d;margin:0">Créez un compte gratuit et commencez à gagner en FCFA</p>
        </div>

        <div
            style="background:linear-gradient(135deg,#e6f9f2,#fff);padding:32px;border-radius:12px;border:1px solid #a7f3d0;box-shadow:0 4px 12px rgba(26,153,145,0.08)">
            <form action="<?= url('actions/register.php') ?>" method="post" class="auth-form">
                <label class="input-enhanced">Nom complet<br>
                    <input type="text" name="name" placeholder="Ex: Ama Kouadio" required
                        style="width:100%;margin-top:4px" minlength="3">
                </label>

                <label class="input-enhanced">Numéro de téléphone<br>
                    <input type="text" name="phone" placeholder="+225 XX XX XX XX XX" required
                        style="width:100%;margin-top:4px">
                </label>

                <label class="input-enhanced">Code de parrainage (optionnel)<br>
                    <input type="text" name="referral_code" placeholder="Ex: AMA2024REF"
                        style="width:100%;margin-top:4px;text-transform:uppercase" pattern="[A-Z0-9]*"
                        value="<?= htmlspecialchars($_GET['ref'] ?? '') ?>">
                    <small style="color:#6c757d;font-size:12px">Si vous avez été invité, entrez le code de
                        parrainage</small>
                </label>

                <div
                    style="padding:12px;background:#e8f4fd;border-radius:8px;margin:16px 0;font-size:13px;color:#0066cc">
                    <strong><i class="bi bi-stars me-1"></i>Avantages :</strong><br>
                    <span style="color:#6c757d">
                        • 5,000 FCFA de bonus si vous utilisez un code de parrainage<br>
                        • Votre parrain recevra également 5,000 FCFA<br>
                        • Accès immédiat à toutes les fonctionnalités
                    </span>
                </div>

                <label style="display:flex;align-items:flex-start;gap:8px;margin-bottom:20px">
                    <input type="checkbox" name="terms" required style="margin-top:4px;cursor:pointer">
                    <span style="font-size:13px">
                        J'accepte les <a href="#" style="color:#0066cc">conditions d'utilisation</a> et la <a href="#"
                            style="color:#0066cc">politique de confidentialité</a>
                    </span>
                </label>

                <button class="btn btn-animated ripple" type="submit"
                    style="width:100%;padding:12px;font-size:15px">Créer un
                    compte gratuit</button>
            </form>
        </div>

        <div style="text-align:center;margin-top:24px;font-size:14px">
            Vous avez déjà un compte ? <a class="btn-animated"
                style="color:#0066cc;font-weight:600;text-decoration:none;padding:6px 8px;border-radius:6px"
                href="<?= url('index.php?page=login') ?>">Se connecter</a>
        </div>
    </div>
</main>
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
        <li>Crédit de démarrage: <?= formatFCFA(5000) ?></li>
        <li>100 points bonus</li>
        <li>Accès à tous les produits</li>
    </ul>
</div>
</div>
</main>