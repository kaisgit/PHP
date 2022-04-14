<?php
///////////////////////////////////////////////////
//            SCRIPT SUMMARY                     //
///////////////////////////////////////////////////
// * STORAGE SCRIPT FOR MAX(LAST RUN DATE) + 1 DAY //
// * SENDS EMAIL IF                              //
//    - SQL ERRORS                               //
//    - $end_date IS EQUAL TO TODAY              //
///////////////////////////////////////////////////
date_default_timezone_set('UTC');
#require_once("/home/tools/splunk_aws_bill/shared_cloud_aws_usage/functions/functions.php");
require_once('splunk_cloudability/functions.php');
connect_to_db();

$error_counter = 0;
$error_message = "";
$date_wrong = 0;

//Global variables
$data = "CONTENT";
$regions = array("us","eu","ap");
$mdate = date("Y-m",strtotime("-1 month"));	
$date = $mdate."-19";
$dates['start_date'] = $date;

$mdate = date("Y-m");	
$enddate = $mdate."-18";
$dates['end_date'] = $enddate;
    
    if(isset($dates['start_date'])) { $start_date = $dates['start_date']; $start_year_month = date("Y-m",strtotime($start_date)); } else { $start_date = NULL; $start_year_month = NULL; }
    if(isset($dates['end_date'])) { $end_date = $dates['end_date']; $end_year_month = date("Y-m",strtotime($end_date)); } else { $end_date = NULL; $end_year_month = NULL; }
    if($start_date == NULL OR $start_date == '' OR empty($start_date))
    { $date_wrong = 1; $error_message .= "Start Date is NULL (Raw value: '".$start_date."')<br>"; $error_counter++; }
    if($end_date == NULL OR $end_date == '' OR empty($end_date))
    { $date_wrong = 1; $error_message .= "End Date is NULL (Raw value: '".$end_date."')<br>"; $error_counter++; }

   // print_r($dates);

foreach($regions as $index=>$region)
{
    //$dates = get_start_and_end_dates();

    if($date_wrong == 0) { $percentage_of_sum2[$region] = percentage_of_sum_of_TB(); }
    send_mail_from_storage($error_message,$error_counter);
}
//print_r($percentage_of_sum2);
if(isset($percentage_of_sum2))
{
    foreach($percentage_of_sum2 as $this_region => $all_data)
    {
        foreach($all_data as $this_data => $all_clients)
        {
            foreach($all_clients as $this_client => $percent)
            {
                if(stripos($this_region,'us') !== FALSE) 
                {
                    $final[$this_data][$this_client][$this_region] = $percentage_of_sum2[$this_region][$this_data][$this_client];
                    if(!isset($final[$this_data][$this_client]['intl'])) { $final[$this_data][$this_client]['intl'] = 0; }
                }
                else
                {
                    if(!isset($percentage_of_sum2['eu'][$this_data][$this_client])) { $percentage_of_sum2['eu'][$this_data][$this_client] = 0; }
                    if(!isset($percentage_of_sum2['ap'][$this_data][$this_client])) { $percentage_of_sum2['ap'][$this_data][$this_client] = 0; }
                    if(isset($percentage_of_sum2['eu'][$this_data][$this_client]) AND isset($percentage_of_sum2['ap'][$this_data][$this_client]))
                    {
                        $final[$this_data][$this_client]['intl'] = ($percentage_of_sum2['eu'][$this_data][$this_client] + $percentage_of_sum2['ap'][$this_data][$this_client])/2;
                    }
                    if(!isset($final[$this_data][$this_client]['us'])) { $final[$this_data][$this_client]['us'] = 0; }
                }
            }
        }
    }
}
if(isset($final["CONTENT"]["SharedCloud"]['us'])) 
{ 
    if(isset($final["CONTENT"]["Image Service"]['us'])) 
    { 
        $final["CONTENT"]["Image Service"]['us'] = $final["CONTENT"]["Image Service"]['us'] + $final["CONTENT"]["SharedCloud"]['us']; 
    } 
    else 
    { 
        $final["CONTENT"]["Image Service"]['us'] = $final["CONTENT"]["SharedCloud"]['us'];
    } 
}
if(isset($final["CONTENT"]["SharedCloud"]['intl'])) 
{ 
    if(isset($final["CONTENT"]["Image Service"]['intl'])) 
    { 
        $final["CONTENT"]["Image Service"]['intl'] = $final["CONTENT"]["Image Service"]['intl'] + $final["CONTENT"]["SharedCloud"]['intl']; 
    } 
    else 
    { 
        $final["CONTENT"]["Image Service"]['intl'] = $final["CONTENT"]["SharedCloud"]['intl'];
    } 
}
//print_r($final);
//UPDATE ADUS_STORAGE AND ADIR_STORAGE COLUMNS IN SC_USAGE TABLE , IF CLIENT EXISTS, ELSE INSERT NEW ROW//
$q = "UPDATE `sc_usage` SET `adus_storage` = '0.00', `adir_storage` = '0.00' where `start_date` LIKE '%".$start_year_month."%' AND `end_date` LIKE '%".$end_year_month."%'";
$r = mysql_query($q);

if(isset($final))
{
    foreach ($final[$data] as $client=>$aws) 
    {
        $query = "SELECT * FROM `sc_usage` where `client` = '".$client."' AND `start_date` LIKE '%".$start_year_month."%' AND `end_date` LIKE '%".$end_year_month."%'";
        //echo $query."\n";
        $result = mysql_query($query);
        $numofrows = mysql_num_rows($result);
        if ($numofrows > 0) 
        {
            $query = "UPDATE `sc_usage` SET `adus_storage` = '".$aws['us']."',`adir_storage` = '".$aws['intl']."' "; 
            $query .= "WHERE `client` = '".$client."' AND `start_date` LIKE '%".$start_year_month."%' AND `end_date` LIKE '%".$end_year_month."%'";
        } 
        else 
        {
            $query = "INSERT INTO `sc_usage`(`start_date`,`end_date`,`client`,`adus_storage`,`adir_storage`) ";
            $query .= "VALUES ('".$start_date."','".$end_date."','".$client."','".$aws['us']."','".$aws['intl']."')";
        }
        $result = mysql_query($query);
        #echo $query."\n\n";
        if (!$result) { $error_message = mysql_error() . "<br><br> Query: " . $query . "\n"; }
    }
}
function total_sum_of_TB()
{
    $total_sum = array();
    global $data,$region,$start_date,$end_date;
    $query = "SELECT SUM(`size_TB`) FROM `sc_storage_dump` where `data` = '".$data."' AND `region` = '".$region."'";
    $query .= " AND `start_date` = '".$start_date."' AND `end_date` = '".$end_date."'";
    $result = mysql_query($query);
    while ($row = mysql_fetch_assoc($result)) { $total_sum[$data][$region] = $row['SUM(`size_TB`)']; } 
    return $total_sum;
}

function percentage_of_sum_of_TB()
{
    $percentage_of_sum = array();
    global $data,$region,$start_date,$end_date;
    $query = "SELECT `client_id`,`size_TB` FROM `sc_storage_dump` where `data` = '".$data."' AND `region` = '".$region."'";
    $query .= " AND `start_date` = '".$start_date."' AND `end_date` = '".$end_date."'";
    $result = mysql_query($query);
    $total_sum_of_TB = total_sum_of_TB();
    while ($row = mysql_fetch_assoc($result)) 
    { 
        $client_id_from_logs = $row['client_id'];
        $client = check_if_storage_client_exists($client_id_from_logs);
        if(stripos($client,"CLIENT NOT FOUND") !== FALSE OR stripos($client,"new client") !== FALSE) { $client = 'CC'; }
        $size_tb = $row['size_TB'];
        if(isset($percentage_of_sum[$data][$client]))
        { $percentage_of_sum[$data][$client] += $size_tb/$total_sum_of_TB[$data][$region]; }
        else { $percentage_of_sum[$data][$client] = $size_tb/$total_sum_of_TB[$data][$region]; }
    } 
    //print_r($percentage_of_sum);
    return $percentage_of_sum;
}

function get_start_and_end_dates()
{
    $date = array();
    global $data,$region;
    $query = "SELECT MAX(`start_date`),MAX(`end_date`) FROM `sc_storage_dump` where `data` = '".$data."' AND `region` = '".$region."'";
    $result = mysql_query($query);
    while ($row = mysql_fetch_assoc($result)) { $date['start_date'] = $row['MAX(`start_date`)']; $date['end_date'] = $row['MAX(`end_date`)']; } 
    
    $mdate = date("Y-m",strtotime("-1 month"));	
    $date = $mdate."-19";
    $date['start_date'] = $date;

    $mdate = date("Y-m");	
    $enddate = $mdate."-18";
    $date['end_date'] = $enddate;
    
    return $date;
}

function percentage_by_region()
{
    $percentage_of_sum_TB_of_all_regions = array();
    global $data;
    if(isset($total_sum_of_TB[$data]['us'])) { $us_sum_TB = $total_sum_of_TB[$data]['us']; } else { $us_sum_TB = 0; }
    if(isset($total_sum_of_TB[$data]['eu'])) { $eu_sum_TB = $total_sum_of_TB[$data]['eu']; } else { $eu_sum_TB = 0; }
    if(isset($total_sum_of_TB[$data]['ap'])) { $ap_sum_TB = $total_sum_of_TB[$data]['ap']; } else { $ap_sum_TB = 0; }
    $total_sum_all_regions = $us_sum_TB + $eu_sum_TB + $ap_sum_TB;
    $percentage_of_sum_TB_of_all_regions['us'] = $us_sum_TB/$total_sum_all_regions;
    $percentage_of_sum_TB_of_all_regions['eu'] = $us_sum_TB/$total_sum_all_regions;
    $percentage_of_sum_TB_of_all_regions['ap'] = $us_sum_TB/$total_sum_all_regions;
    return $percentage_of_sum_TB_of_all_regions;
}

function send_mail_from_storage($error_message,$error_counter) 
{
    if($error_counter > 0)
    {
        global $data,$region;
        $today = date("Y-m-d");
        $to = "email@email.com";
        $subject = 'MESSAGE ABOUT '.$data.' IN '.strtoupper($region);
        $body = "The following message was received while running the script: <br><br> Record Date: ".$today."<br> Error: ".$error_message;
        $body .= "<i><br><br>This is a system generated email. Please do not reply.</i>";
        $headers  = "MIME-Version: 1.0" . "\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1" . "\n";
        $headers .= 'From: Toolz Server <email@email.com>' . "\n";
        $headers .= "X-Mailer: PHP/" . phpversion(); 
        mail($to, $subject, $body, $headers);
    }
}

function check_if_storage_client_exists($splunk_name)
{
    $client = NULL;
    $count_error = 0;
    $query = "SELECT * FROM `Client` where `Splunk Name` = '".$splunk_name."'";
    $result = mysql_query($query);
    if(!$result) { $error_message = mysql_error()."<br><br> Query: ".$query."<br><br>"; $count_error++; send_mail_from_storage($error_message,$count_error); }
    $num_of_rows = mysql_num_rows($result);
    if($num_of_rows > 0) {
        while ($row2 = mysql_fetch_assoc($result)) { $client = $row2['Client']; } }
    else { $client = "CLIENT NOT FOUND"; }
    return $client;
}
?>
