<?php

date_default_timezone_set('UTC');

#$last_updated_timestamp = date("Y-m-d",strtotime("-1 week"));
#$last_updated_timestamp = "2015-03-27";
$last_updated_timestamp = date("Y-m-d",strtotime("-1 year"));

$curl = curl_init("http://localhost/api/get_impact");
curl_setopt($curl, CURLOPT_POST, 1);
#curl_setopt($curl, CURLOPT_POSTFIELDS, array("last_update_datetime"=>"2015-01-10 00:00:00"));
curl_setopt($curl, CURLOPT_POSTFIELDS, array("last_update_datetime"=>$last_updated_timestamp." 00:00:00"));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$output = curl_exec($curl);

$result = array();
$result['data'] =  json_decode($output);

print "TIMESTAMP: $last_updated_timestamp<p />";

foreach ($result['data'] as $item) {
	$count += 1;
	foreach ($item as $key=>$value) {
		print "$key : $value<br>";
	}
	print "<br>==========================<br>";
}

print "TOTAL: $count";

curl_close($curl);
 
?>
