<?php 	

require_once 'core.php';

 
$valid['success'] = array('success' => false, 'messages' => array());

$purchase_id=  $_GET['var'];


if($purchase_id) { 
	
	 
		

 $sql = "DELETE FROM purchase WHERE purchase_id = {$purchase_id}";

 $orderItem = "DELETE FROM purchase_item  WHERE  purchase_id = {$purchase_id}";

 if($connect->query($sql) === TRUE && $connect->query($orderItem) === TRUE) {
 	$valid['success'] = true;
	$valid['messages'] = "Successfully Removed";		
 } else {
 	$valid['success'] = false;
 	$valid['messages'] = "Error while remove the brand";
 }

 
 $connect->close();

 echo '<script>alert("Purchase Deleted......!")</script>';
 echo header('location:../show_purchase.php');

 echo json_encode($valid);
 
} // /if $_POST