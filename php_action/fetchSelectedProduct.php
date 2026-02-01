<?php 	

require_once 'core.php';

$productId = $_POST['productId'];
$type  = @$_POST['type'];
if($type == 'purchase'){

 $sql = "SELECT product.product_id, product.product_name, product.product_image, product.brand_id, product.categories_id, product.quantity, product.rate, product.active, product.status,

 CAST(purchase AS DECIMAL(7,2) ) AS purchase

,product.alert_at,product.percentage,purchase_item.packrate,purchase_item.pack_quantity,purchase_item.discount
	
 FROM product
INNER JOIN purchase_item ON purchase_item.product_id = product.product_id
 WHERE product.product_id = '$productId' ORDER BY purchase_item.purchase_item_id DESC LIMIT 1";
$result = $connect->query($sql);

if($result->num_rows > 0) { 
 $row = $result->fetch_array();
} // if num_rows

$connect->close();

}else{

$sql = "SELECT product_id, product_name, product_image, brand_id, categories_id, quantity, rate, active, status,

 CAST(purchase AS DECIMAL(7,2) ) AS purchase

,alert_at,percentage FROM product WHERE product_id = $productId";
//echo $sql;
$result = $connect->query($sql);

if($result->num_rows > 0) { 
 $row = $result->fetch_array();
} // if num_rows

$connect->close();
}
echo json_encode($row);