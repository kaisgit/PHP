<?php
error_reporting(E_ALL);
require_once "/home/admin/zendesk/zendesk_api_key.php";
require_once "/home/admin/zendesk/db_connect_scexecdash.php";
date_default_timezone_set('UTC');		//All fields with dates in zendesk tickets are all in UTC.

// truncate table to avoid duplicate data (for testing purpose only)
$truncate_result = mysql_query("TRUNCATE TABLE `availability_iaas2`");
if(!$truncate_result) 
{
	echo "TRUNCATE TABLE `availability_iaas2`" . PHP_EOL ; 
}
		$query_string3 = "INSERT INTO `availability_iaas2`(`cso_start_date`,`cso_end_date`,`cso_duration_min`,`percent_impact`,`incident_id`,`service_name`,`availability_percent_per_service`,`cso_customer_impact`,`cso_impact_type`,`cso_region_affected`,`cso_root_cause_category`,`cso_root_cause`,`cso_permanent_solution`,`url`,`is_original_cso`,`cso_start_date_only`,`availability_by_client`,`date_update`,`client_name`,`client`,`root_cause_service`,`client_name_availability_tag`,`client_rcc`,`root_cause_cat_group`,`availability_rcc`,`rcc_cso_start_date`,`rcc_cso_end_date`,`rcc_cso_duration_min`,`rcc_incident_id`,`rcc_service_name`,`rcc_availability_percent_per_service`,`rcc_cso_customer_impact`,`rcc_cso_impact_type`,`rcc_cso_region_affected`,`rcc_cso_root_cause_category`,`rcc_cso_root_cause`,`rcc_cso_permanent_solution`,`rcc_is_original_cso`,`rcc_cso_start_date_only`,`rcc_date_update`,`duplicate`) (SELECT * FROM `availability_iaas22`)";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
		{	
			echo $query_string3 . mysql_error() . PHP_EOL ; 
		}
		$query_string3 = "INSERT INTO `availability_iaas2`(`cso_start_date_only`,`service_name_only`,`availability_rcc`,`service_id`) (SELECT `date`,`service_name`,`availability`,`service_id` FROM `availability_per_month22`)";
		$query_result3 = mysql_query($query_string3);
		if(!$query_result3) 
		{	
			echo $query_string3 . mysql_error() . PHP_EOL ; 
		}
		
		$ticket_field_id = 21686117;// service types
		$query_result_2 = mysql_query("SELECT * FROM `tagger_ticket_fields` WHERE `Ticket Field ID` = ".$ticket_field_id);
		while ($row_2 = mysql_fetch_assoc($query_result_2)) 
		{
			$ticket_fields_dropdown_name[$row_2['vnoc_service_wf_id']] = $row_2['vnoc_service_wf_descr'];
		}
		$query_result = mysql_query("SELECT * FROM `availability_iaas2` WHERE `incident_id` != 0 AND `incident_id` IS NOT NULL");
		while ($row = mysql_fetch_assoc($query_result)) 
		{
			$id = $row['incident_id'];
			$service_id = $row['service_name'];
			$rcc_service = $row['root_cause_service'];
			if(isset($ticket_fields_dropdown_name[$service_id]) AND !isset($ticket_fields_dropdown_name[$rcc_service]))
			{
			$query_string5 = "UPDATE `availability_iaas2` SET `service_details` = '".$ticket_fields_dropdown_name[$service_id]."' WHERE `incident_id` = '".$id."'";		
			$query_result5 = mysql_query($query_string5);
			if(!$query_result5) { echo $query_string5 . mysql_error() . PHP_EOL ; } 
			}
			elseif(!isset($ticket_fields_dropdown_name[$service_id]) AND isset($ticket_fields_dropdown_name[$rcc_service]))
			{
			$query_string5 = "UPDATE `availability_iaas2` SET `rcc_service_details` = '".$ticket_fields_dropdown_name[$rcc_service]."' WHERE `incident_id` = '".$id."'";		
			$query_result5 = mysql_query($query_string5);
			if(!$query_result5) { echo $query_string5 . mysql_error() . PHP_EOL ; } 
			}
			elseif(isset($ticket_fields_dropdown_name[$service_id]) AND isset($ticket_fields_dropdown_name[$rcc_service]))
			{
			$query_string5 = "UPDATE `availability_iaas2` SET `service_details` = '".$ticket_fields_dropdown_name[$service_id]."', `rcc_service_details` = '".$ticket_fields_dropdown_name[$rcc_service]."' WHERE `incident_id` = '".$id."'";		
			$query_result5 = mysql_query($query_string5);
			if(!$query_result5) { echo $query_string5 . mysql_error() . PHP_EOL ; } 
			}
		}
	//copy rcc_group to service_id	
$query_result = mysql_query("SELECT DISTINCT(`rcc_cso_root_cause_category`) FROM `availability_iaas2` WHERE `rcc_cso_root_cause_category` IS NOT NULL");
while($row = mysql_fetch_assoc($query_result)) 
{
	$update_string = "UPDATE `availability_iaas2` SET `service_id` = '".$row['rcc_cso_root_cause_category']."'";
	$update_string .= " WHERE `rcc_cso_root_cause_category` = '".$row['rcc_cso_root_cause_category']."'"; 
	$update_result = mysql_query($update_string);
		if(!$update_result)
		{
			$error_count++;
			$error_messages[] = mysql_error() . " :::: " . $update_string ;
			print_r(mysql_error() . " :::: " . $update_string);
		}
}
//prob_id
$query_result3 = mysql_query("SELECT DISTINCT(`incident_id`) FROM `availability_iaas2`");
while ($row3 = mysql_fetch_assoc($query_result3)) 
{
	$query_result4 = mysql_query("SELECT `id`,`prob_id`,`cso_impact_duration` FROM `zendesk_approved_list` WHERE `id` = '".$row3['incident_id']."'");
	while ($row4 = mysql_fetch_assoc($query_result4)) 
	{
		$query_result5 = mysql_query("SELECT `incident_id`,`prob_id` FROM `prob_id` WHERE `incident_id` = '".$row3['incident_id']."'");
		$num_results = mysql_num_rows($query_result5); 
		if ($num_results > 0)
		{
			$update_string = "UPDATE `prob_id` SET `prob_id` = '".$row4['prob_id']."', `cso_duration_min` = '".$row4['cso_impact_duration']."'";
			$update_string .= " WHERE `incident_id` = {$row3['incident_id']}"; 
			$update_result = mysql_query($update_string);
			if(!$update_result)
			{
				$error_messages[] = mysql_error() . " :::: " . $update_string ;
				print_r(mysql_error() . " :::: " . $update_string);
			}
		}
		else
		{
			$query_string6 = "INSERT INTO `prob_id` values ('" .$row3['incident_id']."','".$row4['prob_id']. "','".$row4['cso_impact_duration']."')";
			$query_result6 = mysql_query($query_string6);
			if(!$query_result6) 
			{	
				echo $query_string6 . mysql_error() . PHP_EOL ; 
			}
		}
	}
}
?>