<?php
include_once "includes/header.php";

if (isset($_POST['save']) && !empty($_POST['check'])) {
	foreach ($_POST['check'] as $del_id) {
		$del_id = (int) $del_id;
		mysqli_query($dbc, "DELETE FROM product WHERE product_id = '" . $del_id . "'");
	}
	$message = "<span class='alert alert-success'>Data deleted successfully !</span>";
}
?>
<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-danger">
			<div class="panel panel-heading" align="center"><em>Show Product</em></div>
			<div class="panel panel-body">
				<?php if (!empty($message))
					echo $message; ?>
				<form method="post" action="" id="completeProductForm">
					<p align="center"><input type="submit" class="btn btn-success" name="save" value="DELETE"></p>
					<table class="table table-responsive" id="myTable">
						<thead>
							<tr>
								<th>Delete</th>
								<th>Product ID</th>
								<th>Product Name</th>
								<th>Product Rate</th>
								<th>Product Quantity</th>
								<th>Category</th>
								<th>Brand</th>
								<th>Status</th>
								<th>Option</th>
								<th>Delete</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('#myTable').DataTable({
			processing: true,
			serverSide: true,
			ajax: { url: 'php_action/fetchCompleteProduct.php', type: 'POST', data: function (d) { return d; } },
			order: [[1, 'asc']],
			pageLength: 20,
			lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]]
		});
	});
</script>
<?php
include_once 'includes/footer.php';
?>