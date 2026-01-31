# 🎨 ARCHITECTURE FRONTEND TRUSTPICK V2

## 📋 VUE D'ENSEMBLE

**Approche technique** : HTML5/CSS3/JavaScript natif + Bootstrap 5  
**Pourquoi ?**

- ✅ Léger et rapide (pas de framework lourd)
- ✅ Compatible tous navigateurs
- ✅ Facile à maintenir
- ✅ Adapté au contexte africain (connexions mobiles)
- ✅ Progressive Enhancement possible

---

## 🗂️ ARBORESCENCE COMPLÈTE

```
public/
├── index.php                    # Point d'entrée (routeur)
├── login.php                    # Page de connexion CAU
├── logout.php                   # Déconnexion
│
├── user/                        # Zone utilisateur
│   ├── dashboard.php            # Dashboard utilisateur
│   ├── products.php             # Liste produits (pagination)
│   ├── product-detail.php       # Détail produit + avis
│   ├── tasks.php                # Tâches quotidiennes
│   ├── wallet.php               # Portefeuille FCFA
│   ├── referrals.php            # Parrainages
│   └── notifications.php        # Centre notifications
│
├── admin/                       # Zone admin entreprise
│   ├── dashboard.php            # Stats entreprise
│   ├── products.php             # Gestion produits
│   ├── reviews.php              # Modération avis
│   └── analytics.php            # Statistiques détaillées
│
├── superadmin/                  # Zone super admin
│   ├── dashboard.php            # Vue globale
│   ├── companies.php            # Gestion entreprises
│   ├── users.php                # Gestion utilisateurs
│   ├── tasks-config.php         # Configuration tâches
│   └── settings.php             # Paramètres système
│
├── assets/                      # Ressources statiques
│   ├── css/
│   │   ├── bootstrap.min.css    # Bootstrap 5.3
│   │   ├── trustpick.css        # Styles personnalisés
│   │   └── components.css       # Composants réutilisables
│   ├── js/
│   │   ├── bootstrap.bundle.min.js
│   │   ├── trustpick-core.js    # Fonctions globales
│   │   ├── pagination.js        # Système 5 + Voir plus
│   │   ├── tasks.js             # Logique tâches
│   │   ├── notifications.js     # Système notifications
│   │   └── wallet.js            # Gestion wallet
│   └── img/
│       ├── logo.png
│       ├── placeholder.jpg
│       └── icons/
│
├── includes/                    # Fichiers backend (déjà créés)
│   ├── config.php
│   ├── db.php
│   ├── auth.php
│   ├── permissions.php
│   ├── product_generator.php
│   ├── tasks.php
│   ├── referrals.php
│   ├── notifications.php
│   └── pagination.php
│
├── api/v2/                      # API REST (déjà créée)
│   ├── auth-login.php
│   ├── tasks-available.php
│   ├── tasks-complete.php
│   ├── products-list.php
│   ├── notifications-list.php
│   ├── referrals-my-link.php
│   └── referrals-stats.php
│
└── layouts/                     # Templates réutilisables
    ├── header.php               # En-tête avec menu
    ├── footer.php               # Pied de page
    ├── sidebar-user.php         # Menu latéral utilisateur
    ├── sidebar-admin.php        # Menu latéral admin
    └── sidebar-superadmin.php   # Menu latéral super admin
```

---

## 🎯 ARCHITECTURE PAR RÔLE

### 👤 UTILISATEUR (role='user')

**Menu principal** :

- 🏠 Accueil / Dashboard
- 🛍️ Produits
- ✅ Mes Tâches
- 👥 Parrainages
- 💰 Portefeuille
- 🔔 Notifications
- 👤 Mon Profil

**Permissions** :

- Voir produits
- Laisser des avis (1 par produit max)
- Compléter des tâches
- Parrainer des amis
- Consulter son wallet
- Demander des retraits

---

### 🏢 ADMIN ENTREPRISE (role='admin_entreprise')

**Menu principal** :

- 📊 Dashboard Entreprise
- 🛍️ Mes Produits
- ⭐ Avis Clients
- 📈 Statistiques
- 👤 Mon Profil

**Permissions** :

- Créer/modifier/supprimer ses produits
- Modérer les avis de ses produits
- Voir les stats de son entreprise
- Gérer les catégories

---

### ⚡ SUPER ADMIN (role='super_admin')

**Menu principal** :

- 🎯 Dashboard Global
- 🏢 Entreprises
- 👥 Utilisateurs
- ⚙️ Configuration
- 📋 Logs Système
- 💵 Retraits en attente
- 👤 Mon Profil

**Permissions** :

- Créer/modifier/supprimer entreprises
- Créer des admins entreprise
- Gérer tous les utilisateurs
- Configurer les tâches et récompenses
- Valider les retraits
- Accès complet système

---

## 🔐 SYSTÈME D'AUTHENTIFICATION

### Flow de connexion :

1. **Page login.php** :
   - Champ unique : "Code d'Accès Utilisateur (CAU)"
   - Validation en temps réel
   - Appel API `/api/v2/auth-login.php`

2. **Réponse API** :

   ```json
   {
     "success": true,
     "user": {
       "id": 3,
       "cau": "USER001",
       "name": "Ama Kouadio",
       "role": "user",
       "balance": 2500,
       "referral_code": "AMA2024REF"
     },
     "token": "session_token_here"
   }
   ```

3. **Redirection automatique** :

- `super_admin` → `index.php?page=superadmin_dashboard`
- `admin_entreprise` → `index.php?page=admin_dashboard`
- `user` → `index.php?page=user_dashboard`

4. **Protection des pages** :
   - Middleware PHP vérifie session
   - Vérifie les permissions
   - Redirige si non autorisé

---

## 📱 RESPONSIVE DESIGN

### Breakpoints Bootstrap 5 :

- **Mobile** : < 576px (1 colonne)
- **Tablette** : 576-992px (2 colonnes)
- **Desktop** : > 992px (3+ colonnes)

### Stratégie :

1. **Mobile First** :
   - Menu hamburger
   - Cards empilées
   - Boutons pleine largeur

2. **Tablette** :
   - Sidebar collapsible
   - Grid 2 colonnes

3. **Desktop** :
   - Sidebar fixe
   - Grid 3-4 colonnes
   - Tous les détails visibles

---

## 🔄 SYSTÈME UNIVERSEL DE PAGINATION

### Principe (5 + Voir plus) :

```javascript
class TrustPickPagination {
  constructor(endpoint, container, itemsPerPage = 5) {
    this.endpoint = endpoint;
    this.container = container;
    this.itemsPerPage = itemsPerPage;
    this.currentPage = 1;
    this.hasMore = true;
  }

  async loadMore() {
    if (!this.hasMore) return;

    const response = await fetch(`${this.endpoint}?page=${this.currentPage}&limit=${this.itemsPerPage}`);
    const data = await response.json();

    this.renderItems(data.items);
    this.currentPage++;
    this.hasMore = data.has_more;

    if (!this.hasMore) {
      this.hideLoadMoreButton();
    }
  }
}
```

### Usage universel :

```javascript
// Produits
const productsPagination = new TrustPickPagination('/api/v2/products-list.php', '#products-container', 5);

// Notifications
const notifsPagination = new TrustPickPagination('/api/v2/notifications-list.php', '#notifications-container', 5);

// Avis
const reviewsPagination = new TrustPickPagination('/api/v2/reviews-list.php?product_id=123', '#reviews-container', 5);
```

---

## 🎨 COMPOSANTS RÉUTILISABLES

### 1. Card Produit

```html
<div class="product-card">
  <img src="..." alt="..." class="product-image" />
  <div class="product-info">
    <h4 class="product-title">Smartphone Galaxy Pro</h4>
    <p class="product-company">TechnoPlus CI</p>
    <div class="product-rating">⭐⭐⭐⭐⭐ <span>(128 avis)</span></div>
    <p class="product-price">450 000 FCFA</p>
    <a href="/user/product-detail.php?id=1" class="btn btn-primary">Voir</a>
  </div>
</div>
```

### 2. Card Tâche

```html
<div class="task-card" data-task-id="1">
  <div class="task-icon">✍️</div>
  <div class="task-info">
    <h5>Laisser un avis</h5>
    <p>Rédiger un avis détaillé sur un produit</p>
    <span class="task-reward">+500 FCFA</span>
  </div>
  <button class="btn btn-success task-complete-btn">Compléter</button>
</div>
```

### 3. Card Notification

```html
<div class="notification-card unread" data-notif-id="42">
  <div class="notif-icon">🎁</div>
  <div class="notif-content">
    <h6>Nouvelle récompense !</h6>
    <p>Vous avez gagné 500 FCFA pour votre avis</p>
    <small>Il y a 5 minutes</small>
  </div>
</div>
```

---

## 🔔 SYSTÈME DE NOTIFICATIONS

### Affichage temps réel :

1. **Badge compteur** dans le header
2. **Dropdown** avec les 5 dernières
3. **Page dédiée** pour l'historique complet

### Format API :

```json
{
  "total_unread": 3,
  "notifications": [
    {
      "id": 42,
      "type": "reward",
      "title": "Nouvelle récompense",
      "message": "Vous avez gagné 500 FCFA",
      "is_read": false,
      "created_at": "2026-01-25 14:30:00",
      "link": "/user/wallet.php"
    }
  ],
  "has_more": true
}
```

---

## 💰 WALLET FCFA

### Sections :

1. **Solde actuel** (grand affichage)
2. **Gains totaux** (statistique)
3. **Historique transactions** (paginé 5 + voir plus)
4. **Bouton retrait** (si solde >= 5000 FCFA)

### Détail transaction :

```
📅 25 janvier 2026 - 14:30
✅ Tâche complétée : "Laisser un avis"
+500 FCFA
Nouveau solde : 3 000 FCFA
```

---

## 🎯 RÈGLES MÉTIER FRONTEND

### Avis :

- ❌ **1 seul avis par utilisateur/produit**
- ✅ Édition possible (dans les 24h)
- ✅ Suppression possible (admin entreprise)
- ✅ Like/Dislike (1 réaction par utilisateur)

### Tâches :

- ❌ **Pas de duplicata** (vérification stricte)
- ✅ Tâches quotidiennes réinitialisées à minuit
- ✅ Récompense automatique immédiate
- ✅ Mise à jour du wallet en temps réel

### Parrainage :

- ✅ 1 lien unique par utilisateur
- ✅ Partage social (WhatsApp, Facebook, Twitter)
- ✅ Bonus automatique à l'inscription du filleul
- ✅ Stats détaillées (nombre, gains)

---

## 🚀 TECHNOLOGIES UTILISÉES

| Couche          | Technologie        | Version |
| --------------- | ------------------ | ------- |
| HTML            | HTML5              | -       |
| CSS             | Bootstrap          | 5.3.0   |
| CSS             | Custom CSS         | -       |
| JS              | Vanilla JavaScript | ES6+    |
| Backend         | PHP                | 8.0+    |
| Base de données | MySQL              | 8.0+    |
| API             | REST JSON          | -       |

---

## 📊 PERFORMANCE

### Optimisations :

1. **Lazy loading** des images
2. **Pagination** systématique (pas de `SELECT *`)
3. **Cache API** (optionnel)
4. **Minification** CSS/JS
5. **CDN** pour Bootstrap

### Metrics cibles :

- ⚡ Time to Interactive : < 3s
- 📱 Mobile Friendly : 100%
- 🎯 Lighthouse Score : > 90

---

## ✅ CHECKLIST D'IMPLÉMENTATION

### Phase 1 : Base

- [ ] Créer layouts (header, footer, sidebars)
- [ ] Page login CAU
- [ ] Système de sessions sécurisées
- [ ] Routing et protection des pages

### Phase 2 : Utilisateur

- [ ] Dashboard utilisateur
- [ ] Liste produits + pagination
- [ ] Détail produit + avis
- [ ] Interface tâches
- [ ] Wallet FCFA
- [ ] Parrainages
- [ ] Notifications

### Phase 3 : Admin Entreprise

- [ ] Dashboard entreprise
- [ ] CRUD produits
- [ ] Modération avis
- [ ] Statistiques

### Phase 4 : Super Admin

- [ ] Dashboard global
- [ ] Gestion entreprises
- [ ] Gestion utilisateurs
- [ ] Configuration système
- [ ] Validation retraits

### Phase 5 : Finitions

- [ ] Responsive mobile
- [ ] Messages d'erreur
- [ ] Feedback utilisateur
- [ ] Tests navigateurs
- [ ] Documentation

---

## 📝 PROCHAINES ÉTAPES

1. ✅ Architecture définie
2. 🔜 Créer les layouts de base
3. 🔜 Implémenter authentification CAU
4. 🔜 Développer composant pagination universel
5. 🔜 Pages utilisateur
6. 🔜 Pages admin
7. 🔜 Dashboards
8. 🔜 Tests et optimisation

---

**Date de création** : 25 janvier 2026  
**Version** : 1.0  
**Statut** : Architecture validée ✅
