<?php 

@include_once 'db_connect.php';

@include_once '../inc/functions.php';

if (isset($_REQUEST['setProductEdit'])) {
	$query=mysqli_query($dbc,"UPDATE product SET product_name='".$_REQUEST['product_name']."',rate='".$_REQUEST['rate']."',alert_at='".$_REQUEST['alert_at']."' WHERE product_id='".$_REQUEST['setProductEdit']."' ");
	if ($query) {
		$msg = "Product Has been Updated";
					$sts = 'success';
	}else{
		$msg = mysqli_error($dbc);
					$sts ="danger";
	}
	echo json_encode(['msg'=>$msg,'sts'=>$sts]);

}

 ?>