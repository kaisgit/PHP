<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 'On');

if(isset($_GET["term"])){
	$keyword = $_GET["term"];
	$filter = "(&(|(displayname={$keyword}*)(mail={$keyword}*))(objectClass=person))";
	search_ldap($filter);
}
	
else if(isset($_GET["username"])){
	$keyword = $_GET["username"];
	$filter = "(&(mail={$keyword}@localhost.com)(objectClass=person))";
	search_ldap($filter);
}
	
if(!isset($filter)){
	echo json_encode(array("-nothing-"));
}

function search_ldap($filter){
	$filter_attributes = array("displayname", "mail", "title","telephonenumber");
	$base_dn = "cn=users,dc=localhost,dc=global,dc=localhost,dc=com";
	$ldap_bind_user  = "CN=toolz2,CN=Users,DC=localhost,DC=global,DC=localhost,DC=com";
	$ldap_bind_pass = "";
	$ldap_connection = ldap_connect( "ldaps://LOCALHOST") or die(">>Could not connect to LDAP server<<");
	ldap_bind($ldap_connection, $ldap_bind_user, $ldap_bind_pass) or die(binding_fail(ldap_error($ldap_connection)));

	// Execute search
	$ldap_sr = ldap_search($ldap_connection, $base_dn, $filter, $filter_attributes) or die(json_encode(array("Unable to search ldap server")));
	$result = ldap_get_entries($ldap_connection, $ldap_sr);

	ldap_close($ldap_connection);

	echo get_result_list($result,$filter_attributes);
}

function binding_fail($message){
	header('HTTP/1.1 503 Service Unavailable', true, 503);
	$output = array();
	$output[0]["displayname"] = $message;
	$output[0]["mail"] = "-";
	$output[0]["telephonenumber"] = "-";
	echo json_encode($output);
}


function get_result_list($ldap_result,$filter_attributes){
	$result_count = $ldap_result['count'];
	$output = array();	
	for ($i=0; $i < $result_count; $i++) { 
		//Populate the displayed list
		if(isset($ldap_result[$i]['displayname'][0]))
			$value_field = $ldap_result[$i]['displayname'][0] . " | " ;
		else
			$value_field = "no-displayed-name | " ;

		if(isset($ldap_result[$i]['title'][0]))
			$value_field .= $ldap_result[$i]['title'][0];
		else
			$value_field .= "no-title";

			
		$output[$i]["value"] = $value_field;
		
		foreach ($filter_attributes as $attribute) {
			if(isset($ldap_result[$i][$attribute][0]))
				$output[$i][$attribute] =  $ldap_result[$i][$attribute][0];
			else
				$output[$i][$attribute] =  "none";
		}
	}
	if(count($output) > 0)
		return json_encode($output);
	else
		return json_encode(array("- No Result -"));
}
?>
