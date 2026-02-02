<?php

require_once 'core.php';

$search = isset($_GET['q']) ? $connect->real_escape_string(trim($_GET['q'])) : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = " WHERE product.status = 1 AND product.active = 1 ";
$params = array();
$types = '';
if ($search !== '') {
	$where .= " AND (product.product_name LIKE ? OR product.product_id LIKE ? OR categories.categories_name LIKE ?) ";
	$term = '%' . $search . '%';
	$params = array($term, $term, $term);
	$types = 'sss';
}

$countSql = "SELECT COUNT(*) FROM product INNER JOIN categories ON product.categories_id = categories.categories_id " . $where;
if ($params && $types) {
	$stmt = $connect->prepare($countSql);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$total = (int) $stmt->get_result()->fetch_row()[0];
	$stmt->close();
} else {
	$total = (int) $connect->query($countSql)->fetch_row()[0];
}

$sql = "SELECT product.product_id, product.product_name, categories.categories_name 
        FROM product 
        INNER JOIN categories ON product.categories_id = categories.categories_id 
        " . $where . " ORDER BY product.product_name ASC LIMIT " . $perPage . " OFFSET " . $offset;

if ($params && $types) {
	$stmt = $connect->prepare($sql);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$result = $stmt->get_result();
} else {
	$result = $connect->query($sql);
}

$results = array();
while ($row = $result->fetch_assoc()) {
	$results[] = array(
		'id' => $row['product_id'],
		'text' => $row['product_name'] . ' (' . $row['categories_name'] . ')'
	);
}
if (isset($stmt))
	$stmt->close();
$connect->close();

$more = ($offset + count($results)) < $total;
echo json_encode(array('results' => $results, 'pagination' => array('more' => $more)));
