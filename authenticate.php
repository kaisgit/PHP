<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

function authenticate($page_name)
{
	///////////////////////////////////
	//	Connect to DB
	///////////////////////////////////
	$db_host = "localhost"; 
	$db_username = "";  
	$db_pass = "";  
	$db_name = "main"; 
	mysql_connect("$db_host","$db_username","$db_pass") or die ("could not connect to mysql");
	mysql_select_db("$db_name") or die ("no database");  


	require_once('/var/simplesaml/lib/_autoload.php');
	$auth = new SimpleSAML_Auth_Simple('scexecdash');
	$auth->requireAuth();

	$attributes = $auth->getAttributes();
	$uid = $attributes["uid"][0];

	$query_result = mysql_query("SELECT `uid`,`access` FROM `one_page_access` WHERE `uid` = \"{$uid}\"");
	$row = mysql_fetch_assoc($query_result);

	if(!in_array($page_name, explode(",", $row["access"])))
	{
		header( 'Location: https://localhost/unauthorized.html' ) ;
	}
	$query_result2 = mysql_query("SELECT `name` FROM `username` WHERE `uid` = \"{$uid}\"");
	$row2 = mysql_fetch_assoc($query_result2);
	$id = $row2['name'];
	return $id;
}
?>
