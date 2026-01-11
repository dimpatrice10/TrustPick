# UI Enhancements — TrustPick (CSS classes)

Bref guide d'utilisation (français)

Objectif
- Fournir des classes utilitaires et composants CSS/JS légers pour améliorer l'UI/UX sans toucher à la logique métier.

Fichiers ajoutés
- `assets/css/ui-enhancements.css` — styles pour boutons animés, cartes dynamiques, inputs améliorés, skeletons, modals, FAB et utilitaires.
- `assets/js/ui-enhancements.js` — micro-interactions (ripple, labels flottants, modals helpers, suppression de skeletons, accessibilité clavier).

Principales classes et utilisation

- `.btn-animated`
  - Usage: appliquer sur les boutons existants (`<a>` ou `<button class="btn">`) pour ajouter hover/focus subtils et couche lumineuse.
  - Exemple: `<a class="btn btn-animated ripple">Action</a>`
  - Option: ajouter `ripple` pour activer l'effet 'ink' au clic (nécessite `ui-enhancements.js`).

- `.card-dynamic`
  - Usage: appliquer sur les éléments `.card` pour profondeur, translation et ombre améliorées.
  - Exemple: `<article class="card card-dynamic">...</article>`

- `.input-enhanced`
  - Usage: appliquer au conteneur `label` ou directement à l'`input`/`select` pour activer l'animation de focus/float label JS.
  - Exemple (label wrapper): `<label class="input-enhanced">Email<br><input ...></label>`
  - La JS détecte la valeur initiale et ajoute `filled` quand nécessaire.

- `.skeleton` / `.skeleton.text` / `.skeleton.card` / `.skeleton.avatar`
  - Usage: placeholder de chargement. `ui-enhancements.js` retire `.skeleton` après `window.load`.
  - Exemple: `<div class="skeleton card"></div>`

- `.tp-modal` (structure)
  - Structure recommandée:
    ```html
    <div class="tp-modal" id="myModal">
      <div class="tp-modal-backdrop"></div>
      <div class="tp-modal-panel">Contenu</div>
    </div>
    ```
  - Ouvrir via: `<button data-modal-target="#myModal">Ouvrir</button>`
  - Fermer via: `<button data-modal-close>Fermer</button>` ou `Esc`.

- `.fab`
  - Usage: Floating Action Button (position fixe). Exemple: `<a class="fab" href="#">+</a>`

Bonnes pratiques
- Préserver les classes existantes (`btn`, `card`, `badge`) et *ajouter* les nouvelles classes (ne pas remplacer).
- Préférer `ripple` seulement sur boutons qui ont un fond contrasté.
- Respecter `prefers-reduced-motion`: les animations sont réduites automatiquement.

Notes techniques
- `ui-enhancements.js` est très léger et autonome (vanilla JS). Il gère ripple, float labels, modals (ouverture/fermeture) et retire les skeletons après chargement.
- Les fichiers ont été inclus dans la page principale `index.html` (racine). Si vous souhaitez appliquer dans les pages statiques du dossier `html/`, ajoutez le lien suivant dans leurs `<head>` et avant `</body>`:

  - Head: `<link rel="stylesheet" href="../assets/css/ui-enhancements.css" />`
  - Body end: `<script src="../assets/js/ui-enhancements.js"></script>`

Prochaine étape proposée
- Optionnel: automatiser l'ajout des classes dans les pages HTML sous `html/` (je peux le faire si vous validez).
- Optionnel: ajouter petites animations JS basées sur IntersectionObserver pour lazy-animation avancée (peu coûteux en perf).

Si vous voulez que j'applique automatiquement ces classes dans `html/` aussi, confirmez et je lancerai la passe.
