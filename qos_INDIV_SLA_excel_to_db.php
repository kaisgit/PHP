<?php

date_default_timezone_set('America/Los_Angeles');

require_once "lib/mysqli_dps_dbconnect.php";

include '/var/www/html/PHPExcleReader/Classes/PHPExcel/IOFactory.php';

########################################################################
# 1. Change $start_date
# 2. Change excel's filename to correct mongh: INDIV_SLA_jul15.xlsx
#
$start_date = "2015-11-01 00:00:00";

$start_date = date("Y-m-d 00:00:00", strtotime($start_date));

$document = PHPExcel_IOFactory::load('/home/admin/dps_service_review/xlsx/INDIV_SLA_nov15.xlsx');

$activeSheetData = $document->getActiveSheet()->toArray(null, true, true, true);

#print_r($activeSheetData);

foreach ($activeSheetData as $rec) {

	$service_desc = null;
	#print $rec['A'] . " :: " . $rec['B'] . "\n";
	$ola = rtrim($rec['B'], "%");		// chop the "100%" percentage sign.

	if (preg_match("/^DPS v2 /", $rec['A'])) {
		$id = 2;	// V2
		$pattern = "/^DPS v2 /";	// remove 
		$replace = "";
		$service_desc = preg_replace($pattern, $replace, $rec['A']);
	} elseif (preg_match("/^DPS v1 /", $rec['A'])) {
		$id = 1;	// V1 = CLASSIC
		$pattern = "/^(DPS v1 )|\(SCS\)$/";		// remove both patterns
		$replace = "";
		$service_desc = preg_replace($pattern, $replace, $rec['A']);
	}

	if ($service_desc) {
		$query = "INSERT INTO dps_monthly_indiv_ola (date, service_desc, percentage, service_id_fk) VALUES ('" . $start_date . "','" . $service_desc . "', $ola, $id)";
		$result = mysqli_query($mysqli, $query);
		if(!$result) { echo mysqli_error() . $query . PHP_EOL; }  

		$service_desc = null;
	}

}

?>
