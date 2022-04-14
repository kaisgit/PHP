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

	$query_result = mysql_query("SELECT `uid`,`access` FROM `page_access` WHERE `uid` = \"{$uid}\"");
	$row = mysql_fetch_assoc($query_result);

	if(!in_array($page_name, explode(",", $row["access"])) AND $page_name != "cso_approve_cc" AND $page_name != "cso_approve_sc" AND $page_name != "cso_approve_adc" AND $page_name != "cso_approve_dps")
	{
		header( 'Location: https://localhost/unauthorized.php' ) ;
		session_start();
		$_SESSION['access'] = 'denied';
	}
	elseif($page_name == "cso_approve_cc" OR $page_name == "cso_approve_adc" OR $page_name == "cso_approve_dps" OR $page_name == "cso_approve_sc" OR $page_name == "sc_exec_dash_storage_input")
	{
		if(!in_array($page_name, explode(",", $row["access"])) AND !in_array("cso_approve", explode(",", $row["access"])))
		{	
			header( 'Location: https://localhost/unauthorized.php' ) ;
			session_start();
			$_SESSION['access'] = 'denied';
		}
	}
	elseif($page_name != "cso_approve_cc")
	{
		if(strpos($row["access"],"sc_exec_dash_approver") !== false && strpos($row["access"],"sc_exec_dash_upload") === false)
		{
			header( 'Location: https://localhost/dashboards/approver.php' ) ;
		}
	}
}

function get_uid()
{
	require_once('/var/simplesaml/lib/_autoload.php');
	$auth = new SimpleSAML_Auth_Simple('scexecdash');
	$auth->requireAuth();

	$attributes = $auth->getAttributes();
	return $attributes["uid"][0];
}
?>
