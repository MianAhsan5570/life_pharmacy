<?php

require_once 'core.php';

function parseReportDate($input)
{
	if (empty($input))
		return null;
	$formats = array('m/d/Y', 'm/d/y', 'Y-m-d', 'd-m-Y', 'd/m/Y', 'd/m/y');
	foreach ($formats as $f) {
		$d = DateTime::createFromFormat($f, trim($input));
		if ($d)
			return $d->format('Y-m-d');
	}
	$d = new DateTime($input);
	return $d ? $d->format('Y-m-d') : null;
}

if (!empty($_POST['startDate']) && !empty($_POST['endDate'])) {

	$start_date = parseReportDate($_POST['startDate']);
	$end_date = parseReportDate($_POST['endDate']);

	if (!$start_date || !$end_date) {
		echo '<p class="text-danger">Invalid date format. Use MM/DD/YYYY or pick from calendar.</p>';
		exit;
	}

	// Single query: orders + profit per order (no N+1).
	$sql = "SELECT o.order_id, o.order_date, o.client_name, o.client_contact, o.grand_total,
	        COALESCE(SUM((oi.rate - (oi.rate * COALESCE(oi.percentage,0) / 100) - COALESCE(p.purchase,0)) * oi.quantity), 0) AS totalprofit
	        FROM orders o
	        LEFT JOIN order_item oi ON oi.order_id = o.order_id
	        LEFT JOIN product p ON p.product_id = oi.product_id
	        WHERE o.order_date >= ? AND o.order_date <= ?
	        GROUP BY o.order_id, o.order_date, o.client_name, o.client_contact, o.grand_total
	        ORDER BY o.order_id DESC
	        LIMIT 500";
	$stmt = $connect->prepare($sql);
	$stmt->bind_param('ss', $start_date, $end_date);
	$stmt->execute();
	$query = $stmt->get_result();

	$table = '
	<table border="1" cellspacing="0" cellpadding="0" style="width:100%;">
		<tr>
			<th>Order Date</th>
			<th>Order ID</th>
			<th>Contact</th>
			<th>Grand Total</th>
			<th>Profit</th>
		</tr>';
	$totalAmount = 0;
	$totalpro = 0;
	$hasRows = false;
	while ($result = $query->fetch_assoc()) {
		$hasRows = true;
		$profit = $result['totalprofit'] !== null ? (float) $result['totalprofit'] : 0.0;
		$table .= '<tr>
			<td><center>' . htmlspecialchars($result['order_date']) . '</center></td>
			<td><center>' . htmlspecialchars($result['order_id']) . '</center></td>
			<td><center>' . htmlspecialchars($result['client_name'] . $result['client_contact']) . '</center></td>
			<td><center>' . htmlspecialchars($result['grand_total']) . '</center></td>
			<td><center>' . $profit . '</center></td>
		</tr>';
		$totalAmount += (float) $result['grand_total'];
		$totalpro += $profit;
	}
	$stmt->close();

	if (!$hasRows) {
		$table .= '<tr><td colspan="5"><center>No orders found for this date range.</center></td></tr>';
	}

	$table .= '
		<tr>
			<td colspan="3"><center><strong>Total Amount</strong></center></td>
			<td><center><strong>' . number_format($totalAmount, 2) . '</strong></center></td>
			<td><center><strong>' . number_format($totalpro, 2) . '</strong></center></td>
		</tr>
	</table>';

	echo $table;

} else {
	echo '<p class="text-warning">Please select Start Date and End Date.</p>';
}

?>