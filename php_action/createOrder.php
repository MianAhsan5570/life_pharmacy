<?php 	

require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array(), 'order_id' => '');
// print_r($valid);
if($_POST) {	

	$orderDate 						= date('Y-m-d', strtotime($_POST['orderDate']));	
  $clientName 					= $_POST['clientName'];
  $clientContact 				= $_POST['clientContact'];
  $subTotalValue 				= $_POST['subTotalValue'];
  $vatValue 						=	$_POST['vatValue'];
  $totalAmountValue     = $_POST['totalAmountValue'];
  $discount 						= $_POST['discount'];
  $grandTotalValue 			= $_POST['grandTotalValue'];
  $paid 								= $_POST['paid'];
  $dueValue 						= $_POST['dueValue'];
  $paymentType 					= $_POST['paymentType'];
  $paymentStatus 				= $_POST['paymentStatus'];

				
	$sql = "INSERT INTO orders (order_date, client_name, client_contact, sub_total, vat, total_amount, discount, grand_total, paid, due, payment_type, payment_status, order_status) VALUES ('$orderDate', '$clientName', '$clientContact', '$subTotalValue', '$vatValue', '$totalAmountValue', '$discount', '$grandTotalValue', '$paid', '$dueValue', $paymentType, $paymentStatus, 1)";
	
	
	$order_id;
	$orderStatus = false;
	if($connect->query($sql) === true) {
		$order_id = $connect->insert_id;
		$valid['order_id'] = $order_id;	

		$orderStatus = true;
	}

	// --- Batched stock update + order_item insert ------------------------
	// BEFORE: this looped over every product and ran 3 queries per row
	// (SELECT stock, UPDATE stock, INSERT order_item) — 10 products meant
	// 30 sequential round trips to the database just for the line items.
	// That, not the printing, is what was making "Save Changes" feel like
	// it took 5-7 seconds.
	//
	// AFTER: current stock for every product on the order is fetched in
	// ONE query, the new stock values are computed here in PHP (same
	// "subtract, floor at 0" rule as before), then ONE UPDATE writes all
	// the new stock values and ONE INSERT adds all the order_item rows.
	// That's 3 queries total, no matter whether the order has 1 product
	// or 50.
	if (!empty($_POST['productName'])) {

		$productIds  = $_POST['productName'];
		$quantities  = $_POST['quantity'];
		$rates       = $_POST['rate'];
		$totals      = $_POST['totalValue'];
		$percentages = $_POST['percentage'];
		$count       = count($productIds);

		$idsList = implode(',', array_map('intval', $productIds));

		// 1. fetch current stock for every product on this order — ONE query
		$stockResult = $connect->query("SELECT product_id, quantity FROM product WHERE product_id IN ($idsList)");
		$stockMap = array();
		while ($r = $stockResult->fetch_assoc()) {
			$stockMap[$r['product_id']] = $r['quantity'];
		}

		// 2. compute the new stock for each product (same rule as before:
		// subtract the ordered quantity if stock > 0, otherwise floor at 0)
		$caseSql = "";
		$insertValues = array();
		for ($x = 0; $x < $count; $x++) {
			$pid = (int) $productIds[$x];
			$qty = (int) $quantities[$x];
			$currentStock = isset($stockMap[$pid]) ? $stockMap[$pid] : 0;
			$newStock = ($currentStock > 0) ? ($currentStock - $qty) : 0;

			$caseSql .= " WHEN $pid THEN $newStock";

			$rate = $connect->real_escape_string($rates[$x]);
			$total = $connect->real_escape_string($totals[$x]);
			$percentage = $connect->real_escape_string($percentages[$x]);
			$insertValues[] = "($order_id, $pid, $qty, '$rate', '$total', 1, '$percentage')";
		}

		// 3. one UPDATE for all products on this order
		$connect->query("UPDATE product SET quantity = CASE product_id $caseSql ELSE quantity END WHERE product_id IN ($idsList)");

		// 4. one INSERT for all order_item rows
		$connect->query("INSERT INTO order_item (order_id, product_id, quantity, rate, total, order_item_status, percentage) VALUES " . implode(',', $insertValues));
	}
	// -----------------------------------------------------------------------

	$valid['success'] = true;
	$valid['messages'] = "Successfully Added";		
	
	$connect->close();

	echo json_encode($valid);
 
} // /if $_POST
// echo json_encode($valid);