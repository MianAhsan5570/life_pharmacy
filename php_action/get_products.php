<?php
include_once "db_connect.php"; // your $dbc connection

// Security: only allow AJAX requests (optional but recommended)
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    exit("Direct access not allowed");
}

$draw        = $_POST['draw'] ?? 1;
$start       = $_POST['start'] ?? 0;
$length      = $_POST['length'] ?? 10;
$searchValue = $_POST['search']['value'] ?? '';
$orderColumn = $_POST['order'][0]['column'] ?? 0;
$orderDir    = $_POST['order'][0]['dir'] ?? 'desc';

// Map DataTables column index to database column
$columns = [
    0 => 'p.product_id',
    1 => 'p.product_name',
    2 => 'p.rate',
    3 => 'p.alert_at',
    4 => 'c.categories_name',
    5 => 'b.brand_name'
];

$orderBy = $columns[$orderColumn] ?? 'p.product_id';
$orderBy .= " " . ($orderDir === 'asc' ? 'ASC' : 'DESC');

// Basic search
$searchSql = "";
$searchParams = [];

if (!empty($searchValue)) {
    $searchSql = " AND (
        p.product_id LIKE ? OR
        p.product_name LIKE ? OR
        p.rate LIKE ? OR
        p.alert_at LIKE ? OR
        c.categories_name LIKE ? OR
        b.brand_name LIKE ?
    )";
    $like = "%$searchValue%";
    $searchParams = [$like, $like, $like, $like, $like, $like];
}

// Total records (without filter)
$totalSql = "SELECT COUNT(*) as total FROM product WHERE status = 1";
$totalResult = mysqli_query($dbc, $totalSql);
$totalRow = mysqli_fetch_assoc($totalResult);
$recordsTotal = $totalRow['total'];

// Filtered records
$filterSql = "SELECT COUNT(*) as filtered 
              FROM product p
              INNER JOIN categories c ON p.categories_id = c.categories_id
              INNER JOIN brands b ON p.brand_id = b.brand_id
              WHERE p.status = 1 $searchSql";

$stmt = mysqli_prepare($dbc, $filterSql);
if ($searchParams) {
    mysqli_stmt_bind_param($stmt, str_repeat('s', count($searchParams)), ...$searchParams);
}
mysqli_stmt_execute($stmt);
$filterResult = mysqli_stmt_get_result($stmt);
$filterRow = mysqli_fetch_assoc($filterResult);
$recordsFiltered = $filterRow['filtered'];

// Main query with pagination + order
$sql = "SELECT 
            p.product_id, p.product_name, p.rate, p.alert_at,
            c.categories_name,
            b.brand_name
        FROM product p
        INNER JOIN categories c ON p.categories_id = c.categories_id
        INNER JOIN brands b ON p.brand_id = b.brand_id
        WHERE p.status = 1 $searchSql
        ORDER BY $orderBy
        LIMIT ?, ?";

$stmt = mysqli_prepare($dbc, $sql);

$types = str_repeat('s', count($searchParams)) . 'ii';
$params = array_merge($searchParams, [(int)$start, (int)$length]);

mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = [
        'product_id'      => $row['product_id'],
        'product_name'    => htmlspecialchars($row['product_name']),
        'rate'            => htmlspecialchars($row['rate']),
        'alert_at'        => htmlspecialchars($row['alert_at']),
        'categories_name' => htmlspecialchars($row['categories_name']),
        'brand_name'      => htmlspecialchars($row['brand_name']),
        // 'deal'            => $row['deal'] ?? 0
    ];
}

// Final response for DataTables
$response = [
    "draw"            => (int)$draw,
    "recordsTotal"    => (int)$recordsTotal,
    "recordsFiltered" => (int)$recordsFiltered,
    "data"            => $data
];

header('Content-Type: application/json');
echo json_encode($response);
exit;