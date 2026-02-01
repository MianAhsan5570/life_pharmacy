<?php 	
date_default_timezone_set("Asia/Karachi");
   $localhost = "localhost";
  // $username = "samziymw_techo_polly";
  // $password = "samziymw_techo_polly";
  // $dbname = "pos_shaikhfaiz";
$localhost = "localhost";
 $username = "root";
 $password = "";
 $dbname = "stock";

$connect = new mysqli($localhost, $username, $password, $dbname);
$dbc =  mysqli_connect($localhost, $username, $password, $dbname);

@session_start();
if($connect->connect_error) {
  die("Connection Failed : " . $connect->connect_error);
} else {
  //echo "Done";
}

?>