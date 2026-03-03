<?php
require_once 'core.php';

function parseDate($d)
{
  $formats = ['m/d/Y', 'Y-m-d', 'd/m/Y'];
  foreach ($formats as $f) {
    $dt = DateTime::createFromFormat($f, $d);
    if ($dt)
      return $dt->format('Y-m-d');
  }
  return false;
}

$start = parseDate($_POST['startDate'] ?? '');
$end = parseDate($_POST['endDate'] ?? '');

if (!$start || !$end) {
  echo "<p class='text-danger'>Invalid date</p>";
  exit;
}

$sql = "SELECT o.order_date,o.order_id,o.client_name,o.client_contact,o.grand_total,
COALESCE(SUM((oi.rate-(oi.rate*COALESCE(oi.percentage,0)/100)-COALESCE(p.purchase,0))*oi.quantity),0) profit
FROM orders o
LEFT JOIN order_item oi ON oi.order_id=o.order_id
LEFT JOIN product p ON p.product_id=oi.product_id
WHERE o.order_date BETWEEN ? AND ?
GROUP BY o.order_id
ORDER BY o.order_id DESC";

$stmt = $connect->prepare($sql);
$stmt->bind_param("ss", $start, $end);
$stmt->execute();
$res = $stmt->get_result();

$totalSale = 0;
$totalProfit = 0;

echo "<table id='orderReportTable' class='table table-bordered table-striped'>
<thead>
<tr>
<th>Date</th>
<th>Order ID</th>
<th>Client</th>
<th>Grand Total</th>
<th>Profit</th>
</tr>
</thead><tbody>";

while ($r = $res->fetch_assoc()) {
  $sale = (float) $r['grand_total'];
  $profit = (float) $r['profit'];

  echo "<tr>
<td>{$r['order_date']}</td>
<td>{$r['order_id']}</td>
<td>{$r['client_name']} {$r['client_contact']}</td>
<td>" . number_format($sale, 2) . "</td>
<td>" . number_format($profit, 2) . "</td>
</tr>";

  $totalSale += $sale;
  $totalProfit += $profit;
}

echo "</tbody>
<tfoot>
<tr>
<th colspan='3'>TOTAL</th>
<th>" . number_format($totalSale, 2) . "</th>
<th>" . number_format($totalProfit, 2) . "</th>
</tr>
</tfoot>
</table>";

$stmt->close();
