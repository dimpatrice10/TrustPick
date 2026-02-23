<?php
/**
 * TrustPick V2 - API CRUD Super Admin : Gestion des Tâches
 * 
 * Endpoints (POST uniquement) :
 *   action=list      → Liste toutes les tâches
 *   action=get       → Détail d'une tâche (id requis)
 *   action=create    → Créer une tâche
 *   action=update    → Modifier une tâche (id requis)
 *   action=delete    → Supprimer une tâche (id requis)
 *   action=toggle    → Basculer un champ booléen (id + field requis)
 *   action=reorder   → Réordonner les tâches (orders[] requis)
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// ── Sécurité : Super Admin uniquement ──
if (!SessionManager::isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé. Super Admin requis.']);
    exit;
}

// ── Méthode POST uniquement ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // ── LISTE ──
        case 'list':
            $stmt = $pdo->query('SELECT * FROM tasks_definitions ORDER BY task_order ASC, id ASC');
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compter les complétions par tâche (stats)
            $statsStmt = $pdo->query('
                SELECT task_id, COUNT(*) as total_completions, COUNT(DISTINCT user_id) as unique_users
                FROM user_tasks GROUP BY task_id
            ');
            $stats = [];
            foreach ($statsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $stats[$row['task_id']] = $row;
            }

            foreach ($tasks as &$task) {
                $task['total_completions'] = $stats[$task['id']]['total_completions'] ?? 0;
                $task['unique_users'] = $stats[$task['id']]['unique_users'] ?? 0;
            }
            unset($task);

            echo json_encode(['success' => true, 'tasks' => $tasks]);
            break;

        // ── DÉTAIL ──
        case 'get':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID invalide.']);
                break;
            }
            $stmt = $pdo->prepare('SELECT * FROM tasks_definitions WHERE id = ?');
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$task) {
                echo json_encode(['success' => false, 'message' => 'Tâche introuvable.']);
                break;
            }
            echo json_encode(['success' => true, 'task' => $task]);
            break;

        // ── CRÉATION ──
        case 'create':
            $errors = validateTaskInput($_POST);
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
                break;
            }

            // Vérifier unicité du code
            $checkStmt = $pdo->prepare('SELECT id FROM tasks_definitions WHERE task_code = ?');
            $checkStmt->execute([trim($_POST['task_code'])]);
            if ($checkStmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Ce code de tâche existe déjà.']);
                break;
            }

            $data = sanitizeTaskInput($_POST);

            $sql = 'INSERT INTO tasks_definitions 
                (task_code, task_name, description, reward_amount, is_daily, is_active, 
                 is_repeatable, is_available_anytime, is_ignorable, start_date, end_date, task_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['task_code'],
                $data['task_name'],
                $data['description'],
                $data['reward_amount'],
                $data['is_daily'],
                $data['is_active'],
                $data['is_repeatable'],
                $data['is_available_anytime'],
                $data['is_ignorable'],
                $data['start_date'],
                $data['end_date'],
                $data['task_order']
            ]);

            $newId = $pdo->lastInsertId();
            echo json_encode(['success' => true, 'message' => 'Tâche créée avec succès.', 'id' => $newId]);
            break;

        // ── MISE À JOUR ──
        case 'update':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID invalide.']);
                break;
            }

            // Vérifier existence
            $existStmt = $pdo->prepare('SELECT id, task_code FROM tasks_definitions WHERE id = ?');
            $existStmt->execute([$id]);
            $existing = $existStmt->fetch(PDO::FETCH_ASSOC);
            if (!$existing) {
                echo json_encode(['success' => false, 'message' => 'Tâche introuvable.']);
                break;
            }

            $errors = validateTaskInput($_POST, $id);
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
                break;
            }

            // Vérifier unicité du code (si modifié)
            $newCode = trim($_POST['task_code']);
            if ($newCode !== $existing['task_code']) {
                $checkStmt = $pdo->prepare('SELECT id FROM tasks_definitions WHERE task_code = ? AND id != ?');
                $checkStmt->execute([$newCode, $id]);
                if ($checkStmt->fetch()) {
                    echo json_encode(['success' => false, 'message' => 'Ce code de tâche existe déjà.']);
                    break;
                }
            }

            $data = sanitizeTaskInput($_POST);

            $sql = 'UPDATE tasks_definitions SET 
                task_code = ?, task_name = ?, description = ?, reward_amount = ?,
                is_daily = ?, is_active = ?, is_repeatable = ?, is_available_anytime = ?,
                is_ignorable = ?, start_date = ?, end_date = ?, task_order = ?
                WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['task_code'],
                $data['task_name'],
                $data['description'],
                $data['reward_amount'],
                $data['is_daily'],
                $data['is_active'],
                $data['is_repeatable'],
                $data['is_available_anytime'],
                $data['is_ignorable'],
                $data['start_date'],
                $data['end_date'],
                $data['task_order'],
                $id
            ]);

            echo json_encode(['success' => true, 'message' => 'Tâche mise à jour avec succès.']);
            break;

        // ── SUPPRESSION ──
        case 'delete':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID invalide.']);
                break;
            }

            // Vérifier existence
            $existStmt = $pdo->prepare('SELECT id, task_name FROM tasks_definitions WHERE id = ?');
            $existStmt->execute([$id]);
            $existing = $existStmt->fetch(PDO::FETCH_ASSOC);
            if (!$existing) {
                echo json_encode(['success' => false, 'message' => 'Tâche introuvable.']);
                break;
            }

            // Vérifier les user_tasks liées
            $usageStmt = $pdo->prepare('SELECT COUNT(*) FROM user_tasks WHERE task_id = ?');
            $usageStmt->execute([$id]);
            $usageCount = intval($usageStmt->fetchColumn());

            // Suppression avec cascade (ON DELETE CASCADE sur user_tasks)
            $pdo->prepare('DELETE FROM tasks_definitions WHERE id = ?')->execute([$id]);

            $msg = sprintf('Tâche "%s" supprimée.', $existing['task_name']);
            if ($usageCount > 0) {
                $msg .= sprintf(' %d complétions associées ont été supprimées.', $usageCount);
            }

            echo json_encode(['success' => true, 'message' => $msg]);
            break;

        // ── BASCULE BOOLÉENNE ──
        case 'toggle':
            $id = intval($_POST['id'] ?? 0);
            $field = $_POST['field'] ?? '';

            $allowedFields = ['is_active', 'is_daily', 'is_repeatable', 'is_available_anytime', 'is_ignorable'];
            if ($id <= 0 || !in_array($field, $allowedFields)) {
                echo json_encode(['success' => false, 'message' => 'Paramètres invalides.']);
                break;
            }

            // Basculer la valeur
            $sql = "UPDATE tasks_definitions SET {$field} = NOT {$field} WHERE id = ?";
            $pdo->prepare($sql)->execute([$id]);

            // Si on désactive is_available_anytime, on ne touche pas aux dates (l'admin les définira)
            // Si on active is_available_anytime, on remet les dates à NULL
            if ($field === 'is_available_anytime') {
                $stmt = $pdo->prepare('SELECT is_available_anytime FROM tasks_definitions WHERE id = ?');
                $stmt->execute([$id]);
                $newValue = (bool) $stmt->fetchColumn();
                if ($newValue) {
                    $pdo->prepare('UPDATE tasks_definitions SET start_date = NULL, end_date = NULL WHERE id = ?')->execute([$id]);
                }
            }

            // Retourner la valeur actuelle
            $stmt = $pdo->prepare("SELECT {$field} FROM tasks_definitions WHERE id = ?");
            $stmt->execute([$id]);
            $newValue = (bool) $stmt->fetchColumn();

            echo json_encode(['success' => true, 'field' => $field, 'value' => $newValue, 'message' => 'Mis à jour.']);
            break;

        // ── RÉORDONNEMENT ──
        case 'reorder':
            $orders = $_POST['orders'] ?? [];
            if (!is_array($orders) || empty($orders)) {
                echo json_encode(['success' => false, 'message' => 'Données de réordonnement manquantes.']);
                break;
            }

            $stmt = $pdo->prepare('UPDATE tasks_definitions SET task_order = ? WHERE id = ?');
            $pdo->beginTransaction();
            foreach ($orders as $item) {
                $stmt->execute([intval($item['order']), intval($item['id'])]);
            }
            $pdo->commit();

            echo json_encode(['success' => true, 'message' => 'Ordre mis à jour.']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Action inconnue: ' . htmlspecialchars($action)]);
            break;
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}

// ── Fonctions utilitaires ──

/**
 * Valider les données d'entrée d'une tâche
 */
function validateTaskInput(array $data, int $editId = 0): array
{
    $errors = [];

    $code = trim($data['task_code'] ?? '');
    if (empty($code)) {
        $errors[] = 'Le code de tâche est requis.';
    } elseif (!preg_match('/^[a-z0-9_]{3,50}$/', $code)) {
        $errors[] = 'Le code doit contenir uniquement des lettres minuscules, chiffres et underscores (3-50 caractères).';
    }

    $name = trim($data['task_name'] ?? '');
    if (empty($name)) {
        $errors[] = 'Le nom de la tâche est requis.';
    } elseif (mb_strlen($name) > 150) {
        $errors[] = 'Le nom ne doit pas dépasser 150 caractères.';
    }

    $reward = $data['reward_amount'] ?? '';
    if ($reward === '' || !is_numeric($reward) || floatval($reward) < 0) {
        $errors[] = 'La récompense doit être un nombre positif ou zéro.';
    }

    $isAvailableAnytime = filter_var($data['is_available_anytime'] ?? true, FILTER_VALIDATE_BOOLEAN);
    if (!$isAvailableAnytime) {
        $startDate = $data['start_date'] ?? '';
        $endDate = $data['end_date'] ?? '';
        if (empty($startDate) || empty($endDate)) {
            $errors[] = 'Les dates de début et de fin sont requises si la tâche n\'est pas disponible tout le temps.';
        } elseif ($startDate > $endDate) {
            $errors[] = 'La date de début doit être antérieure à la date de fin.';
        }
    }

    $taskOrder = $data['task_order'] ?? '';
    if ($taskOrder !== '' && (!is_numeric($taskOrder) || intval($taskOrder) < 0)) {
        $errors[] = 'L\'ordre doit être un nombre positif ou zéro.';
    }

    return $errors;
}

/**
 * Nettoyer et formater les données d'entrée
 */
function sanitizeTaskInput(array $data): array
{
    $isAvailableAnytime = filter_var($data['is_available_anytime'] ?? true, FILTER_VALIDATE_BOOLEAN);

    return [
        'task_code' => trim($data['task_code']),
        'task_name' => trim($data['task_name']),
        'description' => trim($data['description'] ?? ''),
        'reward_amount' => floatval($data['reward_amount'] ?? 0),
        'is_daily' => filter_var($data['is_daily'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
        'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
        'is_repeatable' => filter_var($data['is_repeatable'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
        'is_available_anytime' => $isAvailableAnytime ? 1 : 0,
        'is_ignorable' => filter_var($data['is_ignorable'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
        'start_date' => $isAvailableAnytime ? null : ($data['start_date'] ?: null),
        'end_date' => $isAvailableAnytime ? null : ($data['end_date'] ?: null),
        'task_order' => intval($data['task_order'] ?? 0),
    ];
}
