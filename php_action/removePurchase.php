<?php

require_once 'core.php';


$valid['success'] = array('success' => false, 'messages' => array());

$purchase_id = isset($_POST['purchase_id']) ? (int) $_POST['purchase_id'] : (isset($_GET['var']) ? (int) $_GET['var'] : 0);

if ($purchase_id) {




	$sql = "DELETE FROM purchase WHERE purchase_id = {$purchase_id}";

	$orderItem = "DELETE FROM purchase_item  WHERE  purchase_id = {$purchase_id}";

	if ($connect->query($sql) === TRUE && $connect->query($orderItem) === TRUE) {
		$valid['success'] = true;
		$valid['messages'] = "Successfully Removed";
	} else {
		$valid['success'] = false;
		$valid['messages'] = "Error while remove the brand";
	}


	$connect->close();

	echo json_encode($valid);

} // /if $_POST