<?php
error_reporting(E_ALL);
require_once "zendesk_api_key.php";
require_once "db_connect_scexecdash.php";
//require_once "cso_csv.php";
//require_once( __DIR__. "/../gooddata/script/mysql_dumper.php");
date_default_timezone_set('UTC');		//All fields with dates in zendesk tickets are all in UTC.

////////////////////////////////////////////////////////////////////
//	- This crontab function will grab/update CSO tickets in the last   
//	  4 weeks for services under CCM view through the API
//  - Result is updated in the report_on_cso_date table
//  - Post Mortem URL for security CSO does not exist
//  - Dropdown values will grab the corresponding names from
// 	  tagger_ticket_fields table
// 	- Root cause category, Root cause details and Resolution of
//	  the tickets will be those of the corresponding problem IDs,
//	  if actual CSO IDs have empty values
// 	- This crontab function will grab only partial and total outages
////////////////////////////////////////////////////////////////////

//$test_result = get_multiple_ticket_full_details(771);
//print_r($test_result);

//print_r(get_ticket_ids_from_view(34314147));	//DONE
//update_master_table_from_dump();	//DONE

//update_incremental_tickets();


do_zendesk_search();



function do_zendesk_search()
{
	//print_r("month = " . $month_arg . $year_arg); 
	$next_page_num=1;
	//$arg = strtolower($month_arg);
	$thirty_days = array();
	array_push($thirty_days, "april", "june", "september", "november");
	$thirty_one_days = array();
	array_push($thirty_one_days, "january", "march", "may", "july", "august", "october", "december");
	$month = array();
	$month['january'] = 01;
	$month['february'] = 02;
	$month['march'] = 03;
	$month['april'] = 04;
	$month['may'] = 05;
	$month['june'] = 06;
	$month['july'] = 07;
	$month['august'] = 08;
	$month['september'] = 09;
	$month['october'] = 10;
	$month['november'] = 11;
	$month['december'] = 12;
	
	$truncate_result = mysql_query("TRUNCATE TABLE `report_on_cso_data`");
	if(!$truncate_result) 
	{
		echo "TRUNCATE TABLE `report_on_cso_data`" . PHP_EOL ; 
	}
	$prob_ID_array = array();
	$id_string = array();
	$root_cause = array();
	$root_cause_cat = array();
	$resolution = array();
	
	do {
	$ticket_fields_dropdown_value = array();
	$ticket_fields_dropdown_name = array();
	//only for few service types
	$service_types = array("storm_cloud", "ccm_special_offers", "ccm_student_teacher_edition", "posa", "fulfillment_center_service", "ims", "renga", "business_catalyst", "ccm", "janus", "jem", "jil", "jim", "mcui", "ccm_retail_direct", "typekit","ets", "kuler", "membership_based_licensing_service", "postoffice", "shared_cloud", "vader", "dps_wf_app_building", "dps_wf_content_preview", "behance_cc", "behance_ans", "shared_cloud_hemlock", "shared_cloud_document_conversion", "shared_cloud_collaboration", "ans", "creative_cloud_video"); 
	//$service_types = array("shared_cloud"); 
	// Get custom field id - name translation
	$custom_column_name_list = array();
	$query_result = mysql_query("SELECT * FROM `custom_ticket_fields` WHERE `column_name_for_report_on_cso_data` is not null");
	while ($row = mysql_fetch_assoc($query_result)) 
	{
		$custom_column_name_list[$row['field_id']] = $row['column_name_for_report_on_cso_data'];
	}
//	print_r($custom_column_name_list); //die();
	$endpoint = "/search.json?page=" . $next_page_num ."&query=type:ticket%20created>" . date("Y-m-d",strtotime("-4 weeks"));
//	$endpoint = "/search.json?page=" . $next_page_num ."&query=type:ticket%20created>" . date("Y-m-d",strtotime("2013-04-30"));
	//echo ZDURL . $endpoint . PHP_EOL;
	$result = zendesk_curl_wrapper($endpoint);//print_r($result);
	if ($result['status']!=200 OR count($result['data']->results) < 1)	die($result['status']);
	$next_page = $result['data']->next_page;
	
	// To get the user friendly names for the dropdown values
	/*$endpoint2 = "/ticket_fields.json";
	$result2 = zendesk_curl_wrapper($endpoint2);
	if ($result2['status']!=200 OR count($result2['data']->ticket_fields) < 1)	die($result2['status']);
	//echo json_encode($result['data']);
	
	foreach ($result2['data']->ticket_fields as $ticket_field)
	{	
		$ticket_field_id = $ticket_field->id;
		$ticket_field_type = $ticket_field->type;
		if ($ticket_field_type == "tagger")
		{
			foreach ($ticket_field->custom_field_options as $zendesk_ticket_field_obj)
			{
				$ticket_fields_dropdown_name[$zendesk_ticket_field_obj->value] = $zendesk_ticket_field_obj->name;
				$ticket_fields_dropdown_value[$zendesk_ticket_field_obj->name] = $zendesk_ticket_field_obj->value;
				
			}
			
		}
	}*/
	$ticket_fields_dropdown_value = array();
	$ticket_fields_dropdown_name = array();
	$tagger_query_string = "SELECT * FROM `tagger_ticket_fields`";
	$tagger_query_string_result = mysql_query($tagger_query_string);
	if(!$tagger_query_string_result) 
	{
		echo mysql_error() . " :::: " . $tagger_query_string . PHP_EOL ; 
	}
	while ($row = mysql_fetch_assoc($tagger_query_string_result)) 
	{
		$ticket_fields_dropdown_value[$row['Ticket Field name']] = $row['Ticket Field value'];
		$ticket_fields_dropdown_name[$row['Ticket Field value']] = $row['Ticket Field name'];
	}
	//print_r($ticket_fields_dropdown_value);
	
	$error_count = 0;
	foreach ($result['data']->results as $ticket) 
	{
		//For non-custom fields
		$id = $ticket->id;
		//echo "ID IS :::" . $id . PHP_EOL;	
		$date_created = $ticket->created_at;
		
		//Get new ticket record
		$new_ticket_record = array();
		$new_ticket_record['Incident Report URL'] = "http://localhost/cloudops_social_pm/pm.php?ticketId={$id}" ;
		$new_ticket_record['Incident ID'] = $id;
		$new_ticket_record['Ticket created on'] = $date_created;
		$tags_array = array();
		
		$i = 0;
		foreach ($ticket->tags as $zendesk_tags_value)
		{
			$tags_array[$i] = $zendesk_tags_value;
			//$new_ticket_record['tags'] = $tags_value;
			//$size = count($ticket->tags);
			//echo "TAGS IS :::" . $tags_array[$i] . PHP_EOL;
			//echo "SIZE IS :::" . $size . PHP_EOL;
			$i++;
		}
		$tags_string = implode("; ", $tags_array);
		$pos = strripos($tags_string, "cso");
		$pos_problem_ID = strripos($tags_string, "problem_");
		$pos_security_cso = strripos($tags_string, "s_cso");
		if($pos !== false && $pos_problem_ID !== false)
		{
			$new_ticket_record['Tags'] = $tags_string;
			//echo "TAGS ARE :::" . $tags_string . PHP_EOL;
			foreach($tags_array as $string)
			{
 				if(strpos($string, "problem_") !== false) 
  				{	
  					$break = explode("_", $string);
    				$prob_ID_array[$id] = $break[1];
    				$id_string[$id] = $id;
    				break;
  				}
  				else
  				{
  				}
			}			
		}
		elseif($pos !== false && $pos_problem_ID === false)
		{
			$new_ticket_record['Tags'] = $tags_string;
			$prob_ID_array[$id] = "null";
			
		}
		else
		{
			$tags_array = array();
			$new_ticket_record['Tags'] = "null";
		}
	
		//For custom fields
		foreach ($ticket->custom_fields as $zendesk_field_obj)
		{
			$field_id = $zendesk_field_obj->id;
			$field_value = $zendesk_field_obj->value;	

			if (strlen($field_value) < 1 OR $field_value == "-" OR $field_value == " ")
				{
					$field_value = "null";
				}
			else
				{
					$field_value = str_replace("," , "," , $field_value);
					$field_value = str_replace("–" , "-" , $field_value); //replace double hyphen with single hyphen
					$field_value = str_replace('"',"'",$field_value); 
					$field_value = str_replace(array("\n", "\r"), ' ', $field_value); //remove break lines
					$field_value = str_replace('“',"'",$field_value); 
					$field_value = str_replace('”',"'",$field_value); 
					ini_set('mbstring.substitute_character', "32"); 
  					$field_value= mb_convert_encoding($field_value, 'ISO-8859-1', 'UTF-8'); 
				}
			
			if(isset($custom_column_name_list[$field_id]))
			{
				$new_ticket_record[$custom_column_name_list[$field_id]] = $field_value;
					//Exceptions... Converting impact duration from dd:mm:hh to minutes
				if ($custom_column_name_list[$field_id] == "CSO Impact Duration") 
				{	
					$temp_arr = explode(":", $field_value);
					
					if(count($temp_arr)!=3)	
						{
							$new_ticket_record[$custom_column_name_list[$field_id]] = "null";
						}
					else
						{
							$new_ticket_record[$custom_column_name_list[$field_id]] = ($temp_arr[0] * 1440) + ($temp_arr[1] * 60) + $temp_arr[2];
						}
				}
				//echo $custom_column_name_list[$field_id]." = ".$field_value."\n";
			}
		}
		//print_r($custom_column_name_list[$field_id]);
		//print_r($new_ticket_record);
		
		//delete for testing
		/*$query_delete = "DELETE FROM `report_on_cso_data`";
		$query_delete_result = mysql_query($query_delete);
		if(!$query_delete_result) echo mysql_error() . PHP_EOL ;*/
		
		
		
		// to filter
		foreach ($new_ticket_record as $column_name => $value)
		{
			if ($column_name == "Technical Service")
			{
				if(in_array($value, $service_types))
				{
				}
				else
				{	//clear all the values
					unset($prob_ID_array[$new_ticket_record['Incident ID']]);
					unset($id_string[$new_ticket_record['Incident ID']]);
					$column_name == " ";
					$new_ticket_record[$column_name] = "null";
					$new_ticket_record['Incident ID'] = "null";
				}
				
			}
			/* Commented off to remove checks for `is_approved`
			elseif ($column_name == "Incident ID")
			{
				$query_result = mysql_query("SELECT `id`,`is_approved`,`cso_impact_duration`,`approved_by` FROM `zendesk_approved_list` WHERE `id`={$value}");
				while ($row = mysql_fetch_assoc($query_result))
				{	
					$approved_flag = $row['is_approved']; 
					$approved_by = $row['approved_by']; 
					$duration_from_approved_table = $row['cso_impact_duration'];
					if ($approved_flag != 1)
					{
						$column_name == " ";
						$new_ticket_record[$column_name] = "null";
						$new_ticket_record['Incident ID'] = "null";
					}
					elseif ($approved_flag == 1 AND $new_ticket_record['CSO Impact Duration'] != $duration_from_approved_table AND $approved_by != NULL)
					{
						$new_ticket_record['CSO Impact Duration'] = $duration_from_approved_table;
					}
					elseif($approved_flag == 1 AND $approved_by == NULL)
					{
						$column_name == " ";
						$new_ticket_record[$column_name] = "null";
						$new_ticket_record['Incident ID'] = "null";	
					}
				}	
			} */
			elseif ($column_name == "Tags")
			{
				if($value == "null")
				{	//clear all the values
					unset($prob_ID_array[$new_ticket_record['Incident ID']]);
					unset($id_string[$new_ticket_record['Incident ID']]);
					$column_name == " ";
					$new_ticket_record[$column_name] = "null";
					$new_ticket_record['Incident ID'] = "null";
				}
			}
			elseif ($column_name == "Severity")
			{
				if($value == "s_cso" OR $pos_security_cso !== false)
				{	
					//no URL for security cso 
					$new_ticket_record['Incident Report URL'] = "No URL since this incident is a security CSO";
				}
			}
			elseif ($column_name == "Incident Start Date/Time")
			{
				if($value == "null" || $value < date("Y-m-d",strtotime("-4 weeks")))
				{
					$column_name == " ";
					$new_ticket_record[$column_name] = "null";
					$new_ticket_record['Incident ID'] = "null";	
				}
			}
			elseif ($column_name == "Outage Type")
			{
				if(stristr($value,'outage') === false)
				//if((stristr($value,'partial') === false) && (stristr($value,'total') === false) && (stristr($value,'degradat') === false))
				//if((stristr($value,'degradat') === false))
				{
					$column_name == " ";
					$new_ticket_record[$column_name] = "null";
					$new_ticket_record['Incident ID'] = "null";	
				}
			}
			/*elseif ($column_name == "Customer Impact Statement" OR $column_name == "Incident Root Cause Detail" OR $column_name == "Resolution")
			{
				$new_ticket_record[$column_name] = '\"'.$value.'\"';
			}*/
		}
		foreach($prob_ID_array as $key => $value)
		{
			$query_result = mysql_query("SELECT `id`,`cso_root_cause`,`cso_root_cause_category`,`cso_permanent_solution` FROM `zendesk_master_list` WHERE `id`={$value}");
			while ($row = mysql_fetch_assoc($query_result))
			{
				$root_cause[$key] = $row['cso_root_cause'];
				$root_cause_cat[$key] = $row['cso_root_cause_category'];
				$resolution[$key] = $row['cso_permanent_solution'];
			}
			if(mysql_num_rows($query_result) < 1)
			{
				$root_cause[$key] = "NULL";
				$root_cause_cat[$key] = "NULL";
				$resolution[$key] = "NULL";
			}
		}
		foreach ($new_ticket_record as $column_name => $value)
		{
			
			if ($column_name == "Incident Root Cause Category" && $new_ticket_record['Incident ID'] != "null")
			{
				if($value == "null")
				{
					$new_ticket_record[$column_name] = $root_cause_cat[$new_ticket_record['Incident ID']];
				}
			}
			elseif ($column_name == "Incident Root Cause Detail" && $new_ticket_record['Incident ID'] != "null")
			{
				if($value == "null")
				{
					$new_ticket_record[$column_name] = $root_cause[$new_ticket_record['Incident ID']];
					//echo $new_ticket_record[$column_name];
				}
			}
			elseif ($column_name == "Resolution" && $new_ticket_record['Incident ID'] != "null")
			{
				if($value == "null")
				{
					$new_ticket_record[$column_name] = $resolution[$new_ticket_record['Incident ID']];
					//echo $new_ticket_record[$column_name];
				}
			}
		}
	//	print_r($new_ticket_record);
		// Get root cause details and resolution from master list
	//	"SELECT `id`,`status`,`cso_root_cause`,`cso_root_cause_category`,`cso_permanent_solution`,`cso_short_term_fixes` FROM `zendesk_master_list` WHERE `id`={$new_ticket_record['Incident ID']}"
		
		//Try to insert ticket ID, if not exist
		if ($new_ticket_record['Incident ID'] != "null")
		{	
			// to check if Incident ID is already existing 
			$check_incident_ID_query_string = "SELECT `Incident ID` FROM `report_on_cso_data` WHERE `Incident ID`={$new_ticket_record['Incident ID']}";
			$check_incident_ID_query_result = mysql_query($check_incident_ID_query_string);
			if(!$check_incident_ID_query_result) echo mysql_error() . PHP_EOL ;
			
			if (mysql_num_rows($check_incident_ID_query_result) < 1)
			{ 
				$query_string = "INSERT INTO `report_on_cso_data` (`Incident ID`) values ({$new_ticket_record['Incident ID']})";
				$query_result = mysql_query($query_string);
				if(!$query_result) echo mysql_error() . PHP_EOL ; 
			}
		}
		
		
		//Update the ticket with new entries
		if (isset($prob_ID_array[$new_ticket_record['Incident ID']]))
		{
			$update_string = "UPDATE `report_on_cso_data` SET `Incident ID`={$new_ticket_record['Incident ID']}, `Problem ID`={$prob_ID_array[$new_ticket_record['Incident ID']]} ";
		}
		else
		{
			$update_string = "UPDATE `report_on_cso_data` SET `Incident ID`={$new_ticket_record['Incident ID']}, `Problem ID`= ".NULL;
		}
		foreach ($new_ticket_record as $column_name => $value) 
		{	
			if (strlen($column_name) < 1 OR $column_name == "-" OR $column_name == " " OR $column_name == "Severity" OR $column_name == "Tags")
			{
			}
			else
			{
				if ($value != "null") 
				{
					//print_r($value);
					if (in_array($value, $ticket_fields_dropdown_value))
					{
						$value = $ticket_fields_dropdown_name[$value];
						$update_string .= ", `{$column_name}` = " . '"' . $value . '"' ;
					}
					else
					{
						$update_string .= ", `{$column_name}` = " . '"' . $value . '"' ;
					}
				}
			}
		}
		$update_string .= " WHERE `Incident ID` = {$id}"; 
		echo "UPDATE STRING: ".$update_string. "\n";
		$update_result = mysql_query($update_string);
		//if(!$update_result) echo $update_string . PHP_EOL ; 

	} // end each ticket
	//print_r($id_string);
	//echo PHP_EOL;
	//print_r($prob_ID_array);
	$next_page_num += 1;
} // end do
	while ($next_page != "null");

}


	


//////////////////////////////////////////////////////////////
//	Without parameter, this function will:
//	- Get whe timestamp of when the API was successfully run
//	  (for efficiency reason)
//	- Query Zendesk incremental ticket API
//	- Update tickets
//	- Record the end_time from API to be used for the next run
///////////////////////////////////////////////////////////////
function update_incremental_tickets($last_runtime=-1)
{
	// no $last_runtime supplied, get last successfull run from DB
	if($last_runtime < 0) 
	{
		$query_string = "SELECT MAX(`last_run`) FROM `zendesk_inc_status` WHERE `status` = 'ok'";
		$query_result = mysql_query($query_string);

		$row = mysql_fetch_row($query_result);
		$last_runtime = strtotime($row[0]);
	}

	// Get custom field id - name translation
	$custom_column_name_list = array();
	$query_result = mysql_query("SELECT * FROM `custom_ticket_fields` WHERE `incr_api_name` is not null");
	while ($row = mysql_fetch_assoc($query_result)) 
	{
		$custom_column_name_list[$row['incr_api_name']] = $row['db_name_master'];
	}

	// Grab Zendesk incremental ticket API
	$endpoint = "/exports/tickets.json?start_time=" . $last_runtime;
	$result = zendesk_curl_wrapper($endpoint);
	if ($result['status']!=200 OR count($result['data']->results) < 1)	die($result['status']);
	
	$error_count = 0;
	foreach ($result['data']->results as $ticket) 
	{
		//For non-custom fields
		$summation = 1;
		$id = $ticket->id;

		//Populate new ticket record
		$new_ticket_record = array();
		$new_ticket_record['url'] = "http://localhost/cloudops_social_pm/pm.php?ticketId={$id}" ;
		$new_ticket_record['date_create'] = date("Y-m-d H:i:s",strtotime($ticket->created_at));
		$new_ticket_record['date_update'] = date("Y-m-d H:i:s",strtotime($ticket->updated_at));
		$new_ticket_record['status'] 	  = $ticket->status;
		$new_ticket_record['group'] 	  = $ticket->group_name;
		$new_ticket_record['tags'] 		  = $ticket->current_tags;
		//For each custom column identified.....
		foreach ($custom_column_name_list as $api_column_name => $db_column_name) 
		{
			$value = $ticket->$api_column_name;

			if (strlen($value) < 1 OR $value == "-" OR $value == " ")
				$new_ticket_record[$db_column_name] = "null";
			else
				$new_ticket_record[$db_column_name] = str_replace("," , ";" , $ticket->$api_column_name);

			//Exceptions... Converting impact duration from dd:mm:hh to minutes
			if ($db_column_name == "cso_impact_duration") 
			{
				$temp_arr = explode(":", $value);
				if(count($temp_arr)!=3)	$new_ticket_record[$db_column_name] = "null";
				else
				{
					$new_ticket_record[$db_column_name] = ($temp_arr[0] * 1440) + ($temp_arr[1] * 60) + $temp_arr[2];
				}
			}
		}

		//Try to insert ticket ID, if not exist
		$query_string = "INSERT INTO `zendesk_master_list` (`summation`,`id`) values (1,{$id}) on duplicate key update `summation` = 1";
		$query_result = mysql_query($query_string);
		if(!$query_result) echo mysql_error() . PHP_EOL ; 

		//Update the ticket with new entries
		$update_string = "UPDATE `zendesk_master_list` SET `summation` = 1 ";
		foreach ($new_ticket_record as $column_name => $value) 
		{
			if ($value != "null") 
			{
				$update_string .= ", `{$column_name}` = " . '"' . $value . '" ' ;
			}
		}
		$update_string .= " WHERE `id` = {$id}"; 
		
		//debug//echo $update_string . PHP_EOL;
		
		$update_result = mysql_query($update_string);
		if(!$update_result)
		{
			$error_count++;
			$error_messages[] = myql_error . " :::: " . $update_string ;
		}

	}

	//if no error, capture the timestamp
	$end_time = $result['data']->end_time;
	$status = 'fail';
	if ($error_count < 1)
	{
		$status = 'ok';
		echo date("Y-m-d H:i:s") . " :: Success :: Putting it to the record now" . PHP_EOL;
	}
		
	
	$query_string = "INSERT INTO `zendesk_inc_status` (`last_run`,status) VALUES ('" . date("Y-m-d h:i:s ",$end_time) . "','" . $status . "')";
	$query_result = mysql_query($query_string);
	if (!$query_result) 
		echo date("Y-md-d H:i:s") . ' :: error :: ' . mysql_error() . $query_string . PHP_EOL; 
	else
		echo date("Y-m-d H:i:s") . " :: Success :: Last Run updated" . PHP_EOL;

}





//////////////////////////////////////////////////////////
//	Returns a list of tickets updated in the last 1 hour
///////////////////////////////////////////////////////////
$views_id_recently_updated_ticket = 34314147;
$views_id_recently_closed_ticket = 35070092;
function get_ticket_ids_from_view($view_id)
{
	$api_result = zendesk_curl_wrapper("/views/{$view_id}/execute.json");

	// Won't use if else to avoid indentation chaos
	if($api_result['status']!=200) return (0 - $api_result['status']); //Will get minus the error code
	if($api_result['data']->count < 1) return 0;//No result, so quit

	//Reach here. great. more than 1 ticket result. Get all ticket IDs
	$result = array();
	foreach ($api_result['data']->rows as $entry) 
	{
		$result[] = $entry->ticket->id;
	}

	return $result;
}

//////////////////////////////////////////////////////
//	Get tickets details (all fields included)
//	parameter: [comma_separated_ticket_ids]
//	returns:
//		[ticket_id] => 
//						[fields] => ....
//						...
//		...
//////////////////////////////////////////////////////
function get_multiple_ticket_full_details($ticket_ids_comma_separated)
{
	$endpoint = "/tickets/show_many.json?ids={$ticket_ids_comma_separated}";
	$result = zendesk_curl_wrapper($endpoint);
	return $result;
}



////////////////////////////////////////////////////
//	Custom Curl wrapper. 
//	ZDURL: https://<customer>.zendesk.com/api/v2
////////////////////////////////////////////////////
function zendesk_curl_wrapper($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 10 );
	curl_setopt($ch, CURLOPT_URL, ZDURL.$url);
	curl_setopt($ch, CURLOPT_USERPWD, ZDUSER."/token:".ZDAPIKEY);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
	curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$output = curl_exec($ch);
	
	$result = array();
	$result['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$result['data'] =  json_decode($output);

	curl_close($ch);
	return $result;
}

/*************************************************************************************************************************************
				 ______        _     _________    ______  ____  ____ 
				|_   _ \      / \   |  _   _  | .' ___  ||_   ||   _|
				  | |_) |    / _ \  |_/ | | \_|/ .'   \_|  | |__| |  
				  |  __'.   / ___ \     | |    | |         |  __  |  
				 _| |__) |_/ /   \ \_  _| |_   \ `.___.'\ _| |  | |_ 
				|_______/|____| |____||_____|   `.____ .'|____||____|
*************************************************************************************************************************************/                                                                                                                                      
//////////////////////////////////////////////////////
//	Update zendesk_groups table with the latest
//	group list
//////////////////////////////////////////////////////
function update_zendesk_groups()
{
	$endpoint = "/groups.json";
	$result = zendesk_curl_wrapper($endpoint);
	if ($result['status']!=200 OR $result['data']->count < 1) print_r($result);

	foreach ($result['data']->groups as $group) 
	{
		$query_string = 'INSERT INTO `zendesk_groups` (`group_id`,`group_name`) values (' . $group->id . ',"' . $group->name . '") ';
		$query_string .= 'ON DUPLICATE KEY UPDATE `group_name` = "' . $group->name . '";';
		$query_result = mysql_query($query_string);
		if(!$query_result) echo mysql_error() . PHP_EOL;
	}
}


//////////////////////////////////////////////////////////
//	Grab all valid values from "zendesk_dump" 
//	and updates the "zendesk_master_list"
//	
//	Zendesk dump is obtained by:
//	- clicking "generate" report on manage->report->export
//	- Grab csv from email
//	- Update zendesk_dump table
//////////////////////////////////////////////////////////
function update_master_table_from_dump()
{
	//Get column names to be extracted
	$custom_column_name_list = array();
	$query_result = mysql_query("SELECT * FROM `custom_ticket_fields` WHERE db_name_dump is not null");
	while ($row = mysql_fetch_assoc($query_result)) 
	{
		$custom_column_name_list[] = $row['db_name_dump'];
	}

	$query_res_from_dump_table = mysql_query("SELECT * from `zendesk_dump`");
	$error_count = 0;
	$error_messages = array();

	while ($ticket = mysql_fetch_assoc($query_res_from_dump_table)) 
	{
		//For non-custom fields
		$summation = 1;
		$id = $ticket['id'];

		//Populate new ticket record
		$new_ticket_record = array();
		$new_ticket_record['url'] = "https://localhost/agent/#/tickets/{$id}" ;
		$new_ticket_record['date_create'] = $ticket['date_create'];
		$new_ticket_record['date_update'] = $ticket['date_update'];
		$new_ticket_record['status'] 	  = $ticket['status'];
		$new_ticket_record['group'] 	  = $ticket['group'];
		$new_ticket_record['tags'] 		  = $ticket['tags'];
		foreach ($custom_column_name_list as $custom_column_name) 
		{
			$value = $ticket[$custom_column_name];

			if (strlen($value) < 1 OR $value == "-" OR $value == " ")
				$new_ticket_record[$custom_column_name] = "null";
			else
				$new_ticket_record[$custom_column_name] = $ticket[$custom_column_name];

			//Converting impact duration from dd:mm:hh to minutes
			if ($custom_column_name == "cso_impact_duration") 
			{
				$temp_arr = explode(":", $value);
				if(count($temp_arr)!=3)	$new_ticket_record[$custom_column_name] = "null";
				else
				{
					$new_ticket_record[$custom_column_name] = ($temp_arr[0] * 1440) + ($temp_arr[1] * 60) + $temp_arr[2];
				}
			}
		}

		//print_r($new_ticket_record); 

		//Try to insert ticket ID, if not exist
		$query_string = "INSERT INTO `zendesk_master_list` (`summation`,`id`) values (1,{$id}) on duplicate key update `summation` = 1";
		$query_result = mysql_query($query_string);
		if(!$query_result) echo mysql_error() . PHP_EOL ; 

		//Update the ticket with new entries
		$update_string = "UPDATE `zendesk_master_list` SET `summation` = 1 ";
		foreach ($new_ticket_record as $column_name => $value) 
		{
			if ($value != "null") 
			{
				$update_string .= ", `{$column_name}` = " . '"' . $value . '" ' ;
			}
		}
		$update_string .= " WHERE `id` = {$id}"; 
		$update_result = mysql_query($update_string);
		if(!$update_result)
		{
			$error_count++;
			$error_messages[] = myql_error . " :::: " . $update_string ;
		}
	}

	if($error_count > 0)
		print_r($error_messages);
}



/*************************************************************************************************************************************
				 ________    _______  ___       _______  ___________  _______  
				|"      "\  /"     "||"  |     /"     "|("     _   ")/"     "| 
				(.  ___  :)(: ______)||  |    (: ______) )__/  \\__/(: ______) 
				|: \   ) || \/    |  |:  |     \/    |      \\_ /    \/    |   
				(| (___\ || // ___)_  \  |___  // ___)_     |.  |    // ___)_  
				|:       :)(:      "|( \_|:  \(:      "|    \:  |   (:      "| 
				(________/  \_______) \_______)\_______)     \__|    \_______) 
*************************************************************************************************************************************/
////////////////////////////////////////////////////
//	Dump Zendesk Incremental ticket (max 1000)
//	Zendesk throttles this API CALL to 1 per minute
//	parameter: [updated_ticket_since_unix_time]
//	return possibilities:
//		- An array of ticket_id 
//		- empty array
////////////////////////////////////////////////////
function get_incremental_tickets_id($since_unix_time)
{
	$result = array();

	$endpoint = "/exports/tickets.json?start_time=" . $since_unix_time;
	$response = zendesk_curl_wrapper($endpoint);
	echo 'reponse ' . PHP_EOL . PHP_EOL ;

	if(!isset($response->data->results) or $response->status == 429)	//Either Zendesk returns nothing because of the throttle, or there is no new ticket
		return $result;		
	
	foreach ($raw_result->data->results as $ticket) 
		$result[] = $ticket->id;

	return $result;
}

////////////////////////////////////////////////////
//	Query all ticket fields from Zendesk
//	- Query existing fields in database
//	- Discover new fields and auto add the fields
////////////////////////////////////////////////////
function refresh_ticket_fields()
{
	//Query existing fields ID
	$current_field_list = array();
	$query_string = "SELECT `field_id` FROM ticket_fields";
	$query_result = mysql_query($query_string);
	while ($row = mysql_fetch_row($query_result)) {
		$current_field_list[$row[0]] = true;
	}

	//Query all fields from Zendesk & if the fields does not exist, adds it
	$raw_result = zendesk_curl_wrapper("/ticket_fields.json");
	foreach ($raw_result->ticket_fields as $ticket_object) 
	{
		if(isset($ticket_object->custom_field_options))
			$is_custom_fields = true;
		else
			$is_custom_fields = false;
		if(!isset($current_field_list[$ticket_object->id]))
			add_ticket_field_to_db($ticket_object->id,$ticket_object->title,$is_custom_fields);
	}

}





?>
