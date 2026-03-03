<?php require_once 'includes/header.php'; ?>
<?php require_once 'php_action/core.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-default">

			<div class="panel-heading">
				<i class="glyphicon glyphicon-check"></i> Order Report
			</div>

			<div class="panel-body">

				<?php if (!isset($_POST['passwordbtn'])) { ?>
					<form method="post">
						<div class="form-group row">
							<label class="col-sm-2 control-label">Password</label>
							<div class="col-sm-8">
								<input type="password" name="passwordthis" class="form-control" required>
							</div>
							<div class="col-sm-2">
								<button name="passwordbtn" class="btn btn-success">Submit</button>
							</div>
						</div>
					</form>
				<?php } ?>

				<?php
				if (
					isset($_POST['passwordbtn']) &&
					($_POST['passwordthis'] === 'life68' || $_POST['passwordthis'] === 'LIFE68')
				) {

					$today = date('Y-m-d');

					$todaysale = mysqli_fetch_assoc(mysqli_query(
						$connect,
						"SELECT SUM(grand_total) totalsale, COUNT(order_id) totalorders 
 FROM orders WHERE order_date='$today'"
					));

					$todayProfit = mysqli_fetch_assoc(mysqli_query(
						$connect,
						"SELECT SUM((oi.rate-(oi.rate*oi.percentage/100)-p.purchase)*oi.quantity) profit
 FROM orders o
 JOIN order_item oi ON oi.order_id=o.order_id
 JOIN product p ON p.product_id=oi.product_id
 WHERE o.order_date='$today'"
					));
					?>

					<!-- TODAY CARDS -->
					<div class="row">
						<div class="col-sm-4">
							<div class="panel panel-danger">
								<div class="panel-heading">Total Sale Today</div>
								<div class="panel-body">
									<h1><?= number_format((float) $todaysale['totalsale'], 2) ?> Rs</h1>
								</div>
							</div>
						</div>

						<div class="col-sm-4">
							<div class="panel panel-danger">
								<div class="panel-heading">Total Orders Today</div>
								<div class="panel-body">
									<h1><?= (int) $todaysale['totalorders'] ?></h1>
								</div>
							</div>
						</div>

						<div class="col-sm-4">
							<div class="panel panel-danger">
								<div class="panel-heading">Total Profit Today</div>
								<div class="panel-body">
									<h1><?= number_format((float) $todayProfit['profit'], 2) ?></h1>
								</div>
							</div>
						</div>
					</div>

					<hr>

					<form id="getOrderReportForm" class="form-horizontal">
						<div class="form-group">
							<label class="col-sm-2 control-label">Start Date</label>
							<div class="col-sm-10">
								<input type="text" id="startDate" name="startDate" class="form-control" required>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">End Date</label>
							<div class="col-sm-10">
								<input type="text" id="endDate" name="endDate" class="form-control" required>
							</div>
						</div>

						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<button class="btn btn-success">
									<i class="glyphicon glyphicon-ok-sign"></i> Generate Report
								</button>
							</div>
						</div>
					</form>

					<div id="orderReportResult"></div>

				<?php } ?>

			</div>
		</div>
	</div>
</div>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>

<script src="custom/js/report.js"></script>

<?php require_once 'includes/footer.php'; ?>