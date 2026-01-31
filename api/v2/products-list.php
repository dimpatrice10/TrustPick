<?php
/**
 * API TrustPick V2 - Liste de Produits Paginée
 * GET /api/v2/products/list.php?page=1&category_id=1&search=smartphone
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../includes/db.php';
require_once '../../includes/pagination.php';

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$categoryId = isset($_GET['category_id']) ? (int) $_GET['category_id'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : null;
$companyId = isset($_GET['company_id']) ? (int) $_GET['company_id'] : null;

$filters = [];
if ($categoryId)
    $filters['category_id'] = $categoryId;
if ($search)
    $filters['search'] = $search;
if ($companyId)
    $filters['company_id'] = $companyId;

$pagination = new SmartPagination($pdo, 5);
$result = $pagination->paginateProducts($filters, $page);

http_response_code(200);
echo json_encode($result);
