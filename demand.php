<body aling ="center">
<script type="text/javascript">

 	//window.print()
  
</script>				
		
		<?php
			include_once "php_action/db_connect.php"; 
			?>		
	
<div align="center">
	<?php  echo date('D,d-m-Y')?>
	<button type="button" value="Print this page" onclick="printpage()" class="btn btn-info hidden-print" />Print this page</button>

	
<form method="post">
 
	<select name="brand" >
		<option value="">~~Brand~~</option>
		<?php
		$brand = mysqli_query($dbc,"SELECT * FROM brands");
		while($brands = mysqli_fetch_assoc($brand)):
		?>
			<option value="<?=$brands['brand_id']?>" ><?=$brands['brand_name']?></option>
		<?php
	endwhile;
		?>
	</select>
	<select name="category" >
		<option value="">~~category~~</option>
		<?php
		$brand = mysqli_query($dbc,"SELECT * FROM categories");
		while($brands = mysqli_fetch_assoc($brand)):
		?>
			<option value="<?=$brands['categories_id']?>" ><?=$brands['categories_name']?></option>
		<?php
	endwhile;
		?>
	</select>
	


	<input type="submit" name="numbering" class="btn btn-info">
	</form>

	<hr/>
<script type="text/javascript">
function printpage()
  {
  window.print()
  }
</script>			
	</div>			
<table  border="1px solid" width="50%" style="text-align: center;"  align="center"  >

			<tr>
				<th>Product ID</th>
				<th>Product Name</th>
				<th>Quantity Remaining</th>
				
			</tr>
<?php

// $mysql_path = mysql_connect('localhost','root','');
// $mysql_db = mysql_select_db('mursad_alimobile',$mysql_path);

// if(!$mysql_db){
// 	echo mysql_error();
// }
// else{
// 	//echo "Connection Established";
// }

// if(isset($_POST['numbering'])){

// 	$number = $_POST['number'];
// 	if($number>0){
// 		echo $number = $_POST['number'];
// 	}else{
// echo $number = 10;
// 	}

	//$sql = "SELECT * FROM product WHERE quantity > '$cata[low_stock]' AND categories_id = '$cata[categories_id]'";
if(!empty($_POST['category']) AND !empty($_POST['brand'])){
$sql =mysqli_query($dbc,"SELECT * FROM product WHERE quantity<alert_at AND categories_id =".$_POST['category']." AND brand_id =".$_POST['brand']." AND active = 1 AND status = 1");

}elseif (!empty($_POST['brand'])) {
	

	$sql =mysqli_query($dbc,"SELECT * FROM product WHERE quantity<alert_at AND brand_id =".$_POST['brand']." AND active = 1 AND status = 1");
	# code...
}elseif (!empty($_POST['category'])) {
	$sql =mysqli_query($dbc,"SELECT * FROM product WHERE categories_id = '$_POST[category]' AND quantity<alert_at  ");
	
	# code...
}else{

	$sql =mysqli_query($dbc,"SELECT p.*  
    FROM product p  
    JOIN brands b ON p.brand_id = b.brand_id  
    WHERE p.quantity < p.alert_at  
    AND p.active = 1  
    AND p.status = 1  
    AND p.brand_id NOT IN ('47', '103', '104')  
    AND b.brand_active != 2
    ");

	//echo "SELECT * FROM product WHERE quantity<alert_at AND active = 1 AND status = 1 AND brand_name !='47' AND brand_name != '103' AND brand_name != '104'";	
}
	
	while($row = mysqli_fetch_assoc($sql)):
		$categorythis = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM categories WHERE categories_id = '$row[categories_id]'"));
		$brandthis = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM brands WHERE brand_id = '$row[brand_id]'"));
		?>

			<tr>
				<td><?=$row['product_id']?></td>
				<td><?=$row['product_name']?>(<?=$categorythis['categories_name']?>)[<?=$brandthis['brand_name']?>]</td>
				<td><?=$row['quantity']?></td>
			</tr>

		

	
 <?php
	
endwhile;
	//cateloop
	?>
 </table>
   			
</div>
	<div align="center">
	<!-- <strong>Software Developed By : Sam'z creations (+92-345-7573667)</strong> -->
	
	</div>
</body>