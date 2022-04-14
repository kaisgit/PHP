<?php
error_reporting(E_ALL);
require_once "zendesk_api_key.php";
require_once "db_connect_scexecdash.php";
date_default_timezone_set('UTC');		//All fields with dates in zendesk tickets are all in UTC.

$add_on_msg = array(array());
$no_incident_owner = array();
$incident_owner = array();
$area_array = array("Entitlement", "Identity", "Shared Cloud", "Behance", "Design and Web", "Digital Imaging", "Cross Segment", "License Management", "Membership", "Digital Video and Audio", "Acrobat", "DPS");

foreach($area_array as $key => $eacharea)
{ 
    $$eacharea = 0; 
}
$query_result = mysql_query("SELECT * FROM `dashboard_this_week` ORDER BY `Service` ASC, `CSO Start Time` ASC");
while ($row = mysql_fetch_assoc($query_result)) 
{
	$owner = NULL;
	$area = NULL;
    
    $impact_start = $row['impact_start'];
    if(stripos($impact_start,"0000") !== FALSE OR $impact_start == "null" OR $impact_start == NULL OR $impact_start == "") { $impact_start = "TBD"; }
    
    $impact_end = $row['impact_end'];
    if(stripos($impact_end,"0000") !== FALSE OR $impact_end == "null" OR $impact_end == NULL OR $impact_end == "") { $impact_end = "TBD"; }
    
	$id = $row['Incident ID'];
	
    $service_id = $row['service_id'];
    if(empty($service_id) OR $service_id == NULL OR $service_id == 'null' OR $service_id == '') { $service_id = "TBD"; }
	
    $cso_request_time = $row['cso_request_time']; 
    if(stripos($cso_request_time,"0000") !== FALSE OR $cso_request_time == "null" OR $cso_request_time == NULL OR $cso_request_time == "") { $cso_request_time = "TBD"; }
	
    $start = $row['CSO Start Time']; 
    if(stripos($start,"0000") !== FALSE OR $start == "null" OR $start == NULL OR $start == '') { $start = $cso_request_time; }
	
    $service = $row['Service'];
    if(empty($service) OR $service == NULL OR $service == 'null' OR $service == '') { $service = "TBD"; }
    
    $impact_type = $row['Impact Type'];
	if(empty($impact_type) OR $impact_type == NULL OR $impact_type == 'null' OR $impact_type == '') { $impact_type = "TBD"; }
    
    $customer_impact = $row['Customer Impact'];
	if(empty($customer_impact) OR $customer_impact == NULL OR $customer_impact == 'null' OR $customer_impact == '') { if(stripos($impact_type,'No Impact') === FALSE) { $customer_impact = "TBD"; } else { $customer_impact = "No Impact"; } }
    
    $duration = $row['CSO Duration']; 
	if(empty($duration) OR $duration == NULL OR $duration == 'null' OR $duration == '') { if(stripos($impact_type,'TBD') !== FALSE) { $duration = "TBD"; } }
    else { $duration = $duration." min"; }
    
    $prob_id = $row['Problem ID']; 
    if(empty($prob_id) OR $prob_id == NULL OR $prob_id == 'null' OR $prob_id == '') { $prob_id = "TBD"; }
    
	$percent_impact = $row['Impact %']; 
    if(empty($percent_impact) OR $percent_impact == NULL OR $percent_impact == 'null' OR $percent_impact == '') 
    { if(stripos($impact_type,'Partial') !== FALSE) { $percent_impact = "100"; } elseif(stripos($impact_type,'TBD') !== FALSE) { $percent_impact = "TBD"; } }
    if ($impact_type == "Degradation" OR stripos($impact_type,"full") !== FALSE) { $percent_impact = "N/A"; }
    
	$root_cause = $row['Root Cause'];
	if(empty($root_cause) OR $root_cause == NULL OR $root_cause == 'null' OR $root_cause == '') { $root_cause = "TBD"; }
    
    $root_cause_service = $row['Root Cause Service'];
    if(empty($root_cause_service) OR $root_cause_service == NULL OR $root_cause_service == 'null' OR $root_cause_service == '') { $root_cause_service = "TBD"; }
	
	#
	# Getting the service name and Distribution List emails.
	#
    if($service_id != "TBD")
    {
        $query2 = "SELECT * FROM `service_fields_vnoc` WHERE `service_wf_id` = '".$service_id."'";
        $query2_result = mysql_query($query2);
        while ($row2 = mysql_fetch_assoc($query2_result)) 
        {
            $owner = $row2['availability_owner'];
            $area = $row2['area'];
            if($impact_end != "TBD" AND $impact_start != "TBD") { array_push($incident_owner,$owner); }
        }
    }
	if(isset($owner) AND $owner != NULL) 
    {
        if($impact_end != "TBD" AND $impact_start != "TBD" AND $impact_type != "TBD")
        {
            $$area = 1;
            $data[$owner][$area][$id]['prob_service_id'] = $root_cause_service;  
            $data[$owner][$area][$id]['service'] = $service;
            $data[$owner][$area][$id]['cso_start_time'] = $start; 
            $data[$owner][$area][$id]['cso_customer_impact'] = $customer_impact; 
            $data[$owner][$area][$id]['cso_impact_type'] = $impact_type; 
            $data[$owner][$area][$id]['cso_impact_duration'] = $duration; 
            $data[$owner][$area][$id]['percent_impact'] = $percent_impact; 
            $data[$owner][$area][$id]['prob_id'] = $prob_id;
            $data[$owner][$area][$id]['root_cause_prob_id'] = $root_cause; 
        }
        else
        {
            $missing_data[$owner][$area][$id]['prob_service_id'] = $root_cause_service;  
            $missing_data[$owner][$area][$id]['service'] = $service;
            $missing_data[$owner][$area][$id]['cso_start_time'] = $start; 
            $missing_data[$owner][$area][$id]['cso_customer_impact'] = $customer_impact; 
            $missing_data[$owner][$area][$id]['cso_impact_type'] = $impact_type; 
            $missing_data[$owner][$area][$id]['cso_impact_duration'] = $duration; 
            $missing_data[$owner][$area][$id]['percent_impact'] = $percent_impact; 
            $missing_data[$owner][$area][$id]['prob_id'] = $prob_id;
            $missing_data[$owner][$area][$id]['root_cause_prob_id'] = $root_cause; 
        }
    }
}
$start_period = date('Y-m-d',strtotime('last friday'));
$end_period = date('Y-m-d',strtotime('yesterday'));

foreach($area_array as $eacharea)
{
    //initialisation
	if($$eacharea == 0) 
    { 
        $query2a = "SELECT DISTINCT(`availability_owner`) FROM `service_fields_vnoc` WHERE `area` = '".$eacharea."'";
        $query2a_result = mysql_query($query2a);
        while ($row2a = mysql_fetch_assoc($query2a_result)) 
        {
            $owner2a = $row2a['availability_owner'];
            array_push($no_incident_owner,$owner2a);
            $add_on_msg[$owner2a][$eacharea] = "<b>".$eacharea."</b><br/>No incidents under ".$eacharea." were created in the period beginning ".$start_period." through ".$end_period.".<br/><br/>"; 
        } 
    }
}
if(!isset($missing_data)) { $missing_data = NULL; }
if(isset($data)) {
	create_table($data,$missing_data,$owner,$add_on_msg,$no_incident_owner,$incident_owner);
	create_summary_table($data,$missing_data);
} 

function create_table($data,$missing_data,$sendto,$add_on_msg,$no_incident_owner,$incident_owner) {
	$start_period = date('Y-m-d',strtotime('last friday'));
	$end_period = date('Y-m-d',strtotime('yesterday'));
    
	foreach($data as $owner => $areas)
	{	
		$service_area = "";
		$table = "<html><body>Below is the list of Incidents created in the period beginning ".$start_period." through ".$end_period.". If there are any errors, please check with the engineering team and notify the TDOs. Incidents can also be viewed in the <a href=\"https://localhost/\">QoS Dashboard</a> from the history view. From this view you can also view SLA % Availability per service and group for any time period.<br><br>";
		foreach($areas as $area => $ids)
		{
            //echo $area;
			//$table .= "<strong>".$area."</strong><table width='1250' style=\"border: 1px solid black;border-collapse: collapse;\">";
			$table .= "<strong>".$area."</strong><table cellspacing='0' border='1'>";
			$table .= "<tr>";
				$table .= "<th>Root Cause Service</th>";
				$table .= "<th nowrap>Service Impacted</th>";
				$table .= "<th>CSO Start Date</th>";
				$table .= "<th>Customer Impact</th>";
				$table .= "<th>Impact Type</th>";
				$table .= "<th>Duration</th>";
				$table .= "<th>Percent Impact</th>";
				$table .= "<th>Problem ID</th>";
				$table .= "<th>Root Cause</th>";
			$table .= "</tr>";
			if($service_area != "") { $service_area .= ", ".$area; } else { $service_area = $area; }
		foreach($ids as $id => $alldata)
		{
			$table .= "<tr>\n";
			foreach($alldata as $column_name => $eachdata)
			{	$table .= "<td>".$eachdata."</td>\n"; }
			$table .= "</tr>\n";
		}
		$table .= "</table><br><br>";
		}
		
		foreach($add_on_msg as $owner2a => $areas2a)
		{
			if($owner == $owner2a) { 
			foreach($areas2a as $msg) {
			$table .= $msg; }
			}
		}
        
        if(isset($missing_data[$owner]) AND $missing_data != NULL)
        {
            $table .= "Incidents may be missing from this report pending impact details being entered in to VNOC. The following may be the list of incidents missed in this report:<br/>";
            foreach($missing_data[$owner] as $area => $ids)
            {
                $table .= "<strong>".$area."</strong><table cellspacing='0' border='1'>";
			    $table .= "<tr>";
				$table .= "<th>Root Cause Service</th>";
				$table .= "<th nowrap>Service Impacted</th>";
				$table .= "<th>CSO Start Date</th>";
				$table .= "<th>Customer Impact</th>";
				$table .= "<th>Impact Type</th>";
				$table .= "<th>Duration</th>";
				$table .= "<th>Percent Impact</th>";
				$table .= "<th>Problem ID</th>";
				$table .= "<th>Root Cause</th>";
			    $table .= "</tr>";
                
                foreach($ids as $id => $alldata)
                {
                    $table .= "<tr>\n";
                    foreach($alldata as $column_name => $eachdata)
                    {	$table .= "<td>".$eachdata."</td>\n"; }
                    $table .= "</tr>\n";
                }
                $table .= "</table><br><br>";
            }
            
        }
        $table .= "</body></html>";
		send_mail($table,$owner,$service_area);
        //echo $owner.": ".$service_area."\n";
        //print_r($table);
	}
    $left_out_owner_checked = array();
	foreach($no_incident_owner as $left_out_owner) {
	if (!in_array($left_out_owner, $incident_owner)) 
	 { 
	 	$service_area2 = "";
	 	$query2b = "SELECT DISTINCT(`area`) FROM `service_fields_vnoc` WHERE `availability_owner` = '".$left_out_owner."'";
		$query2b_result = mysql_query($query2b);
		while ($row2b = mysql_fetch_assoc($query2b_result)) 
		{ 	
            $area2 = $row2b['area'];
			if($service_area2 != "") { $service_area2 .= ", ".$area2; } else { $service_area2 = $area2; }
		}
        
        if(isset($missing_data[$left_out_owner]) AND $missing_data != NULL)
        {
            if (!in_array($left_out_owner, $left_out_owner_checked)) 
            { 
                array_push($left_out_owner_checked,$left_out_owner); 
                $table = "<b>".$service_area2."</b><br/>No incidents under ".$service_area2." were created in the period beginning ".$start_period." through ".$end_period.".<br/><br/>";
                $table .= "Incidents may be missing from this report pending impact details being entered in to VNOC. The following may be the list of incidents missed in this report:<br/>";
                foreach($missing_data[$left_out_owner] as $area => $ids)
                {
                    $table .= "<strong>".$area."</strong><table cellspacing='0' border='1'>";
                    $table .= "<tr>";
                    $table .= "<th>Root Cause Service</th>";
                    $table .= "<th nowrap>Service Impacted</th>";
                    $table .= "<th>CSO Start Date</th>";
                    $table .= "<th>Customer Impact</th>";
                    $table .= "<th>Impact Type</th>";
                    $table .= "<th>Duration</th>";
                    $table .= "<th>Percent Impact</th>";
                    $table .= "<th>Problem ID</th>";
                    $table .= "<th>Root Cause</th>";
                    $table .= "</tr>";

                    foreach($ids as $id => $alldata)
                    {
                        $table .= "<tr>\n";
                        foreach($alldata as $column_name => $eachdata)
                        {	$table .= "<td>".$eachdata."</td>\n"; }
                        $table .= "</tr>\n";
                    }
                    $table .= "</table><br><br>";
                }
                $table .= "</body></html>";
                send_mail($table,$left_out_owner,$service_area2);
                //echo $left_out_owner.": ".$service_area2."\n";
                //print_r($table);
            }
        }
        else
        {
            send_mail("<b>".$service_area2."</b><br/>No incidents under ".$service_area2." were created in the period beginning ".$start_period." through ".$end_period.".<br/>",$left_out_owner,$service_area2); 
            //echo $left_out_owner.": ".$service_area2."\n";
            //echo "test: <b>".$service_area2."</b><br/>No incidents under ".$service_area2." were created in the period beginning ".$start_period." through ".$end_period.".<br/><br/>";
        }
	 }
	}
}

function create_summary_table ($data,$missing_data) {
	foreach ($data as $dl) {
		foreach ($dl as $name => $array) {
			foreach ($array as $id => $value) {

				$service_name = $value['service'];
            	$prob_id = $value['prob_id'];

				$summary[$name][$prob_id]['prob_service_id'] = $value['prob_service_id'];
				#if ( !in_array("$service_name", $summary[$name][$prob_id]['service_name'])) {
					#$summary[$name][$prob_id]['service_name'][]  = "$service_name";
				#}
				$summary[$name][$prob_id]['service_name'][]  = "$service_name";

				$summary[$name][$prob_id]['cso_start_time'] = $value['cso_start_time'];
				$summary[$name][$prob_id]['cso_customer_impact'] = $value['cso_customer_impact'];
				$summary[$name][$prob_id]['cso_impact_type'] = $value['cso_impact_type'];
				$summary[$name][$prob_id]['cso_impact_duration'] = $value['cso_impact_duration'];
				$summary[$name][$prob_id]['percent_impact'] = $value['percent_impact'];
				$summary[$name][$prob_id]['root_cause_prob_id'] = $value['root_cause_prob_id'];
			}
		}
	}
    
    foreach ($missing_data as $dl) {
		foreach ($dl as $name => $array) {
			foreach ($array as $id => $value) {

				$service_name = $value['service'];
            	$prob_id = $value['prob_id'];

				$summary[$name][$prob_id]['prob_service_id'] = $value['prob_service_id'];
				#if ( !in_array("$service_name", $summary[$name][$prob_id]['service_name'])) {
					#$summary[$name][$prob_id]['service_name'][]  = "$service_name";
				#}
				$summary[$name][$prob_id]['service_name'][]  = "$service_name";

				$summary[$name][$prob_id]['cso_start_time'] = $value['cso_start_time'];
				$summary[$name][$prob_id]['cso_customer_impact'] = $value['cso_customer_impact'];
				$summary[$name][$prob_id]['cso_impact_type'] = $value['cso_impact_type'];
				$summary[$name][$prob_id]['cso_impact_duration'] = $value['cso_impact_duration'];
				$summary[$name][$prob_id]['percent_impact'] = $value['percent_impact'];
				$summary[$name][$prob_id]['root_cause_prob_id'] = $value['root_cause_prob_id'];
			}
		}
	}
    
    //print_r($summary);

	$summary_table = "<html>";
	$summary_table .= "<style type='text/css'></style>";
	$summary_table .= "<body>";
	
	foreach ($summary as $name => $data1) {
		$summary_table .= "<strong>" . $name . "</strong>\n";
			$summary_table .= "<table cellspacing='0' border='1'>\n";
            $summary_table .= "<tr>\n";
                $summary_table .= "<th style='width: 180px'>Root Cause Service</th>\n";
                $summary_table .= "<th style='width: 250px'>Impact Services</th>\n";
                $summary_table .= "<th style=width: 125px'>CSO Start Date</th>\n";
                $summary_table .= "<th style='width: 25%'>Customer Impact</th>\n";
                $summary_table .= "<th>Impact Type</th>\n";
                $summary_table .= "<th>Duration</th>\n";
                $summary_table .= "<th>Percent Impact</th>\n";
                $summary_table .= "<th>Problem ID</th>\n";
                $summary_table .= "<th style='width: 25%'>Root Cause</th>\n";
				$summary_table .= "</tr>\n";

		foreach ($data1 as $prob_id => $value) {
				$summary_table .= "<tr>\n";
				$summary_table .= "<td style='width: 180px' nowrap>" . $value['prob_service_id'] . "</td>\n";

				#$service_name = explode('|', $value['service_name']);
				$summary_table .= "<td style='width: 250px' nowrap>";
				$value['service_name'] = array_unique($value['service_name']);
				foreach ($value['service_name'] as $item) {
					$summary_table .= "$item<br />";
				}
				$summary_table .= "</td>\n";

				$summary_table .= "<td style=width: 125px'>" . $value['cso_start_time'] . "</td>\n";
				$summary_table .= "<td style='width: 25%'>" . $value['cso_customer_impact'] . "</td>\n";
				$summary_table .= "<td>" . $value['cso_impact_type'] . "</td>\n";
				$summary_table .= "<td>" . $value['cso_impact_duration'] . "</td>\n";
				$summary_table .= "<td>" . $value['percent_impact'] . "</td>\n";
				$summary_table .= "<td>" . $prob_id . "</td>\n";
				$summary_table .= "<td style='width: 25%'>" . $value['root_cause_prob_id'] . "</td>\n";
				$summary_table .= "</tr>\n";
		}
		$summary_table .= "</table><br /><br />\n\n";
	}
	$summary_table .= "</body></html>";
	send_mail($summary_table, null, null);
	#print $summary_table;
}

function send_mail($table,$owner,$service_area) {
	$service_area2 = "";
	$query2b = "SELECT DISTINCT(`area`) FROM `service_fields_vnoc` WHERE `availability_owner` = '".$owner."'";
	$query2b_result = mysql_query($query2b);
	while ($row2b = mysql_fetch_assoc($query2b_result)) 
	{ 	$area2 = $row2b['area']; 
		if($service_area2 != "") { $service_area2 .= ", ".$area2; } else { $service_area2 = $area2; }
	}
	$to = $owner;
	$cc = "email@email.com";
	if ($owner && $service_area) {
		$subject = 'Weekly Incident Report - ' . $service_area2;
	} else {
		$subject = 'Weekly Incident Report - SUMMARY';
	}
	$headers  = "MIME-Version: 1.0" . "\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1" . "\n";
	$headers .= 'From: Cloudops Team <email@email.com>' . "\n";
	$headers .= 'Cc: ' . $cc . "\n";
	$headers .= "X-Mailer: PHP/" . phpversion(); 
	mail($to, $subject, $table, $headers);
}

?>
