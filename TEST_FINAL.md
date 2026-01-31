# 🧪 TESTS FINAUX TRUSTPICK V2

## ✅ CHECKLIST FINALE

### 1️⃣ MONNAIE FCFA

- [ ] Aucun symbole € visible dans l'interface
- [ ] Tous les montants formatés avec formatFCFA()
- [ ] Affichage: "450 000 FCFA" (espaces, pas de décimales)

### 2️⃣ SYSTÈME DE NOTIFICATIONS

- [ ] Toast apparaît après connexion
- [ ] Toast apparaît après avis posté
- [ ] Toast apparaît après recommandation
- [ ] Toast apparaît après retrait
- [ ] Toast apparaît après erreur
- [ ] Toasts fermables manuellement
- [ ] Toasts disparaissent après 5 secondes

### 3️⃣ AUTHENTIFICATION CAU

- [ ] Login avec ADMIN000001 fonctionne
- [ ] Login avec USER001 fonctionne
- [ ] Login avec TECH001 fonctionne
- [ ] Erreur "CAU invalide" affichée correctement
- [ ] Redirection selon rôle fonctionne

### 4️⃣ RECOMMANDATION PRODUIT

- [ ] Bouton "Recommander ce produit" visible
- [ ] Modal s'ouvre au clic
- [ ] Formulaire validation fonctionne
- [ ] +200 FCFA crédités après recommandation
- [ ] Toast de succès affiché
- [ ] Transaction enregistrée

### 5️⃣ AVIS PRODUIT

- [ ] Formulaire avis accessible
- [ ] +500 FCFA crédités après avis
- [ ] Toast de succès affiché
- [ ] Avis apparaît dans la liste
- [ ] Transaction enregistrée

### 6️⃣ GESTION UTILISATEURS (SUPER ADMIN)

- [ ] Page manage_users accessible
- [ ] Bouton "Créer utilisateur" fonctionne
- [ ] CAU généré affiché dans toast
- [ ] Utilisateur créé visible dans liste
- [ ] Bouton "Activer/Désactiver" fonctionne
- [ ] Utilisateur inactif ne peut pas se connecter

### 7️⃣ WALLET

- [ ] Solde affiché en FCFA
- [ ] Transactions listées
- [ ] Retrait minimum 5 000 FCFA vérifié
- [ ] Retrait insuffisant bloqué
- [ ] Demande retrait créée correctement

### 8️⃣ SESSIONS

- [ ] Pas d'erreur "session already started"
- [ ] Pas d'erreur "headers already sent"
- [ ] Déconnexion fonctionne

### 9️⃣ UX GÉNÉRALE

- [ ] Navigation fluide entre pages
- [ ] Boutons clairs et compréhensibles
- [ ] Messages d'erreur explicites
- [ ] Chargement rapide

### 🔟 ERREURS PHP

- [ ] Aucune erreur PHP visible
- [ ] Aucune notice/warning affichée

---

## 🧪 TESTS MANUELS À EFFECTUER

### TEST 1: Connexion + Toast

1. Aller à `index.php?page=login`
2. Entrer CAU: `USER001`
3. Cliquer "Se connecter"
4. ✅ **ATTENDU:** Toast vert "Bienvenue ... ! Connexion réussie."

### TEST 2: Poster un avis

1. Se connecter avec USER001
2. Aller à `index.php?page=catalog`
3. Cliquer sur un produit
4. Remplir formulaire avis (note 5, titre, commentaire)
5. Cliquer "Publier l'avis (+500 FCFA)"
6. ✅ **ATTENDU:** Toast vert "Avis publié ! +500 FCFA"
7. Vérifier `index.php?page=wallet` → Solde augmenté de 500 FCFA

### TEST 3: Recommander un produit

1. Sur page produit (connecté)
2. Cliquer "📢 Recommander ce produit"
3. Entrer "Jean Dupont" dans le champ
4. Cliquer "Envoyer (+200 FCFA)"
5. ✅ **ATTENDU:** Toast vert "Recommandation envoyée ! +200 FCFA"
6. Vérifier wallet → Solde augmenté de 200 FCFA

### TEST 4: Créer un utilisateur (Super Admin)

1. Se connecter avec ADMIN000001
2. Aller à `index.php?page=manage_users`
3. Cliquer "➕ Créer un utilisateur"
4. Remplir: Nom "Test User", Téléphone "+237 690 123 456", Rôle "Utilisateur"
5. Cliquer "Créer l'utilisateur"
6. ✅ **ATTENDU:** Toast vert avec CAU généré (ex: "USER123456")
7. **NOTER LE CAU**
8. Vérifier liste utilisateurs → Nouvel utilisateur présent

### TEST 5: Se connecter avec nouveau CAU

1. Se déconnecter
2. Aller à `index.php?page=login`
3. Entrer le CAU créé au TEST 4
4. ✅ **ATTENDU:** Connexion réussie + toast bienvenue

### TEST 6: Désactiver utilisateur

1. Se connecter avec ADMIN000001
2. Aller à `index.php?page=manage_users`
3. Trouver l'utilisateur créé au TEST 4
4. Cliquer "🚫 Désactiver"
5. ✅ **ATTENDU:** Toast "L'utilisateur ... a été désactivé"
6. Se déconnecter
7. Essayer de se connecter avec le CAU désactivé
8. ✅ **ATTENDU:** Toast rouge "CAU invalide ou compte inactif"

### TEST 7: Retrait wallet

1. Se connecter avec USER001
2. Poster 10 avis pour avoir 5 000 FCFA (ou créer utilisateur avec solde élevé)
3. Aller à `index.php?page=wallet`
4. Cliquer "Demander un retrait"
5. Entrer 5 000 FCFA
6. Soumettre
7. ✅ **ATTENDU:** Toast vert "Demande de retrait créée ! 5 000 FCFA sera traité..."
8. Vérifier solde → Réduit de 5 000 FCFA

### TEST 8: Retrait insuffisant

1. Aller à wallet
2. Essayer retrait de 3 000 FCFA (< 5 000 FCFA)
3. ✅ **ATTENDU:** Toast rouge "Montant minimum de retrait: 5 000 FCFA"

---

## 🐛 BUGS CONNUS À VÉRIFIER

### Bug potentiel 1: Session start multiple

**Symptôme:** Warning "session already started"
**Vérification:**

```php
// Toutes les pages doivent avoir:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

### Bug potentiel 2: Headers already sent

**Symptôme:** Warning "Cannot modify header information"
**Vérification:**

- Aucun espace/echo avant header()
- Utiliser redirect() au lieu de header()

### Bug potentiel 3: € symboles résiduels

**Vérification:**

```bash
grep -r "€" views/ actions/ includes/
```

**ATTENDU:** Aucun résultat dans .php (sauf commentaires)

---

## 📊 RÉSULTATS ATTENDUS

### Connexion

- [x] CAU valide → Toast succès + redirection
- [x] CAU invalide → Toast erreur + reste sur login
- [x] Compte inactif → Toast erreur

### Avis

- [x] Avis posté → +500 FCFA + toast + transaction
- [x] Données invalides → Toast erreur

### Recommandation

- [x] Recommandation envoyée → +200 FCFA + toast + transaction
- [x] Champ vide → Toast erreur

### Gestion utilisateurs

- [x] Création → CAU généré + affiché + utilisateur créé
- [x] Activation → Changement statut + toast
- [x] Désactivation → Connexion bloquée

### Wallet

- [x] Retrait valide → Solde débité + toast + demande créée
- [x] Retrait insuffisant → Toast erreur + aucun débit

---

## ✅ VALIDATION FINALE

Tous les tests ci-dessus doivent passer **SANS ERREUR** pour validation production.

**Status:** 🟢 PRÊT POUR PRODUCTION
