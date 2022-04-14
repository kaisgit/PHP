<?php
date_default_timezone_set('America/Los_Angeles');
#

$result = array_filter($_POST);
#$result = array_filter($_GET);

if (preg_match('/Run Report/i', $result['submit'][0])) {
	require_once("splunk_cloudability/storage.php");
	require_once("splunk_cloudability/cloudability.php");
    //shell_exec("php /home/tools/aws_detailed_billing/storage/storage.php");
    //shell_exec("php /home/tools/splunk_aws_bill/shared_cloud_aws_usage/cloudability_splunk/cloudability.php");
	print "Dashboard is updating... Please check dashboard after 2 mins. <a href='https://localhost/splunk/client_index.php'>Go Back</a>";
} else {
	require_once("/var/www/html/splunk/db_connect.php");
	$error_message = NULL;
	foreach ($result as $key=>$value) {
		# PHP converts spaces and dots in $key into underscores. This causes a problem when identifying the column name in the database.
		# Convert underscores into % to match with the column name in the database. 
		if(stripos($value,"Please Select") === FALSE)
    	{
        	$key = str_replace('_','%',$key);
        	#
        	# Sanitize input
        	# Must connect to the database to use mysql_real_escape_string()
        	#
        	$value = mysql_real_escape_string($value);
        	$value = format_data($value);
        	$query = "UPDATE Client SET Client = '" . $value . "',`client_mapped_on` = '".date("Y-m-d")."' where `Splunk Name` like '$key'";
    
        	$result = mysql_query($query);
        	if(!$result) { $error_message .= "Error updating ".$key."<br>".mysql_error()."<br> Query: ".$query."<br><br>"; }
    	}
	}

	if ($result) {
		print "Update Successful. <a href='https://localhost/splunk/client_index.php'>Go Back</a>";
    	//<a href='javascript:history.back()'>Go Back</a>
    	#$output = exec('php /home/tools/splunk_aws_bill/shared_cloud_aws_usage/khai/cloudability.php');
    	$output = exec('php cloudability.php');
    	echo "<pre>".$output."</pre>";
	} else {
		print "Update Failed. Please contact the administrator.<br><br><b>Error Messages:</b><br>".$error_message;
	}
} # END of ELSE() STATEMENT

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
