<?php
/**
 * Server-side DataTables for completeproduct.php (Show Total Product)
 */
require_once 'core.php';

$draw = isset($_POST['draw']) ? (int) $_POST['draw'] : 1;
$start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
$length = isset($_POST['length']) ? (int) $_POST['length'] : 20;
if ($length < 1 || $length > 500)
	$length = 20;
$searchValue = isset($_POST['search']['value']) ? $connect->real_escape_string(trim($_POST['search']['value'])) : '';
$orderCol = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 1;
$orderDir = isset($_POST['order'][0]['dir']) && strtoupper($_POST['order'][0]['dir']) === 'ASC' ? 'ASC' : 'DESC';

$orderColumns = array(0, 'product_id', 'product_name', 'rate', 'quantity', 'categories_id', 'brand_id', 'status', 'product_id', 'product_id');
$orderBy = isset($orderColumns[$orderCol]) ? $orderColumns[$orderCol] : 'product_id';
if ($orderBy === 0)
	$orderBy = 'product_id';

$where = " WHERE 1=1 ";
$params = array();
$types = '';
if ($searchValue !== '') {
	$where .= " AND (product_id LIKE ? OR product_name LIKE ? OR rate LIKE ? OR quantity LIKE ? OR categories_id LIKE ? OR brand_id LIKE ? OR status LIKE ?) ";
	$term = '%' . $searchValue . '%';
	$params = array($term, $term, $term, $term, $term, $term, $term);
	$types = 'sssssss';
}

$countSql = "SELECT COUNT(*) FROM product";
$recordsTotal = (int) $connect->query($countSql)->fetch_row()[0];
$recordsFiltered = $recordsTotal;
if ($searchValue !== '' && $params) {
	$stmt = $connect->prepare("SELECT COUNT(*) FROM product " . $where);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$recordsFiltered = (int) $stmt->get_result()->fetch_row()[0];
	$stmt->close();
}

$sql = "SELECT product_id, product_name, rate, quantity, categories_id, brand_id, status FROM product " . $where . " ORDER BY " . $orderBy . " " . $orderDir . " LIMIT " . $start . ", " . $length;
if ($params && $types) {
	$stmt = $connect->prepare($sql);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$result = $stmt->get_result();
} else {
	$result = $connect->query($sql);
}

$output = array('data' => array(), 'draw' => $draw, 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $recordsFiltered);
if ($result && $result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$pid = (int) $row['product_id'];
		$checkbox = '<input type="checkbox" name="check[]" value="' . $pid . '">';
		$editLink = '<a href="addproduct.php?i=' . $pid . '" target="_blank">Edit</a>';
		$delLink = '<a href="deleteproduct.php?i=' . $pid . '" target="_blank"><button type="button" class="btn btn-primary btn-xs"><span class="glyphicon glyphicon-trash"></span> Delete</button></a>';
		$output['data'][] = array($checkbox, $row['product_id'], $row['product_name'], $row['rate'], $row['quantity'], $row['categories_id'], $row['brand_id'], $row['status'], $editLink, $delLink);
	}
	if ($params && isset($stmt))
		$stmt->close();
}
$connect->close();
echo json_encode($output);
