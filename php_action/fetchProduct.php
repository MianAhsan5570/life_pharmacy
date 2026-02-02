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

$orderColumns = array('product.product_id', 'product.product_name', 'product.rate', 'product.quantity', 'brands.brand_name', 'categories.categories_name', 'product.active', 'product.product_id');
$orderBy = isset($orderColumns[$orderCol]) ? $orderColumns[$orderCol] : 'product.product_id';

$where = " WHERE product.status = 1 ";
$params = array();
$types = '';
if ($searchValue !== '') {
	$where .= " AND (product.product_name LIKE ? OR product.product_id LIKE ? OR brands.brand_name LIKE ? OR categories.categories_name LIKE ?) ";
	$term = '%' . $searchValue . '%';
	$params = array($term, $term, $term, $term);
	$types = 'ssss';
}

$countSql = "SELECT COUNT(*) FROM product INNER JOIN brands ON product.brand_id = brands.brand_id INNER JOIN categories ON product.categories_id = categories.categories_id WHERE product.status = 1";
$recordsTotal = (int) $connect->query($countSql)->fetch_row()[0];

$recordsFiltered = $recordsTotal;
if ($searchValue !== '' && $params) {
	$countFilterSql = "SELECT COUNT(*) FROM product INNER JOIN brands ON product.brand_id = brands.brand_id INNER JOIN categories ON product.categories_id = categories.categories_id " . $where;
	$stmt = $connect->prepare($countFilterSql);
	$stmt->bind_param($types, ...$params);
	$stmt->execute();
	$recordsFiltered = (int) $stmt->get_result()->fetch_row()[0];
	$stmt->close();
}

$sql = "SELECT product.product_id, product.product_name, product.product_image, product.quantity, product.rate, product.active,
        brands.brand_name, categories.categories_name
        FROM product 
        INNER JOIN brands ON product.brand_id = brands.brand_id 
        INNER JOIN categories ON product.categories_id = categories.categories_id  
        " . $where . " ORDER BY " . $orderBy . " " . $orderDir . " LIMIT " . $start . ", " . $length;

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
		$productId = $row[0];
		$active = $row[5] == 1 ? "<label class='label label-success'>Available</label>" : "<label class='label label-danger'>Not Available</label>";
		$button = '<div class="btn-group">
	  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
	  <ul class="dropdown-menu">
	    <li><a type="button" data-toggle="modal" data-target="#editProductModal" onclick="editProduct(' . $productId . ')"> <i class="glyphicon glyphicon-edit"></i> Edit</a></li>
	    <li><a type="button" data-toggle="modal" data-target="#removeProductModal" onclick="removeProduct(' . $productId . ')"> <i class="glyphicon glyphicon-trash"></i> Remove</a></li>
	  </ul>
	</div>';
		$imageUrl = substr($row[2], 3);
		$productImage = "<img class='img-round' src='" . $imageUrl . "' style='height:30px; width:50px;' />";
		$output['data'][] = array($productImage, $row[1], $row[4], $row[3], $row[6], $row[7], $active, $button);
	}
	if ($params && isset($stmt))
		$stmt->close();
}

$connect->close();
echo json_encode($output);
