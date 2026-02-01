<?php
include_once "includes/header.php";
?>

<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-info">
			<div class="panel panel-heading">Profit Report</div>
				<div class="panel panel-body">

					<table class="table table-reponsive table-border">
						<thead>
							<tr>
								<th>Order No</th>
								<th>Order Date</th>
								<th>Order Details</th>
								<th>Amount</th>
								<th>Profit</th>
							</tr>
						</thead>

						<tbody>
							<?php
							$today = date('Y-m-d');
							$totalorderProfit =    0;
								$totalProfitt = 0;
							$q = mysqli_query($dbc,"SELECT * FROM orders WHERE order_date = '$today' ORDER BY order_id ASC  ");
							while($r = mysqli_fetch_assoc($q)):
								$totalProfit = $TotalBill = 0;

							
							?>
							<tr>
								<td><?=$r['order_id']?></td>
								<td><?=$r['order_date']?></td>
								<td>
									<table border="1px">
										<tr>
											<th>Product(Category)</th>
											<th>Quantity</th>
											<th>P Rate</th>
											<th> Rate</th>
											<th>s Rate</th>
											<th>Profit</th>
										</tr>
										<?php
$q1 = mysqli_query($dbc, "SELECT 
        order_item.*, 
        product.*, 
        categories.*, 
        order_item.rate AS order_item_rate, 
        order_item.quantity AS order_item_quantity,
        order_item.percentage AS order_item_percentage,
        product.rate AS product_rate 
    FROM 
        order_item 
        INNER JOIN product ON product.product_id = order_item.product_id
        INNER JOIN categories ON categories.categories_id = product.categories_id
    WHERE 
        order_item.order_id = '".$r['order_id']."';");

$totalProfit = 0; // Initialize total profit outside the loop
while($od = mysqli_fetch_assoc($q1)):
   // $asr =  $od['product_rate'] - (($od['product_rate']/100)*$od['order_item_percentage']);

	$asr = floatval($od['product_rate']) - ((floatval($od['product_rate']) / 100) * floatval($od['order_item_percentage']));

?>
<tr>
    <td><?= $od['product_name']?>(<?= $od['categories_name']?>)</td>
    <td><?= $od['order_item_quantity']?></td>
    <?php
    error_log('Purchase value: ' . print_r($od['purchase'], true));
?>
<td><?= is_numeric($od['purchase']) ? number_format((float)$od['purchase']) : 'Invalid number' ?></td>

   <td>
    <?= number_format(floatval($od['product_rate']) - (floatval($od['product_rate']) * (floatval($od['order_item_percentage']) / 100)), 2) ?>

</td>

    <td><?= $asr?></td>
    <td><?= number_format(floatval($asr) - floatval($od['purchase']), 2) ?></td>
</tr>
<?php
$TotalBill += 	$asr*$od['order_item_quantity']; 
$totalProfit += ((floatval($asr) - floatval($od['purchase'])) * floatval($od['order_item_quantity']));
endwhile;
?>
<tr>
    <td colspan="5">Total Profit</td>
    <td><?= number_format($totalProfit, 2) ?></td>
</tr>

									</table>

								</td>
								<?php 
								$grand_total_float = (float)$TotalBill;
								?>
								<td><?=number_format($grand_total_float,2)?></td>
								<td><?=number_format($totalProfit,2)?></td>

							</tr>
							<?php
								$totalProfitt += $totalProfit;
						endwhile;
							?>

							<tr>
								<td colspan="4">Total P</td>
								<td><h3><?=@number_format($totalProfitt,2)?></h3></td>
							</tr>
						</tbody>
						
					</table>

				</div>
		</div>
	</div>
</div>



<?php
include_once "includes/header.php";
?>


