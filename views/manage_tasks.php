<?php
/**
 * TrustPick V2 - Super Admin : Gestion des Tâches
 * Interface CRUD complète avec toggles, dates, validation, modal de suppression
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

SessionManager::requireRole('super_admin', 'index.php?page=login');
?>

<main class="container py-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1"><i class="bi bi-list-check me-2"></i>Gestion des Tâches</h1>
            <p class="text-muted mb-0">Créer, modifier et gérer les tâches et récompenses de la plateforme</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('index.php?page=superadmin_dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Tableau de bord
            </a>
            <button class="btn btn-primary btn-sm" onclick="openTaskForm()">
                <i class="bi bi-plus-lg me-1"></i>Nouvelle Tâche
            </button>
        </div>
    </div>

    <!-- Alertes -->
    <div id="taskAlert" class="alert d-none" role="alert"></div>

    <!-- Tableau des tâches -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>Tâches configurées</h5>
            <span class="badge bg-primary" id="taskCount">...</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tasksTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Tâche</th>
                            <th>Code</th>
                            <th class="text-end">Récompense</th>
                            <th class="text-center">Active</th>
                            <th class="text-center">Quotidienne</th>
                            <th class="text-center">Répétable</th>
                            <th class="text-center">Toujours dispo</th>
                            <th class="text-center">Ignorable</th>
                            <th>Période</th>
                            <th class="text-center">Stats</th>
                            <th style="width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tasksBody">
                        <tr>
                            <td colspan="12" class="text-center py-4 text-muted">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>Chargement...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- Modal : Formulaire Créer / Modifier une tâche                 -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="taskFormModal" tabindex="-1" aria-labelledby="taskFormLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskFormLabel">Nouvelle Tâche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form id="taskForm" onsubmit="return saveTask(event)">
                <input type="hidden" id="taskId" name="id" value="">
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger d-none"></div>

                    <div class="row g-3">
                        <!-- Code -->
                        <div class="col-md-6">
                            <label for="taskCode" class="form-label fw-semibold">
                                Code de la tâche <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="taskCode" name="task_code"
                                pattern="[a-z0-9_]{3,50}" placeholder="ex: leave_review" required>
                            <div class="form-text">Lettres minuscules, chiffres, underscores (3-50 car.)</div>
                        </div>

                        <!-- Nom -->
                        <div class="col-md-6">
                            <label for="taskName" class="form-label fw-semibold">
                                Nom de la tâche <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="taskName" name="task_name" maxlength="150"
                                placeholder="ex: Laisser un avis" required>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="taskDescription" class="form-label fw-semibold">Description</label>
                            <textarea class="form-control" id="taskDescription" name="description" rows="2"
                                placeholder="Description de la tâche..."></textarea>
                        </div>

                        <!-- Récompense -->
                        <div class="col-md-4">
                            <label for="taskReward" class="form-label fw-semibold">
                                Récompense (FCFA) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="taskReward" name="reward_amount" min="0"
                                    step="1" value="0" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                        </div>

                        <!-- Ordre -->
                        <div class="col-md-4">
                            <label for="taskOrder" class="form-label fw-semibold">Ordre de progression</label>
                            <input type="number" class="form-control" id="taskOrder" name="task_order" min="0"
                                value="0">
                            <div class="form-text">0 = pas de contrainte d'ordre</div>
                        </div>

                        <!-- Statut -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Statut</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="taskIsActive" name="is_active"
                                    checked>
                                <label class="form-check-label" for="taskIsActive">Active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <hr class="my-1">
                        </div>

                        <!-- ── Toggles booléens ── -->
                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body py-2 px-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="taskIsDaily" name="is_daily"
                                            checked>
                                        <label class="form-check-label fw-semibold" for="taskIsDaily">
                                            <i class="bi bi-calendar-day me-1"></i>Quotidienne
                                        </label>
                                    </div>
                                    <small class="text-muted">Peut être faite chaque jour</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body py-2 px-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="taskIsRepeatable"
                                            name="is_repeatable" checked>
                                        <label class="form-check-label fw-semibold" for="taskIsRepeatable">
                                            <i class="bi bi-repeat me-1"></i>Répétable
                                        </label>
                                    </div>
                                    <small class="text-muted">Peut être recommencée</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light border-0">
                                <div class="card-body py-2 px-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="taskIsIgnorable"
                                            name="is_ignorable">
                                        <label class="form-check-label fw-semibold" for="taskIsIgnorable">
                                            <i class="bi bi-skip-forward me-1"></i>Ignorable
                                        </label>
                                    </div>
                                    <small class="text-muted">Ne bloque pas la progression</small>
                                </div>
                            </div>
                        </div>

                        <!-- ── Disponibilité temporelle ── -->
                        <div class="col-12">
                            <div class="card bg-light border-0">
                                <div class="card-body py-2 px-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="taskIsAnytime"
                                            name="is_available_anytime" checked onchange="toggleDateFields()">
                                        <label class="form-check-label fw-semibold" for="taskIsAnytime">
                                            <i class="bi bi-clock-history me-1"></i>Disponible tout le temps
                                        </label>
                                    </div>
                                    <div id="dateFieldsRow" class="row g-2 d-none">
                                        <div class="col-md-6">
                                            <label for="taskStartDate" class="form-label small fw-semibold">Date
                                                de début</label>
                                            <input type="date" class="form-control form-control-sm" id="taskStartDate"
                                                name="start_date">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="taskEndDate" class="form-label small fw-semibold">Date
                                                de fin</label>
                                            <input type="date" class="form-control form-control-sm" id="taskEndDate"
                                                name="end_date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="taskSubmitBtn">
                        <i class="bi bi-check-lg me-1"></i><span id="taskSubmitText">Créer la tâche</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- Modal : Confirmation de suppression                           -->
<!-- ══════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Confirmation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Voulez-vous vraiment supprimer la tâche :</p>
                <p class="fw-bold" id="deleteTaskName"></p>
                <p class="text-muted small mb-0" id="deleteTaskWarning"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn" onclick="confirmDelete()">
                    <i class="bi bi-trash me-1"></i>Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- JavaScript : Logique CRUD                                     -->
<!-- ══════════════════════════════════════════════════════════════ -->
<script>
    const API_URL = '<?= url("actions/manage_task.php") ?>';
    let taskFormModal, deleteModalInstance;
    let deleteTargetId = null;

    document.addEventListener('DOMContentLoaded', () => {
        taskFormModal = new bootstrap.Modal(document.getElementById('taskFormModal'));
        deleteModalInstance = new bootstrap.Modal(document.getElementById('deleteModal'));
        loadTasks();
    });

    // ── Charger la liste des tâches ──
    function loadTasks() {
        const body = document.getElementById('tasksBody');
        body.innerHTML = '<tr><td colspan="12" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Chargement...</td></tr>';

        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=list'
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    body.innerHTML = `<tr><td colspan="12" class="text-center py-4 text-danger">${data.message}</td></tr>`;
                    return;
                }

                const tasks = data.tasks;
                document.getElementById('taskCount').textContent = tasks.length + ' tâche' + (tasks.length > 1 ? 's' : '');

                if (tasks.length === 0) {
                    body.innerHTML = '<tr><td colspan="12" class="text-center py-4 text-muted">Aucune tâche configurée</td></tr>';
                    return;
                }

                body.innerHTML = tasks.map(t => renderTaskRow(t)).join('');
            })
            .catch(err => {
                body.innerHTML = `<tr><td colspan="12" class="text-center py-4 text-danger">Erreur de chargement: ${err.message}</td></tr>`;
            });
    }

    // ── Générer une ligne du tableau ──
    function renderTaskRow(t) {
        const boolToggle = (field, value, label) => {
            const checked = value == 1 || value === true ? 'checked' : '';
            return `<div class="form-check form-switch d-flex justify-content-center mb-0">
            <input class="form-check-input" type="checkbox" ${checked} 
                   onchange="toggleField(${t.id}, '${field}')" title="${label}">
        </div>`;
        };

        const period = (t.is_available_anytime == 1 || t.is_available_anytime === true)
            ? '<span class="badge bg-success-subtle text-success">Permanent</span>'
            : (t.start_date && t.end_date
                ? `<small>${formatDate(t.start_date)}<br>→ ${formatDate(t.end_date)}</small>`
                : '<span class="badge bg-warning-subtle text-warning">Non défini</span>');

        const reward = Number(t.reward_amount).toLocaleString('fr-FR') + ' F';
        const rowClass = (t.is_active == 0 || t.is_active === false) ? 'table-secondary opacity-75' : '';

        return `<tr class="${rowClass}" data-id="${t.id}">
        <td><span class="badge bg-secondary">${t.task_order || '-'}</span></td>
        <td>
            <div class="fw-semibold">${escHtml(t.task_name)}</div>
            ${t.description ? `<small class="text-muted">${escHtml(t.description).substring(0, 60)}${t.description.length > 60 ? '...' : ''}</small>` : ''}
        </td>
        <td><code class="text-primary">${escHtml(t.task_code)}</code></td>
        <td class="text-end fw-semibold">${reward}</td>
        <td class="text-center">${boolToggle('is_active', t.is_active, 'Active')}</td>
        <td class="text-center">${boolToggle('is_daily', t.is_daily, 'Quotidienne')}</td>
        <td class="text-center">${boolToggle('is_repeatable', t.is_repeatable, 'Répétable')}</td>
        <td class="text-center">${boolToggle('is_available_anytime', t.is_available_anytime, 'Toujours dispo')}</td>
        <td class="text-center">${boolToggle('is_ignorable', t.is_ignorable, 'Ignorable')}</td>
        <td>${period}</td>
        <td class="text-center">
            <span class="badge bg-info-subtle text-info" title="${t.unique_users || 0} utilisateurs uniques">
                ${t.total_completions || 0}
            </span>
        </td>
        <td>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" onclick="editTask(${t.id})" title="Modifier">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-outline-danger" onclick="deleteTask(${t.id}, '${escAttr(t.task_name)}', ${t.total_completions || 0})" title="Supprimer">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>`;
    }

    // ── Toggle inline ──
    function toggleField(id, field) {
        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=toggle&id=${id}&field=${field}`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', `${fieldLabel(field)} mis à jour.`);
                    // Recharger pour actualiser l'affichage (dates, styles)
                    if (field === 'is_active' || field === 'is_available_anytime') {
                        loadTasks();
                    }
                } else {
                    showAlert('danger', data.message);
                    loadTasks(); // Reverser le toggle
                }
            })
            .catch(err => {
                showAlert('danger', 'Erreur réseau.');
                loadTasks();
            });
    }

    // ── Ouvrir le formulaire (création) ──
    function openTaskForm() {
        document.getElementById('taskFormLabel').textContent = 'Nouvelle Tâche';
        document.getElementById('taskSubmitText').textContent = 'Créer la tâche';
        document.getElementById('taskForm').reset();
        document.getElementById('taskId').value = '';
        document.getElementById('taskIsActive').checked = true;
        document.getElementById('taskIsDaily').checked = true;
        document.getElementById('taskIsRepeatable').checked = true;
        document.getElementById('taskIsAnytime').checked = true;
        document.getElementById('taskIsIgnorable').checked = false;
        document.getElementById('formErrors').classList.add('d-none');
        toggleDateFields();
        taskFormModal.show();
    }

    // ── Ouvrir le formulaire (édition) ──
    function editTask(id) {
        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=get&id=${id}`
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    showAlert('danger', data.message);
                    return;
                }
                const t = data.task;
                document.getElementById('taskFormLabel').textContent = 'Modifier la Tâche';
                document.getElementById('taskSubmitText').textContent = 'Enregistrer';
                document.getElementById('taskId').value = t.id;
                document.getElementById('taskCode').value = t.task_code;
                document.getElementById('taskName').value = t.task_name;
                document.getElementById('taskDescription').value = t.description || '';
                document.getElementById('taskReward').value = t.reward_amount;
                document.getElementById('taskOrder').value = t.task_order || 0;
                document.getElementById('taskIsActive').checked = t.is_active == 1;
                document.getElementById('taskIsDaily').checked = t.is_daily == 1;
                document.getElementById('taskIsRepeatable').checked = t.is_repeatable == 1;
                document.getElementById('taskIsAnytime').checked = t.is_available_anytime == 1;
                document.getElementById('taskIsIgnorable').checked = t.is_ignorable == 1;
                document.getElementById('taskStartDate').value = t.start_date || '';
                document.getElementById('taskEndDate').value = t.end_date || '';
                document.getElementById('formErrors').classList.add('d-none');
                toggleDateFields();
                taskFormModal.show();
            })
            .catch(err => showAlert('danger', 'Erreur réseau: ' + err.message));
    }

    // ── Enregistrer (Créer / Modifier) ──
    function saveTask(e) {
        e.preventDefault();
        const form = document.getElementById('taskForm');
        const formData = new FormData(form);
        const id = formData.get('id');
        formData.set('action', id ? 'update' : 'create');

        // Les checkboxes non cochées ne sont pas envoyées — forcer les valeurs
        ['is_active', 'is_daily', 'is_repeatable', 'is_available_anytime', 'is_ignorable'].forEach(field => {
            const el = document.getElementById(fieldToId(field));
            formData.set(field, el && el.checked ? '1' : '0');
        });

        // Validation front-end basique
        const code = formData.get('task_code') || '';
        const name = formData.get('task_name') || '';
        const errors = [];
        if (!/^[a-z0-9_]{3,50}$/.test(code)) errors.push('Code invalide (a-z, 0-9, _ uniquement, 3-50 car.)');
        if (name.trim().length === 0) errors.push('Le nom est requis.');
        if (formData.get('is_available_anytime') === '0') {
            if (!formData.get('start_date') || !formData.get('end_date')) {
                errors.push('Les dates sont requises si la tâche n\'est pas disponible tout le temps.');
            }
        }
        if (errors.length > 0) {
            const errDiv = document.getElementById('formErrors');
            errDiv.innerHTML = errors.map(e => `<div>• ${e}</div>`).join('');
            errDiv.classList.remove('d-none');
            return false;
        }

        const btn = document.getElementById('taskSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...';

        fetch(API_URL, {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i><span id="taskSubmitText">' + (id ? 'Enregistrer' : 'Créer la tâche') + '</span>';

                if (data.success) {
                    taskFormModal.hide();
                    showAlert('success', data.message);
                    loadTasks();
                } else {
                    const errDiv = document.getElementById('formErrors');
                    errDiv.textContent = data.message;
                    errDiv.classList.remove('d-none');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-1"></i><span id="taskSubmitText">' + (id ? 'Enregistrer' : 'Créer la tâche') + '</span>';
                showAlert('danger', 'Erreur réseau: ' + err.message);
            });

        return false;
    }

    // ── Suppression ──
    function deleteTask(id, name, completions) {
        deleteTargetId = id;
        document.getElementById('deleteTaskName').textContent = '"' + name + '"';
        document.getElementById('deleteTaskWarning').textContent = completions > 0
            ? `⚠️ Cette tâche a ${completions} complétion(s) enregistrée(s) qui seront également supprimées.`
            : 'Aucune complétion associée.';
        deleteModalInstance.show();
    }

    function confirmDelete() {
        if (!deleteTargetId) return;
        const btn = document.getElementById('confirmDeleteBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Suppression...';

        fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${deleteTargetId}`
        })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash me-1"></i>Supprimer';
                deleteModalInstance.hide();
                if (data.success) {
                    showAlert('success', data.message);
                    loadTasks();
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-trash me-1"></i>Supprimer';
                deleteModalInstance.hide();
                showAlert('danger', 'Erreur réseau.');
            });
    }

    // ── Utilitaires ──
    function toggleDateFields() {
        const anytime = document.getElementById('taskIsAnytime').checked;
        const row = document.getElementById('dateFieldsRow');
        row.classList.toggle('d-none', anytime);
        if (anytime) {
            document.getElementById('taskStartDate').value = '';
            document.getElementById('taskEndDate').value = '';
        }
    }

    function showAlert(type, message) {
        const el = document.getElementById('taskAlert');
        el.className = `alert alert-${type} alert-dismissible fade show`;
        el.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        setTimeout(() => el.classList.add('d-none'), 5000);
    }

    function fieldLabel(f) {
        const labels = {
            is_active: 'Statut',
            is_daily: 'Quotidienne',
            is_repeatable: 'Répétable',
            is_available_anytime: 'Disponibilité',
            is_ignorable: 'Ignorable'
        };
        return labels[f] || f;
    }

    function fieldToId(f) {
        const map = {
            is_active: 'taskIsActive',
            is_daily: 'taskIsDaily',
            is_repeatable: 'taskIsRepeatable',
            is_available_anytime: 'taskIsAnytime',
            is_ignorable: 'taskIsIgnorable'
        };
        return map[f] || f;
    }

    function escHtml(s) {
        if (!s) return '';
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function escAttr(s) {
        return (s || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    function formatDate(d) {
        if (!d) return '';
        const parts = d.split('-');
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }
</script>