<?php
/**
 * Documentation intégrée — TrustPick V2
 * Contenu adapté au rôle de l'utilisateur connecté
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

// Déterminer le rôle
$userRole = 'guest';
if (!empty($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $userRole = $stmt->fetchColumn() ?: 'user';
    } catch (Exception $e) {
        $userRole = 'user';
    }
}

// Onglets visibles selon le rôle
$tabs = [
    'guide' => ['label' => 'Guide utilisateur', 'icon' => 'bi-book', 'roles' => ['guest', 'user', 'admin_entreprise', 'super_admin']],
    'tasks' => ['label' => 'Tâches & Gains', 'icon' => 'bi-list-check', 'roles' => ['user', 'admin_entreprise', 'super_admin']],
    'wallet' => ['label' => 'Portefeuille', 'icon' => 'bi-wallet2', 'roles' => ['user', 'admin_entreprise', 'super_admin']],
    'admin' => ['label' => 'Administration', 'icon' => 'bi-building', 'roles' => ['admin_entreprise', 'super_admin']],
    'super' => ['label' => 'SuperAdmin', 'icon' => 'bi-shield-lock', 'roles' => ['super_admin']],
];

$activeTab = $_GET['tab'] ?? 'guide';
if (!isset($tabs[$activeTab]) || !in_array($userRole, $tabs[$activeTab]['roles'])) {
    $activeTab = 'guide';
}
?>
<main class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2 mb-1"><i class="bi bi-book me-2"></i>Documentation TrustPick</h1>
            <p class="text-muted mb-0">Tout ce que vous devez savoir pour utiliser la plateforme efficacement.</p>
        </div>
    </div>

    <!-- Navigation onglets -->
    <ul class="nav doc-nav flex-wrap gap-2 mb-4">
        <?php foreach ($tabs as $key => $tab): ?>
            <?php if (in_array($userRole, $tab['roles'])): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $activeTab === $key ? 'active' : '' ?>"
                        href="<?= url('index.php?page=documentation&tab=' . $key) ?>">
                        <i class="bi <?= $tab['icon'] ?> me-1"></i><?= $tab['label'] ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>

    <!-- Contenu -->
    <div class="card">
        <div class="card-body doc-section">

            <?php if ($activeTab === 'guide'): ?>
                <!-- ========== GUIDE UTILISATEUR ========== -->
                <h2><i class="bi bi-book me-2"></i>Guide de démarrage rapide</h2>

                <h3>1. Créer votre compte</h3>
                <p>L'inscription est <strong>gratuite</strong> et ne prend que 2 minutes :</p>
                <ul>
                    <li>Rendez-vous sur la page <a href="<?= url('index.php?page=register') ?>">Inscription</a></li>
                    <li>Entrez votre <strong>nom complet</strong> et votre <strong>numéro de téléphone</strong></li>
                    <li>Si vous avez un <strong>code de parrainage</strong>, entrez-le pour recevoir un bonus de 5 000 FCFA
                    </li>
                    <li>Acceptez les conditions et créez votre compte</li>
                </ul>

                <h3>2. Se connecter</h3>
                <p>Utilisez votre <strong>Code d'Accès Utilisateur (CAU)</strong> reçu lors de l'inscription. Ce code est
                    unique et personnel — conservez-le précieusement.</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i><strong>Important :</strong> Ne partagez jamais votre
                    CAU. Il remplace le mot de passe et donne accès complet à votre compte.
                </div>

                <h3>3. Explorer le catalogue</h3>
                <p>Le <a href="<?= url('index.php?page=catalog') ?>">Catalogue</a> contient tous les produits disponibles.
                    Vous pouvez :</p>
                <ul>
                    <li>Rechercher par nom ou description</li>
                    <li>Filtrer par entreprise, prix ou note minimum</li>
                    <li>Trier par meilleure note, plus récent, tendance, etc.</li>
                </ul>

                <h3>4. Laisser un avis</h3>
                <p>Cliquez sur un produit, puis utilisez le formulaire en bas de page pour laisser votre évaluation (note de
                    1 à 5 étoiles + commentaire).</p>
                <p>Chaque avis <strong>honnête et utile</strong> vous rapporte des points et crédite votre solde.</p>

                <h3>5. Programme de parrainage</h3>
                <p>Partagez votre code de parrainage pour inviter vos amis. Pour chaque filleul inscrit :</p>
                <ul>
                    <li>Vous recevez <strong>5 000 FCFA</strong> de bonus</li>
                    <li>Votre filleul reçoit également <strong>5 000 FCFA</strong></li>
                </ul>

            <?php elseif ($activeTab === 'tasks'): ?>
                <!-- ========== TÂCHES & GAINS ========== -->
                <h2><i class="bi bi-list-check me-2"></i>Système de tâches quotidiennes</h2>

                <p>Chaque jour, complétez vos tâches pour gagner des récompenses. Les tâches suivent un <strong>ordre
                        progressif</strong> :</p>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ordre</th>
                                <th>Tâche</th>
                                <th>Description</th>
                                <th>Récompense</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-primary">1</span></td>
                                <td><strong>Connexion quotidienne</strong></td>
                                <td>Se connecter à la plateforme — <em>validée automatiquement</em></td>
                                <td class="text-success fw-bold">150 FCFA</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">2</span></td>
                                <td><strong>Laisser un avis</strong></td>
                                <td>Évaluer un produit dans le catalogue</td>
                                <td class="text-success fw-bold">200 FCFA</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">3</span></td>
                                <td><strong>Liker un avis</strong></td>
                                <td>Liker l'avis d'un autre utilisateur</td>
                                <td class="text-success fw-bold">100 FCFA</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">4</span></td>
                                <td><strong>Recommander un produit</strong></td>
                                <td>Recommander un produit que vous appréciez</td>
                                <td class="text-success fw-bold">250 FCFA</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning text-dark">5</span></td>
                                <td><strong>Dépôt de 5 000 FCFA</strong></td>
                                <td>Disponible après avoir accumulé 20 000 FCFA de gains</td>
                                <td class="text-success fw-bold">5 000 FCFA</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>Règles importantes</h3>
                <ul>
                    <li>Les tâches 1 à 4 doivent être complétées <strong>dans l'ordre</strong> chaque jour (sauf le dépôt)
                    </li>
                    <li>La connexion quotidienne (tâche 1) est <strong>automatiquement validée</strong> dès votre connexion
                    </li>
                    <li>Le dépôt (tâche 5) ne bloque pas les autres tâches — il est <strong>optionnel</strong> jusqu'à ce
                        que vos gains cumulés atteignent 20 000 FCFA</li>
                    <li>Les récompenses sont créditées <strong>immédiatement</strong> à votre solde</li>
                    <li>Une notification vous informe après chaque tâche complétée</li>
                </ul>

                <h3>Seuil de dépôt de 20 000 FCFA</h3>
                <p>Le dépôt de 5 000 FCFA est débloqué lorsque vos gains cumulés (tâches + parrainages) atteignent
                    <strong>20 000 FCFA</strong>. Une barre de progression sur la page Tâches vous montre votre avancement.
                </p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>Le dépôt est un mécanisme de confiance : il confirme votre
                    engagement et double votre potentiel de gains quotidiens.
                </div>

            <?php elseif ($activeTab === 'wallet'): ?>
                <!-- ========== PORTEFEUILLE ========== -->
                <h2><i class="bi bi-wallet2 me-2"></i>Portefeuille & Retraits</h2>

                <h3>Votre solde</h3>
                <p>Votre solde est alimenté par :</p>
                <ul>
                    <li><strong>Tâches quotidiennes</strong> — connexion, avis, likes, recommandations</li>
                    <li><strong>Parrainages</strong> — bonus pour chaque filleul inscrit</li>
                    <li><strong>Dépôts</strong> — récompenses de confiance</li>
                </ul>

                <h3>Effectuer un retrait</h3>
                <p>Pour retirer vos gains :</p>
                <ol>
                    <li>Accédez à votre <a href="<?= url('index.php?page=wallet') ?>">Portefeuille</a></li>
                    <li>Vérifiez que votre solde atteint le <strong>minimum requis</strong></li>
                    <li>Choisissez votre méthode de retrait (Mobile Money, PayPal, virement)</li>
                    <li>Entrez le montant et confirmez</li>
                </ol>

                <h3>Délais de traitement</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Méthode</th>
                                <th>Délai</th>
                                <th>Frais</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Mobile Money</td>
                                <td>24-48h</td>
                                <td>Gratuit</td>
                            </tr>
                            <tr>
                                <td>PayPal</td>
                                <td>48-72h</td>
                                <td>Gratuit</td>
                            </tr>
                            <tr>
                                <td>Virement bancaire</td>
                                <td>3-5 jours</td>
                                <td>Gratuit</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>Historique des transactions</h3>
                <p>Toutes vos transactions (gains, dépôts, retraits) sont visibles dans l'onglet <strong>Historique</strong>
                    de votre portefeuille. Chaque opération est horodatée et traçable.</p>

            <?php elseif ($activeTab === 'admin'): ?>
                <!-- ========== ADMIN ENTREPRISE ========== -->
                <h2><i class="bi bi-building me-2"></i>Guide Administrateur Entreprise</h2>

                <h3>Tableau de bord</h3>
                <p>Votre dashboard affiche en temps réel :</p>
                <ul>
                    <li><strong>Nombre de produits</strong> enregistrés</li>
                    <li><strong>Nombre d'avis</strong> reçus sur vos produits</li>
                    <li><strong>Note moyenne</strong> de votre entreprise</li>
                    <li><strong>Tendances</strong> d'engagement utilisateur</li>
                </ul>

                <h3>Gestion des produits</h3>
                <p>Depuis votre dashboard, vous pouvez :</p>
                <ul>
                    <li><strong>Ajouter</strong> de nouveaux produits (titre, description, prix, image)</li>
                    <li><strong>Modifier</strong> les informations existantes</li>
                    <li><strong>Supprimer</strong> les produits obsolètes</li>
                    <li>Consulter les <strong>avis et notes</strong> de chaque produit</li>
                </ul>

                <h3>Analyse des avis</h3>
                <p>Les avis clients sont une mine d'or :</p>
                <ul>
                    <li>Identifiez les produits les mieux notés</li>
                    <li>Repérez les points d'amélioration</li>
                    <li>Suivez l'évolution des notes dans le temps</li>
                </ul>

                <div class="alert alert-info">
                    <i class="bi bi-lightbulb me-2"></i><strong>Conseil :</strong> Répondez aux avis pour montrer à la
                    communauté que vous prenez les retours au sérieux. Cela améliore votre image et votre note moyenne.
                </div>

            <?php elseif ($activeTab === 'super'): ?>
                <!-- ========== SUPERADMIN ========== -->
                <h2><i class="bi bi-shield-lock me-2"></i>Guide SuperAdmin</h2>

                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-octagon me-2"></i><strong>Accès restreint :</strong> Cette section est
                    réservée aux super-administrateurs. Toutes les actions sont journalisées.
                </div>

                <h3>Gestion des utilisateurs</h3>
                <ul>
                    <li>Voir la liste complète des utilisateurs inscrits</li>
                    <li>Modifier les rôles (<code>user</code>, <code>admin_entreprise</code>, <code>super_admin</code>)</li>
                    <li>Activer/désactiver des comptes</li>
                    <li>Consulter les détails (solde, historique, parrainages)</li>
                </ul>

                <h3>Gestion des entreprises</h3>
                <ul>
                    <li>Créer, modifier ou supprimer des entreprises</li>
                    <li>Assigner des administrateurs aux entreprises</li>
                    <li>Voir les statistiques par entreprise</li>
                </ul>

                <h3>Gestion des retraits</h3>
                <ul>
                    <li>Voir tous les retraits en attente</li>
                    <li>Approuver ou rejeter les demandes</li>
                    <li>Suivre l'historique complet des retraits</li>
                </ul>

                <h3>Statistiques système</h3>
                <ul>
                    <li>Total utilisateurs, entreprises, produits, avis</li>
                    <li>Volume financier (dépôts, retraits, soldes)</li>
                    <li>Activité quotidienne et tendances</li>
                </ul>

                <h3>Paramètres</h3>
                <p>Via la table <code>settings</code>, vous pouvez ajuster :</p>
                <ul>
                    <li><code>min_deposit</code> — Montant minimum de dépôt</li>
                    <li><code>min_withdrawal</code> — Montant minimum de retrait</li>
                    <li><code>referral_bonus</code> — Bonus de parrainage</li>
                    <li>Récompenses des tâches quotidiennes</li>
                </ul>

            <?php endif; ?>
        </div>
    </div>

    <!-- Lien retour -->
    <div class="text-center mt-4">
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="<?= url('index.php?page=user_dashboard') ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>Retour au Dashboard
            </a>
        <?php else: ?>
            <a href="<?= url('index.php?page=home') ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i>Retour à l'accueil
            </a>
        <?php endif; ?>
    </div>
</main>