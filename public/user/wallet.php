<?php
$page_title = 'Portefeuille';
$current_page = 'wallet';

include '../layouts/header.php';
include '../layouts/sidebar-user.php';
?>

<!-- Contenu principal -->
<div class="col-md-9 col-lg-10" style="padding: 2rem;">
    <h2 style="margin-bottom: 2rem;">💰 Mon Portefeuille FCFA</h2>

    <!-- Solde principal -->
    <div class="wallet-balance">
        <p style="margin: 0; opacity: 0.9;">Solde disponible</p>
        <h2 id="wallet-balance"><?php echo number_format($currentUser['balance'], 0, ',', ' '); ?> FCFA</h2>
        <button class="btn btn-light btn-lg mt-3" onclick="openWithdrawalModal()" id="withdraw-btn">
            💵 Demander un retrait
        </button>
        <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.8;">
            Montant minimum de retrait : 5 000 FCFA
        </p>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-value" id="total-earned">0 FCFA</div>
                <div class="stat-label">Gains totaux</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">📤</div>
                <div class="stat-value" id="total-withdrawn">0 FCFA</div>
                <div class="stat-label">Retraits effectués</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-value" id="pending-withdrawals">0</div>
                <div class="stat-label">Retraits en attente</div>
            </div>
        </div>
    </div>

    <!-- Onglets -->
    <div class="tabs-container">
        <div class="tabs-header">
            <button class="tab-button active" data-tab="transactions">💳 Transactions</button>
            <button class="tab-button" data-tab="withdrawals">💵 Retraits</button>
        </div>

        <!-- Onglet Transactions -->
        <div class="tab-content active" id="tab-transactions">
            <div id="transactions-container">
                <div class="pagination-items"></div>
                <div class="pagination-controls"></div>
            </div>
        </div>

        <!-- Onglet Retraits -->
        <div class="tab-content" id="tab-withdrawals">
            <div id="withdrawals-list"></div>
        </div>
    </div>
</div>

<!-- Modal de retrait -->
<div id="withdrawal-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">💵 Demande de retrait</h3>
            <button class="modal-close" onclick="closeWithdrawalModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="withdrawal-form">
                <div class="form-group">
                    <label class="form-label">Montant à retirer (FCFA)</label>
                    <input type="number" class="form-control" id="withdrawal-amount" min="5000" step="1000" required>
                    <small class="text-muted">Minimum : 5 000 FCFA</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Numéro Mobile Money</label>
                    <input type="tel" class="form-control" id="withdrawal-phone" placeholder="+225 XX XX XX XX XX"
                        required>
                    <small class="text-muted">Le retrait sera effectué sur ce numéro</small>
                </div>

                <div class="alert alert-info">
                    <span class="alert-icon">ℹ️</span>
                    <div>
                        <strong>Délai de traitement :</strong> 24-48 heures<br>
                        <strong>Frais :</strong> Aucun frais
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeWithdrawalModal()">Annuler</button>
            <button class="btn-tp-primary" onclick="submitWithdrawal()">Confirmer</button>
        </div>
    </div>
</div>

<script>
    let transactionsPagination;

    // Charger les stats du wallet
    async function loadWalletStats() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/wallet-stats.php');
            const data = await response.json();

            if (data.success) {
                document.getElementById('total-earned').textContent = TrustPick.formatFCFA(data.total_earned || 0);
                document.getElementById('total-withdrawn').textContent = TrustPick.formatFCFA(data.total_withdrawn || 0);
                document.getElementById('pending-withdrawals').textContent = data.pending_count || 0;

                // Mettre à jour le solde
                document.getElementById('wallet-balance').textContent = TrustPick.formatFCFA(data.current_balance || 0);
            }
        } catch (error) {
            console.error('Erreur chargement stats wallet:', error);
        }
    }

    // Initialiser la pagination des transactions
    function initTransactionsPagination() {
        transactionsPagination = new TrustPickPagination({
            endpoint: TrustPick.API_BASE + '/transactions-list.php',
            containerId: 'transactions-container',
            itemsPerPage: 5,
            renderItem: PaginationRenderers.transaction,
            emptyMessage: 'Aucune transaction'
        });

        window['pagination_transactions-container'] = transactionsPagination;
    }

    // Charger les retraits
    async function loadWithdrawals() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/withdrawals-list.php');
            const data = await response.json();

            const container = document.getElementById('withdrawals-list');

            if (data.success && data.withdrawals && data.withdrawals.length > 0) {
                const withdrawalsHTML = data.withdrawals.map(w => {
                    const statusClass = {
                        'pending': 'badge-pending',
                        'approved': 'badge-active',
                        'completed': 'badge-active',
                        'rejected': 'badge-inactive'
                    };

                    const statusText = {
                        'pending': '⏳ En attente',
                        'approved': '✓ Approuvé',
                        'completed': '✓ Complété',
                        'rejected': '❌ Rejeté'
                    };

                    return `
                    <div class="tp-card" style="padding: 1.2rem; margin-bottom: 1rem;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 style="margin: 0;">${TrustPick.formatFCFA(w.amount)}</h6>
                                <small class="text-muted">Numéro : ${w.phone_number}</small><br>
                                <small class="text-muted">${TrustPick.formatRelativeTime(w.created_at)}</small>
                                ${w.notes ? `<br><small class="text-muted">${w.notes}</small>` : ''}
                            </div>
                            <span class="badge-tp ${statusClass[w.status]}">${statusText[w.status]}</span>
                        </div>
                    </div>
                `;
                }).join('');
                container.innerHTML = withdrawalsHTML;
            } else {
                container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">💵</div>
                    <h3 class="empty-state-title">Aucun retrait</h3>
                    <p class="empty-state-description">Vous n'avez pas encore effectué de demande de retrait.</p>
                </div>
            `;
            }
        } catch (error) {
            console.error('Erreur chargement retraits:', error);
        }
    }

    // Gestion des onglets
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', () => {
            // Désactiver tous les onglets
            document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Activer l'onglet cliqué
            button.classList.add('active');
            const tabId = button.getAttribute('data-tab');
            document.getElementById('tab-' + tabId).classList.add('active');

            // Charger les données si nécessaire
            if (tabId === 'withdrawals') {
                loadWithdrawals();
            }
        });
    });

    // Modal de retrait
    function openWithdrawalModal() {
        const currentBalance = <?php echo $currentUser['balance']; ?>;

        if (currentBalance < 5000) {
            TrustPick.showToast('Solde insuffisant. Minimum 5 000 FCFA requis.', 'warning');
            return;
        }

        const modal = document.getElementById('withdrawal-modal');
        modal.classList.add('active');
    }

    function closeWithdrawalModal() {
        const modal = document.getElementById('withdrawal-modal');
        modal.classList.remove('active');
        document.getElementById('withdrawal-form').reset();
    }

    // Soumettre le retrait
    async function submitWithdrawal() {
        const amount = document.getElementById('withdrawal-amount').value;
        const phone = document.getElementById('withdrawal-phone').value;

        if (!amount || amount < 5000) {
            TrustPick.showToast('Le montant minimum est de 5 000 FCFA', 'error');
            return;
        }

        if (!phone) {
            TrustPick.showToast('Veuillez saisir un numéro de téléphone', 'error');
            return;
        }

        try {
            const response = await TrustPick.api('withdrawal-request.php', {
                method: 'POST',
                body: {
                    amount: parseFloat(amount),
                    phone_number: phone
                }
            });

            if (response && response.success) {
                TrustPick.showToast('Demande de retrait enregistrée avec succès', 'success');
                closeWithdrawalModal();
                loadWalletStats();
                loadWithdrawals();
            }
        } catch (error) {
            console.error('Erreur soumission retrait:', error);
        }
    }

    // Initialiser
    window.addEventListener('load', () => {
        loadWalletStats();
        initTransactionsPagination();
    });
</script>

<?php include '../layouts/footer.php'; ?>