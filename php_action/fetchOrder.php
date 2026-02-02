<?php

require_once 'core.php';

// DataTables server-side: only fetch the page that is shown + apply search/sort
$draw = isset($_POST['draw']) ? (int) $_POST['draw'] : (isset($_GET['draw']) ? (int) $_GET['draw'] : 1);
$start = isset($_POST['start']) ? (int) $_POST['start'] : 0;
$length = isset($_POST['length']) ? (int) $_POST['length'] : 20;
if ($length < 1 || $length > 500)
	$length = 20;
$searchValue = isset($_POST['search']['value']) ? $connect->real_escape_string(trim($_POST['search']['value'])) : '';
$orderCol = isset($_POST['order'][0]['column']) ? (int) $_POST['order'][0]['column'] : 0;
$orderDir = isset($_POST['order'][0]['dir']) && strtoupper($_POST['order'][0]['dir']) === 'ASC' ? 'ASC' : 'DESC';

$orderColumns = array('orders.order_id', 'orders.order_date', 'orders.client_name', 'orders.client_contact', 'orders.order_id', 'orders.payment_status', 'orders.order_id');
$orderBy = isset($orderColumns[$orderCol]) ? $orderColumns[$orderCol] : 'orders.order_id';

$where = " WHERE orders.order_status = 1 ";
$params = array();
$types = '';

if ($searchValue !== '') {
	$where .= " AND (orders.order_id LIKE ? OR orders.order_date LIKE ? OR orders.client_name LIKE ? OR orders.client_contact LIKE ?) ";
	$term = '%' . $searchValue . '%';
	$params = array($term, $term, $term, $term);
	$types = 'ssss';
}

// Total count (no filter)
$countSql = "SELECT COUNT(*) FROM orders WHERE order_status = 1";
$totalResult = $connect->query($countSql);
$recordsTotal = $totalResult ? (int) $totalResult->fetch_row()[0] : 0;

// Filtered count (with search)
$recordsFiltered = $recordsTotal;
if ($searchValue !== '' && $params) {
	$countFilterSql = "SELECT COUNT(*) FROM orders " . $where;
	$stmt = $connect->prepare($countFilterSql);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$recordsFiltered = (int) $stmt->get_result()->fetch_row()[0];
	$stmt->close();
}

// Page of orders (only this page)
$sql = "SELECT orders.order_id, orders.order_date, orders.client_name, orders.client_contact, orders.payment_status
        FROM orders " . $where . " ORDER BY " . $orderBy . " " . $orderDir . " LIMIT " . $start . ", " . $length;

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
	$orders = array();
	while ($row = $result->fetch_array()) {
		$orders[] = $row;
	}
	if ($params && isset($stmt))
		$stmt->close();

	$orderIds = array_map(function ($r) {
		return (int) $r[0]; }, $orders);
	$idsList = implode(',', $orderIds);
	$countSql = "SELECT order_id, COUNT(*) AS item_count FROM order_item WHERE order_id IN ($idsList) GROUP BY order_id";
	$countResult = $connect->query($countSql);
	$counts = array();
	if ($countResult) {
		while ($cr = $countResult->fetch_assoc()) {
			$counts[$cr['order_id']] = $cr['item_count'];
		}
	}

	foreach ($orders as $row) {
		$orderId = $row[0];
		$itemCount = isset($counts[$orderId]) ? $counts[$orderId] : 0;

		if ($row[4] == 1)
			$paymentStatus = "<label class='label label-success'>Full Payment</label>";
		else if ($row[4] == 2)
			$paymentStatus = "<label class='label label-info'>Advance Payment</label>";
		else
			$paymentStatus = "<label class='label label-warning'>No Payment</label>";

		$button = '<div class="btn-group">
	  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action <span class="caret"></span></button>
	  <ul class="dropdown-menu">
	    <li><a href="orders.php?o=editOrd&i=' . $orderId . '"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
	    <li><a href="orders.php?o=editOrd&i=' . $orderId . '&type=new"> <i class="glyphicon glyphicon-edit"></i> Edit As new</a></li>
	    <li><a type="button" data-toggle="modal" id="paymentOrderModalBtn" data-target="#paymentOrderModal" onclick="paymentOrder(' . $orderId . ')"> <i class="glyphicon glyphicon-save"></i> Payment</a></li>
	    <li><a type="button" onclick="printOrder(' . $orderId . ')"> <i class="glyphicon glyphicon-print"></i> Print </a></li>
	    <li><a type="button" onclick="printOrder2(' . $orderId . ')"> <i class="glyphicon glyphicon-print"></i> Print As New </a></li>
	    <li><a type="button" data-toggle="modal" data-target="#removeOrderModal" id="removeOrderModalBtn" onclick="removeOrder(' . $orderId . ')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>
	  </ul>
	</div>';

		$output['data'][] = array($row[0], $row[1], $row[2], $row[3], array($itemCount), $paymentStatus, $button);
	}
}

$connect->close();

echo json_encode($output);
