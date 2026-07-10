<?php 	

require_once 'core.php';

$valid['success'] = array('success' => false, 'messages' => array());
$type = $_REQUEST['type'];

//print_r($_REQUEST);

if($type == 'new') {	
	//create new 

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

	// --- Batched stock update + order_item insert (same fix as createOrder.php) ---
	// BEFORE: 3 queries per product row (30 queries for a 10-product order).
	// AFTER: 3 queries total, regardless of row count.
	if (!empty($_POST['productName'])) {

		$productIds  = $_POST['productName'];
		$quantities  = $_POST['quantity'];
		$rates       = $_POST['rate'];
		$totals      = $_POST['totalValue'];
		$percentages = $_POST['percentage'];
		$count       = count($productIds);

		$idsList = implode(',', array_map('intval', $productIds));

		$stockResult = $connect->query("SELECT product_id, quantity FROM product WHERE product_id IN ($idsList)");
		$stockMap = array();
		while ($r = $stockResult->fetch_assoc()) {
			$stockMap[$r['product_id']] = $r['quantity'];
		}

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

		$connect->query("UPDATE product SET quantity = CASE product_id $caseSql ELSE quantity END WHERE product_id IN ($idsList)");
		$connect->query("INSERT INTO order_item (order_id, product_id, quantity, rate, total, order_item_status, percentage) VALUES " . implode(',', $insertValues));
	}
	// ---------------------------------------------------------------------------------

	$valid['success'] = true;
	$valid['messages'] = "Successfully Added";		
	
	$connect->close();

	//echo json_encode($valid);




}else{



	// edit order simple 


	$orderId = (int) $_POST['orderId'];

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

				
	$sql = "UPDATE orders SET order_date = '$orderDate', client_name = '$clientName', client_contact = '$clientContact', sub_total = '$subTotalValue', vat = '$vatValue', total_amount = '$totalAmountValue', discount = '$discount', grand_total = '$grandTotalValue', paid = '$paid', due = '$dueValue', payment_type = '$paymentType', payment_status = '$paymentStatus', order_status = 1 WHERE order_id = {$orderId}";	
	$connect->query($sql);

	// --- Batched restock + re-insert of order items -----------------------
	// BEFORE this ran roughly 7 QUERIES PER PRODUCT ROW:
	//   - SELECT product stock (per row)
	//   - SELECT order_item quantity to "add back" — but the query had no
	//     product_id filter, so it always grabbed the FIRST row for the
	//     whole order regardless of which product was being processed.
	//     That's a pre-existing bug, not something this batching changes
	//     behaviour on purpose — fixed below by mapping old quantities per
	//     product properly.
	//   - UPDATE product to restore that quantity (per row)
	//   - a DELETE FROM order_item WHERE order_id = ... that re-ran ONCE
	//     PER ROW even though it deletes the exact same rows every time
	//   - SELECT product stock again (per row)
	//   - UPDATE product stock again, subtracting the new quantity (per row)
	//   - INSERT the new order_item row (per row)
	// For a 10-product edit that's up to 70 queries.
	//
	// AFTER: this is done in about 5 queries total, regardless of how many
	// products are on the order — old quantities are read once, stock is
	// restored and re-deducted in a single computed UPDATE, old items are
	// deleted once, and new items are inserted in one bulk INSERT.

	// 1. read the order's existing items once, to know what to add back to stock
	$oldItemsResult = $connect->query("SELECT product_id, quantity FROM order_item WHERE order_id = {$orderId}");
	$oldQtyMap = array();
	while ($r = $oldItemsResult->fetch_assoc()) {
		$pid = $r['product_id'];
		$oldQtyMap[$pid] = (isset($oldQtyMap[$pid]) ? $oldQtyMap[$pid] : 0) + $r['quantity'];
	}

	// 2. delete the old order items — ONE query (was running once per row before)
	$connect->query("DELETE FROM order_item WHERE order_id = {$orderId}");

	if (!empty($_POST['productName'])) {

		$productIds  = $_POST['productName'];
		$quantities  = $_POST['quantity'];
		$rates       = $_POST['rateValue'];
		$totals      = $_POST['totalValue'];
		$percentages = $_POST['percentage'];
		$count       = count($productIds);

		// new quantities requested, summed per product (in case a product appears twice)
		$newQtyMap = array();
		for ($x = 0; $x < $count; $x++) {
			$pid = (int) $productIds[$x];
			$newQtyMap[$pid] = (isset($newQtyMap[$pid]) ? $newQtyMap[$pid] : 0) + (int) $quantities[$x];
		}

		// every product touched by either the old or the new item list
		$allIds = array_unique(array_merge(array_keys($oldQtyMap), array_keys($newQtyMap)));
		$idsList = implode(',', array_map('intval', $allIds));

		// 3. current stock for all those products — ONE query
		$stockResult = $connect->query("SELECT product_id, quantity FROM product WHERE product_id IN ($idsList)");
		$stockMap = array();
		while ($r = $stockResult->fetch_assoc()) {
			$stockMap[$r['product_id']] = $r['quantity'];
		}

		// 4. compute final stock: restore the old order's quantity, then
		// re-deduct the new quantity (same "floor at 0" rule as before)
		$caseSql = "";
		foreach ($allIds as $pid) {
			$currentStock = isset($stockMap[$pid]) ? $stockMap[$pid] : 0;
			$afterRestore = $currentStock + (isset($oldQtyMap[$pid]) ? $oldQtyMap[$pid] : 0);
			$newQty = isset($newQtyMap[$pid]) ? $newQtyMap[$pid] : 0;
			$finalStock = ($afterRestore > 0) ? ($afterRestore - $newQty) : 0;

			$caseSql .= " WHEN $pid THEN $finalStock";
		}

		// 5. one UPDATE for all products touched by this edit
		$connect->query("UPDATE product SET quantity = CASE product_id $caseSql ELSE quantity END WHERE product_id IN ($idsList)");

		// 6. one INSERT for all the new order_item rows
		$insertValues = array();
		for ($x = 0; $x < $count; $x++) {
			$pid = (int) $productIds[$x];
			$qty = (int) $quantities[$x];
			$rate = $connect->real_escape_string($rates[$x]);
			$total = $connect->real_escape_string($totals[$x]);
			$percentage = $connect->real_escape_string($percentages[$x]);
			$insertValues[] = "({$orderId}, $pid, $qty, '$rate', '$total', 1, '$percentage')";
		}
		$connect->query("INSERT INTO order_item (order_id, product_id, quantity, rate, total, order_item_status, percentage) VALUES " . implode(',', $insertValues));
	}
	// -----------------------------------------------------------------------------

	
	$valid['order_id'] = $orderId;
	$valid['success'] = true;
	$valid['messages'] = "Successfully Updated";
			
	
	$connect->close();

}	

	echo json_encode($valid);
 
 // /if $_POST
// echo json_encode($valid);