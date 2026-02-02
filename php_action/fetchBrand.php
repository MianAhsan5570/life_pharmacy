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

$orderColumns = array('brand_id', 'brand_name', 'brand_active', 'brand_id');
$orderBy = isset($orderColumns[$orderCol]) ? $orderColumns[$orderCol] : 'brand_id';

$where = " WHERE brand_status = 1 ";
$params = array();
$types = '';
if ($searchValue !== '') {
	$where .= " AND brand_name LIKE ? ";
	$params = array('%' . $searchValue . '%');
	$types = 's';
}

$countSql = "SELECT COUNT(*) FROM brands WHERE brand_status = 1";
$recordsTotal = (int) $connect->query($countSql)->fetch_row()[0];
$recordsFiltered = $recordsTotal;
if ($searchValue !== '' && $params) {
	$stmt = $connect->prepare("SELECT COUNT(*) FROM brands " . $where);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$recordsFiltered = (int) $stmt->get_result()->fetch_row()[0];
	$stmt->close();
}

$sql = "SELECT brand_id, brand_name, brand_active FROM brands " . $where . " ORDER BY " . $orderBy . " " . $orderDir . " LIMIT " . $start . ", " . $length;
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
	while ($row = $result->fetch_array()) {
		$brandId = $row[0];
		$activeBrands = $row[2] == 1 ? "<label class='label label-success'>Available</label>" : "<label class='label label-danger'>Not Available</label>";
		$button = '<div class="btn-group">
	  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
	  <ul class="dropdown-menu">
	    <li><a type="button" data-toggle="modal" data-target="#editBrandModel" onclick="editBrands(' . $brandId . ')"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
	    <li><a type="button" data-toggle="modal" data-target="#removeMemberModal" onclick="removeBrands(' . $brandId . ')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>
	  </ul>
	</div>';
		$output['data'][] = array($row[1], $activeBrands, $button);
	}
	if ($params && isset($stmt))
		$stmt->close();
}
$connect->close();
echo json_encode($output);
