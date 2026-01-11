# TrustPick — Version PHP (interface statique)

Instructions rapides pour lancer l'interface statique en PHP (XAMPP):

- Copier le dossier dans votre htdocs (déjà présent dans ce repo).
- Accéder à `http://localhost/TrustPick/public/index.php` ou selon votre configuration Apache.
- Pages disponibles via le paramètre `page` :
  - `index.php?page=home` — Accueil
  - `index.php?page=catalog` — Catalogue
  - `index.php?page=product` — Fiche produit
  - `index.php?page=company` — Profil entreprise
  - `index.php?page=login` — Connexion
  - `index.php?page=register` — Inscription
  - `index.php?page=user_dashboard` — Tableau de bord utilisateur
  - `index.php?page=company_dashboard` — Tableau de bord entreprise
  - `index.php?page=admin_dashboard` — Dashboard admin
  - `index.php?page=wallet` — Porte-monnaie

Remarques:
- Tout est statique: intégration dynamique (base de données, authentification, logique de récompense) à implémenter ensuite.
- Les assets CSS/JS sont dans `assets/css/app.css` et `assets/js/app.js`.
