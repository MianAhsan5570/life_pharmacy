<?php
include_once "includes/header.php";
?>

<script type="text/javascript">
	$(document).ready(function () {
		$('#myTable').DataTable();
	});
</script>

<div class="row">
	<div class=""><?= @$msg; ?></div>
	<?php
	if (@$_GET['i']) {
		$productId = $_GET['i'];
		$selectProduct = "SELECT * FROM product WHERE product_id = '$productId' ";
		$row = mysqli_fetch_assoc(mysqli_query($dbc, $selectProduct));

		$pro_name = $row['product_name'];
		$pro_rate = $row['rate'];
		$pro_q = $row['quantity'];
		$category = $row['categories_id'];
		$brand = $row['brand_id'];
		$purchase = $row['purchase'];
		$product_type = $row['deal'];

	}
	?>
	<div class="col-sm-4 hidden">
		<div class="panel panel-info">
			<div class="panel panel-heading" align="center"><em>Add Product</em></div>
			<div class="panel panel-body">
				<form action="" method="post">



					<div class="form-group">
						<label for="email">Product name:</label>
						<input type="text" class="form-control" autofocus="" id="email" name="productName"
							value="<?= @$pro_name ?>">
					</div>
					<div class="form-group">
						<label for="pwd">Purchase Rate:</label>
						<input type="text" class="form-control" id="pwd" name="purchase" value="<?= @$purchase ?>">
					</div>
					<div class="form-group">
						<label for="pwd">Sale Rate:</label>
						<input type="text" class="form-control" id="pwd" name="rate" value="<?= @$pro_rate ?>">
					</div>

					<div class="form-group">
						<label for="pwd">Quantity:</label>
						<input type="text" class="form-control" id="pwd" name="quantity" value="<?= @$pro_q ?>">
					</div>
					<div class="form-group">
						<label for="pwd">Category</label>

						<select class="form-control" autocomplete="off" required id="editCategoryName"
							name="categoryName">
							<!--  <select type="text" class="form-control"   > -->


							<?php

							$sql = "SELECT categories_id, categories_name, categories_active, categories_status FROM categories WHERE categories_status = 1 AND categories_active = 1";
							$result = $connect->query($sql);

							while ($row = $result->fetch_array()) {
								$selected = ($row[0] == $category) ? "selected" : "";
								echo "<option " . $selected . " value='" . $row[0] . "'>" . $row[1] . "</option>";
							} // while
							
							?>
							<!--  </select> -->
						</select>
					</div>
					<div class="form-group">
						<label for="pwd">Brand</label>
						<select class="form-control" autocomplete="off" required id="editBrandName" name="brandName">


							<?php

							$sql = "SELECT brand_id, brand_name, brand_active, brand_status FROM brands WHERE brand_status = 1 AND brand_active = 1";
							$result = $connect->query($sql);

							while ($row = $result->fetch_array()) {
								$selected = ($row[0] == $brand) ? "selected" : "";
								echo "<option " . $selected . " value='" . $row[0] . "'>" . $row[1] . "</option>";
							} // while
							
							?>
						</select>
					</div>
					<div class="form-group">
						<label for="pwd">Status</label>
						<select class="form-control" name="productStatus">

							<option value="1">Available</option>
							<option value="2">Not Available</option>
						</select>
					</div>
					<div class="form-group">
						<label for="pwd">Type</label>
						<label class="radio-inline">
							<input type="radio" <?= @($product_type == "0") ? "checked" : "" ?> value="0" name="product_type"
								checked>Product
						</label>
						<label class="radio-inline">
							<input type="radio" <?= @($product_type == "1") ? "checked" : "" ?> value="1"
								name="product_type">Deal
						</label>
					</div>
					<?php
					if (isset($_GET['i'])) {
						?>
						<input type="submit" name="editproduct" class="btn btn-info" value="Edit product">
						<?php
					} else {
						?>
						<input type="submit" name="addproduct" class="btn btn-success" value="Add product">
						<?php
					}
					?>
				</form>
			</div>
		</div>

	</div> <!-- col-sm-4 end -->


	<div class="col-sm-12">
		<div class="panel panel-danger">
			<div class="panel panel-heading" align="center"><em>Show Product</em></div>
			<div class="panel panel-body">
				<div class="responseAlert"></div>
				<table class="table" class="table-responsive" id="example">

					<thead>
						<tr class="">
							<th>Product ID</th>
							<th>Product Name</th>

							<th>Product Sale Rate</th>
							<th>Alert At</th>
							<th>Option</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$q = mysqli_query($dbc, "SELECT  product.*,categories.*,brands.* FROM product INNER JOIN categories ON product.categories_id=categories.categories_id INNER JOIN brands ON product.brand_id=brands.brand_id   WHERE product.status = 1 ORDER BY product.product_id DESC  ");
						while ($r = mysqli_fetch_assoc($q)):
							//$customer_id = $r['customer_id'];
							?>
							<tr>
								<td contenteditable='false'><?= $r['product_id'] ?></td>
								<td contenteditable='true' class="text-capitalize"><?= $r['product_name'] ?></td>
								<td contenteditable='true' class="text-lowercase"><?= $r['rate'] ?></td>
								<td contenteditable='true'><?= $r['alert_at'] ?></td>
								<td contenteditable='false'><a href="addproduct.php?i=<?= $r['product_id']; ?>"> <button
											class="btn btn-primary"><span
												class="glyphicon glyphicon-edit"></span>Edit</button></a>
									<?php if (@$r['deal'] == 1): ?>


										<a href="deals.php?pro_id=<?= $r['product_id']; ?>" target="_blank"> <button
												class="btn btn-warning"><span class="glyphicon glyphicon-plus"></span>Add
												Incre</button></a>
									<?php endif ?>
								</td>

							</tr>
						<?php endwhile; ?>
					</tbody>
				</table>
			</div>
		</div>

	</div> <!-- col-sm-8 end -->


</div><!-- row end -->

<?php
if (isset($_POST['addproduct'])) {


	$productName = $_POST['productName'];
	$productImage = 'abcd';
	$quantity = $_POST['quantity'];
	$rate = $_POST['rate'];
	$brandName = $_POST['brandName'];
	$categoryName = $_POST['categoryName'];
	$productStatus = $_POST['productStatus'];
	$purchase = $_POST['purchase'];
	$product_type = $_POST['product_type'];

	$sql = "INSERT INTO product (product_name, product_image, brand_id, categories_id, quantity, rate, purchase ,active, status,deal) 
				VALUES ('$productName', '$productImage', '$brandName', '$categoryName', '$quantity', '$rate', '$purchase', '1', 1,'$product_type')";

	if (mysqli_query($dbc, $sql)) {
		$msg = "<label class='label label-success'>Product Added</label>";
		echo "<script>alert('Product Added')</script>";
		echo '<script>window.location.assign("addproduct.php")</script>';
	} else {
		$msg = "<label class='label label-warning'>Error.....!</label>";
	}


}

if (isset($_POST['editproduct'])) {
	$productId = $_GET['i'];
	$productName = $_POST['productName'];
	$productImage = 'abcd';
	$quantity = $_POST['quantity'];
	$rate = $_POST['rate'];
	$brandName = $_POST['brandName'];
	$categoryName = $_POST['categoryName'];
	$productStatus = $_POST['productStatus'];
	$purchase = $_POST['purchase'];
	$product_type = $_POST['product_type'];


	$sql = "UPDATE product SET product_name = '$productName', brand_id = '$brandName', categories_id = '$categoryName', quantity = '$quantity', rate = '$rate', active = '$productStatus', status = 1, purchase = '$purchase',deal='$product_type' WHERE product_id = $productId ";

	if (mysqli_query($dbc, $sql)) {
		$msg = "<label class='label label-success'>Product Added</label>";
		echo "<script>alert('Product Updated')</script>";
		echo '<script>window.location.assign("addproduct.php")</script>';
	} else {
		$msg = "<label class='label label-warning'>Error.....!</label>";
	}
}


?>

<?php

include_once "includes/footer.php";
?>