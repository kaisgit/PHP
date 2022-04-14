<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$headers = array(
	"X-Api-Key:3TMd2X7T4395C2U3j737FL2rZ69mzJ6T",
	"Content-Type: application/json"
	);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://localhost/1.0/names.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$res = json_decode(curl_exec($ch),true);


$result = array();
foreach ($res["data"]["names"] as $service) {
	$tmp = array();
	$tmp["service_id"] = $service["ID"];
	$tmp["service_name"] = $service["NAME"];
	$tmp["client_name"] = "";
	$result[] = $tmp;
}
echo json_encode($result);

?>
