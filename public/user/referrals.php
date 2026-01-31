<?php
$page_title = 'Parrainages';
$current_page = 'referrals';

include '../layouts/header.php';
include '../layouts/sidebar-user.php';
?>

<!-- Contenu principal -->
<div class="col-md-9 col-lg-10" style="padding: 2rem;">
    <h2 style="margin-bottom: 2rem;">👥 Système de Parrainage</h2>

    <!-- Lien de parrainage -->
    <div class="referral-link-box">
        <h4 style="margin: 0 0 1rem 0;">🎁 Votre lien de parrainage</h4>
        <p style="margin-bottom: 1rem; opacity: 0.9;">Partagez ce lien et gagnez 5 000 FCFA pour chaque inscription !
        </p>

        <div class="referral-link" id="referral-link">
            Chargement...
        </div>

        <button class="btn btn-light mt-3" onclick="copyReferralLink()">
            📋 Copier le lien
        </button>

        <div class="social-share-buttons" id="social-buttons"></div>
    </div>

    <!-- Stats de parrainage -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value" id="referrals-count">0</div>
                <div class="stat-label">Filleuls inscrits</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value" id="referrals-earnings">0 FCFA</div>
                <div class="stat-label">Gains de parrainage</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-value" id="referrals-rank">-</div>
                <div class="stat-label">Classement</div>
            </div>
        </div>
    </div>

    <!-- Comment ça marche -->
    <div class="tp-card mb-4">
        <h4 class="tp-card-header">💡 Comment ça marche ?</h4>
        <div class="row">
            <div class="col-md-4 text-center mb-3">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📤</div>
                <h5>1. Partagez</h5>
                <p class="text-muted">Envoyez votre lien de parrainage à vos amis par WhatsApp, Facebook, etc.</p>
            </div>
            <div class="col-md-4 text-center mb-3">
                <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                <h5>2. Inscription</h5>
                <p class="text-muted">Votre ami s'inscrit sur TrustPick via votre lien</p>
            </div>
            <div class="col-md-4 text-center mb-3">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💰</div>
                <h5>3. Gagnez !</h5>
                <p class="text-muted">Recevez automatiquement 5 000 FCFA dans votre portefeuille</p>
            </div>
        </div>
    </div>

    <!-- Liste des filleuls -->
    <div class="tp-card">
        <h4 class="tp-card-header">📋 Mes filleuls</h4>
        <div id="referrals-list"></div>
    </div>
</div>

<script>
    let referralLink = '';

    // Charger le lien de parrainage
    async function loadReferralLink() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/referrals-my-link.php');
            const data = await response.json();

            if (data.success && data.referral_link) {
                referralLink = data.referral_link;
                document.getElementById('referral-link').textContent = referralLink;

                // Générer les boutons de partage social
                generateSocialButtons(referralLink, data.referral_code);
            }
        } catch (error) {
            console.error('Erreur chargement lien de parrainage:', error);
        }
    }

    // Générer les boutons de partage social
    function generateSocialButtons(link, code) {
        const message = `Rejoins-moi sur TrustPick et gagne de l'argent en donnant ton avis ! Utilise mon code : ${code}`;
        const encodedMessage = encodeURIComponent(message);
        const encodedLink = encodeURIComponent(link);

        const buttons = [
            {
                name: 'WhatsApp',
                icon: '📱',
                url: `https://wa.me/?text=${encodedMessage}%20${encodedLink}`,
                class: 'social-btn-whatsapp'
            },
            {
                name: 'Facebook',
                icon: '📘',
                url: `https://www.facebook.com/sharer/sharer.php?u=${encodedLink}`,
                class: 'social-btn-facebook'
            },
            {
                name: 'Twitter',
                icon: '🐦',
                url: `https://twitter.com/intent/tweet?text=${encodedMessage}&url=${encodedLink}`,
                class: 'social-btn-twitter'
            },
            {
                name: 'Telegram',
                icon: '✈️',
                url: `https://t.me/share/url?url=${encodedLink}&text=${encodedMessage}`,
                class: 'social-btn-facebook'
            }
        ];

        const container = document.getElementById('social-buttons');
        container.innerHTML = buttons.map(btn => `
        <a href="${btn.url}" target="_blank" class="social-btn ${btn.class}">
            ${btn.icon} Partager sur ${btn.name}
        </a>
    `).join('');
    }

    // Copier le lien
    function copyReferralLink() {
        TrustPick.copyToClipboard(referralLink);
    }

    // Charger les stats de parrainage
    async function loadReferralStats() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/referrals-stats.php');
            const data = await response.json();

            if (data.success) {
                document.getElementById('referrals-count').textContent = data.total_referrals || 0;
                document.getElementById('referrals-earnings').textContent = TrustPick.formatFCFA(data.total_earnings || 0);
                document.getElementById('referrals-rank').textContent = data.rank ? `#${data.rank}` : '-';
            }
        } catch (error) {
            console.error('Erreur chargement stats parrainage:', error);
        }
    }

    // Charger la liste des filleuls
    async function loadReferralsList() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/referrals-list.php');
            const data = await response.json();

            const container = document.getElementById('referrals-list');

            if (data.success && data.referrals && data.referrals.length > 0) {
                const referralsHTML = data.referrals.map(ref => `
                <div class="tp-card" style="padding: 1rem; margin-bottom: 0.8rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${ref.referred_name || 'Utilisateur'}</strong>
                            <small class="text-muted d-block">${ref.referred_cau}</small>
                            <small class="text-muted">Inscrit ${TrustPick.formatRelativeTime(ref.created_at)}</small>
                        </div>
                        <div class="text-end">
                            <div class="text-fcfa" style="font-size: 1.1rem; font-weight: bold;">
                                ${TrustPick.formatFCFA(ref.reward_amount || 0)}
                            </div>
                            <span class="badge-tp ${ref.is_rewarded ? 'badge-active' : 'badge-pending'}">
                                ${ref.is_rewarded ? '✓ Payé' : '⏳ En attente'}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
                container.innerHTML = referralsHTML;
            } else {
                container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">👥</div>
                    <h3 class="empty-state-title">Aucun filleul</h3>
                    <p class="empty-state-description">Partagez votre lien pour commencer à gagner !</p>
                </div>
            `;
            }
        } catch (error) {
            console.error('Erreur chargement liste filleuls:', error);
        }
    }

    // Initialiser
    window.addEventListener('load', () => {
        loadReferralLink();
        loadReferralStats();
        loadReferralsList();
    });
</script>

<?php include '../layouts/footer.php'; ?>