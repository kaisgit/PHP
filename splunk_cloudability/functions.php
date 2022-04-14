<?php
//////////////////////////////////////////////////////
//            SCRIPT SUMMARY                        //
//////////////////////////////////////////////////////
// * INCLUDES ALL COMMON FUNCTIONS USED BY FOLDERS: // 
//    - AN1                                         //
//    - EW1                                         //
//    - UE1                                         //
//////////////////////////////////////////////////////
date_default_timezone_set('UTC'); //Since Splunk is using UTC

function check_if_client_exists($splunk_name,$script)
{
    $client = NULL;
    $query = "SELECT * FROM `Client` where `Splunk Name` = '".$splunk_name."'";
    $result = mysql_query($query);
    if(!$result) { $error_message = mysql_error()."<br><br> Query: ".$query."<br><br>"; send_mail($error_message,$script); }
    $num_of_rows = mysql_num_rows($result);
    if($num_of_rows > 0) {
        while ($row2 = mysql_fetch_assoc($result)) { $client = $row2['Client']; } }
    else { $client = "CLIENT NOT FOUND"; }
    return $client;
}

function update_Client_table($new_client,$script,$report_end_date)
{
    $clientname = explode(",",$new_client);
    foreach($clientname as $value)
    {
        if(stripos($value,"Creativ") !== false)
        { $creative_client = "CC"; }
        else { $creative_client = "<new client>"; }
        $returned = check_if_client_exists($value,"SPLUNK CLIENT TABLE");
        if($returned == "CLIENT NOT FOUND") 
        {
            $insert = "INSERT INTO `Client`(`Splunk Name`,`Client`,`report_end_date`) VALUES ('".$value."','".$creative_client."','".$report_end_date."')";
            $insert_result = mysql_query($insert);
            if(!$insert_result) { $error_message = mysql_error()."<br><br> Query: ".$insert."<br><br>"; send_mail($error_message,$script); }
        }
    }
}

function send_mail($error_message,$index) 
{
    global $region,$today;
	$to = "email@emali.com";
	$subject = 'MESSAGE FROM '.$index.'-'.strtoupper($region);
    $body = "The following message was received while running the script: <br><br> Record Date: ".$today."<br> Error: ".$error_message;
    $body .= "<i><br><br>This is a system generated email. Please do not reply.</i>";
	$headers  = "MIME-Version: 1.0" . "\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1" . "\n";
	$headers .= 'From: Toolz Server <email@email.com>' . "\n";
	$headers .= "X-Mailer: PHP/" . phpversion(); 
	mail($to, $subject, $body, $headers);
}

function connect_to_db()
{
    //MYSQL database connect
	$db_host = "localhost"; 
	$db_username = "";  
	$db_pass = "";  
	$db_name = "ec2_count"; 
	$link = mysql_connect("$db_host","$db_username","$db_pass");
    if (!$link) { die('Could not connect to mysql: ' . mysql_error()); }
	mysql_select_db("$db_name") or die ("no database\n");
    return $link;
}

function update_job_start($script,$job_id,$command,$datetime,$status)
{
    $link = connect_to_db();
    $insert = "INSERT INTO `inc_status`(`script`,`job_id`,`query`,`job_start_time`,`status`) VALUES ('".$script."','".$job_id."','".format_data($command)."','".$datetime."','".$status."')";
    $insert_result = mysql_query($insert);
    if(!$insert_result) { $error_message = mysql_error()."<br><br> Query: ".$insert."<br><br>"; send_mail($error_message,$script); }
    mysql_close($link);
}

function update_job_end($script,$job_id,$datetime,$status)
{
    $link = connect_to_db();
    $update = "UPDATE `inc_status` SET `job_end_time` = '".$datetime."',`status` = '".$status."' WHERE `script` = '".$script."' AND `job_id` = '".$job_id."'";
    $update_result = mysql_query($update);
    if(!$update_result) { $error_message = mysql_error()."<br><br> Query: ".$update."<br><br>"; send_mail($error_message,$script); }
    mysql_close($link);
}

function format_data_duplicate($text)
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
