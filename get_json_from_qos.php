<?php

error_reporting(E_ALL ^ E_NOTICE);

date_default_timezone_set('America/Los_Angeles');

$headers = array(
        "X-Api-Key: 3TMd2X7T4395C2U3j737FL2rZ69mzJ6T",
        "Content-Type: application/json"
        );

$date_start = "12/01/2014";
$date_end = "04/21/2015";
$service_id = 174;

$date_start_millisecs = strtotime($date_start)*1000;
$date_end_millisecs = strtotime($date_end)*1000;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://localhost/api/1.0/historical/ticket.json?ids=$service_id&caused_by_change=true&range_start=$date_start_millisecs&range_end=$date_end_millisecs");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$res = json_decode(curl_exec($ch),true);

print count($res['data']['tickets']) . "\n";

$summary = array();
foreach ($res['data']['tickets'] as $rec) {
	$start = $rec['IMPACT_START']/1000;
	$date_start = date("Y-m-d", $start);

	$start_week = date('mdY', strtotime('Sunday Last Week', strtotime($date_start)));
	$end_week = date('mdY', strtotime('Saturday This Week', strtotime($date_start)));

	$summary["$start_week $end_week"]['incidents_caused_by_change'] += 1;
	$summary["$start_week $end_week"]['date_start'] = $date_start;
}

print "<pre>";
print_r($summary);
print "</pre>";

?>
