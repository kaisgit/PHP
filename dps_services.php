<?php

error_reporting(0);

date_default_timezone_set('America/Los_Angeles');

require_once "lib/mysqli_dps_dbconnect.php";

#########################################################################
# Get service id and types
#
# [1] => DPS = Classic V1
# [2] => DIGITAL_PUBLISHING_SOLUTIONS = V2
#
# 1. Add service id to array to run for that service.
#
$services = array(1);
require_once("lib/get_services.php");

foreach ($services as $id) {
	$service_id = $dps_services[$id]['name'];			// $id = 1
                                                        // $service_id = "DPS"

	#########################################################################
	# Use earlier start date to have more data to work with.
	#
	$start_date = "2015-06-01 00:00:00";
	$end_date = "2015-06-30 23:59:59";
	#$start_date = "2015-07-01 00:00:00";
	#$end_date = "2015-07-31 23:59:59";
	#$start_date = "2015-08-01 00:00:00";
	#$end_date = "2015-08-31 23:59:59";
	#$start_date = "2015-09-01 00:00:00";
	#$end_date = "2015-09-30 23:59:59";
	#$start_date = "2015-10-01 00:00:00";
	#$end_date = "2015-10-31 23:59:59";
	#$start_date = "2015-11-01 00:00:00";
	#$end_date = "2015-11-30 23:59:59";
	
	$query = "SELECT
				z.service_id, s.service_wf_desc, prob_service_id, cso_time_impact_start, cso_time_impact_end, cso_impact_type,
				cso_impact_duration, percentage_of_users_per_transaction_impacted, cso_root_cause_category, rcc_id
		  	FROM zendesk_approved_list_new z, service_fields_vnoc s
		  	WHERE
				cso_impact_type in ('Full Outage','Partial Outage','Degradation')
				AND cso_time_impact_start >= '$start_date' and cso_time_impact_end <= '$end_date'
				AND z.service_id in (select service_wf_id from service_fields_vnoc where service_id in ('$service_id'))
				AND rcc_id in (select vnoc_rcc_id from root_cause_category_list)
				AND z.service_id = s.service_wf_id
		  	ORDER BY z.service_id, cso_time_impact_start, cso_impact_type";
	
	if (!$result = $mysqli->query($query)) { die("Query Failed: [" . $mysqli->error . "]\n"); }
	
	$dps_services = array();
	while ($row = $result->fetch_assoc()) {
    	$service_id = $row['service_id'];

		# remove the colon to match with the names from QoS.
    	$service_wf_desc = $row['service_wf_desc'];
		$service_wf_desc = preg_replace("/:/", "", $service_wf_desc);

		$cso_time_impact_start = $row['cso_time_impact_start'];
		$cso_time_impact_end = $row['cso_time_impact_end'];
    	$cso_impact_type = $row['cso_impact_type'];
		$cso_impact_duration = $row['cso_impact_duration'];
    	$percent_users_impacted = $row['percentage_of_users_per_transaction_impacted'];
	
		$dps_services[$service_wf_desc][] = array('cso_time_impact_start' => $cso_time_impact_start,
											  	'cso_time_impact_end' => $cso_time_impact_end,
											  	'cso_impact_type' => $cso_impact_type,
											  	'cso_impact_duration' => $cso_impact_duration);
	}
	#print_r($dps_services);
	
	#########################################################################
	# Group by Services, Incident count, Full + Partial + Degraded minutes.
	#
	$dps_services_final = array();
	foreach ($dps_services as $service_desc=>$data) {
		$incidents = 0;
	
		foreach ($data as $rec) {
			$impact_type = $rec['cso_impact_type'];
			$cso_impact_duration = $rec['cso_impact_duration'];
	
			$dps_services_final[$service_desc][$impact_type] += $cso_impact_duration;
			$incidents++;
		}
		$dps_services_final[$service_desc]['count'] = $incidents;
	}
	#print_r($dps_services_final);
	
	#########################################################################
	# Getting individual OLA services to populate to database.
	#
	$query = "SELECT service_desc FROM dps_monthly_indiv_ola WHERE date = '$start_date' and service_id_fk = $id";
	if (!$result = $mysqli->query($query)) { die("Query Failed: [" . $mysqli->error . "]\n"); }
	
	while ($row = $result->fetch_assoc()) {
    	$service_desc = $row['service_desc'];
	
		/*
		if ($dps_services_final[$service_desc]) {
			$dps_services_final[$service_desc]['Monthly OLA'] = $percentage;
		} else {
			$dps_services_final[$service_desc]['Full Outage'] = 0;
			$dps_services_final[$service_desc]['Partial Outage'] = 0;
			$dps_services_final[$service_desc]['Degradation'] = 0;
			$dps_services_final[$service_desc]['count'] = 0;
			$dps_services_final[$service_desc]['Monthly OLA'] = $percentage;
		}
		*/

		#####################################################################
		# if a service doesn't exist, default to 0.
		#
		$incident = $dps_services_final[$service_desc]['count'] ? : 0;
		$full = $dps_services_final[$service_desc]['Full Outage'] ? : 0;
		$partial = $dps_services_final[$service_desc]['Partial Outage'] ? : 0;
		$degradation = $dps_services_final[$service_desc]['Degradation'] ? : 0;

		$query2 = "INSERT INTO dps_grouped_cso_data (date, service_desc, incident, full, partial, degradation, service_id_fk)
										VALUES ('$start_date', '$service_desc', $incident, $full, $partial, $degradation, $id)";
		$result2 = mysqli_query($mysqli, $query2);
		if(!$result2) { echo mysqli_error() . $query2 . PHP_EOL; }  
	}


$mysqli->close();
} # END MAIN of IF()

?>
