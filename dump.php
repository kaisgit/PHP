<?php
require_once __DIR__ . '/db_connect.php';


$connection = new DbConnection();
$query_string = "SELECT * from `changeform`";

$query_result = $connection->query($query_string);


if(isset($_GET["format"]))
	echo_result_to_csv($query_result);
else
	echo_result_to_json($query_result);


function echo_result_to_csv($query_result){
	header( 'Content-Type: text/csv' );
	header( 'Content-Disposition: attachment;filename=dumps.csv' );

	$count = 1;
	while($row = $query_result->fetch_assoc()){
		$row["desc"] = htmlspecialchars(stripslashes($row["desc"]));
		//Print Header
		if($count ==1) echocsv( array_keys($row));

		echocsv($row);		
		$count++;
	}
}


function echo_result_to_json($query_result){
	header('Content-Type: application/json');
	header( 'Content-Disposition: attachment;filename=dumps.json' );
	$output = array();
	while($row = $query_result->fetch_assoc()){
		$row["desc"] = htmlspecialchars(stripslashes($row["desc"]));
		$output[] = $row;
	}
	echo json_encode($output);
}


	
function echocsv( $fields ){
	$separator = '';
	foreach ( $fields as $field ){
		if ( preg_match( '/\\r|\\n|,|"/', $field ) ){
			$field = '"' . str_replace( '"', '""', $field ) . '"';
		}
		echo $separator . $field;
		$separator = ',';
	}
	echo "\r\n";
}

?>