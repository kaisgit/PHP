<?php

date_default_timezone_set('America/Los_Angeles');

$today = date("Y-m-d");
$cur_wday = strtolower(date("D"));		// tue wed thu
$cur_month = strtolower(date("M"));		// jan feb mar
$cur_day = date("d");					// 12th of the month 
$cur_time = date("H:00:00");			// 23:00:00
$last_day_of_month = date("j", strtotime('last day of this month', time()));
#print "$today :: $cur_wday :: $cur_month :: $cur_day :: $cur_time :: $last_day_of_month\n";

$short_month = array("apr","jun","sep","nov");
//$cur_time = "01:00:00"; //for testing only; remove after testing
################################################
# FOR TESTING ONLY
#
#$cur_wday = "thu";
#$cur_month = "apr";
#$cur_day = "30";
#$last_day_of_month = "30";
#$cur_time = "16:00:00";

##############################################################
# DATABASE CONN
#
require_once "lib/mysqli_dbconnect.php";

##############################################################
# MONTHLY
#
$emails = array();

$sql ="SELECT *
		FROM scheduler
		WHERE
			repeat_event = 'monthly'
			AND (('$today' between start_date and end_date) OR ('$today' >= start_date and end_date is null))
			AND hr = '$cur_time'";

if (!$result = $mysqli->query($sql)) { die("Query Failed: [" . $mysqli->error . "]\n"); }

while ($row = $result->fetch_assoc()) {
   	$recipient = $row['recipient'];
   	$frequency = $row['frequency'];
	#$frequency = "31";

	if ($cur_day == $frequency) {
		$emails[] = $recipient;
	} elseif ($cur_month == "feb" && $cur_day == $last_day_of_month && $frequency >= 29) {
		$emails[] = $recipient;
	} elseif (in_array("$cur_month", $short_month) && $cur_day == $last_day_of_month && $frequency == 31) {
		$emails[] = $recipient;
	}
}

##############################################################
# DAILY / WEEKLY
#
$sql ="SELECT *
		FROM scheduler
		WHERE
			frequency LIKE '%$cur_wday%'
			AND (('$today' between start_date and end_date) OR ('$today' >= start_date and end_date is null))
			AND hr = '$cur_time'";

if (!$result = $mysqli->query($sql)) { die("Query Failed: [" . $mysqli->error . "]\n"); }

while ($row = $result->fetch_assoc()) {
   	$recipient = $row['recipient'];

	$emails[] = $recipient;
}

if(isset($emails) AND !empty($emails))
{
    $email_ids = "";
    foreach($emails as $key=>$id)
    {
        if(stripos($id,"@localhost") !== false)
        { $email_ids .= $id.";"; }
    }
    //echo $email_ids;
    # RUN SCRIPT HERE
    shell_exec("php /var/www/html/workfront_email2.php --recipients '".$email_ids."'");

}

?>
