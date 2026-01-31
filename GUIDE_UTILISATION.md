# 📘 GUIDE D'UTILISATION TRUSTPICK V2

## 🎯 VUE D'ENSEMBLE

TrustPick V2 est une plateforme de recommandation de produits avec système de récompenses en FCFA (Franc CFA).

**Système d'authentification:** Code d'Accès Utilisateur (CAU) unique - **PAS d'email/mot de passe**

---

## 🔑 CONNEXION

### 1. Accéder à la page de connexion

- URL: `http://localhost/TrustPick/index.php?page=login`
- Vous aurez besoin de votre **CAU** (Code d'Accès Utilisateur)

### 2. Comptes de test disponibles

| CAU           | Rôle             | Description                       |
| ------------- | ---------------- | --------------------------------- |
| `ADMIN000001` | Super Admin      | Accès total, gestion utilisateurs |
| `TECH001`     | Admin Entreprise | Gestion produits et entreprise    |
| `USER001`     | Utilisateur      | Navigation, avis, gains           |

### 3. Se connecter

1. Entrer votre CAU dans le champ
2. Cliquer sur "Se connecter"
3. Notification de bienvenue s'affiche
4. Redirection automatique selon votre rôle

---

## 👤 UTILISATEUR NORMAL

### Parcours utilisateur

#### 1. Inscription

- URL: `index.php?page=register`
- Remplir: Nom complet + Téléphone
- (Optionnel) Code parrain → +5 000 FCFA pour vous ET le parrain
- Votre **CAU** s'affiche après inscription → **LE NOTER**

#### 2. Explorer le catalogue

- URL: `index.php?page=catalog`
- Filtres disponibles: Catégorie, Prix (en FCFA), Tri
- Cliquer sur un produit pour voir détails

#### 3. Consulter un produit

- Note moyenne, avis, prix indicatif en FCFA
- **Actions possibles:**
  - 📝 **Laisser un avis** → +500 FCFA
  - 📢 **Recommander le produit** → +200 FCFA

#### 4. Poster un avis

- Choisir note (1-5 étoiles)
- Écrire titre + commentaire
- Cliquer "Publier l'avis (+500 FCFA)"
- **Récompense instantanée:** +500 FCFA dans votre solde

#### 5. Recommander un produit

- Cliquer sur "📢 Recommander ce produit"
- Entrer nom/email/téléphone du destinataire
- Cliquer "Envoyer"
- **Récompense instantanée:** +200 FCFA

#### 6. Consulter votre wallet

- URL: `index.php?page=wallet`
- Voir:
  - Solde disponible (FCFA)
  - Total gains (FCFA)
  - Historique transactions
  - Historique retraits
- **Demander un retrait:** Minimum 5 000 FCFA

---

## 🏢 ADMIN ENTREPRISE

### Accès

- CAU: `TECH001` (ou autre CAU admin_entreprise)
- URL après connexion: `index.php?page=admin_dashboard`

### Fonctionnalités

#### 1. Gérer les produits de l'entreprise

- Ajouter nouveaux produits
- Modifier produits existants
- Voir statistiques produits

#### 2. Voir statistiques financières

- Retraits cette semaine
- Commissions gagnées
- Abonnements actifs

---

## 👑 SUPER ADMIN

### Accès

- CAU: `ADMIN000001`
- URL après connexion: `index.php?page=superadmin_dashboard`

### Fonctionnalités principales

#### 1. Tableau de bord global

- **KPIs:**
  - Total utilisateurs
  - Total entreprises
  - Total produits
  - Total avis
  - Total parrainages
  - Retraits en attente
  - Récompenses distribuées

#### 2. Gestion des utilisateurs

- **URL:** `index.php?page=manage_users`
- **Actions possibles:**

##### ➕ Créer un utilisateur

1. Cliquer "➕ Créer un utilisateur"
2. Remplir formulaire:
   - Nom complet
   - Téléphone
   - Rôle (Utilisateur / Admin Entreprise / Super Admin)
   - Solde initial (défaut: 5 000 FCFA)
3. Cliquer "Créer l'utilisateur"
4. **IMPORTANT:** Le **CAU généré** s'affiche dans la notification → **LE NOTER ET LE COMMUNIQUER** à l'utilisateur

##### 🚫 Activer/Désactiver un utilisateur

- Cliquer "🚫 Désactiver" ou "✅ Activer"
- Un utilisateur inactif ne peut plus se connecter

---

## 💰 SYSTÈME DE RÉCOMPENSES (FCFA)

| Action                     | Récompense                            |
| -------------------------- | ------------------------------------- |
| **Inscription**            | 5 000 FCFA                            |
| **Parrainage**             | 5 000 FCFA (pour parrainé ET parrain) |
| **Avis posté**             | 500 FCFA                              |
| **Recommandation produit** | 200 FCFA                              |

**Retrait minimum:** 5 000 FCFA

---

## 🔔 NOTIFICATIONS

### Types de notifications

- ✅ **Succès** (vert): Connexion, action réussie, gain
- ❌ **Erreur** (rouge): Échec connexion, erreur validation
- ⚠️ **Attention** (jaune): Avertissement
- ℹ️ **Info** (bleu): Information générale

### Comportement

- S'affichent en haut à droite
- Disparaissent après 5 secondes
- Peuvent être fermées manuellement (X)

---

## 📊 NAVIGATION

### Menu principal (toujours visible)

| Page               | URL                                   | Description                              |
| ------------------ | ------------------------------------- | ---------------------------------------- |
| Accueil            | `index.php?page=home`                 | Page d'accueil, produits populaires      |
| Catalogue          | `index.php?page=catalog`              | Liste complète des produits avec filtres |
| Mon Dashboard      | `index.php?page=user_dashboard`       | Stats personnelles (utilisateur)         |
| Wallet             | `index.php?page=wallet`               | Solde, transactions, retraits            |
| Admin Dashboard    | `index.php?page=admin_dashboard`      | Stats entreprise (admin)                 |
| Super Admin        | `index.php?page=superadmin_dashboard` | Stats globales (super admin)             |
| Gérer Utilisateurs | `index.php?page=manage_users`         | CRUD utilisateurs (super admin)          |

---

## 🆘 DÉPANNAGE

### Problème: "CAU invalide"

- **Solution:** Vérifier que le CAU est exact (sensible à la casse)
- Essayer: `ADMIN000001`, `TECH001`, `USER001`

### Problème: "Compte inactif"

- **Solution:** Demander à un super admin de réactiver le compte

### Problème: "Impossible de poster un avis"

- **Solution:** Vérifier que vous êtes connecté (`index.php?page=login`)

### Problème: "Solde insuffisant pour retrait"

- **Solution:** Minimum 5 000 FCFA requis

### Problème: "Je ne vois pas mon CAU après inscription"

- **Solution:** Le CAU s'affiche 1 fois après inscription → noter immédiatement

---

## 💡 CONSEILS D'UTILISATION

### Pour les utilisateurs

1. **Noter votre CAU** après inscription (impossible de le récupérer sans admin)
2. **Parrainer vos amis** pour gagner 5 000 FCFA par parrain
3. **Poster des avis honnêtes** pour gagner 500 FCFA
4. **Recommander des produits** à vos contacts pour 200 FCFA
5. **Vérifier votre solde** régulièrement dans `Wallet`

### Pour les admins

1. **Créer des utilisateurs** avec CAU lisibles (noter immédiatement)
2. **Activer/désactiver** rapidement en cas de problème
3. **Surveiller les KPIs** pour suivre la croissance

---

## 🔐 SÉCURITÉ

- **Pas de mot de passe** → Le CAU est confidentiel, ne le partagez pas
- **Session sécurisée** → Déconnexion automatique après inactivité
- **Rôles stricts** → Super admin uniquement pour gestion utilisateurs

---

## 📞 SUPPORT

Pour toute question ou problème:

- Contacter le super admin
- Vérifier ce guide en premier

---

## 🎉 RÉSUMÉ RAPIDE

1. **Connexion:** Entrer CAU → Connecté
2. **Gagner de l'argent:** Avis (+500 FCFA), Recommandations (+200 FCFA), Parrainage (+5 000 FCFA)
3. **Retirer:** Wallet → Retrait (min 5 000 FCFA)
4. **Admin:** Gérer utilisateurs, voir stats, créer comptes

**Monnaie:** FCFA uniquement
**Authentification:** CAU uniquement (pas d'email/password)
**Notifications:** Toujours visibles pour chaque action

---

**Version:** TrustPick V2 - Finale
**Devise:** FCFA (Franc CFA)
**Système:** CAU (Code d'Accès Utilisateur)
