<?php

error_reporting(0);

date_default_timezone_set('America/Los_Angeles');

require_once "lib/mysqli_dps_dbconnect.php";
require_once("lib/get_root_cause_desc.php");

$required_rootcause_categ = array('Partners - AWS','Partners - Vendor','People - Human Error','People - Process Noncompliance','Process - Procedural','Process - Application Bug (Engineering Defect)','Process - Application Design','Technology - Internal Services','Technology - Hardware Failure','Technology - False Alert','Technology - Unknown');

#########################################################################
# Get service id
#
# [1] => DPS = Classic V1
# [2] => DIGITAL_PUBLISHING_SOLUTIONS = V2
#
# 1. Add service id to array to run for that service.
#
$services = array(1);
require_once("lib/get_services.php");

foreach ($services as $id) {							// $id = 1
    $service_id = $dps_services[$id]['name'];			// $service_id = "DPS"
}

#####################################################################################################################
# 1. Update $start_date and $end_date
#
$start_date = "2015-12-01 00:00:00";
$end_date = "2015-12-31 23:59:59";

# Full + Partial ONLY. Remove Degradation from query. 
$query = "SELECT cso_impact_type, percentage_of_users_per_transaction_impacted, cso_impact_duration,
			CASE
				when cso_impact_type = 'Full Outage'
				then cso_impact_duration
            	when cso_impact_type = 'Partial Outage'
				then cso_impact_duration * (percentage_of_users_per_transaction_impacted / 100)
			END as cso_finalized_time,
			cso_time_impact_start, cso_time_impact_end, rcc_tier1_descr as tier1, rcc_id as tier2
		  FROM zendesk_approved_list_new
		  WHERE
			cso_impact_type in ('Full Outage','Partial Outage')
			AND cso_time_impact_start >= '$start_date' and cso_time_impact_end <= '$end_date'
			AND service_id in (select service_wf_id from service_fields_vnoc where service_id in ('$service_id'))
			AND rcc_id in (select vnoc_rcc_id from root_cause_category_list)
		  ORDER BY rcc_id, cso_time_impact_start, percentage_of_users_per_transaction_impacted desc";

if (!$result = $mysqli->query($query)) { die("Query Failed: [" . $mysqli->error . "]\n"); }

$root_cause = array();
while ($row = $result->fetch_assoc()) {
	$cso_impact_type = $row['cso_impact_type'];
	$cso_finalized_time = $row['cso_finalized_time'];
	$cso_time_impact_start = $row['cso_time_impact_start'];
	$cso_time_impact_end = $row['cso_time_impact_end'];

	$tier1 = $row['tier1'];
	$tier2 = $row['tier2'];
	$tier2 = $rc_desc[$tier2];		// from library. use description not ID per the powerpoint requirement.

	$root_cause["$tier1 - $tier2"][] = array('cso_impact_type' => $cso_impact_type,
								     'cso_finalized_time' => $cso_finalized_time,
								     'cso_time_impact_start' => $cso_time_impact_start,
								     'cso_time_impact_end' => $cso_time_impact_end);
}

#print_r($rc_desc);
#print_r($root_cause);

$root_cause_analysis = array();
foreach ($root_cause as $rcc_desc=>$data) {
	$from_prev = null;
	$to_prev = null;
	foreach ($data as $rec) {
		$impact_type = $rec['cso_impact_type'];
		$finalized_time = $rec['cso_finalized_time'];
			$start = $rec['cso_time_impact_start'];				// for testing only
			$end = $rec['cso_time_impact_end'];					// for testing only
		$from = strtotime($rec['cso_time_impact_start']);
		$to = strtotime($rec['cso_time_impact_end']);

		if (count($data) > 1) {											// more than 1 record in an array as a group.
			if ($from_prev && $to_prev) {	
				print "NEXT: ";

				$intersect = min($to, $to_prev) - max($from, $from_prev);
				if ( $intersect < 0 ) $intersect = 0;
				$overlap = round($intersect/60);

				if ($overlap <= 0) {									// if no overlap
					$root_cause_analysis[$rcc_desc] += $finalized_time;
					$total_mins += $finalized_time;

					print "$rcc_desc :: $impact_type :: $start :: $end :: $finalized_time\n";
				} else {												// overlap
					$outage = round(($to - $from)/60);
					$non_overlap = $outage - $overlap;					// watch for rounding errors.

					$root_cause_analysis[$rcc_desc] += $non_overlap;
					$total_mins += $non_overlap;

					$from = min($from, $from_prev); 
					$to = max($to, $to_prev);
					print "$rcc_desc :: $impact_type :: $start :: $end :: $non_overlap\n";
				}
			} else {													// first record in group.
				$root_cause_analysis[$rcc_desc] += $finalized_time;
				$total_mins += $finalized_time;

				print "\n1ST : ";
				print "$rcc_desc :: $impact_type :: $start :: $end :: $finalized_time\n";
			}
			$from_prev = $from;
			$to_prev = $to;
		} else {														// if there is only 1 record in array
			$root_cause_analysis[$rcc_desc] += $finalized_time;
			$total_mins += $finalized_time;

			print "\n1ST : ";
			print "$rcc_desc :: $impact_type :: $start :: $end :: $finalized_time\n";
		}
	}
}


#print_r($root_cause_analysis);
#print "Total mins: $total_mins\n";

$start_date = date("Y-m-d 00:00:00", strtotime($start_date));

foreach ($required_rootcause_categ as $name) {
	if ($root_cause_analysis[$name]) {
		$percentage = round((($root_cause_analysis[$name]/$total_mins)*100),2);
		#print "$name :: " . $percentage . "\n";
	} else {
		$percentage = 0;
		#print "$name :: $percentage\n";
	}
	
	$query = "INSERT INTO `dps_rootcause_analysis` (date,rcc,percentage,service_id_fk) VALUES ('" . $start_date . "','" . $name . "',$percentage,$id)";
	#print "$query\n";
	$result = mysqli_query($mysqli, $query);
	if(!$result) { echo mysql_error() . $query . PHP_EOL; }  
}

$mysqli->close();

?>
