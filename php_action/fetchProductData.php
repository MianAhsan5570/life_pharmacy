<?php 	
 

require_once 'core.php';
require_once 'db_connect.php';

// Single query returns everything both order.js and purchase.js need to
// build the dropdown AND populate rate/percentage/stock without a second
// AJAX call per row. This result is cached client-side (see order.js /
// purchase.js loadProducts()) so this query only runs ONCE per page load,
// not once per "Add Row" click.
$sql = "SELECT 
			product.product_id,
			product.product_name,
			product.categories_id,
			product.status,
			categories.categories_name,
			product.rate,
			product.percentage,
			product.quantity,
			product.alert_at,
			CAST(product.purchase AS DECIMAL(7,2)) AS purchase,
			product.product_image
		FROM categories 
		INNER JOIN product ON categories.categories_id = product.categories_id 
		WHERE product.status = 1 
		ORDER BY product_name ASC";

$result = mysqli_query($dbc, $sql);

$arr = [];
while ($r = mysqli_fetch_assoc($result)) {
	$arr[] = $r;
}

echo json_encode(["data" => $arr]);

//$connect->close();
