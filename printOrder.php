<META content="text/html; charset=utf-8" http-equiv=Content-Type>
<center>
<?php


require_once 'core.php';

$orderId = $_POST['orderId'];
//$orderId = '3';


$sql = "SELECT order_date, client_name, client_contact, sub_total, vat, total_amount, discount, grand_total, paid, due FROM orders WHERE order_id = $orderId";

$orderResult = $connect->query($sql);
$orderData = $orderResult->fetch_array();

$orderDate = $orderData[0];
$clientName = $orderData[1];
$clientContact = $orderData[2]; 
$subTotal = $orderData[3];
$vat = $orderData[4];
$totalAmount = $orderData[5]; 
$discount = $orderData[6];
$grandTotal = $orderData[7];
$paid = $orderData[8];
$due = $orderData[9];
 $date= mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM orders WHERE order_id = '$orderId'"));


$orderItemSql = "SELECT order_item.product_id, order_item.rate, order_item.quantity, order_item.total,
product.product_name, order_item.percentage FROM order_item
	INNER JOIN product ON order_item.product_id = product.product_id 
 WHERE order_item.order_id = $orderId";
$orderItemResult = $connect->query($orderItemSql);
if ( mysqli_num_rows($orderItemResult) > 0) {
	 $time2 = ($date['orderdatetime']);

 ?>
 

 	<div class="" style="width: 80%" align="center">
 				<?php
 				include_once "logo.php";
 				?>
 				<!-- <p style="font-size: 22px; margin-top: 10px;"><strong>Cell No:</strong> 0300-7225467</p> -->
					<hr/>
 	</div><br/>
 <table border="1" cellspacing="0" cellpadding="2" width="100%" style="font-size:20px;">
	<thead>
				
		<tr >
			<th colspan="5">

			<div align="Left">
					
			
				<table style="text-align: left;width: 80%">
					<tr>
						<th>	Bill No</th>
						<td>:</td>
						<td><?php echo $orderId ; ?> </td>
					</tr>
					<tr>
						<th>		Order Date  </th>
						<td>:</td>
						<td><?php echo $orderDate ; ?>  <?=date('h:i a',strtotime($time2)) ?> </td>
					</tr>
					<tr>
						<th>	Client Name  </th>
						<td>:</td>
						<td> <?php echo $clientName ; ?> </td>
					</tr>
					<tr>
						<th>Contact :</th>
						<td>:</td>
						<td> <?php echo $clientContact ; ?> </td>
					</tr>
				</table>
			</div>

			</th>
			</tr>

			
	</thead>
</table>
<table border="0" width="90%;" cellpadding="1" style="border:1px solid black;font-size:20px;border-top-style:1px solid black;border-bottom-style:1px solid black ;">

	<tbody>
		<tr>
			<th>S.no</th>
			<th>Product</th>
			<th>Rate</th>
			<th>QTY</th>
			<th>DISC</th>
			<th>Total</th>
		</tr>
		<?php
		$x = 1;	
		$subamount = 0;
		$totaldisc = 0;
		$grand_total_show = 0;
		$gttotal = 0;
		$gttotaltotaldics = 0;
		while($row = $orderItemResult->fetch_array()) {
				$product_id = $row['product_id'];
				$fetchProduct = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM product WHERE product_id='$product_id'"));
				$fetchCategory = mysqli_fetch_assoc(mysqli_query($dbc,"SELECT * FROM categories WHERE categories_id='$fetchProduct[categories_id]'"));
	 		
		?>				
			 <tr>
				<th><?php echo $x ?></th>
				<td style="font-size:20px;"><?php echo ucwords(strtolower($row[4])); ?>(<?php
if($fetchCategory['categories_name'] == 'offdiscount' OR $fetchCategory['categories_name'] == 'OFFDISCOUNT'){
echo "-";
}else{
echo $fetchCategory['categories_name'];
} 
 ?>)  </td>
				<th><?php echo $fetchProduct['rate']; ?></th>
				<?php
				$fetchprorate =  $fetchProduct['rate']*$row['quantity'];
				?>
				<th><?php echo $row[2]; ?></th>
				 <th><?php 
				 if($row['percentage'] == '0'){
				 	echo "0";
				 	$totaldiscthis = 0;
				 }else{
				  $totaldiscthis = (($fetchprorate/100)* $row['percentage']);
				   echo number_format($totaldiscthis,2);
				 //echo $row['percentage'];
				 
				 }

				?>
				 	
				 </th> 
				<th><?php echo (($fetchProduct['rate']*$row['quantity'])-$totaldiscthis);  ?></th>


		<?php

		$gttotal +=  $fetchProduct['rate']*$row['quantity'];
		if($row['percentage'] > 0 ){
		 $gttotaltotaldics +=(($fetchprorate/100)* $row['percentage']);
		}
		
		$x++;
		} // /while
?>
		</tr>
</tbody>


</table>
		<table style="float: right;margin-right: 22px; font-size:20px;" width="90%;" > 
		<tr > 
			<td style="text-align: right">Gross Total</td>
			<td  style="text-align: right"><?php echo $gttotal; ?></td>	
		</tr>
		<tr>
			<td style="text-align: right">You Saved </td>
			<td  style="text-align: right"><b><?php echo number_format($gttotaltotaldics); ?></b></td>	
						
		</tr>

		<tr>
			<td style="text-align: right">Net Totel</td>
			<td style="text-align: right"><h4 style="font-size:25px;"><?php echo round($gttotal-$gttotaltotaldics) ; ?></h4></td>
		</tr>
		
	
</table>
		

		
<div style="margin-top:20px;">	
<p style=" font-size:20px">
	ادویات بل کے ساتھ 15 دن کے اندر واپس یا تبدیل ہو سکتی ہے

اپنا بقایاجات اور سامان کاونٹر پر چیک کرلیں بعد میں کوئی ذمہ داری  نہ ہو گی
کھلی ادویات ، فریج آٹمز ، سیرپ ، انہلرز اور ٹیسٹ اسٹرپ نا قابلِ واپس ہوگی
<br>

</p>
<p style="font-size: 22px;"> 0300-7225467</p>
<hr/>
<p style="margin-top:0px;font-size:15px"><strong>Software Developed By: <br/> SAM'Z Creation(0345-7573667)</strong></p>
</div> <br/> 
<?php
}
?>

</center>
<style type="text/css">
	p{
		margin-top:-10px;
		font-size:10px;
	}
	body {
    font-family: 'Roboto', sans-serif;
}
</style>