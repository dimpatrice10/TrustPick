/**
 * TrustPick V2 - Like System JS
 * Gère les likes/unlikes sur les avis en temps réel
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeLikeButtons();
});

/**
 * Initialise tous les boutons like
 */
function initializeLikeButtons() {
    document.querySelectorAll('.like-btn').forEach(btn => {
        // Éviter double bind
        if (!btn.dataset.likeInit) {
            btn.dataset.likeInit = 'true';
            btn.addEventListener('click', handleLikeClick);
        }
    });
}

/**
 * Gère le clic sur un bouton like
 */
async function handleLikeClick(e) {
    e.preventDefault();

    const btn = e.currentTarget;
    const reviewId = btn.dataset.reviewId;

    if (!reviewId) {
        console.error('Review ID manquant');
        return;
    }

    // Désactiver le bouton pendant la requête
    btn.disabled = true;
    btn.classList.add('loading');

    try {
        const response = await fetch(getBaseUrl() + 'actions/toggle_like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'review_id=' + encodeURIComponent(reviewId)
        });

        const data = await response.json();

        if (data.status === 'error') {
            showLikeToast('error', data.message);
            return;
        }

        // Mettre à jour l'affichage du compteur
        const countSpan = btn.querySelector('.like-count');
        if (countSpan) {
            countSpan.textContent = data.likes;
        }

        // Mettre à jour l'icône
        const icon = btn.querySelector('i');
        if (icon) {
            if (data.status === 'liked') {
                icon.className = 'bi bi-hand-thumbs-up-fill';
            } else {
                icon.className = 'bi bi-hand-thumbs-up';
            }
        }

        // Toggle la classe liked
        if (data.status === 'liked') {
            btn.classList.add('liked');
            btn.setAttribute('aria-pressed', 'true');
        } else {
            btn.classList.remove('liked');
            btn.setAttribute('aria-pressed', 'false');
        }

        // Afficher le message
        const toastType = data.reward > 0 ? 'success' : 'info';
        showLikeToast(toastType, data.message);

    } catch (error) {
        console.error('Erreur lors du like:', error);
        showLikeToast('error', 'Erreur de connexion. Veuillez réessayer.');
    } finally {
        // Réactiver le bouton
        btn.disabled = false;
        btn.classList.remove('loading');
    }
}

/**
 * Obtenir l'URL de base du projet
 */
function getBaseUrl() {
    // 1. Utiliser la meta tag si disponible (injectée par PHP)
    const meta = document.querySelector('meta[name="base-url"]');
    if (meta && meta.content) {
        return meta.content.endsWith('/') ? meta.content : meta.content + '/';
    }

    const path = window.location.pathname;

    // 2. Chercher un sous-dossier connu dans le chemin (dev local)
    const match = path.match(/(.+?\/(?:TrustPick|trustpick)\/)/i);
    if (match) {
        return match[1];
    }

    // 3. Chercher /public/ dans le chemin
    const publicMatch = path.match(/(.+?)\/public\//i);
    if (publicMatch) {
        return publicMatch[1] + '/';
    }

    // 4. Production (InfinityFree) : app à la racine du domaine
    return '/';
}

/**
 * Afficher un toast de notification
 */
function showLikeToast(type, message) {
    // Utiliser la fonction globale si disponible
    if (typeof showToast === 'function') {
        showToast(type, message);
        return;
    }

    // Supprimer toast existant
    const existingToast = document.querySelector('.like-toast');
    if (existingToast) {
        existingToast.remove();
    }

    // Créer le toast
    const toast = document.createElement('div');
    toast.className = 'like-toast like-toast-' + type;
    
    const iconClass = type === 'error' ? 'x-circle' : (type === 'success' ? 'check-circle' : 'info-circle');
    toast.innerHTML = '<i class="bi bi-' + iconClass + '"></i><span>' + message + '</span>';

    document.body.appendChild(toast);

    // Animation d'entrée
    setTimeout(function() {
        toast.classList.add('show');
    }, 10);

    // Supprimer après 3 secondes
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() {
            toast.remove();
        }, 300);
    }, 3000);
}

/**
 * Rafraîchir les boutons like (utile après chargement AJAX)
 */
function refreshLikeButtons() {
    initializeLikeButtons();
}

// Exposer les fonctions globalement
window.TrustPickLikes = {
    init: initializeLikeButtons,
    refresh: refreshLikeButtons
};
