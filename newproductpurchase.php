<?php
include_once "includes/header.php";
?>

<script type="text/javascript">
	$(document).ready( function () {
    $('#myTable').DataTable();
} );
</script>

<div class="row">
	<div class=""><?= @$msg;?></div>
	<?php
		if(@$_GET['del_id']){
		$querythis = "DELETE FROM productnew WHERE productnew_id = '$_GET[del_id]' ";
			if(mysqli_query($dbc,$querythis)){
echo "<script>alert('Product delete')</script>";
					echo '<script>window.location.assign("newproductpurchase.php")</script>';

}

} 
		if (@$_GET['i']) {
			$productId = $_GET['i'];
			$selectProduct = "SELECT * FROM productnew WHERE productnew_id = '$productId' ";
			$run = mysqli_query($dbc,$selectProduct);
				while($row = mysqli_fetch_assoc($run)){

					$productnew_name = $row['productnew_name'];
					$productnew_rate = $row['productnew_rate'];
					
				}

			
		}
	?>
	<div class="col-sm-4">
		<div class="panel panel-info">
			<div class="panel panel-heading" align="center"><em>Add Product</em></div>
			<div class="panel panel-body">
				<form action="" method="post">
				


				  <div class="form-group">
				    <label for="email">Product name:</label>
				    <input type="text" class="form-control" id="email" name="productnew_name" value="<?= @$productnew_name ?>">
				  </div>
				  <div class="form-group">
				    <label for="pwd">Product Purchase Rate:</label>
				    <input type="number" class="form-control" id="pwd" name="productnew_rate" value="<?= @$productnew_rate?>">
				  </div>

					
				<?php
					if (isset($_GET['i'])) {
						?>
						<input type="submit" name="editproduct" class="btn btn-info" value="Edit product">
						<?php
					}else{
				?>
				 <input type="submit" name="addproduct" class="btn btn-success" value="Add product">
				 <?php
				}
				 ?>
				</form>
			</div>
		</div>

	</div>  <!-- col-sm-4 end -->


	<div class="col-sm-8">
		<div class="panel panel-danger">
			<div class="panel panel-heading" align="center"><em>Show Product</em></div>
			<div class="panel panel-body">
					<table class="table" id="myTable" class="table-responsive">

	<thead>
		<tr class="">
			<th>Product ID</th>
			<th>Product Name</th>
			<th>Product Purchase Rate</th>
			<th>Edit</th>
<th>Delete</th>

							
		</tr>
	</thead>
	<tbody>
		<?php $q=mysqli_query($dbc,"SELECT * FROM productnew  ORDER BY productnew_id DESC ");
		while($r=mysqli_fetch_assoc($q)):
			//$customer_id = $r['customer_id'];
		 ?>
		<tr>
			<td><?=$r['productnew_id']?></td>
			<td class="text-capitalize"><?=$r['productnew_name']?></td>
			<td class="text-lowercase"><?=$r['productnew_rate']?></td>
			
			<td><a href="newproductpurchase.php?i=<?=$r['productnew_id'];?>"> <button class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span>Edit</button></a></td>
		<td><a href="newproductpurchase.php?del_id=<?=$r['productnew_id'];?>"> <button class="btn btn-danger"><span class="glyphicon glyphicon-trash"></span>Delete</button></a></td>			
		</tr>
	<?php endwhile; ?>
	</tbody>
</table>
			</div>
		</div>

	</div>  <!-- col-sm-8 end -->
 

</div><!-- row end -->

<?php
if (isset($_POST['addproduct'])) {


	$productName 		= $_POST['productnew_name'];

  $rate			= $_POST['productnew_rate'];
  
$sql = "INSERT INTO productnew (productnew_name, productnew_rate) 
				VALUES ('$productName', '$rate')";

				if(mysqli_query($dbc,$sql)){
					$msg = "<label class='label label-success'>Product Added</label>";
					echo "<script>alert('Product Added')</script>";
					echo '<script>window.location.assign("newproductpurchase.php")</script>';
				}else{
					$msg = "<label class='label label-warning'>Error.....!</label>";
				}


}

if (isset($_POST['editproduct'])) {
	$productId = $_GET['i'];
	$productName 		= $_POST['productnew_name'];

  $rate 					= $_POST['productnew_rate'];
  

  $sql2 = "UPDATE productnew SET productnew_name = '$productName', productnew_rate = '$rate' WHERE productnew_id = $productId ";

  if(mysqli_query($dbc,$sql2)){
					$msg = "<label class='label label-success'>Product Added</label>";
					echo "<script>alert('Product Updated')</script>";
					echo '<script>window.location.assign("newproductpurchase.php")</script>';
				}else{
					$msg = "<label class='label label-warning'>Error.....!</label>";
				}
}


?>

<?php

include_once "includes/footer.php";
?>