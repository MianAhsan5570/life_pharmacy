<?php
include_once "includes/header.php";

// Check connection
if (!$dbc) {
	die("Database connection failed: " . mysqli_connect_error());
}
?>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
	.dataTables_wrapper {
		padding: 10px;
	}

	.dt-buttons .btn {
		margin-right: 5px;
	}

	.table-responsive {
		overflow-x: auto;
	}
</style>

<div class="row">
	<?php if (!empty($msg)): ?>
		<div class="col-12">
			<div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
		</div>
	<?php endif; ?>

	<div class="col-sm-12">
		<div class="panel panel-danger">
			<div class="panel-heading" align="center">
				<h4><em>Product List</em></h4>
			</div>
			<div class="panel-body">
				<div class="responseAlert"></div>
				<div class="table-responsive">
					<table class="table table-striped table-bordered" id="productsTable">
						<thead class="table-dark">
							<tr>
								<th>ID</th>
								<th>Product Name</th>
								<th>Category</th>
								<th>Brand</th>
								<th>Sale Rate</th>
								<th>Alert At</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
							<?php
							// Select only the columns needed
							$sql = "
                                SELECT 
                                    p.product_id, p.product_name, p.rate, p.alert_at,
                                    c.categories_name,
                                    b.brand_name
                                FROM product p
                                INNER JOIN categories c ON p.categories_id = c.categories_id
                                INNER JOIN brands b ON p.brand_id = b.brand_id
                                WHERE p.status = 1
                                ORDER BY p.product_id DESC
                            ";

							$result = mysqli_query($dbc, $sql);

							if (!$result) {
								die("Query failed: " . mysqli_error($dbc));
							}

							if (mysqli_num_rows($result) > 0):
								while ($row = mysqli_fetch_assoc($result)):
									?>
									<tr>
										<td><?= htmlspecialchars($row['product_id']) ?></td>
										<td class="text-capitalize"><?= htmlspecialchars($row['product_name']) ?></td>
										<td><?= htmlspecialchars($row['categories_name']) ?></td>
										<td><?= htmlspecialchars($row['brand_name']) ?></td>
										<td><?= number_format($row['rate'], 2) ?></td>
										<td><?= htmlspecialchars($row['alert_at']) ?></td>
										<td>
											<div class="btn-group" role="group">
												<a href="addproduct.php?i=<?= $row['product_id'] ?>"
													class="btn btn-sm btn-primary">
													<span class="glyphicon glyphicon-edit"></span> Edit
												</a>
												<?php if (isset($row['deal']) && $row['deal'] == 1): ?>
													<a href="deals.php?pro_id=<?= $row['product_id'] ?>" target="_blank"
														class="btn btn-sm btn-warning">
														<i class="fas fa-plus"></i> Add Incre
													</a>
												<?php endif; ?>
											</div>
										</td>
									</tr>
								<?php
								endwhile;
							else:
								?>
								<tr>
									<td colspan="7" class="text-center">No products found.</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- DataTables + Extensions -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

<!-- Export dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
	$(document).ready(function () {
		var table = $('#productsTable').DataTable({
			"responsive": true,
			"pageLength": 25,
			"lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
			"order": [[0, "desc"]],

			// Buttons configuration
			dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'B>>" +
				"<'row'<'col-sm-12'tr>>" +
				"<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",

			buttons: [
				{
					extend: 'excelHtml5',
					text: '<i class="fas fa-file-excel"></i> Excel',
					title: 'Products_List_<?= date('Y-m-d') ?>',
					className: 'btn btn-success btn-sm',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5]
					}
				},
				{
					extend: 'pdfHtml5',
					text: '<i class="fas fa-file-pdf"></i> PDF',
					title: 'Products_List_<?= date('Y-m-d') ?>',
					orientation: 'landscape',
					pageSize: 'A4',
					className: 'btn btn-danger btn-sm',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5]
					}
				},
				{
					extend: 'print',
					text: '<i class="fas fa-print"></i> Print',
					className: 'btn btn-info btn-sm',
					exportOptions: {
						columns: [0, 1, 2, 3, 4, 5]
					}
				}
			],

			"language": {
				"processing": "<i class='fa fa-spinner fa-spin fa-2x'></i> Loading...",
				"lengthMenu": "Show _MENU_ entries",
				"zeroRecords": "No products found",
				"info": "Showing _START_ to _END_ of _TOTAL_ products",
				"infoEmpty": "Showing 0 to 0 of 0 products",
				"infoFiltered": "(filtered from _MAX_ total products)",
				"search": "Search:",
				"paginate": {
					"first": "First",
					"last": "Last",
					"next": "Next",
					"previous": "Previous"
				}
			}
		});

		// Remove server-side processing since we're loading all data at once
		// You were mixing client-side and server-side processing

		// Add Bootstrap styling to DataTables elements
		$('.dt-buttons').addClass('btn-group');
		$('.dt-button').addClass('btn');
	});
</script>

<?php
include_once "includes/footer.php";
?>