<?php
date_default_timezone_set('UTC'); 
require_once __DIR__ . '/db_connect.php';
$connection = new DbConnection();

$field_string = "(`source`";
$value_string = "('webform'";
foreach ($_POST as $key => $value){
	$field_string .= ",`" . $key . "`";
	
	$value_string .= ",'";
	if($key == 'start' OR $key == 'end')
		$value_string .= date("Y-m-d h:i:s",$value/1000);
	else if($key == 'desc')
		$value_string .= $connection->real_escape_string($value);
	else if($key == 'cso'){
		if($value == "yes") $value_string .= 1;
		else $value_string .= 0;
	}
	else
		$value_string .= $value;
	$value_string .= "'";
}
$field_string .= ") ";
$value_string .= ") ";

$query_string = "INSERT INTO `changeform` $field_string VALUES $value_string";
$result = $connection->query($query_string);

$res = array();
if($connection->error){
	$res["status"] = "error";
	$res["message"] = $connection->error;
}
else{
	$res["status"] = "ok";
	$res["message"] = "Successfully submited form. Your ID is: <strong>" . $connection->insert_id ;
}
echo json_encode($res);
