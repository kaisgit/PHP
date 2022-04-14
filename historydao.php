<?php
//----------------//
// SKMS WEB API   //
// Object: CmrDao //
// Method: search //
//----------------//
require_once(dirname(__FILE__) . '/SkmsWebApiClient.php');
require_once(dirname(__FILE__) . '/SkmsWebApiCredentials.php');
require_once(dirname(__FILE__) . '/db_connect.php');

$last_updated = date("Y-m-d",strtotime("1 week"));

$api->disableSslChainVerification();
$total_num_of_pages = 1;
$record_type = 'CMR'; 
$parameters = "cmr_id,
    cmr_state_id,
    completion_status,
    creation_datetime,
    priority,
    function_id,
    change_executor_user_id,
    maintenance_window_announced,
    business_justification,
    implementation_plan,
    function.name,
    service.name, 
    service.service_id, 
    service.qos_catalogue_id,
    service.service_type_id,
    maintenance_window.start_date,
    maintenance_window.end_date,
    maintenance_window.maintenance_window_id,
    change_executor_user.full_name";
for($i = 1; $i<=$total_num_of_pages; $i++){
$param_arr = Array(
	// Searches for load balancer pools where the name starts with 'sitecat' : change_executor_user.full_name
    "query" => "SELECT history_datetime, action_label.value, field_name,old_value,new_value  WHERE subject_type_label.value='CMR' AND history_datetime >= '".$last_updated." 00:00:00' PAGE ".$i.", 500",
);
if($api->sendRequest('CmrDao', 'search', $param_arr) == true) {
	$response_arr = $api->getResponseArray();
    //print_r($response_arr);
    //get num of pages for reading through pages
    if(isset($response_arr['data']['paging_info']) && is_array($response_arr['data']['paging_info']))
    {
        if(isset($response_arr['data']['paging_info']['last_page'])) { $total_num_of_pages = $response_arr['data']['paging_info']['last_page']; }
    }
	if(isset($response_arr['data']['results']) && is_array($response_arr['data']['results'])) {
		$result_counter = 0;
		foreach($response_arr['data']['results'] as $row) {
			++$result_counter;
            $cmr_id = NULL;
            $cmr_state_id = NULL;
            $completion_status = NULL;
            $creation_datetime = NULL;
            $priority = NULL;
            
            $function_id = NULL;
            $change_executor_user_id = NULL;
            $maintenance_window_announced = NULL;
            $business_justification = NULL;
            $implementation_plan = NULL;
            
            $function_name = NULL;
            $maintenance_window_start_date = NULL;
            $maintenance_window_end_date = NULL;
            $maintenance_window_maintenance_window_id = NULL;
            $change_executor_user_full_name = NULL;
            
            $service_name = NULL;
            $service_service_id = NULL;
            $service_qos_catalogue_id = NULL;
            $service_service_type_id = NULL;
            
            if(isset($row['cmr_id'])){ $cmr_id = $row['cmr_id']; }
			if(isset($row['cmr_state_id'])){ $cmr_state_id = $row['cmr_state_id']; }
            if(isset($row['completion_status'])){ $completion_status = $row['completion_status']; }
            if(isset($row['creation_datetime'])){ $creation_datetime = $row['creation_datetime']; }
            if(isset($row['priority'])){ $priority = $row['priority']; }
            
            if(isset($row['function_id'])){ $function_id = $row['function_id']; }
            if(isset($row['change_executor_user_id'])){ $change_executor_user_id = format_data($row['change_executor_user_id']); }
            if(isset($row['maintenance_window_announced'])){ $maintenance_window_announced = $row['maintenance_window_announced']; }
            if(isset($row['business_justification'])){ $business_justification = format_data($row['business_justification']); }
            if(isset($row['implementation_plan'])){ $implementation_plan = format_data($row['implementation_plan']); }
            
            if(isset($row['function.name'])){ $function_name = format_data($row['function.name']); }
            if(isset($row['maintenance_window.start_date'])){ $maintenance_window_start_date = $row['maintenance_window.start_date']; }
            if(isset($row['maintenance_window.end_date'])){ $maintenance_window_end_date = $row['maintenance_window.end_date']; }
            if(isset($row['maintenance_window.maintenance_window_id'])){ $maintenance_window_maintenance_window_id = $row['maintenance_window.maintenance_window_id']; }
            if(isset($row['change_executor_user.full_name'])){ $change_executor_user_full_name = format_data($row['change_executor_user.full_name']); }
            
            if(isset($row['service']))
            { 
                foreach($row['service'] as $index) 
                {
                    foreach($index as $key => $value) 
                    {
                        if(isset($index['name'])) { $service_name = format_data($index['name']); } 
                        if(isset($index['service_id'])) { $service_service_id = format_data($index['service_id']); } 
                        if(isset($index['qos_catalogue_id'])) { $service_qos_catalogue_id = $index['qos_catalogue_id']; } 
                        if(isset($index['service_type_id'])) { $service_service_type_id = $index['service_type_id']; } 
                        $query = "INSERT INTO `cmr_api_dump`"; 
                        $query .= "(`id`,
                        `cmr_state_id`,
                        `completion_status`,
                        `creation_datetime`,
                        `priority`,
                        `function_id`,
                        `change_executor_user_id`,
                        `maintenance_window_announced`,
                        `business_justification`,
                        `implementation_plan`,
                        `function`,
                        `name`,
                        `service_id`,
                        `qos_catalogue_id`,
                        `service_type_id`,
                        `start_date`,
                        `end_date`,
                        `maintenance_window_id`,
                        `change_executor_user_name`,
                        `type`)";
                        $query .= " VALUES ('".$cmr_id."',"; 
                        $query .= "'".$cmr_state_id."',";
                        $query .= "'".$completion_status."',";
                        $query .= "'".$creation_datetime."',";
                        $query .= "'".$priority."',";
                        $query .= "'".$function_id."',";
                        $query .= "'".$change_executor_user_id."',";
                        $query .= "'".$maintenance_window_announced."',";
                        $query .= "'".$business_justification."',";
                        $query .= "'".$implementation_plan."',";
                        $query .= "'".$function_name."',";
                        $query .= "'".$service_name."',";
                        $query .= "'".$service_service_id."',";
                        $query .= "'".$service_qos_catalogue_id."',";
                        $query .= "'".$service_service_type_id."',";
                        $query .= "'".$maintenance_window_start_date."',";
                        $query .= "'".$maintenance_window_end_date."',";
                        $query .= "'".$maintenance_window_maintenance_window_id."',";
                        $query .= "'".$change_executor_user_full_name."',";
                        $query .= "'".$record_type."')";
                        $link = connect_to_db();
                        $result = mysql_query($query);
                        if(!$result) { echo $query.mysql_error().PHP_EOL; }
                        mysql_close($link);
                    }
                }
            } 
		}
	} else {
		echo "No cmr data was returned.\n";
	}
} else {
 	echo "ERROR:\n";
 	echo "   STATUS: " . $api->getResponseStatus() . "\n";
 	echo "   TYPE: " . $api->getErrorType() . "\n";
 	echo "   MESSAGE: " . $api->getErrorMessage() . "\n";
 	if(preg_match('/unable to json decode/i', $api->getErrorMessage())) {
 		echo "RESPONSE STRING:\n";
 		echo $api->getResponseString() . "\n";
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
	ini_set('mbstring.substitute_character', 32); 
  	$text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
  
  	return $text;
}

?>
