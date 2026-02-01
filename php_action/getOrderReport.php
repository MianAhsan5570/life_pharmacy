<?php 

require_once 'core.php';

if($_POST) {

	$startDate = $_POST['startDate'];
	$date = DateTime::createFromFormat('m/d/Y',$startDate);
	$start_date = $date->format("Y-m-d");


	$endDate = $_POST['endDate'];
	$format = DateTime::createFromFormat('m/d/Y',$endDate);
	$end_date = $format->format("Y-m-d");

	$sql = "SELECT * FROM orders WHERE order_date >= '$start_date' AND order_date <= '$end_date' ORDER BY order_id DESC limit 50";
	$query = $connect->query($sql);

	$table = '
	<table border="1" cellspacing="0" cellpadding="0" style="width:100%;">
		<tr>
			<th>Order Date</th>
			<th>Order ID</th>
			<th>Contact</th>
			<th>Grand Total</th>
			<th>Profit </th>
		</tr>

		<tr>';
		$totalAmount = "";
		$totalpro = "";
		while ($result = $query->fetch_assoc()) {

			$profit = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT SUM(((order_item.rate - (order_item.rate * order_item.percentage / 100) - product.purchase) * order_item.quantity)) AS totalprofit FROM orders
							INNER JOIN order_item ON order_item.order_id = orders.order_id
							INNER JOIN product ON product.product_id = order_item.product_id
								WHERE orders.order_id = '$result[order_id]'"));


			$table .= '<tr>
				<td><center>'.$result['order_date'].'</center></td>
				<td><center>'.$result['order_id'].'</center></td>
				<td><center>'.$result['client_name'].$result['client_contact'].'</center></td>
				<td><center>'.$result['grand_total'].'</center></td>
				<td><center>'.$profit['totalprofit'].'</center></td>
			</tr>';	
			$totalAmount += $result['grand_total'];
			$totalpro += $profit['totalprofit'];
		}
		$table .= '
		</tr>

		<tr>
			<td colspan="3"><center>Total Amount</center></td>
			<td><center>'.$totalAmount.'</center></td>
			<td><center>'.$totalpro.'</center></td>
		</tr>
	</table>
	';	

	echo $table;

}

?>