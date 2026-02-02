<?php include_once "includes/header.php"; ?>
<script type="text/javascript" src="custom/js/purchase.js"></script>
<ol class="breadcrumb">
	<li><a href="dashboard.php">Home</a></li>
	<li>Purchases</li>
	<li class="active">Show Purchases</li>
</ol>
<div class="success-messages"></div>
<div class="panel panel-danger">
	<div class="panel-heading">Show Purchases</div>
	<div class="panel-body">
		<table class="table table-responsive" id="managePurchaseTable">
			<thead>
				<tr>
					<th>Purchase Id</th>
					<th>Date</th>
					<th>Account</th>
					<th>Customer</th>
					<th>Total Amount</th>
					<th>Option</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="removeOrderModal">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
						aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><i class="glyphicon glyphicon-trash"></i> Remove Purchase</h4>
			</div>
			<div class="modal-body">
				<div class="removeOrderMessages"></div>
				<p>Do you really want to remove ?</p>
			</div>
			<div class="modal-footer removeProductFooter">
				<button type="button" class="btn btn-default" data-dismiss="modal"> <i
						class="glyphicon glyphicon-remove-sign"></i> Close</button>
				<button type="button" class="btn btn-primary" id="removeOrderBtn" data-loading-text="Loading..."> <i
						class="glyphicon glyphicon-ok-sign"></i> Save changes</button>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function () {
		purchase_table = $('#managePurchaseTable').DataTable({
			processing: true,
			serverSide: true,
			ajax: { url: 'php_action/fetchPurchase.php', type: 'POST', data: function (d) { return d; } },
			order: [[0, 'desc']],
			pageLength: 20,
			lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]]
		});
	});
</script>
<?php include_once "includes/footer.php"; ?>