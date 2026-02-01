<?php 	
 


require_once 'core.php';
require_once 'db_connect.php';



$sql = "SELECT product.product_id,product.product_name,product.categories_id,product.status,categories.categories_id,categories.categories_name FROM categories INNER JOIN product ON categories.categories_id=product.categories_id WHERE  product.status = 1 ORDER BY product_name ASC";

$result = mysqli_query($dbc,$sql);



while($r=mysqli_fetch_assoc($result)){
				$arr[]=$r;
			}
	echo json_encode(["data"=>$arr]);

 //$connect->close();



