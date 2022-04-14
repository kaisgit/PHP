<?php

date_default_timezone_set('America/Los_Angeles');

require_once "lib/mysqli_dps_dbconnect.php";

include '/var/www/html/PHPExcleReader/Classes/PHPExcel/IOFactory.php';

$start_date = "2015-11-01 00:00:00";
$start_date = date("Y-m-d 00:00:00", strtotime($start_date));

$document = PHPExcel_IOFactory::load('/home/admin/dps_service_review/xlsx/nov15.xlsx');

$activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

#print_r($activeSheetData);

foreach ($activeSheetData as $rec) {

	#print $rec['A'] . " :: " . $rec['B'] . "\n";

	# ONE-LINER: Get the ID of the service type.
	if ($rec['A'] == 'Digital Publishing Solution (Classic)') {

		$sla = rtrim($rec['B'], "%");		// chop the "100%" percentage sign before inserting into database.
		$id = $mysqli->query("SELECT id FROM dps_services WHERE description LIKE '%Digital Publishing Suite (Classic)%'")->fetch_object()->id; 
	}
}

$query = "INSERT INTO dps_monthly_ola (date, percentage, service_id_fk) VALUES ('" . $start_date . "',$sla,$id)";
$result = mysqli_query($mysqli, $query);
if(!$result) { echo mysqli_error() . $query . PHP_EOL; }  

?>
