<?php
//----------------//
// SKMS WEB API   //
// Object: CmrDao //
// Method: search //
//----------------//
require_once(dirname(__FILE__) . '/db_connect.php');
clear_tables();
$min_start_date = array();

$query = "SELECT DISTINCT
			id, cmr_state_id, completion_status, creation_datetime, priority, function_id, change_executor_user_id, maintenance_window_announced,
			business_justification, implementation_plan, start_date, end_date, maintenance_window_state_id, change_executor_user_name, function, completion_notes, s.service_name,
			s.service_type_id
		  FROM cmr_api_dump, cmr_service s
		  WHERE
			cmr_api_dump.id = s.cmr_id
			AND type = 'CMR'
			AND s.service_type_id >= 3
		  ORDER BY id, s.service_name";
$result = mysql_query($query);
if(!$result) { echo $query.mysql_error().PHP_EOL; }  

while ($row = mysql_fetch_assoc($result)) 
{
	$cmr_id = $row['id'];
	$service_name = $row['service_name'];
	$service_type_id = $row['service_type_id'];

	$q1 = "SELECT DISTINCT service_name, risk, impact, affected_type
		   FROM cmr_to_service
		   WHERE cmr_id = $cmr_id
		   AND service_name = '" . $service_name . "'";

		$r1 = mysql_query($q1);
		if(!$r1) { echo $q1.mysql_error().PHP_EOL; }
		while ($row2 = mysql_fetch_assoc($r1)) 
		{
			$risk = $row2['risk'];
			$impact = $row2['impact'];
			$affected_type = $row2['affected_type'];
	
			$q3 = "INSERT INTO `f_dme_change_management`(`id`,`cmr_state_id`,`completion_status`,`creation_datetime`,`priority`,`function_id`,`change_executor_user_id`,`maintenance_window_announced`,`business_justification`,`implementation_plan`,`start_date`,`end_date`,`change_executor_user_name`,`function`,`risk`,`impact`,`affected_type`,`name`,`type`,`service_type_id`,`completion_notes`,`maintenance_window_state_id`) VALUES ('".$cmr_id."','".$row['cmr_state_id']."','".$row['completion_status']."','".$row['creation_datetime']."','".$row['priority']."','".$row['function_id']."','".$row['change_executor_user_id']."','".$row['maintenance_window_announced']."','".format_data($row['business_justification'])."','".format_data($row['implementation_plan'])."','".$row['start_date']."','".$row['end_date']."','".$row['change_executor_user_name']."','".$row['function']."','".$risk."','".$impact."','".$affected_type."','".format_data($service_name)."','CMR','".$service_type_id."','".format_data($row['completion_notes'])."','".$row['maintenance_window_state_id']."')"; 
			$r3 = mysql_query($q3);
			if(!$r3) { echo $q3.mysql_error().PHP_EOL; }
		}

	$q2 = "SELECT MIN(`start_date`)
			FROM `cmr_api_dump` LEFT JOIN `cmr_to_service` ON `cmr_api_dump`.`id` = `cmr_to_service`.`cmr_id` 
			WHERE `service_name` = '".$service_name."' and `affected_type` = 'Direct' and `start_date` > '1980-01-01'"; // > unix start of timestamp
	$r2 = mysql_query($q2);
	if(!$r2) { echo $q2.mysql_error().PHP_EOL; }
	while ($row3 = mysql_fetch_assoc($r2)) 
	{
   		$min_start_date[$service_name] = $row3['MIN(`start_date`)'];
	}
}



connect_to_db();

$query = "SELECT distinct(`root_cause_service_id`) FROM `cloudops_db`.`qos_cso_data`";
$result = mysql_query($query);
if(!$result) { echo $query.mysql_error().PHP_EOL; }  
while ($row = mysql_fetch_assoc($result)) 
{
	$rc_id = $row['root_cause_service_id'];

	$q = "SELECT distinct * FROM `cloudops_skms`.`cmr_service` where `qos_catalogue_id` = '".$rc_id."' and service_type_id >= '1'";
	$r = mysql_query($q);
	if(!$r) { echo $q.mysql_error().PHP_EOL; }
	$num_of_rows = mysql_num_rows($r);
	if($num_of_rows > 0) 
	{
    	while ($row2 = mysql_fetch_assoc($r)) 
    	{
        	$service = $row2['service_name'];
        	$service_type_id = $row2['service_type_id'];
    	}

    $q1 = "SELECT * FROM `cloudops_db`.`qos_cso_data` WHERE `root_cause_service_id` = '".$rc_id."' AND `earliest_impact_start` >= '".$min_start_date[$service]."'";
    $r1 = mysql_query($q1);
    if(!$r1) { echo $q1.mysql_error().PHP_EOL; }
    while ($row3 = mysql_fetch_assoc($r1))
    {
    	$q2 = "INSERT INTO `cloudops_skms`.`f_dme_change_management`(`id`,`name`,`start_date`,`problem_id`,`cso_summary`,`short_term_fixes`,`permanent_solution`,`root_cause`,`root_cause_category`,`service_type_id`,`type`)";
    	$q2 .= " VALUES('".$row3['cso_id']."','".$service."','".$row3['earliest_impact_start']."','".$row3['problem_id']."','".$row3['cso_summary']."','".$row3['short_term_fixes']."','".$row3['permanent_solution']."','".$row3['root_cause']."','".$row3['root_cause_category']."','".$service_type_id."','CSO')";
    	$r2 = mysql_query($q2);
    	if(!$r2) { echo $q2.mysql_error().PHP_EOL; }
    }
	}
}

function format_data($text)
{
	$text = str_replace("," , "," , $text);
	$text = str_replace("–" , "-" , $text); //replace double hyphen with single hyphen
	$text = str_replace('"',"",$text); 
	$text = str_replace("'", " ", $text);
	$text = str_replace(array("\n", "\r"), ' ', $text); //remove break lines
	$text = preg_replace('/  */', ' ', $text); // Replace sequences of spaces with a single space
	$text = str_replace('“',"'",$text); 
	$text = str_replace('”',"'",$text); 
    $text = mysql_real_escape_string($text);
	ini_set('mbstring.substitute_character', 32); 
  	$text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
  
  	return $text;
}

function clear_tables()
{
    connect_to_skms_db();
    $query = "TRUNCATE `f_dme_change_management`";
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
}

?>
