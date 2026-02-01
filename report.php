<?php require_once 'includes/header.php'; ?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="glyphicon glyphicon-check"></i> Order Report
			</div>
			<!-- /panel-heading -->
			<div class="panel-body">
				<?php
				if (!isset($_POST['passwordbtn'])) {
					?>
					<form method="post" action="">
						<div class="form-group">
							<label for="startDate" class="col-sm-2 control-label">Password</label>
							<div class="col-sm-8">
								<input type="Password" class="form-control" id="passwordthis" name="passwordthis"
									placeholder="Passsword" />
							</div>
							<div class="col-sm-2">
								<input type="submit" name="passwordbtn" class="btn btn-success">
							</div>
						</div>


					</form>

					<?php
				}
				if (isset($_POST['passwordbtn'])) {
					if ($_POST['passwordthis'] == 'life68' OR $_POST['passwordthis'] == 'LIFE68') {
						$todatDate = date('Y-m-d');
						$todaysale = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT SUM(grand_total) AS totalsale , count(order_id) AS totalorders FROM orders WHERE order_date = '$todatDate'"));
						$profit = mysqli_fetch_assoc(mysqli_query($dbc, "SELECT SUM(((order_item.rate - (order_item.rate * order_item.percentage / 100) - product.purchase) * order_item.quantity)) AS totalprofit FROM orders
							INNER JOIN order_item ON order_item.order_id = orders.order_id
							INNER JOIN product ON product.product_id = order_item.product_id
								WHERE orders.order_date = '$todatDate'"));



						$today = date('Y-m-d');
						$totalProfitt = 0;

						// Query to get orders for today
						$q = mysqli_query($dbc, "SELECT * FROM orders WHERE order_date = '$today' ORDER BY order_id ASC");

						// Loop through each order
						while ($r = mysqli_fetch_assoc($q)) {
							$totalProfit = 0;

							// Query to get order items for each order
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
        order_item.order_id = '" . $r['order_id'] . "';");

							// Calculate profit for each order item and accumulate total profit for the order
							while ($od = mysqli_fetch_assoc($q1)) {
								$asr = floatval($od['product_rate']) - ((floatval($od['product_rate']) / 100) * floatval($od['order_item_percentage']));
								$totalProfit += ((floatval($asr) - floatval($od['purchase'])) * floatval($od['order_item_quantity']));
							}

							$totalProfitt += $totalProfit;
						}

						// Now $totalProfitt contains the total profit for all orders for today
				

						?>

						<div class="row">
							<div class="col-sm-4">
								<div class="panel panel-danger">
									<div class="panel-heading">Total Sale Today</div>
									<div class="panel panel-body">
										<h1><?= number_format($todaysale['totalsale'], 2) ?> Rs.</h1>
									</div>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="panel panel-danger">
									<div class="panel-heading">Total Orders Today</div>
									<div class="panel panel-body">
										<h1><?= $todaysale['totalorders'] ?></h1>
									</div>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="panel panel-danger">
									<div class="panel-heading">Total Profit Today <a href="profit.php">Details</a> </div>
									<div class="panel panel-body">
										<h1><?= number_format($totalProfitt, 2) ?></h1>
									</div>
								</div>
							</div>
						</div>
						<form class="form-horizontal" action="php_action/getOrderReport.php" method="post"
							id="getOrderReportForm">
							<div class="form-group">
								<label for="startDate" class="col-sm-2 control-label">Start Date</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" id="startDate" name="startDate"
										placeholder="Start Date" />
								</div>
							</div>
							<div class="form-group">
								<label for="endDate" class="col-sm-2 control-label">End Date</label>
								<div class="col-sm-10">
									<input type="text" class="form-control" id="endDate" name="endDate"
										placeholder="End Date" />
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-offset-2 col-sm-10">
									<button type="submit" class="btn btn-success" id="generateReportBtn"> <i
											class="glyphicon glyphicon-ok-sign"></i> Generate Report</button>
								</div>
							</div>
						</form>
						<?php
					} else {
						echo "password wrong";
					}
				}
				?>



			</div>
			<!-- /panel-body -->
		</div>
	</div>
	<!-- /col-dm-12 -->
</div>
<!-- /row -->

<script src="custom/js/report.js"></script>

<?php require_once 'includes/footer.php'; ?>