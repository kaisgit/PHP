<?php

error_reporting(1);

date_default_timezone_set('America/Los_Angeles');

require_once "../lib/mysqli_dps_dbconnect.php";

#########################################################################
#
include("get_service_names.php");
include("get_vnoc_tag_to_zendesk_desc.php");

#########################################################################
# service_sid, service_id, service_desc, service_wf_id, service_wf_desc from service_fields_vnoc
# ARRAY: $map_group_names_and_services
#
include("get_group_names_and_services.php");

#####################################################################################################################
#
$yesterday = date("Y-m-d", strtotime("-1 day")); 
$sql ='SELECT start_of_month, end_of_month, mth, qtr FROM fiscal_cal WHERE "' . $yesterday . '" between start_of_month and end_of_month';

if (!$result1 = $mysqli->query($sql)) { die("Query Failed: [" . $mysqli->error . "]\n"); }

while ($row1 = $result1->fetch_assoc()) {
    $mth = $row1['mth'];
    $qtr = $row1['qtr'];
    $start_of_month = $row1['start_of_month'];
    $end_of_month = $row1['end_of_month'];

		// Full + Partial ONLY. Remove Degradation
		// resets the hh:mm:00 (seconds) in the date fields for the ORDER BY clause to sort cso_finalized_time properly.
		foreach ($map_group_names_and_services as $service_id=>$rec) {
			foreach ($rec as $service_desc=>$item) {
				foreach ($item as $wf_id=>$wf_desc) {

					$query = "SELECT DISTINCT
								s.service_sid, s.service_id, s.service_desc, s.service_wf_desc, cso_impact_type,
								percentage_of_users_per_transaction_impacted, cso_impact_duration,
							  CASE
								WHEN cso_impact_type = 'Full Outage'
								THEN cso_impact_duration
            					WHEN cso_impact_type = 'Partial Outage'
								THEN cso_impact_duration * (percentage_of_users_per_transaction_impacted / 100)
            					WHEN cso_impact_type = 'Degradation'
            					THEN cso_impact_duration
							  END as cso_finalized_time,
								date_format(cso_time_impact_start, '%Y-%m-%d %H:%i:00') as cso_time_impact_start,
								date_format(cso_time_impact_end, '%Y-%m-%d %H:%i:00') as cso_time_impact_end,
								rcc_id
					  		  FROM zendesk_approved_list_new z, service_fields_vnoc s
					  		  WHERE
								cso_impact_type in ('Full Outage','Partial Outage')
								AND cso_time_impact_start >= '$start_of_month 00:00:00' and cso_time_impact_end <= '$end_of_month 23:59:59'
    							AND z.service_id = s.service_wf_id
    							AND s.service_desc = '$service_desc'
                        		AND s.service_wf_desc = '$wf_desc'
					  		  ORDER BY
								s.service_desc, s.service_wf_desc, cso_time_impact_start, cso_time_impact_end, cso_finalized_time desc";

					if (!$result = $mysqli->query($query)) { die("Query Failed: [" . $mysqli->error . "]\n"); }

					$all_services = array();
					if($result->num_rows != 0){
						while ($row = $result->fetch_assoc()) {
							$service_desc = $row['service_desc'];
							$service_wf_desc = $row['service_wf_desc'];
							$cso_impact_type = $row['cso_impact_type'];
							$percent_users_impacted = $row['percentage_of_users_per_transaction_impacted'];
							$cso_impact_duration = $row['cso_impact_duration'];
							$cso_finalized_time = $row['cso_finalized_time'];
							$cso_time_impact_start = $row['cso_time_impact_start'];
							$cso_time_impact_end = $row['cso_time_impact_end'];
							$rcc = $row['rcc_id'];

							$all_services["$service_desc - $service_wf_desc"][$rcc][] = array('cso_impact_type' => $cso_impact_type,
									 	'percent_users_impacted' => $percent_users_impacted,
									 	'cso_impact_duration' => $cso_impact_duration,
									 	'cso_finalized_time' => $cso_finalized_time,
									 	'cso_time_impact_start' => $cso_time_impact_start,
									 	'cso_time_impact_end' => $cso_time_impact_end);
						}
					} else {
						#################################################################################################################
						# This is needed so tableau can display all the "group names - services" in the search filter.
						#
                        $query2 ="SELECT * FROM rootcause_INCIDENT_ANALYSIS_NEW
                                WHERE
                                    date = '$mth' AND
                                    qtr = '$qtr' AND
                                    group_and_services = '$service_desc - $wf_desc' AND
                                    rc_flag = 'RCC'";
                        if (!$result2 = $mysqli->query($query2)) { die("Query Failed: [" . $mysqli->error . "]\n"); }

                        if ($result2->num_rows == 0) {
							$query3 = "INSERT INTO rootcause_INCIDENT_ANALYSIS_NEW (date, qtr, group_and_services, rc_flag) VALUES ";
							$query3 .= "('" . $mth . "','" . $qtr . "','" . $service_desc . " - " . $wf_desc .  "','RCC');";
							$result3 = mysqli_query($mysqli, $query3);
							if(!$result3) { echo mysqli_error() . $query3 . PHP_EOL; }
						}
					}

					#print_r($all_services);

					$attributed = array();
					foreach ($all_services as $s_name=>$rec) {
						foreach ($rec as $rc_categ=>$data) {
							$from_prev = null;
							$to_prev = null;
							foreach ($data as $rec) {
								$impact_type = $rec['cso_impact_type'];
								$percentage_impacted = $rec['percent_users_impacted'];
								$finalized_time = $rec['cso_finalized_time'];
									$cso_impact_duration = $rec['cso_impact_duration'];			// for testing only.
									$start = $rec['cso_time_impact_start'];						// for testing only.
									$end = $rec['cso_time_impact_end'];							// for testing only.
								$from = strtotime($rec['cso_time_impact_start']);
								$to = strtotime($rec['cso_time_impact_end']);
		
								if (count($data) > 1) {											// more than 1 record in an array as a group.
									if ($from_prev && $to_prev) {	
										#print "NEXT: ";
	
										$intersect = min($to, $to_prev) - max($from, $from_prev);
										if ( $intersect < 0 ) $intersect = 0;
										$overlap = round($intersect/60);
	
										if ($overlap <= 0) {									// if no overlap
											$attributed[$s_name][$rc_categ] += $finalized_time;
		
											#print "$rc_categ :: $start :: $end :: $impact_type :: $cso_impact_duration :: $percentage_impacted :: $finalized_time\n";
										} else {												// overlap
											$outage = round(($to - $from)/60);
											$non_overlap = $outage - $overlap;					// watch for rounding errors.
	
											// get the non-overlap mins then multiplies with the percent impact.
											$new_min = $non_overlap * ($percentage_impacted / 100);
		
											$attributed[$s_name][$rc_categ] += $new_min;
	
											$from = min($from, $from_prev); 
											$to = max($to, $to_prev);
											#print "$rc_categ :: $start :: $end :: $impact_type :: $cso_impact_duration :: $percentage_impacted% :: $non_overlap :: $new_min (OVERLAP)\n";
										}
									} else {													// first record in group of more than 1 record.
										$attributed[$s_name][$rc_categ] += $finalized_time;
	
										#print "\n1ST : ";
										#print "$rc_categ :: $start :: $end :: $impact_type :: $cso_impact_duration :: $percentage_impacted% :: $finalized_time\n";
									}
									$from_prev = $from;
									$to_prev = $to;
								} else {														// if there is only 1 record in array
									$attributed[$s_name][$rc_categ] += $finalized_time;
	
									#print "\n1ST : ";
									#print "$rc_categ :: $start :: $end :: $impact_type :: $cso_impact_duration :: $percentage_impacted% :: $finalized_time\n";
								}
							}
						}
					}
	
				#print_r($attributed);

				#################################################################################################
				# Add rcc_id records to database. 
				#
                foreach ($attributed as $s_group_and_service=>$item) {
                    foreach ($item as $key=>$value) {

                        $query ="SELECT * FROM rootcause_INCIDENT_ANALYSIS_NEW
                                 WHERE
                                    date = '$mth' AND
                                    qtr = '$qtr' AND
                                    group_and_services = '$s_group_and_service' AND
                                    rc_flag = 'RCC' AND
                                    rc_categ_id = '" . $map_vnoc_tag_to_zendesk_desc[$key] . "'";

                        if (!$result = $mysqli->query($query)) { die("Query Failed: [" . $mysqli->error . "]\n"); }

                        if ($result->num_rows >  0) {
                            $query = "UPDATE rootcause_INCIDENT_ANALYSIS_NEW
                                      SET rc_categ_mins = $value
                                      WHERE
                                          date = '$mth' AND
                                          qtr = '$qtr' AND
                                          group_and_services = '$s_group_and_service' AND
                                          rc_flag = 'RCC' AND
                                          rc_categ_id = '" . $map_vnoc_tag_to_zendesk_desc[$key] . "'";
                            if (!$result = $mysqli->query($query)) { die("Query Failed: [" . $mysqli->error . "]\n"); }
                        } else {

							$query3 = "INSERT INTO rootcause_INCIDENT_ANALYSIS_NEW (date, qtr, group_and_services, rc_flag, rc_categ_id, rc_categ_mins) VALUES ";
							$query3 .= "('" . $mth . "','" . $qtr . "','" . $s_group_and_service . "','RCC','" . $map_vnoc_tag_to_zendesk_desc[$key] . "',$value);";
							#print "$query3\n";
							$result3 = mysqli_query($mysqli, $query3);
							if(!$result3) { echo mysqli_error() . $query3 . PHP_EOL; }  
						}
					}
				}

######################################
				} # END OF INNER FOREACH()
			}
		}
} # END OF FISCAL DATES WHILE() LOOP
######################################

$mysqli->close();

?>
