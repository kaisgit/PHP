<?php
error_reporting(E_ALL);
require_once "zendesk_api_key.php";
require_once "db_connect_scexecdash.php";
date_default_timezone_set('UTC');		//All fields with dates in zendesk tickets are all in UTC.

$error_count = 0;
$service = array();
$tags_array = array();
$break = array();
$prob_id = array(array());
$next_page = "";
//$ai_id = array(array());
$ticket_field_id = 21686117;// service types
$query_result = mysql_query("SELECT `Ticket Field value` FROM `tagger_ticket_fields` WHERE `Ticket Field ID` = ".$ticket_field_id);
while ($row = mysql_fetch_assoc($query_result)) 
{
	array_push($service, $row['Ticket Field value']);
}
if(!$query_result) 
{
	echo mysql_error() . " :::: " . $query_result . PHP_EOL ; 
	$error_count++;
	$error_messages[] = mysql_error() . " :::: " . $query_result ;
}
	$prob_array = array();
	$query_result9 = mysql_query("SELECT * FROM `zendesk_approved_list` WHERE `date_update` > '".date("Y-m-d",strtotime("-1 week"))."' AND `status` != 'Deleted' AND `is_problem` is not null");
	while ($row9 = mysql_fetch_assoc($query_result9)) 
	{
		array_push($prob_array, $row9['id']);
	}
	if(!$query_result9) 
	{
		echo mysql_error() . " :::: " . $query_result9 . PHP_EOL ; 
		$error_count++;
		$error_messages[] = mysql_error() . " :::: " . $query_result9 ;
	}
/*
foreach($service as $value)
{
	$next_page_num=1;
	$endpoint = "/search.json?page=" . $next_page_num ."&query=type:ticket%20tags:\"problem%20".$value."\"";
	//echo $endpoint."\n";
	$result = zendesk_curl_wrapper($endpoint);
	echo "SERVICE: ".$value. "\nProblem IDs are: \n";
	if ($result['status']!=200)	
	{
		if(isset($result['data']->error))
		{
			echo $result['data']->error."\n";
		}
		else
		{
			echo "no result \n";
		}
	}
	elseif((isset($result['data']->error)) AND $result['data']->error == "unavailable")
	{
		//echo "Result unavailable at the moment";
	}
	else
	{
		foreach ($result['data']->results as $ticket) 
		{
			$prob_id[$value][$ticket->id] = $ticket->id;
			echo $prob_id[$value][$ticket->id]."\n";
		}
		$next_page = $result['data']->next_page;
		echo "NEXT PAGE_1: ".$next_page."\n";	
		//while($next_page != "null" OR !empty($next_page) OR $next_page != "" OR $next_page != NULL)
		while(stripos($next_page,"problem") !== false)
		{
			$next_page_num += 1;
			$endpoint = "/search.json?page=" . $next_page_num ."&query=type:ticket%20tags:\"problem%20".$value."\"";
			//echo $endpoint."\n";
			$result = zendesk_curl_wrapper($endpoint);
			if ($result['status']==200 AND (!isset($result['data']->error)))
			{
				foreach ($result['data']->results as $ticket) 
				{	 
					$prob_id[$value][$ticket->id] = $ticket->id;
					echo $prob_id[$value][$ticket->id]."\n";
				}	
				$next_page = $result['data']->next_page;
				echo "NEXT PAGE_2: ".$next_page."\n";	
			}
			else
			{
				$next_page = "null";
			}
		}
	}
}*/
/*foreach($prob_id as $service_name => $keyvalue)
{
	foreach($keyvalue as $probid => $id)
	{*/
	$next_page_num=1;
	do{
	$ai_id = array(array());
	$endpoint = "/search.json?page=" . $next_page_num ."&query=type:ticket%20";
	foreach ($prob_array as $key => $id)
	{
//	echo "PROBLEM ID FOR SERVICE ".$service_name." IS ".$id."\n";
	$endpoint .= "tags:\"problem_".$id."%20action_item\"%20";
	}
	//echo "ENDPOINT: " .$endpoint."\n";
	$result = zendesk_curl_wrapper($endpoint);	
	//echo "STATUS : ".$result['status']."\n";
	//echo "COUNT OF RESULTS: ".count($result['data']). "\n";
	if ($result['status']!=200 OR count($result['data']) < 1)	
	{
		if(isset($result['data']->error))
		{
			echo $result['data']->error."\n";
		}
		else
		{
			echo "no result \n";
		}
		die($result['status']);
	}
	elseif((isset($result['data']->error)) AND $result['data']->error == "unavailable")
	{
		echo "Result unavailable at the moment";
	}
	else
	{
		$next_page = $result['data']->next_page;
		//echo "NEXT PAGE_1: ".$next_page."\n";
		if($result['data']->results == "" OR $result['data']->results == " " OR $result['data']->results == NULL OR empty($result['data']->results) OR !isset($result['data']->results))
		{
			die($result['status']);
		}
		foreach ($result['data']->results as $ticket) 
		{
		
			$i = 0;
			foreach ($ticket->tags as $zendesk_tags_value)
			{
				$tags_array[$i] = $zendesk_tags_value;
				if(stripos($tags_array[$i], "problem_") !== false) 
  				{	
  					$break = explode("_", $tags_array[$i]);
  				}
  				else
  				{
  					//echo "NO PROBLEM_ FOR AXN ID: ".$ticket->id."\n" ;  				
  				}
				if (in_array($tags_array[$i], $service))
    			{
    				$service_tag = $tags_array[$i];
    				if(isset($break[1]))
    				{
    					$ai_id[$break[1]][$ticket->id] = $service_tag;
    				}
    			}
    			else
    			{
    				if(isset($break[1]))
    				{
    					$ai_id[$break[1]][$ticket->id] = NULL;
					}				
    			}
				$i++;
			}
			//echo "ACTION ITEM ID FOR PROB ".$break[1]." is ".$ticket->id."\n";
			$break = array();
		}
		
		foreach($ai_id as $problem_id => $keyvalue)
{
	foreach($keyvalue as $axnid => $servicename)
	{
		//Try to insert action item ID, if not exist
		$query_string = "INSERT INTO `zendesk_approved_list` (`summation`,`id`) values (1,{$axnid}) on duplicate key update `summation` = 1";
		$query_result = mysql_query($query_string);
		if(!$query_result)
		{
			print_r(mysql_error() . " :::: " . $query_string . PHP_EOL);
		} 

		//Update the ticket with new entries
		$update_string = "UPDATE `zendesk_approved_list` SET `summation` = 1 , `action_item_prob_id` = '".$problem_id."'";
		$update_string .= ", `action_item_service_name` = " . '"' . $servicename . '"' ;
		$update_string .= " WHERE `id` = {$axnid}"; 
		$update_result = mysql_query($update_string);
		if(!$update_result)
		{
			print_r(mysql_error() . " :::: " . $update_string);
		}
		//Update the service name if null
		$select = mysql_query("SELECT * from `zendesk_approved_list` WHERE `id` = {$axnid}");
		if(!$select)
		{
			$error_count++;
			print_r(mysql_error() . " :::: SELECT * FROM ZENDESK MASTER LIST FOR SERVICE NAME OF AXN ID");
		}
		while($row10 = mysql_fetch_assoc($select))
		{
			if($row10['service'] = NULL OR empty($row10['service']))
			{
				$select2 = mysql_query("SELECT * from `zendesk_approved_list` WHERE `id` = {$problem_id}");
				if(!$select2)
				{
					$error_count++;
					print_r(mysql_error() . " :::: SELECT * FROM ZENDESK MASTER LIST FOR SERVICE NAME OF PROB ID");
				}
				while($row11 = mysql_fetch_assoc($select2))
				{
					$update = mysql_query("UPDATE `zendesk_approved_list` SET `service` = '".$row11['service']."' WHERE `id` = {$axnid}");
					if(!$update)
					{
						$error_count++;
						print_r(mysql_error() . " :::: UPDATE ZENDESK MASTER LIST AXN ITEM WITH SERVICE NAME");
					}				
				}
			}
		}
	}
}

		
		$next_page_num += 1;
		//while($next_page != "null" OR !empty($next_page) OR $next_page != "" OR $next_page != NULL)
		/*while(stripos($next_page,"problem") !== false)
		{
			$next_page_num += 1;
			$endpoint = "/search.json?page=" . $next_page_num ."&query=type:ticket%20tags:\"problem_".$id."%20action_item\"";
			echo $endpoint."\n";
			$result = zendesk_curl_wrapper($endpoint);
			if ($result['status']==200 AND (!isset($result['data']->error)))
			{
				foreach ($result['data']->results as $ticket) 
				{	 
					foreach ($ticket->tags as $zendesk_tags_value)
					{
						$tags_array[$i] = $zendesk_tags_value;
						if (in_array($tags_array[$i], $service))
    					{
    						$service_tag = $tags_array[$i];
    						$ai_id[$id][$ticket->id] = $service_tag;
    					}
    					else
    					{
    						$ai_id[$id][$ticket->id] = NULL;
    					}
						$i++;
					}
					echo "ACTION ITEM ID FOR PROB ".$id." is ".$ticket->id."\n";
				}	
				$next_page = $result['data']->next_page;
				echo "NEXT PAGE_2: ".$next_page."\n";	
			}
			else
			{
				$next_page = "null";
			}
		}*/
	}
	}
	while ($next_page != "null" OR (!empty($next_page)) OR $next_page != NULL OR $next_page == " " OR (!isset($next_page)) OR (stripos($next_page, " ") !== false));
	//}
//}

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
	//echo "URL IS:::" . $url . PHP_EOL;
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
	//unset($result);
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
	if ($result['status']!=200 OR $result['data']->count < 1) //print_r($result);

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
