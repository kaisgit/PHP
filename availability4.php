<?php
error_reporting(E_ALL);
require_once "/home/admin/zendesk/zendesk_api_key.php";
require_once "/home/admin/zendesk/db_connect_scexecdash.php";
date_default_timezone_set('UTC');		//All fields with dates in zendesk tickets are all in UTC.

$checked_services = array();
$checked_rcc = array();
$outage_duration = array(array(array(array())));
$ids = array(array(array(array())));


/*echo "-------------------------------------------------------------------------------\n";
echo "Date\t\tDuration\tImpact%\t\tAvailability%\t\tGroup\n";
echo "-------------------------------------------------------------------------------\n";*/
$query_result = mysql_query("SELECT DISTINCT `root_cause_cat_group` FROM `availability_iaas22` WHERE `cso_impact_type` LIKE '%Outage%'");
while($row = mysql_fetch_assoc($query_result)) 
{
	$rcc_group = $row['root_cause_cat_group'];
	if(!in_array($rcc_group, $checked_rcc))
	{
		array_push($checked_rcc,$rcc_group);
		$query_result2 = mysql_query("SELECT DISTINCT `cso_start_date_only` FROM `availability_iaas22` WHERE `root_cause_cat_group`='".$rcc_group."' AND `cso_impact_type` LIKE '%Outage%' ORDER BY `cso_start_date_only`");
		while($row2 = mysql_fetch_assoc($query_result2)) 
		{
			$start_date = $row2['cso_start_date_only'];
			$query_result4 = mysql_query("SELECT DISTINCT `service_name` FROM `availability_iaas22` WHERE `root_cause_cat_group`='".$rcc_group."' AND `cso_start_date_only`='".$start_date."' AND `cso_impact_type` LIKE '%Outage%'");
			$num_of_service_rows4 = mysql_num_rows($query_result4);
			while($row4 = mysql_fetch_assoc($query_result4)) 
			{
			$service = $row4['service_name'];
			$query_result3 = mysql_query("SELECT * FROM `availability_iaas22` WHERE `root_cause_cat_group`='".$rcc_group."' AND `service_name`='".$service."' AND `cso_start_date_only`='".$start_date."' AND `cso_impact_type` LIKE '%Outage%' ORDER BY `cso_start_date` ASC, `percent_impact` ASC");
			$num_of_service_rows = mysql_num_rows($query_result3);
			$last_checked_end_time = "00:00:00";
			$last_checked_percent_impact = "-9999999";
			$last_checked_service = "null";
			while($row3 = mysql_fetch_assoc($query_result3)) 
			{
				$id = $row3['incident_id'];
				$service = $row3['service_name'];
				$start_date_value = strtotime($row3['cso_start_date']);
				$start_time = date('H:i:s',$start_date_value);
				$end_date_value = strtotime($row3['cso_end_date']);
				$end_time = date('H:i:s',$end_date_value);
				$cso_duration_min = $row3['cso_duration_min'];
				/*if ($cso_duration_min == "1440")
				{
					$cso_duration_min = "1439";
				}*/
				$percent_impact = $row3['percent_impact'];
				if(!isset($percent_impact) OR empty($percent_impact) OR $percent_impact == NULL OR $percent_impact == "null" OR $percent_impact == "" OR $percent_impact == " ")
				{
					$percent_impact = "null";
				}
				if($last_checked_end_time <= $start_time)
				{
					if(!isset($outage_duration[$rcc_group][$start_date][$service][$percent_impact]))
					{
						$outage_duration[$rcc_group][$start_date][$service][$percent_impact] = $cso_duration_min;
					}
					else
					{
						$outage_duration[$rcc_group][$start_date][$service][$percent_impact] += $cso_duration_min;
					}
					if(!isset($ids[$rcc_group][$start_date][$service][$percent_impact]))
					{
						$ids[$rcc_group][$start_date][$service][$percent_impact] = $id;
					}
					else
					{
						$ids[$rcc_group][$start_date][$service][$percent_impact] = $ids[$rcc_group][$start_date][$service][$percent_impact].", ".$id;
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
							if(!isset($outage_duration[$rcc_group][$start_date][$service][$percent_impact]))
							{
								$outage_duration[$rcc_group][$start_date][$service][$percent_impact] = $cso_duration_min;
							}
							else
							{
								$outage_duration[$rcc_group][$start_date][$service][$percent_impact] += $cso_duration_min;
							}
							if(!isset($ids[$rcc_group][$start_date][$service][$percent_impact]))
							{
								$ids[$rcc_group][$start_date][$service][$percent_impact] = $id;
							}
							else
							{
								$ids[$rcc_group][$start_date][$service][$percent_impact] = $ids[$rcc_group][$start_date][$service][$percent_impact].", ".$id;
							}
						}
						else
						{
							if(!isset($outage_duration[$rcc_group][$start_date][$service][$percent_impact]))
							{
								$outage_duration[$rcc_group][$start_date][$service][$percent_impact] = $diff_min;
							}
							else
							{
								$outage_duration[$rcc_group][$start_date][$service][$percent_impact] += $diff_min;
							}
							if(!isset($ids[$rcc_group][$start_date][$service][$percent_impact]))
							{
								$ids[$rcc_group][$start_date][$service][$percent_impact] = $id;
							}
							else
							{
								$ids[$rcc_group][$start_date][$service][$percent_impact] = $ids[$rcc_group][$start_date][$service][$percent_impact].", ".$id;
							}
						}
					}
					else
					{
						if(!isset($outage_duration[$rcc_group][$start_date][$service][$percent_impact]))
						{
							$outage_duration[$rcc_group][$start_date][$service][$percent_impact] = $cso_duration_min;
						}
						else
						{
							$outage_duration[$rcc_group][$start_date][$service][$percent_impact] += $cso_duration_min;
						}
						if(!isset($ids[$rcc_group][$start_date][$service][$percent_impact]))
						{
							$ids[$rcc_group][$start_date][$service][$percent_impact] = $id;
						}
						else
						{
							$ids[$rcc_group][$start_date][$service][$percent_impact] = $ids[$rcc_group][$start_date][$service][$percent_impact].", ".$id;
						}
					}
				}
				
				$last_checked_end_time = $end_time;
				$last_checked_percent_impact = $percent_impact;
				$last_checked_service = $service;
			}
			}
		}
	}
}

$rcc_groups = array("ccm_other","ccm_client_app","ccm_infra","ccm_sj","adc_other","adc_client_app","adc_infra","adc_sj","dps_other","dps_client_app","dps_infra","dps_sj","sj_infra","sc_client_app","sj_other","janus_client_app","c_combined","a_combined","d_combined","js_combined");
$query_string5 = "SELECT MIN(`cso_start_date`) FROM `availability_iaas22`";
$query_result5 = mysql_query($query_string5);
$row5 = mysql_fetch_row($query_result5);
$last_runtime = explode(" ", $row5[0]);
//$last_runtime[0] = '2014-01-01';
//$last_runtime[0] = date('Y-m-d',strtotime("2013-06-01"));
$runtime = $last_runtime[0];

foreach($rcc_groups as $key => $value)
{
	while ($last_runtime[0] <= date('Y-m-d'))
	{	
		if($last_runtime[0] > '2013-05-31')
		{
		$query_result6 = mysql_query("SELECT * FROM `availability_iaas22` WHERE `cso_start_date_only` = '".$last_runtime[0]."' AND `rcc_cso_root_cause_category`='".$rcc_groups[$key]."'");
		$row6 = mysql_fetch_assoc($query_result6);
		$num_results = mysql_num_rows($query_result6); 
		if ($num_results <= 0)
		{
		$query_string3 = "INSERT INTO `availability_iaas22` (`cso_start_date_only`,`rcc_cso_root_cause_category`,`availability_rcc`) values ('" .$last_runtime[0]."','".$rcc_groups[$key]. "','100.00')";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
			{	
				echo $query_string3 . mysql_error() . PHP_EOL ; 
			}
		}
		else
		{
			$query_string6 = "UPDATE `availability_iaas22` SET `availability_rcc` = '100.00' WHERE `cso_start_date_only` = '".$last_runtime[0]."' AND `rcc_cso_root_cause_category` = '".$rcc_groups[$key]."'";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
		}
		$last_runtime[0] = date('Y-m-d',strtotime("+1 day", strtotime($last_runtime[0])));
	}
	//$last_runtime = explode(" ", $row5[0]);
	$last_runtime[0] = $runtime;
	//$last_runtime[0] = '2014-01-01';
	//$last_runtime[0] = date('Y-m-d',strtotime("-19 weeks"));

}

foreach($outage_duration as $rcc => $value3)
{
foreach($value3 as $start_date2 => $value)
{
	$outage_per_day_per_service = "null";
	$service_imp = "null";
	$service_count = 0;
	foreach($value as $service2 => $value2)
	{	
		//$service_count++;
		if($rcc == "c_combined")
		{
		$service_count=23;
		}
		elseif($rcc=="js_combined")
		{
		$service_count=12;
		}
		elseif($rcc=="d_combined")
		{
		$service_count=11;
		}
		elseif($rcc=="a_combined")
		{
		$service_count=9;
		}
		else
		{
		$service_count++;
		}
		$outage2 = "null";
		//echo "\n".$start_date2."\t";
		foreach($value2 as $percent_impact2 => $outage)
		{
			if(isset($outage_per_day_per_service) AND $outage_per_day_per_service != "null")
			{
				//echo " && ".$outage."\t\t".$percent_impact2.", ";
				if($percent_impact2 == "null")
				{
					$outage_per_day_per_service += ((100/100) * $outage);
				}
				else
				{
					$outage_per_day_per_service += (($percent_impact2/100) * $outage);
				}
				$outage2 += $outage;
			}
			else
			{
				//echo $outage."\t\t".$percent_impact2;
				if($percent_impact2 == "null")
				{
					$outage_per_day_per_service = ((100/100) * $outage);
				}
				else
				{
					$outage_per_day_per_service = ($percent_impact2/100) * $outage;
				}
				$outage2 = $outage;
			}
			
		}
	}
	//$avail = round((1-((($outage_per_day_per_service)+((0/100)*(1440-$outage2)))/($outage2 + (1440-$outage2))))*100,3);
	$avail = round((1-($outage_per_day_per_service/(1440*$service_count)))*100,4);
	//echo "\t\t".$avail."\t\t".$rcc."\n";
		if($avail == NULL OR empty($avail) OR $avail == "" OR $avail == " " OR $avail == "null" OR !isset($avail))
		{
			if($avail != 0)
			{
				$query_string4 = "UPDATE `availability_iaas22` SET `availability_rcc` = NULL WHERE `cso_start_date_only` = '".$start_date2."' AND `rcc_cso_root_cause_category` = '".$rcc."'";
			}
			else
			{
				$query_string4 = "UPDATE `availability_iaas22` SET `availability_rcc` = '" .$avail. "' WHERE `cso_start_date_only` = '".$start_date2."' AND `rcc_cso_root_cause_category` = '".$rcc."'";
			}
		}
		else
		{
			$query_string4 = "UPDATE `availability_iaas22` SET `availability_rcc` = '" .$avail. "' WHERE `cso_start_date_only` = '".$start_date2."' AND `rcc_cso_root_cause_category` = '".$rcc."'";
		}
		//echo $query_string4."\n";
		$query_result4 = mysql_query($query_string4);
		if(!$query_result4) 
		{	
			echo $query_string4 . mysql_error() . PHP_EOL ; 
		}
}
}

?>