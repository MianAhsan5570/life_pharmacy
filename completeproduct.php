<?php
include_once "includes/header.php";
?>

<script type="text/javascript">
	$(document).ready( function () {
    $('#myTable').DataTable();
} );
</script>

<div class="row">
	<div class="col-sm-12">
		<div class="panel panel-danger">
			<div class="panel panel-heading" align="center"><em>Show Product</em></div>
			<div class="panel panel-body">
				<?php
if(isset($_POST['save'])){

	  $checkbox = $_POST['check'];
	for($i=0;$i<count($checkbox);$i++){
	$del_id = $checkbox[$i]; 
	mysqli_query($dbc,"DELETE FROM product WHERE product_id='".$del_id."'");
	$message = "<span class='alert alert-success'>Data deleted successfully !</span>";
}
}
				?>
				<form method="post" action="">
	<p align="center"><input type="submit" class="btn btn-success" name="save" value="DELETE"></p>
					<table class="table" id="myTable" class="table-responsive">

	<thead>
		<tr class="">
			<th>Delete</th>
			<th>Product ID</th>
			<th>Product Name</th>
			<th>Product Rate</th>
			<th>Product Quantity </th>
			<th>Category </th>
			<th>Brand</th>
			<th>Status</th>
			<th>Option</th>
			<th>Delete</th>

		</tr>
	</thead>
	<tbody>
		<?php $q=mysqli_query($dbc,"SELECT * FROM product ");
		while($r=mysqli_fetch_assoc($q)):
			$product_id = $r['product_id'];
		 ?>
		<tr>
		 <td><input type="checkbox" id="checkItem" name="check[]" value="<?php echo $r['product_id']; ?>"></td>
			<td><?=$r['product_id']?></td>
			<td class="text-capitalize"><?=$r['product_name']?></td>
			<td class="text-lowercase"><?=$r['rate']?></td>
			<td><?=$r['quantity']?></td>
			<td><?=$r['categories_id']?></td>
			<td><?=$r['brand_id']?></td>
			<td><?=$r['status']?></td>
			<td><a href="addproduct.php?i=<?=$product_id?>" target="_blank"> Edit</a></td>
			<td><a href="deleteproduct.php?i=<?=$r['product_id'];?>" target="_blank"> <button class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span>Delete</button></a></td>
			
		</tr>
	<?php endwhile; ?>
	</tbody>
</table>
</form>
			</div>
		</div>
	</div>
</div>


<?php
include_once 'includes/footer.php';
?>