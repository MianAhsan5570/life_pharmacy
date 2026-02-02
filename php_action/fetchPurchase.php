<?php
require_once 'core.php';

$draw = isset($_POST['draw']) ? (int) $_POST['draw'] : 1;
$start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
$length = isset($_POST['length']) ? (int) $_POST['length'] : 20;
if ($length < 1 || $length > 500)
	$length = 20;
$searchValue = isset($_POST['search']['value']) ? $connect->real_escape_string(trim($_POST['search']['value'])) : '';
$orderCol = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 0;
$orderDir = isset($_POST['order'][0]['dir']) && strtoupper($_POST['order'][0]['dir']) === 'ASC' ? 'ASC' : 'DESC';

$orderColumns = array('purchase_id', 'purchase_date', 'client_name', 'client_contact', 'sub_total', 'purchase_id');
$orderBy = isset($orderColumns[$orderCol]) ? $orderColumns[$orderCol] : 'purchase_id';

$where = " WHERE 1=1 ";
$params = array();
$types = '';
if ($searchValue !== '') {
	$where .= " AND (purchase_id LIKE ? OR purchase_date LIKE ? OR client_name LIKE ? OR client_contact LIKE ? OR sub_total LIKE ?) ";
	$term = '%' . $searchValue . '%';
	$params = array($term, $term, $term, $term, $term);
	$types = 'sssss';
}

$countSql = "SELECT COUNT(*) FROM purchase";
$recordsTotal = (int) $connect->query($countSql)->fetch_row()[0];
$recordsFiltered = $recordsTotal;
if ($searchValue !== '' && $params) {
	$stmt = $connect->prepare("SELECT COUNT(*) FROM purchase " . $where);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$recordsFiltered = (int) $stmt->get_result()->fetch_row()[0];
	$stmt->close();
}

$sql = "SELECT purchase_id, purchase_date, client_name, client_contact, sub_total FROM purchase " . $where . " ORDER BY " . $orderBy . " " . $orderDir . " LIMIT " . $start . ", " . $length;
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
		$pid = (int) $row['purchase_id'];
		$button = '<div class="btn-group">
	  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>
	  <ul class="dropdown-menu">
	    <li><a href="php_action/print_purchase.php?var=' . $pid . '" target="_blank"> <i class="glyphicon glyphicon-print"></i> Print</a></li>
	    <li><a href="edit_purchase.php?var=' . $pid . '"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
	    <li><a type="button" data-toggle="modal" data-target="#removeOrderModal" onclick="removePurchase(' . $pid . ')"> <i class="glyphicon glyphicon-trash"></i> Delete</a></li>
	  </ul>
	</div>';
		$output['data'][] = array($row['purchase_id'], $row['purchase_date'], $row['client_name'], $row['client_contact'], $row['sub_total'], $button);
	}
	if ($params && isset($stmt))
		$stmt->close();
}

$connect->close();
echo json_encode($output);
