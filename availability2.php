<?php
error_reporting(E_ALL);
require_once "/home/admin/zendesk/zendesk_api_key.php";
require_once "/home/admin/zendesk/db_connect_scexecdash.php";
date_default_timezone_set('UTC');		//All fields with dates in zendesk tickets are all in UTC.
////////////////////////////////////////////////////////
// This function calculates the availability % of each 
// root cause category under Shared Cloud for each day  
// and updates the availability_per_day_rcc table
////////////////////////////////////////////////////////
$rcc_groups = array(
    "application_bug" => "Client Application",
    "hardware_failure" => "Infrastructure",
    "internal_services" => "Infrastructure",
    "aws" => "Infrastructure",
    "vendor" => "Infrastructure",
    "application_design" => "Other",
    "procedural" => "Other",
    "human_error" => "Other",
    "process_noncompliance" => "Other",
    "demand_management" => "Other",
    "false_alert" => "Other",
    "security" => "Other",
    "unknown" => "Other"
);
$clients = array("ccm_other","ccm_client_app");

$clientsccm1 = array("ccm_other","ccm_client_app","ccm_infra","ccm_janus","ccm_sc","ccm_sj");
$clientsscjanus1 = array("sj_infra","sc_client_app","sj_other","janus_client_app","sj_infra","sj_other");

$clientsccm = array("ccm_janus","ccm_sc");
$clientsinfra = array("sj_infra");
$clientsother = array("sj_other");



$query_result6 = mysql_query("SELECT DISTINCT `incident_id` FROM `availability_iaas22`");
while($row6 = mysql_fetch_assoc($query_result6)) 
{
	$id = $row6['incident_id'];
	$row_count = 0;
	$date_start = array();
	$date_end = array();
	$dur = array();
	$percent_impact = array();
	$service_name = array();
	$is_orig = array();
	$date_start_only = array();
	$updated = array();
	$client_name = array();
	$rc_service = array();
	$query_result61 = mysql_query("SELECT * FROM `availability_iaas22` WHERE `incident_id` = {$id}");
	while($row61 = mysql_fetch_assoc($query_result61)) 
	{
	$row_count++;
	$date_start[$row_count] = $row61['cso_start_date'];
	$date_end[$row_count] = $row61['cso_end_date'];
	$dur[$row_count] = $row61['cso_duration_min'];
	$percent_impact[$row_count] = $row61['percent_impact'];
	$service_name[$row_count] = $row61['service_name'];
	$date_start_only[$row_count] = $row61['cso_start_date_only'];
	$updated[$row_count] = $row61['date_update'];
	$client_name[$row_count] = $row61['client_name'];
	$rc_service[$row_count] = $row61['root_cause_service'];
	}
	
	$query_result4 = mysql_query("SELECT * FROM `zendesk_approved_list` WHERE `id`='".$id."'");
	while($row4 = mysql_fetch_assoc($query_result4)) 
	{
	$url = $row4['url'];
	$outage_type = $row4['cso_impact_type'];
	$perm_soln = $row4['perm_solution_prob_id'];
	$rc = $row4['root_cause_prob_id'];
	$rcc = $row4['rcc_id'];
	$category = $row4['client_for_iaas_rollup'];
	if(in_array($category,$clientsccm))
	{
		$category = "ccm_sj"; // sj == shared cloud and janus
	}
	elseif(in_array($category,$clientsadc))
	{
		$category = "adc_sj";
	}
	elseif(in_array($category,$clientsdps))
	{
		$category = "dps_sj";
	}
	elseif(in_array($category,$clientsinfra))
	{
		$category = "sj_infra";
	}
	elseif(in_array($category,$clientsother))
	{
		$category = "sj_other";
	}
	
	if(in_array($category,$clientsccm1))
	{
		$category2 = "c_combined"; 
	}
	elseif(in_array($category,$clientsadc1))
	{
		$category2 = "a_combined";
	}
	elseif(in_array($category,$clientsdps1))
	{
		$category2 = "d_combined";
	}
	elseif(in_array($category,$clientsscjanus1))
	{
		$category2 = "js_combined";
	}
	else
	{
		$category2 = "Other";
	}
	
	$regions = $row4['cso_region_affected'];
	$impact_statement = $row4['cso_customer_impact'];
	$prob_id = $row4['prob_id'];

		$perm_soln = str_replace("," , "," , $perm_soln);
		$perm_soln = str_replace("–" , "-" , $perm_soln); //replace double hyphen with single hyphen
		$perm_soln = str_replace('"',"'",$perm_soln); 
		$perm_soln = str_replace("'", "", $perm_soln);
		$perm_soln = str_replace(array("\n", "\r"), ' ', $perm_soln); //remove break lines
		//$perm_soln = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $perm_soln); // Remove all characters except A-Z, a-z, 0-9, dots, hyphens and spaces	
		$perm_soln = preg_replace('/  */', ' ', $perm_soln); // Replace sequences of spaces with  with a single space
		$perm_soln = str_replace('“',"'",$perm_soln); 
		$perm_soln = str_replace('”',"'",$perm_soln); 
		ini_set('mbstring.substitute_character', 32); 
  		$perm_soln = mb_convert_encoding($perm_soln, 'ISO-8859-1', 'UTF-8'); 
	

		$rc = str_replace("," , "," , $rc);
		$rc = str_replace("–" , "-" , $rc); //replace double hyphen with single hyphen
		$rc = str_replace('"',"'",$rc); 
		$rc = str_replace("'", " ", $rc);
		$rc = str_replace(array("\n", "\r"), ' ', $rc); //remove break lines
		//$rc = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $rc); // Remove all characters except A-Z, a-z, 0-9, dots, hyphens and spaces	
		$rc = preg_replace('/  */', ' ', $rc); // Replace sequences of spaces with  with a single space
		$rc = str_replace('“',"'",$rc); 
		$rc = str_replace('”',"'",$rc);
		ini_set('mbstring.substitute_character', 32); 
  		$rc = mb_convert_encoding($rc, 'ISO-8859-1', 'UTF-8'); 
	
		$rcc = str_replace("," , "," , $rcc);
		$rcc = str_replace("–" , "-" , $rcc); //replace double hyphen with single hyphen
		$rcc = str_replace('"',"'",$rcc); 
		$rcc = str_replace("'", " ", $rcc);
		$rcc = str_replace(array("\n", "\r"), ' ', $rcc); //remove break lines
		//$rcc = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $rcc); // Remove all characters except A-Z, a-z, 0-9, dots, hyphens and spaces	
		$rcc = preg_replace('/  */', ' ', $rcc); // Replace sequences of spaces with a single space
		$rcc = str_replace('“',"'",$rcc); 
		$rcc = str_replace('”',"'",$rcc); 
		ini_set('mbstring.substitute_character', 32); 
  		$rcc = mb_convert_encoding($rcc, 'ISO-8859-1', 'UTF-8');
	
	
		$impact_statement = str_replace("," , "," , $impact_statement);
		$impact_statement = str_replace("–" , "-" , $impact_statement); //replace double hyphen with single hyphen
		$impact_statement = str_replace('"',"",$impact_statement); 
		$impact_statement = str_replace("'", " ", $impact_statement);
		$impact_statement = str_replace(array("\n", "\r"), ' ', $impact_statement); //remove break lines
		//$impact_statement = preg_replace('/[^A-Za-z0-9\. -]/', ' ', $impact_statement); // Remove all characters except A-Z, a-z, 0-9, dots, hyphens and spaces	
		$impact_statement = preg_replace('/  */', ' ', $impact_statement); // Replace sequences of spaces with a single space
		$impact_statement = str_replace('“',"'",$impact_statement); 
		$impact_statement = str_replace('”',"'",$impact_statement); 
		ini_set('mbstring.substitute_character', 32); 
  		$impact_statement = mb_convert_encoding($impact_statement, 'ISO-8859-1', 'UTF-8');

		$query_string5 = "UPDATE `availability_iaas22`  SET `cso_customer_impact` = '".$impact_statement."', `cso_impact_type` = '".$outage_type."', `cso_permanent_solution` = '".$perm_soln."', `cso_root_cause` = '".$rc."', `cso_root_cause_category` = '".$rcc."', `cso_region_affected` = '".$regions."', `url` = '".$url. "', `root_cause_cat_group` = '".$category."' WHERE `incident_id` = '".$id."'";		
		$query_result5 = mysql_query($query_string5);
		if(!$query_result5) { echo $query_string5 . mysql_error() . PHP_EOL ; } 
		foreach ($date_start as $key => $value)
		{
		$query_string31 = "INSERT INTO `availability_iaas22` (`incident_id`,`cso_start_date`,`cso_end_date`,`cso_duration_min`,`percent_impact`,`service_name`,`cso_start_date_only`,`date_update`,`client_name`,`root_cause_service`,`duplicate`) values ({$id},'" .$date_start[$key]."','".$date_end[$key]."','".$dur[$key]."','".$percent_impact[$key]."','".$service_name[$key]."','" .$date_start_only[$key]."','".$updated[$key]."','".$client_name[$key]."','".$rc_service[$key]."','Y')";
		$query_result31 = mysql_query($query_string31);
		if(!$query_result31) { echo $query_string31 . mysql_error() . PHP_EOL ; }
		}
		$query_string51 = "UPDATE `availability_iaas22`  SET `cso_customer_impact` = '".$impact_statement."', `cso_impact_type` = '".$outage_type."', `cso_permanent_solution` = '".$perm_soln."', `cso_root_cause` = '".$rc."', `cso_root_cause_category` = '".$rcc."', `cso_region_affected` = '".$regions."', `url` = '".$url. "', `root_cause_cat_group` = '".$category2."' WHERE `incident_id` = '".$id."' AND `duplicate` = 'Y'";		
		$query_result51 = mysql_query($query_string51);
		if(!$query_result51) { echo $query_string51 . mysql_error() . PHP_EOL ; } 
	} //end row4
} //end row6
?>
