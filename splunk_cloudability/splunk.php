<?php
#require_once('/home/tools/splunk_aws_bill/shared_cloud_aws_usage/cloudability_splunk/cloudability.php');
require_once('splunk_cloudability/cloudability.php');

function splunk($start_date,$end_date)
{   
    //echo $start_date.$end_date;
    global $needed_splunk_components, $percentageByComponent_FORALL;
    $msProcessingTime = array();
    $msProcessingTime_TotalByComponent = array();
    $msProcessingTime_TotalByClient = array();
    $discardedClients = array();
    $percentageByComponent_FORALL = array();
    
    $query = "select * from `splunk_api_dump` where `start_date` >= '".$start_date."' AND `end_date` <= '".$end_date."'";
    $result = mysql_query($query);
    //echo "test"; print_r($result);
    if(!$result) { $error_message = mysql_error()."<br><br> Query: ".$query."<br><br>"; $error_count++; }
    $get_client = get_client();
    while ($row = mysql_fetch_assoc($result)) 
    {
        $splunk_name = $row['client_id'];
        $region = $row['region'];
        $component = $row['instance_type'];
            if ($component == "Search v2" OR $component == "NorrisContainer" ) {
				$component = 'Norris';
			}
        $processing_time_ms = $row['processing_time_ms'];
        if(in_array(strtolower($component),$needed_splunk_components))
        {
            if(isset($get_client[$splunk_name])) 
            { 
                $client = $get_client[$splunk_name]; 
            } 
            else 
            { 
                $client = $splunk_name; 
            }
        
        }
    }
    
    foreach($msProcessingTime_TotalByClient as $Each_Region=>$Region)
    {
        foreach($Region as $Client => $Processing_time_ms)
        {
            if(isset($msProcessingTime_TotalOfClients[$Each_Region])) { $msProcessingTime_TotalOfClients[$Each_Region] += $msProcessingTime_TotalByClient[$Each_Region][$Client]; }
            else { $msProcessingTime_TotalOfClients[$Each_Region] = $msProcessingTime_TotalByClient[$Each_Region][$Client]; }
        }
    }
    
    foreach($msProcessingTime as $Each_Region=>$Region)
    {
        foreach($Region as $Each_Component=>$Component)
        {
            foreach($Component as $Client => $Processing_time_ms)
            {
                $percentageByComponent[$Each_Region][$Each_Component][$Client] = ($Processing_time_ms/$msProcessingTime_TotalByComponent[$Each_Region][$Each_Component])*100;
            }
        }
    }
    
    //print_r($discardedClients); // --> 1;
    //echo "\n=========================================================================================\n";
    //print_r($percentageByComponent_FORALL); // --> 2; // store 1 intersection 2 in database
    //echo "\n=========================================================================================\n";
    //print_r($percentageByComponent); 
}

function calc_final_aws()
{
    //echo "test";
    global $clients_by_components_aws;
    global $start_date,$end_date;
    $start_year_month = date("Y-m",strtotime($start_date));
    $end_year_month = date("Y-m",strtotime($end_date));
    //print_r($clients_by_components_aws);
    foreach ($clients_by_components_aws as $client=>$regions)
    {
        $adus_aws = NULL; //initialise for each client
        $adeu_aws = NULL;
        $adap_aws = NULL;
        $adir_aws = NULL;
        $adus_aws_fifty_percent = NULL;
        $adir_aws_fifty_percent = NULL;
        $ad_aws_combined = NULL;
        foreach($regions as $region=>$value)
        {    
            if(stripos($region,"us") !== FALSE) { $adus_aws = $value; }
            if(stripos($region,"eu") !== FALSE) { $adeu_aws = $value; }
            if(stripos($region,"ap") !== FALSE) { $adap_aws = $value; }
        }
        $adir_aws = ($adeu_aws + $adap_aws)/2; //average of eu and ap
        //echo $client.": ".$adir_aws."\n";
        $adus_aws_fifty_percent = (50/100) * $adus_aws; //50% of US
        $adir_aws_fifty_percent = (50/100) * $adir_aws; //50% of IR
        $ad_aws_combined = $adus_aws_fifty_percent + $adir_aws_fifty_percent;
        
        $num_of_rows = check_if_exists($start_year_month,$end_year_month,$client);
        if($num_of_rows > 0)
        {
            $query = "UPDATE `sc_usage` SET `adus_aws` = '".$adus_aws."',`adir_aws` = '".$adir_aws."',`ad_aws_combined` = '".$ad_aws_combined."'";
            $query .= "WHERE `start_date` LIKE '%".$start_year_month."%' AND `end_date` LIKE '%".$end_year_month."%' AND `client` = '".$client."'";
        }
        else
        {
            $query = "INSERT INTO `sc_usage`(`start_date`,`end_date`,`client`,`adus_aws`,`adir_aws`,`ad_aws_combined`)";
            $query .= " VALUES('".$start_date."','".$end_date."','".$client."','".$adus_aws."','".$adir_aws."','".$ad_aws_combined."')";
        }
        //echo $query."\n";
        $result = mysql_query($query);
    }
}

function get_client()
{
    $query = "select * from `Client`";
    $result = mysql_query($query);
    if(!$result) { $error_message = mysql_error()."<br><br> Query: ".$query."<br><br>"; $error_count++; }
    while ($row = mysql_fetch_assoc($result)) { $get_client[$row['Splunk Name']] = $row['Client']; }
    return $get_client;
}

function check_if_exists($start_year_month,$end_year_month,$client)
{
    $query = "SELECT * FROM `sc_usage` WHERE `start_date` LIKE '%".$start_year_month."%' AND `end_date` LIKE '%".$end_year_month."%' AND `client` = '".$client."'";
    $result = mysql_query($query);
    if(!$result) { $error_message = mysql_error()."<br><br> Query: ".$query."<br><br>"; $error_count++; }
    $num_of_rows = mysql_num_rows($result);
    return $num_of_rows;
}

function execute($query)
{
    $result = mysql_query($query);
    if(!$result) { $error_message = mysql_error()."<br><br> Query: ".$query."<br><br>"; $error_count++; }
}
?>
