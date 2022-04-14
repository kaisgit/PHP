<?php 

error_reporting(E_ALL);
require_once "/home/admin/zendesk/zendesk_api_key.php";
require_once "/home/admin/zendesk/db_connect_scexecdash.php";
date_default_timezone_set('UTC');		//All fields with dates in zendesk tickets are all in UTC.

////////////////////////////////////////////////////////
// - This function splits each CSO ticket in 
// 	 zendesk_master_list table into separate days  
//   and inserts into the availability_per_day_combined table
// - CSO less than 1 day remains the same and is 
//   also inserted into the availability_per_day_combined table
////////////////////////////////////////////////////////

// truncate table to avoid duplicate data (for testing purpose only)
$truncate_result = mysql_query("TRUNCATE TABLE `availability_iaas22`");
if(!$truncate_result) 
{
	echo "TRUNCATE TABLE `availability_iaas22`" . PHP_EOL ; 
}
$query_result = mysql_query("SELECT `id`,`prob_id`,`cso_time_impact_start`,`cso_time_impact_end`,`cso_impact_duration`,`percentage_of_users_per_transaction_impacted`,`client_for_iaas_rollup`,`service_id`,`date_update`,`prob_service_id`,`problem_service_name` FROM `zendesk_approved_list` WHERE `status` != 'Deleted' AND `id` != '5785' AND `cso_time_impact_start` > '2013-05-31 23:59:59' AND `client_for_iaas_rollup` IS NOT NULL AND `cso_impact_type` IS NOT NULL && `cso_time_impact_start` IS NOT NULL ORDER BY `cso_time_impact_start`");
$new_record=array(array());
$num_rows = mysql_num_rows($query_result);
while($row = mysql_fetch_assoc($query_result)) 
{
	$t1 = strtotime($row['cso_time_impact_start']);
	$t2 = strtotime($row['cso_time_impact_end']);
	$dur_min = $row['cso_impact_duration'];
	$service = $row['service_id'];
	$client = $row['client_for_iaas_rollup'];
	$update_date = $row['date_update'];	
	$percent_impact = $row['percentage_of_users_per_transaction_impacted'];
	$rcc_service = $row['prob_service_id'];
	$rcc_service_name = $row['problem_service_name'];
	if(!isset($rcc_service) OR empty($rcc_service) OR $rcc_service == NULL OR $rcc_service == "null")
	{
		$rcc_service = NULL;
	}

	$end_t2 = $t2;
	while ($t1 < $t2) 
	{
		if (date('Y-m-d', $t1) == date('Y-m-d', $t2))
		{
			$time_diff_query = "SELECT TIMEDIFF('".date('H:i:s', $t2)."','".date('H:i:s', $t1)."')";
			$time_diff_result = mysql_query($time_diff_query);
			$row_value=mysql_fetch_array($time_diff_result);  
			$time_diff_value = $row_value["TIMEDIFF('".date('H:i:s', $t2)."','".date('H:i:s', $t1)."')"];
			//convert $time_diff_value from H:i:s to minutes
			$temp_arr = explode(":", $time_diff_value);
			if(count($temp_arr)!=3)	
			{
				$duration_min = "null";
			}
			else
			{
				$duration_min = ($temp_arr[0] * 60) + $temp_arr[1];
			}
			$time = $row['cso_impact_duration'];
			$duration_hours = date('H:i:s',strtotime($time));
			$hours = floor($time/60);
    		$minutes = $time%60;
    		if ($minutes < 10) 
    		{
        		$minutes = '0' . $minutes;
   			}
   			if($hours < 10)
   			{
   				$hours = '0' . $hours;
   			}
   			$seconds = '00';
   			$format ='%s:%s:%s';
   			$duration_hours2 = sprintf($format, $hours, $minutes, $seconds);
			$hours2 = floor($duration_min/60);
			$minutes2 = $duration_min%60;
			if ($minutes2 < 10) 
    		{
        		$minutes2 = '0' . $minutes2;
   			}
   			if($hours2 < 10)
   			{
   				$hours2 = '0' . $hours2;
   			}
   			$seconds2 = '00';
   			$format2 ='%s:%s:%s';
   			$duration_in_hours = sprintf($format2, $hours2, $minutes2, $seconds2);
			if ($dur_min != "0")
			{
			if (date('Y-m-d', strtotime($row['cso_time_impact_start'])) == date('Y-m-d', strtotime($row['cso_time_impact_end'])))
			{
				if ($duration_min == "1439")
				{
					$duration_min = "1440";
				}
				elseif ($duration_min > "1440")
				{
					$duration_min = "1440";
				}
				if ($row['cso_impact_duration'] > "1440")
				{
					$row['cso_impact_duration'] = "1440";
				}
				if($duration_min == $row['cso_impact_duration'])
				{	
					$query_string2 = "INSERT INTO `availability_iaas22` (`incident_id`,`cso_start_date`,`cso_end_date`,`cso_duration_min`,`percent_impact`,`service_name`,`cso_start_date_only`,`date_update`,`client_name`,`root_cause_service`) values ({$row['id']},'" .date('Y-m-d H:i:s', $t1)."','".date('Y-m-d H:i:s', $t2)."','".$duration_min."','".$percent_impact."','".$service."','" .date('Y-m-d', $t1)."','".$update_date."','".$client."','".$rcc_service."')";
				}
				else
				{
					$query_string2 = "INSERT INTO `availability_iaas22` (`incident_id`,`cso_start_date`,`cso_end_date`,`cso_duration_min`,`percent_impact`,`service_name`,`cso_start_date_only`,`date_update`,`client_name`,`root_cause_service`) values ({$row['id']},'" .date('Y-m-d H:i:s', $t1)."','".date('Y-m-d H:i:s', $t2)."','".$row['cso_impact_duration']."','".$percent_impact."','".$service."','" .date('Y-m-d', $t1)."','".$update_date."','".$client."','".$rcc_service."')";
				}
			}
			else
			{
				if ($duration_min == "1439")
				{
					$duration_min = "1440";
				}
				elseif ($duration_min > "1440")
				{
					$duration_min = "1440";
				}
				$query_string2 = "INSERT INTO `availability_iaas22` (`incident_id`,`cso_start_date`,`cso_end_date`,`cso_duration_min`,`percent_impact`,`service_name`,`cso_start_date_only`,`date_update`,`client_name`,`root_cause_service`) values ({$row['id']},'" .date('Y-m-d H:i:s', $t1)."','".date('Y-m-d H:i:s', $t2)."','".$duration_min."','".$percent_impact."','".$service."','" .date('Y-m-d', $t1)."','".$update_date."','".$client."','".$rcc_service."')";
			}
			//echo $query_string2 ."\n";
			$query_result2 = mysql_query($query_string2);
			if(!$query_result2) 
			{
				echo $query_string2 . mysql_error() . PHP_EOL ; 
			} // end if
			} // end if ($dur_min != "0")
			$t1 = strtotime('+24 hours', $t1);
		} // end if
		else
		{
			$t1_temp = explode(" " , date('Y-m-d H:i:s', $t1));
			$end_t2 = $t1_temp[0]. " 23:59:59";
			$time_diff_query = "SELECT TIMEDIFF('24:00:00','".date('H:i:s', $t1)."')";
			$time_diff_result = mysql_query($time_diff_query);
			$row_value=mysql_fetch_array($time_diff_result);  
			$time_diff_value = $row_value["TIMEDIFF('24:00:00','".date('H:i:s', $t1)."')"];
			$temp_array = explode(":", $time_diff_value);
			if(count($temp_array)!=3)	
			{
				$duration_min = "null";
			}
			else
			{
				$duration_min = ($temp_array[0] * 60) + $temp_array[1];
			}
			$hours2 = floor($duration_min/60);
			$minutes2 = $duration_min%60;
			if ($minutes2 < 10) 
    		{
        		$minutes2 = '0' . $minutes2;
   			}
   			if($hours2 < 10)
   			{
   				$hours2 = '0' . $hours2;
   			}
   			$seconds2 = '00';
   			$format2 ='%s:%s:%s';
   			$duration_in_hours = sprintf($format2, $hours2, $minutes2, $seconds2);
			if ($duration_min == "1439")
			{
				$duration_min = "1440";
			}
			elseif ($duration_min > "1440")
			{
				$duration_min = "1440";
			}
			
			if($dur_min < $duration_min)
			{
				$query_string3 = "INSERT INTO `availability_iaas22` (`incident_id`,`cso_start_date`,`cso_end_date`,`cso_duration_min`,`percent_impact`,`service_name`,`cso_start_date_only`,`date_update`,`client_name`,`root_cause_service`) values ({$row['id']},'" .date('Y-m-d H:i:s', $t1)."','".date('Y-m-d H:i:s', $t2)."','".$dur_min."','".$percent_impact."','".$service."','" .date('Y-m-d', $t1)."','".$update_date."','".$client."','".$rcc_service."')";
				$dur_min = "0";
			}
			elseif($dur_min > $duration_min)
			{
				$query_string3 = "INSERT INTO `availability_iaas22` (`incident_id`,`cso_start_date`,`cso_end_date`,`cso_duration_min`,`percent_impact`,`service_name`,`cso_start_date_only`,`date_update`,`client_name`,`root_cause_service`) values ({$row['id']},'" .date('Y-m-d H:i:s', $t1)."','".$t1_temp[0]." 23:59:59','".$duration_min."','".$percent_impact."','".$service."','" .date('Y-m-d', $t1)."','".$update_date."','".$client."','".$rcc_service."')";
			}
			else
			{
				$query_string3 = "INSERT INTO `availability_iaas22` (`incident_id`,`cso_start_date`,`cso_end_date`,`cso_duration_min`,`percent_impact`,`service_name`,`cso_start_date_only`,`date_update`,`client_name`,`root_cause_service`) values ({$row['id']},'" .date('Y-m-d H:i:s', $t1)."','".date('Y-m-d H:i:s', $t2)."','".$dur_min."','".$percent_impact."','".$service."','" .date('Y-m-d', $t1)."','".$update_date."','".$client."','".$rcc_service."')";
				$dur_min = "0";
			}
			$query_result3 = mysql_query($query_string3);
			//echo "\n".$query_string3 ."\n";
			if(!$query_result3) 
			{
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			} // end if
			//echo $end_t2."\t";
			
			//echo $time_diff_value."\t".$duration_min."\t".$duration_in_hours."\n"; 
			$timestamp = strtotime($end_t2);
			$beginOfDay = strtotime("midnight", $timestamp);
			$endOfDay   = strtotime("tomorrow", $beginOfDay); 
			$t1 = $endOfDay;			
   		} //end else
	} // end while $t1 < $t2
} // end while $row
?>
