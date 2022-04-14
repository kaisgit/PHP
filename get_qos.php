<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

#curl -X GET 'https://localhost/api/1.0/names.json' -H 'X-Api-Key: 3TMd2X7T4395C2U3j737FL2rZ69mzJ6T' -d 'filter[alias]=JANUS_RENGA'
$headers = array(
        "X-Api-Key: 3TMd2X7T4395C2U3j737FL2rZ69mzJ6T",
        "Content-Type: application/json",
        );

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://localhost/api/1.0/names.json");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$res = json_decode(curl_exec($ch),true);

print "<pre>";
print_r($res);
print "</pre>";

foreach ($res["data"]["names"] as $service) {
	$id = $service["ID"];
	$name = $service["NAME"];

	print "$id : $name<br/>";
}

?>
