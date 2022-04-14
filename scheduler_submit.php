<?php

date_default_timezone_set('America/Los_Angeles');
$count_fields = 0;
foreach ($_POST as $key=>$value) {
	if ($key == "recipient1" AND $value != '' AND !empty($value)) {
		$recipient = explode(";", $value);
	}
	if ($key == "repeat_event" AND $value != '' AND !empty($value) AND $value != ' ') {
		if ($value == "daily") {
			$repeat_event[$value] = "mon,tue,wed,thu,fri,sat,sun";
		}
		if ($value == "weekly" AND $_POST[$value] != '' AND !empty($_POST[$value])) {
			$repeat_event[$value] = implode(",", $_POST[$value]);
		} 
		if ($value == "monthly" AND $_POST[$value] != '' AND !empty($_POST[$value])) {
			$repeat_event[$value] = $_POST[$value];
		}
	}
	if ($key == "from" AND $value != '' AND !empty($value)) {
		$startdate = date("Y-m-d", strtotime($value));
	}
	if ($key == "to" AND $value != '' AND !empty($value)) {
		$enddate = date("Y-m-d", strtotime($value));
		$enddate = "'$enddate'";
	}
	if ($key == "time") {
		$time = "$value:00:00";
	}
	if ($key == "delete") {
		$delete = "$value";
	}
}

#print_r($recipient);
#print_r($repeat_event);
#print "$startdate<br>";
#print "$enddate<br>";
#print "$time<br>";
#print "$delete<br>";

require_once "lib/mysqli_dbconnect.php";

$recipient_db = implode("','", $recipient);

if ($delete) {
	$sql ="DELETE FROM scheduler WHERE recipient IN ('$recipient_db')";

	if (!$result = $mysqli->query($sql)) { die("Query Failed: [" . $mysqli->error . "]\n"); }
} else {
if(isset($recipient) AND isset($repeat_event) AND isset($startdate))
{
	if (empty($enddate)) {
		$enddate = "null";
	}
foreach($recipient as $person)
{
	$sql ="SELECT recipient
        	FROM scheduler
        	WHERE
            	recipient in ('$person')";

	if (!$result = $mysqli->query($sql)) { die("Query Failed: [" . $mysqli->error . "]\n"); }
	$row_cnt = mysqli_num_rows($result);

	if ($row_cnt >= 1) {
		################################################
		# UPDATE db
		#
			foreach ($repeat_event as $key=>$value) {
				$query = "UPDATE scheduler
					  	SET recipient='$person',repeat_event='$key',frequency='$value',start_date='$startdate',end_date=$enddate,hr='$time'
					  	WHERE recipient = '$person';";
				//if (!$result = $mysqli->query($query)) { die("Query Failed: [" . $mysqli->error . "]\n"); }
			}
	} else {
		################################################
		# INSERT into db
		#
		$query = "INSERT INTO scheduler (recipient, repeat_event, frequency, start_date, end_date, hr) VALUES ";
			$query .= "('" . $person . "','";
			foreach ($repeat_event as $key=>$value) {
				$query .= "$key','$value','";
			}
			$query .= "$startdate',$enddate,'$time'),";
		

		$query = rtrim($query, ",");
		$query .= ";";
	}
    $result = mysqli_query($mysqli, $query);
    if(!$result) { echo mysqli_error() . $query . PHP_EOL; }
}
}
}

header('Location: index.php?status=1');

?>
