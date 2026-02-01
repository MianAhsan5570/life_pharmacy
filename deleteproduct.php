

<?php

include_once "php_action/db_connect.php";
if ($_REQUEST['i']) {

	echo $id = $_REQUEST['i'];

	$sql = "DELETE FROM product WHERE product_id = ' $id'";
	if(mysqli_query($dbc,$sql))
	{
		?>
		<script type="text/javascript"> alert('Product Deleted...!');
		 //window.location.assign("completeproduct.php");
		</script>


		<?php
	}
	# code...
}

?>