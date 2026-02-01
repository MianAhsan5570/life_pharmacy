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
		if (@$_GET['i']) {
			$productId = $_GET['i'];
			$selectProduct = "SELECT * FROM product WHERE product_id = '$productId' ";
			$run = mysqli_query($dbc,$selectProduct);
				while($row = mysqli_fetch_assoc($run)){

					$pro_name = $row['product_name'];
					$pro_rate = $row['rate'];
					$pro_q = $row['quantity'];
					 $category = $row['categories_id'];
					 $brand = $row['brand_id'];
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
				    <input type="text" class="form-control" id="email" name="productName" value="<?= @$pro_name ?>">
				  </div>
				  <div class="form-group">
				    <label for="pwd">Rate:</label>
				    <input type="text" class="form-control" id="pwd" name="rate" value="<?= @$pro_rate?>">
				  </div>

					 <div class="form-group">
				    <label for="pwd">Quantity:</label>
				    <input type="text" class="form-control" id="pwd" name="quantity" value="<?=@$pro_q ?>">
				  </div>
				   <div class="form-group">
				    <label for="pwd">Categoty</label>
				   <input list="lst" class="form-control livesearch" id="editCategoryName" name="categoryName" value="<?= @$category ;?>">
				   	<datalist id="lst">
				   <!--  <select type="text" class="form-control"   > -->
						      
						      
						      	<?php 
						      
						      	$sql = "SELECT categories_id, categories_name, categories_active, categories_status FROM categories WHERE categories_status = 1 AND categories_active = 1";
										$result = $connect->query($sql);

										while($row = $result->fetch_array()) {
											echo "<option value='".$row[0]."'>".$row[1]."</option>";
										} // while
										
						      	?>
						     <!--  </select> -->
						      </datalist>
				  </div>
				   <div class="form-group">
				    <label for="pwd">Brand</label>
				     <input list="2st" class="form-control livesearch" id="editBrandName" name="brandName" value="<?= @$brand ;?>" />
				   	<datalist id="2st">
				   
						     
						      	<?php 
						      	
						      	$sql = "SELECT brand_id, brand_name, brand_active, brand_status FROM brands WHERE brand_status = 1 AND brand_active = 1";
										$result = $connect->query($sql);

										while($row = $result->fetch_array()) {
											echo "<option value='".$row[0]."'>".$row[1]."</option>";
										} // while
										
						      	?>
						      </datalist>
				  </div>
				    <div class="form-group">
				    <label for="pwd">Status</label>
				   		<select class="form-control" name="productStatus">
				   			
				   			<option value="1">Available</option>
				   			<option value="2">Not Available</option>
				   		</select>
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
			<th>Product Rate</th>
			<th>Product Quantity </th>
			<th>Category </th>
			<th>Brand</th>
			<th>Status</th>
			<th>Option</th>
		</tr>
	</thead>
	<tbody>
		<?php $q=mysqli_query($dbc,"SELECT * FROM product WHERE status = 1 ORDER BY product_id DESC LIMIT 50 ");
		while($r=mysqli_fetch_assoc($q)):
			//$customer_id = $r['customer_id'];
		 ?>
		<tr>
			<td><?=$r['product_id']?></td>
			<td class="text-capitalize"><?=$r['product_name']?></td>
			<td class="text-lowercase"><?=$r['rate']?></td>
			<td><?=$r['quantity']?></td>
			<td><?=$r['categories_id']?></td>
			<td><?=$r['brand_id']?></td>
			<td><?=$r['status']?></td>
			<td><a href="addproduct.php?i=<?=$r['product_id'];?>"> <button class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span>Edit</button></a></td>
			
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


	$productName 		= $_POST['productName'];
$productImage 	= 'abcd';
  $quantity 			= $_POST['quantity'];
  $rate 					= $_POST['rate'];
  $brandName 			= $_POST['brandName'];
  $categoryName 	= $_POST['categoryName'];
  $productStatus 	= $_POST['productStatus'];
$sql = "INSERT INTO product (product_name, product_image, brand_id, categories_id, quantity, rate, purchase ,active, status) 
				VALUES ('$productName', '$productImage', '$brandName', '$categoryName', '$quantity', '$rate', '0', '1', 1)";

				if(mysqli_query($dbc,$sql)){
					$msg = "<label class='label label-success'>Product Added</label>";
					echo "<script>alert('Product Added')</script>";
					echo '<script>window.location.assign("addproduct.php")</script>';
				}else{
					$msg = "<label class='label label-warning'>Error.....!</label>";
				}


}

if (isset($_POST['editproduct'])) {
	$productId = $_GET['i'];
	$productName 		= $_POST['productName'];
$productImage 	= 'abcd';
  $quantity 			= $_POST['quantity'];
  $rate 					= $_POST['rate'];
  $brandName 			= $_POST['brandName'];
  $categoryName 	= $_POST['categoryName'];
  $productStatus 	= $_POST['productStatus'];

  $sql = "UPDATE product SET product_name = '$productName', brand_id = '$brandName', categories_id = '$categoryName', quantity = '$quantity', rate = '$rate', active = '$productStatus', status = 1 WHERE product_id = $productId ";

  if(mysqli_query($dbc,$sql)){
					$msg = "<label class='label label-success'>Product Added</label>";
					echo "<script>alert('Product Updated')</script>";
					echo '<script>window.location.assign("addproduct.php")</script>';
				}else{
					$msg = "<label class='label label-warning'>Error.....!</label>";
				}
}


?>

<?php

include_once "includes/footer.php";
?>