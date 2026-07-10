<META content="text/html; charset=utf-8" http-equiv=Content-Type>
<center>
<?php


require_once 'core.php';

$orderId = $_POST['orderId'];
//$orderId = '3';

// Previously this ran a SELECT + UPDATE per order item (2 queries per
// product) to deduct stock — for a 10-item order that's 20 queries
// firing sequentially before the print window even opens. A single
// UPDATE...JOIN does the same job in one query regardless of how many
// items are on the order.
mysqli_query($dbc, "UPDATE product p
INNER JOIN order_item oi ON oi.product_id = p.product_id
SET p.quantity = p.quantity - oi.quantity
WHERE oi.order_id = '$orderId'");


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

// Same fix as printOrder.php: pull product rate + category name in the
// same query instead of running 2 extra queries per line item in the
// loop below.
$orderItemSql = "SELECT order_item.product_id, order_item.rate, order_item.quantity, order_item.total,
product.product_name, order_item.percentage, product.categories_id, categories.categories_name
FROM order_item
INNER JOIN product ON order_item.product_id = product.product_id
LEFT JOIN categories ON categories.categories_id = product.categories_id
WHERE order_item.order_id = $orderId";
$orderItemResult = $connect->query($orderItemSql);
if ( mysqli_num_rows($orderItemResult) > 0) {
	 $time2 = ($date['orderdatetime']);

 ?>
 

 <div class="" style="width: 85%" align="center">
 				<?php
 				include_once "logo.php";
 				?>
 				<!-- <p style="font-size: 22px; margin-top: 10px;"><strong>Cell No:</strong> 0300-7225467</p> -->
					<hr/>
 	</div><br/>
<table border="1" cellspacing="0" cellpadding="2" width="85%" style="font-size:20px;">
	<thead>
				
		<tr >
			<th colspan="5">

			<div align="Left">
					
			
				<table style="text-align: left;">
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
<table border="0" width="85%;" cellpadding="1" style="border:1px solid black;font-size:20px;border-top-style:1px solid black;border-bottom-style:1px solid black ;">

	<tbody>
		<tr>
			<th>Sr.</th>
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
				// category data now comes straight from $row (via the
				// JOIN above) — no more per-row queries here.
	 		
		?>				
			 <tr>
				<th><?=$x?></th>
				<td style="font-size:20px;"><?php echo ucwords(strtolower($row[4])); ?><?php
if($row['categories_name'] == 'offdiscount' OR $row['categories_name'] == 'OFFDISCOUNT'){
echo "";
}else{
echo "(".$row['categories_name'].")";
} 
 ?>  </td>
				<th><?php echo $row['rate']; ?></th>
				<?php
				$fetchprorate =  $row['rate']*$row[2];
				?>
				<th><?php echo $row[2]; ?></th>
				 <th><?php 
				 if($row['percentage'] == '0'){
				 	echo "0";
				 }else{
				 $totaldiscthis = (($fetchprorate/100)* $row['percentage']);
				 echo round($totaldiscthis);
				 }

				?>
				 	
				 </th> 
				<th><?php echo  $row['total'] ; ?></th>


		<?php

		$gttotal +=  $row['rate']*$row['quantity'];
		 $gttotaltotaldics +=(($fetchprorate/100)* $row['percentage']);
		
		$x++;
		} // /while
?>
		</tr>
</tbody>


</table>
		<table style="float: right;margin-right: 25px; font-size:20px;" width="90%;" > 
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
			<td style="text-align: right"><h4 style="font-size:25px;"><?php echo round($grandTotal) ; ?></h4></td>
		</tr>
		
	
</table>
		

		
<div style="margin-top:20px;">	
<p style=" font-size:20px">
	ادویات بل کے ساتھ 15 دن کے اندر واپس یا تبدیل ہو سکتی ہے

اپنا بقایاجات اور سامان کاونٹر پر چیک کرلیں بعد میں کوئی ذمہ داری  نہ ہو گی
کھلی ادویات ، فریج آٹمز ، سیرپ ، انہلرز اور ٹیسٹ اسٹرپ نا قابلِ واپس ہوگی
</p>
<br>
</p>
<p style="font-size: 22px;"> 0300-7225467</p>

<hr/>
<p style="margin-top:0px;font-size:15px"><strong>Software Developed By: <br/> TheWebConcept (0313-7573667)</strong></p>
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
