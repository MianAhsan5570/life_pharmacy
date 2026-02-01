
<?php
//include_once 'php_action/db_connect.php';




include_once 'includes/header.php';
if(isset($_POST['save'])){
	$checkbox = $_POST['check'];
	for($i=0;$i<count($checkbox);$i++){
	$del_id = $checkbox[$i]; 
	mysqli_query($dbc,"DELETE FROM product WHERE product_id='".$del_id."'");
	$message = "<span class='alert alert-success'>Data deleted successfully !</span>";
}
}
  //$id = $_REQUEST['product_id'];
 $result = mysqli_query($dbc,"SELECT * FROM product ORDER BY product_name ASC ");
?>
<!DOCTYPE html>
<html>
<head>

<title>Delete Colors   </title>
</head>
<body>
	<div class="container">
		<br/>
<div><?php if(isset($message)) { echo $message; } ?>
</div><br>
<form method="post" action="">
	<p align="center"><button type="submit" class="btn btn-success" name="save">DELETE</button></p>
	
<table class="table table-bordered" id="myTable"> 
<thead>
<tr>
    <th><input type="checkbox" id="checkAl"> Select All</th>
	<!-- <th>Product IMG ID</th> -->
	<th>product ID</th>
	<th>Product name</th>
	<th>Product quantity</th>
	<!-- <th>Bag Price</th> -->
	
	
</tr>
</thead>
<?php
$i=0;
while($row = mysqli_fetch_array($result)) {

	 //$src2 = str_replace('../', '', $row['product_img']);


?>
<?php $product_name = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM categories WHERE categories_id = '$row[categories_id]'")) ?>
<tr>

    <td><input type="checkbox" id="checkItem" name="check[]" value="<?php echo $row["product_id"]; ?>"></td>
    <td><?=$row['product_id']?></td>
	<!-- <td><?php echo $row["color_id"]; ?></td> -->
	<td><?=$row['product_name']?>(<?=$product_name['categories_name']?>)</td>
	
	<!-- <td><?php echo $row["bag_price"]; ?></td> -->
	<td><?php echo $row["quantity"]; ?></td>
	

	
</tr>
<?php
$i++;
}
?>
</table>

</form>
<script>
$("#checkAl").click(function () {
$('input:checkbox').not(this).prop('checked', this.checked);
});
</script>
<script type="text/javascript">
	$(document).ready( function () {
    $('#myTable').DataTable();
} );
</script>
</body>
</html>
</div>

<?php
include_once"includes/footer.php";
?>