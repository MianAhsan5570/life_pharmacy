<!-- ALTER TABLE orders 
ADD INDEX idx_client_contact (client_contact); -->



<?php
include_once "includes/header.php";

?>

<div class="row">
	<div class="col-sm-12">
		<form action="" method="post" accept-charset="utf-8">



			<div class="panel panel-info">
				<div class="panel-heading">Search Order </div>
				<div class="panel-body">
					<form action="" method="post" class="form-inline print_hide">
						<div class="form-group col-sm-4 print_hide">
							<label for="">Order Number </label>
							<input type="text" class="form-control" id="clientName" name="ordernumber" autofocus="true">

						</div><!-- group -->

						<div class="form-group col-sm-4 print_hide">
							<label for="">Conatct Number</label>
							<input type="text" class="form-control" autocomplete="off" name="client_number" id="from"
								placeholder="Conatct Number">
						</div><!-- group -->

						<div class="form-group col-sm-4 print_hide" align="center">
							<label for="">Click To Search</label><br />
							<button class="btn btn-success" name="findorder" type="submit"><span
									class="glyphicon glyphicon-search">_</span>Search</button>
						</div><!-- group -->

					</form>
				</div>
		</form>
	</div>
</div>

<?php
if (isset($_POST['findorder'])) {

	$order_id = $_POST['ordernumber'];
	$client_number = $_POST['client_number']
		# code...
		?>
	<div class="row">
		<div class="col-sm-12">
			<div class="panel panel-danger">
				<div class="panel-heading">Show Order </div>
				<div class="panel-body">
					<table class="table myTable" id="" class="table-responsive">

						<thead>
							<tr>
								<th>order No#</th>
								<th>Date</th>
								<th>Contact</th>
								<!-- <th>Order Item</th> -->
								<th>Total</th>
								<th>option</th>
							</tr>

						</thead>
						<?php
						$order_id = trim($_POST['ordernumber'] ?? '');
						$client_number = trim($_POST['client_number'] ?? '');

						// Remove leading underscore if exists
						$client_number_clean = ltrim($client_number, '_');

						$sql = "SELECT order_id, order_date, client_contact, sub_total FROM orders ";
						$where = [];
						$params = [];
						$types = "";

						// Add order_id condition
						if ($order_id !== '') {
							$where[] = "order_id = ?";
							$types .= "i";
							$params[] = $order_id;
						}

						// Add client_contact condition
						if ($client_number_clean !== '') {
							// Handle both with and without leading underscore
							$where[] = "(client_contact = ? OR client_contact = CONCAT('_', ?))";
							$types .= "ss";
							$params[] = $client_number_clean;
							$params[] = $client_number_clean;
						}

						if (!empty($where)) {
							$sql .= " WHERE " . implode(" AND ", $where);
						}

						$sql .= " ORDER BY order_id DESC";

						$stmt = mysqli_prepare($dbc, $sql);

						if (!empty($params)) {
							mysqli_stmt_bind_param($stmt, $types, ...$params);
						}

						mysqli_stmt_execute($stmt);
						$result = mysqli_stmt_get_result($stmt);
						?>

						<tbody>
							<?php while ($r = mysqli_fetch_assoc($result)): ?>
								<tr>
									<td><?= $r['order_id'] ?></td>
									<td><?= $r['order_date'] ?></td>
									<td><?= $r['client_contact'] ?></td>
									<td><?= $r['sub_total'] ?></td>
									<td>
										<div class="btn-group">
											<button type="button" class="btn btn-default dropdown-toggle"
												data-toggle="dropdown">
												Action <span class="caret"></span>
											</button>
											<ul class="dropdown-menu">
												<li><a onclick="printOrder3(<?= $r['order_id'] ?>)"><i
															class="glyphicon glyphicon-print"></i> Print</a></li>
												<li><a onclick="printOrder4(<?= $r['order_id'] ?>)"><i
															class="glyphicon glyphicon-print"></i> Print As New</a></li>
												<li><a href="orders.php?o=editOrd&i=<?= $r['order_id'] ?>"><i
															class="glyphicon glyphicon-edit"></i> Edit</a></li>
												<li><a href="orders.php?o=editOrd&i=<?= $r['order_id'] ?>&type=new"><i
															class="glyphicon glyphicon-edit"></i> Edit As New</a></li>
												<li><a href="php_action/removeOrder.php?var=<?= $r['order_id'] ?>"><i
															class="glyphicon glyphicon-trash"></i> Delete</a></li>
											</ul>
										</div>
									</td>
								</tr>
							<?php endwhile; ?>
						</tbody>

					</table>

				</div>
			</div>
		</div>
	</div>
	<?php
}
?>

<script type="text/javascript">
	// print order function
	function printOrder3(orderId = null) {
		if (orderId) {

			$.ajax({
				url: 'php_action/printOrder.php',
				type: 'post',
				data: { orderId: orderId },
				dataType: 'text',
				success: function (response) {

					var mywindow = window.open('', 'Stock Management System', 'height=400,width=600');
					mywindow.document.write('<html><head><title>Order Invoice</title>');
					mywindow.document.write('</head><body>');
					mywindow.document.write(response);
					mywindow.document.write('</body></html>');

					mywindow.document.close(); // necessary for IE >= 10
					mywindow.focus(); // necessary for IE >= 10

					mywindow.print();
					mywindow.close();

				}// /success function
			}); // /ajax function to fetch the printable order
		} // /if orderId
	} // /print order function


	function printOrder4(orderId = null) {
		if (orderId) {

			$.ajax({
				url: 'php_action/printOrder2.php',
				type: 'post',
				data: { orderId: orderId },
				dataType: 'text',
				success: function (response) {

					var mywindow = window.open('', 'Stock Management System', 'height=400,width=600');
					mywindow.document.write('<html><head><title>Order Invoice</title>');
					mywindow.document.write('</head><body>');
					mywindow.document.write(response);
					mywindow.document.write('</body></html>');

					mywindow.document.close(); // necessary for IE >= 10
					mywindow.focus(); // necessary for IE >= 10

					mywindow.print();
					mywindow.close();

				}// /success function
			}); // /ajax function to fetch the printable order
		} // /if orderId
	} // /print order function

</script>