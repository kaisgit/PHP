<?php
error_reporting(E_ALL);
require_once "/home/admin/zendesk/zendesk_api_key.php";
require_once "/home/admin/zendesk/db_connect_scexecdash.php";
date_default_timezone_set('UTC');		//All fields with dates in zendesk tickets are all in UTC.

$servicenames = array();
$ticket_field_id = 21686117;// service types
$clients = array("ccm","adc","dps","sc","janus","sj");
foreach($clients as $client)
{
	$query_result_2 = mysql_query("SELECT * FROM `tagger_ticket_fields` WHERE `Ticket Field ID` = ".$ticket_field_id." AND `client_for_iaas_rollup` = '".$client."'");
	while ($row_2 = mysql_fetch_assoc($query_result_2)) 
	{
		array_push($servicenames, $row_2['vnoc_service_wf_id']);
		$new_service[$row_2['vnoc_service_wf_id']] = $row_2['vnoc_service_wf_descr'];
	}
}
$new_service['acrobat_com']="Acrobat.com";
$new_service['dps']="DPS";

$checked_services = array();
$outage_duration = array(array(array()));
$ids = array(array(array()));


/*echo "-------------------------------------------------------------------------------------------------------------\n";
echo "Date\t\tDuration\tImpact%\t\tAvailability%\t\tService\t\t\tIncident ID\n";
echo "-------------------------------------------------------------------------------------------------------------\n";*/
$query_result = mysql_query("SELECT DISTINCT `service_name` FROM `availability_iaas22` WHERE `cso_impact_type` LIKE '%Outage%'");
while($row = mysql_fetch_assoc($query_result)) 
{
	$service = $row['service_name'];
	if(in_array($service, $servicenames))
	{
	if(!in_array($service, $checked_services))
	{
		array_push($checked_services,$service);
		$query_result2 = mysql_query("SELECT DISTINCT `cso_start_date_only` FROM `availability_iaas22` WHERE `service_name`='".$service."' AND `cso_impact_type` LIKE '%Outage%' ORDER BY `cso_start_date_only`");
		while($row2 = mysql_fetch_assoc($query_result2)) 
		{
			$start_date = $row2['cso_start_date_only'];
			$query_result3 = mysql_query("SELECT * FROM `availability_iaas22` WHERE `service_name`='".$service."' AND `cso_start_date_only`='".$start_date."' AND `cso_impact_type` LIKE '%Outage%' ORDER BY `cso_start_date` ASC, `percent_impact` ASC");
			$num_of_service_rows = mysql_num_rows($query_result3);
			$last_checked_end_time = "00:00:00";
			$last_checked_percent_impact = "-9999999";
			while($row3 = mysql_fetch_assoc($query_result3)) 
			{
				$id = $row3['incident_id'];
				$start_date_value = strtotime($row3['cso_start_date']);
				$start_time = date('H:i:s',$start_date_value);
				$end_date_value = strtotime($row3['cso_end_date']);
				$end_time = date('H:i:s',$end_date_value);
				$cso_duration_min = $row3['cso_duration_min'];
				//if ($cso_duration_min == "1440")
				//{
				//	$cso_duration_min = "1439";
				//}
				$percent_impact = $row3['percent_impact'];
				if(!isset($percent_impact) OR empty($percent_impact) OR $percent_impact == NULL OR $percent_impact == "null" OR $percent_impact == "" OR $percent_impact == " ")
				{
					if($percent_impact == "0")
					{
					$percent_impact = "0";
					}
					else
					{
					$percent_impact = "null";
					}
				}
				if($last_checked_end_time <= $start_time)
				{
					if(!isset($outage_duration[$service][$start_date][$percent_impact]))
					{
						$outage_duration[$service][$start_date][$percent_impact] = $cso_duration_min;
					}
					else
					{
						$outage_duration[$service][$start_date][$percent_impact] += $cso_duration_min;
					}
					if(!isset($ids[$service][$start_date][$percent_impact]))
					{
						$ids[$service][$start_date][$percent_impact] = $id;
					}
					else
					{
						$ids[$service][$start_date][$percent_impact] = $ids[$service][$start_date][$percent_impact].", ".$id;
					}
				}
				elseif($last_checked_end_time > $start_time AND $last_checked_end_time < $end_time)
				{
					if($last_checked_percent_impact == $percent_impact)
					{
						$diff1 = strtotime($end_time)-strtotime($last_checked_end_time);
						$diff_time = date('H:i:s',$diff1);
						$diff_time1 = explode(":",$diff_time);
						$diff_min = ($diff_time1[0]*60) + ($diff_time1[1]);
						if ($cso_duration_min < $diff_min)
						{
							if(!isset($outage_duration[$service][$start_date][$percent_impact]))
							{
								$outage_duration[$service][$start_date][$percent_impact] = $cso_duration_min;
							}
							else
							{
								$outage_duration[$service][$start_date][$percent_impact] += $cso_duration_min;
							}
							if(!isset($ids[$service][$start_date][$percent_impact]))
							{
								$ids[$service][$start_date][$percent_impact] = $id;
							}
							else
							{
								$ids[$service][$start_date][$percent_impact] = $ids[$service][$start_date][$percent_impact].", ".$id;
							}
						}
						else
						{
							if(!isset($outage_duration[$service][$start_date][$percent_impact]))
							{
								$outage_duration[$service][$start_date][$percent_impact] = $diff_min;
							}
							else
							{
								$outage_duration[$service][$start_date][$percent_impact] += $diff_min;
							}
							if(!isset($ids[$service][$start_date][$percent_impact]))
							{
								$ids[$service][$start_date][$percent_impact] = $id;
							}
							else
							{
								$ids[$service][$start_date][$percent_impact] = $ids[$service][$start_date][$percent_impact].", ".$id;
							}
						}
					}
					else
					{
						if(!isset($outage_duration[$service][$start_date][$percent_impact]))
						{
							$outage_duration[$service][$start_date][$percent_impact] = $cso_duration_min;
						}
						else
						{
							$outage_duration[$service][$start_date][$percent_impact] += $cso_duration_min;
						}
						if(!isset($ids[$service][$start_date][$percent_impact]))
						{
							$ids[$service][$start_date][$percent_impact] = $id;
						}
						else
						{
							$ids[$service][$start_date][$percent_impact] = $ids[$service][$start_date][$percent_impact].", ".$id;
						}
					}
				}
				
				$last_checked_end_time = $end_time;
				$last_checked_percent_impact = $percent_impact;
			}
		}
	}
	}
}
$query_string5 = "SELECT MIN(`cso_start_date`) FROM `availability_iaas22` WHERE `date_update` > '".date("Y-m-d",strtotime("-1 week"))."'";
$query_result5 = mysql_query($query_string5);
$row5 = mysql_fetch_row($query_result5);
$last_runtime = explode(" ", $row5[0]);
$last_runtime[0] = date('Y-m-d',strtotime("2015-01-31"));
$runtime = $last_runtime[0];
foreach($new_service as $key => $value)
{
	$query_result8 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".date("Y-m-d",strtotime("-2 days"))."' AND `service_id`='".$key."'");
	$row8 = mysql_fetch_assoc($query_result8);
	if($new_service[$key] != $row8['service_name'])
	{
		$query_string7 = "UPDATE `availability_per_month22` SET `service_name` = '".$new_service[$key]."' WHERE `service_id` = '".$key."'";
		$query_result7 = mysql_query($query_string7);
		if(!$query_result7) 
		{	
			echo $query_string7 . mysql_error() . PHP_EOL ; 
		}	
	}
	//echo "Service: ".$new_service[$key]."\n";
	//$start_date = date('Y-m-d',strtotime("2010-01-01"));
	while ($last_runtime[0] <= date('Y-m-d'))
	{	
		//echo "Date: ".$last_runtime[0]."\n";
		if($key == 'janus' && $last_runtime[0] < '2013-06-01')
		{
		$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		elseif($key == 'OZZY' && $last_runtime[0] < '2013-12-01')
		{
		$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		elseif($key == 'ccm' && $last_runtime[0] < '2013-07-01' && $last_runtime[0] > '2013-05-31')
		{
		$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		elseif($key == 'acrobat_com' && $last_runtime[0] < '2013-10-01' && $last_runtime[0] > '2013-05-31')
		{
		$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		elseif($key == 'dps' && $last_runtime[0] < '2013-07-01' && $last_runtime[0] > '2013-05-31')
		{
		$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		elseif($key == 'DEX_POLLY' && $last_runtime[0] > '2014-08-07')
		{
		$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		elseif($key == 'POSTOFFICE' OR $key == 'JANUS_ADMIN_UI' OR $key == 'JANUS_CUSTOMER_ADMIN_UI' OR $key == 'CC_DEVELOPER_PORTAL' OR $key == 'SC_SYNCSERVICE')
		{
		if($last_runtime[0] > '2014-06-13')
		{
		$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		}
		elseif($key == 'CC_IMAGING' OR $key == 'BEHANCE_PRISM' OR $key == 'CC_VIDEO' OR $key == 'CC_STORAGE' OR $key == 'DEX_REDHAWK')
		{
		if($last_runtime[0] > '2014-02-28')
		{
		$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		}
		elseif($key == 'DEX_FILL_SIGN' && $last_runtime[0] > '2014-03-31')
		{
		$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		elseif(stripos($key, "ADC_CLASSIC") !== false OR stripos($key, "DEX") !== false OR stripos($key, "BLUE_HERON") !== false OR stripos($key, "CPDF") !== false OR stripos($key, "device_central") !== false OR stripos($key, "ECHOSIGN") !== false OR stripos($key, "FORMSCENTRAL") !== false OR stripos($key, "ngdoc") !== false OR stripos($key, "vando") !== false)
		{
			if($last_runtime[0] > '2013-05-31' && $key != 'DEX_REDHAWK' && $key != 'DEX_FILL_SIGN')
			{
			 if ((ctype_lower($key) AND $last_runtime[0] < '2015-01-29') OR (ctype_upper($key))) {
			$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
			$row6 = mysql_fetch_assoc($query_result6);
			$num_results = mysql_num_rows($query_result6); 
			if ($num_results <= 0)
			{
				$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
				$query_result3 = mysql_query($query_string3);
				if(!$query_result3) 
				{	
					echo $query_string3 . mysql_error() . PHP_EOL ; 
				}
			}
			else
			{
				$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
				$query_result6 = mysql_query($query_string6);
				if(!$query_result6) 
				{	
					echo $query_string6 . mysql_error() . PHP_EOL ; 
				}
			}
			}
			}
		}
		elseif($key != 'ccm' && $key != 'janus' && $key != 'acrobat_com' && $key != 'dps' && $key != 'OZZY' && stripos($key, "ADC_CLASSIC") === false && $key != 'CC_IMAGING' && $key != 'BEHANCE_PRISM' && $key != 'CC_VIDEO' && $key != 'CC_STORAGE' && $key != 'DEX_REDHAWK' && $key != 'DEX_FILL_SIGN' && $key != 'POSTOFFICE' && $key != 'JANUS_ADMIN_UI' && $key != 'JANUS_CUSTOMER_ADMIN_UI' && $key != 'CC_DEVELOPER_PORTAL' && $key != 'SC_SYNCSERVICE' && $key != 'DEX_POLLY' && $last_runtime[0] > '2013-05-31')
		{
		 if ((ctype_lower($key) AND $last_runtime[0] < '2015-01-29') OR (ctype_upper($key))) {
		$query_result6 = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$last_runtime[0]."' AND `service_id`='".$key."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_per_month22` (`date`,`service_name`,`availability`,`service_id`) values ('" .$last_runtime[0]."','".$new_service[$key]. "','100.00','".$key."')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_per_month22` SET `availability` = '100.00' WHERE `date` = '".$last_runtime[0]."' AND `service_id` = '".$key."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		}
		$last_runtime[0] = date('Y-m-d',strtotime("+1 day", strtotime($last_runtime[0])));
	}
	//$last_runtime = explode(" ", $row5[0]);
	$last_runtime[0] = $runtime;
	//$last_runtime[0] = date('Y-m-d',strtotime("-19 weeks"));
}
foreach($outage_duration as $service2 => $value)
{
	foreach($value as $start_date2 => $value2)
	{
		$percent_count = 0;
		$temp_outage = 0;
		$temp_impact = 0;
		foreach($value2 as $percent_impact2 => $outage)
		{
			$percent_count++;
			//echo $start_date2."\t".$outage."\t\t".$percent_impact2."\t\t";
			if($percent_count == 1)
			{
			$temp_outage = $outage;
			if($percent_impact2 == "null")
			{
				$temp_impact = ((100/100) * $outage);
			}
			else
			{
				$temp_impact = (($percent_impact2/100) * $outage);
			}
			}
			else
			{
			$temp_outage += $outage;
			if($percent_impact2 == "null")
			{
				$temp_impact += ((100/100) * $outage);
			}
			else
			{
				$temp_impact += (($percent_impact2/100) * $outage);
			}	
			}
		}
		$avail = round((1-((($temp_impact)+((0/100)*(1440-$temp_outage)))/($temp_outage + (1440-$temp_outage))))*100,4);
		//echo $avail."\t\t".$service2."\t\t";
		//echo $ids[$service2][$start_date2][$percent_impact2]."\n";
		//echo "\n";
		$q = mysql_query("SELECT * FROM `availability_per_month22` WHERE `date` = '".$start_date2."' AND `service_id` = '".$service2."'");
		$n = mysql_num_rows($q);
		if ($n > 0){
		while($r = mysql_fetch_assoc($q)) 
		{
		
		$query_string4 = "UPDATE `availability_per_month22` SET `availability` = '" .$avail. "' WHERE `date` = '".$start_date2."' AND `service_id` = '".$service2."'";
		//echo $query_string4."\n";
		$query_result4 = mysql_query($query_string4);
		if(!$query_result4) 
		{	
			echo $query_string4 . mysql_error() . PHP_EOL ; 
		}
		}
		}
	}
}

?>
