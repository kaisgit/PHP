<?php
ini_set("display_errors",1);
///////////////////////////////////
	//	Connect to DB
	///////////////////////////////////
 
$db_host = "toolz.com";
$db_username = "";
$db_password = "";
$db_name = "dbname";


mysql_connect("$db_host","$db_username","$db_pass") or die ("could not connect to mysql");
mysql_select_db("$db_name") or die ("no database");              


if(!isset($_POST['method'])) die("no method"); 

if($_POST['method'] == 'authenticate_ldap')				authenticate_ldap($_POST["username"] , $_POST["password"]);
else if($_POST['method'] == 'cookie_check_validity')	cookie_check_validity();
else if($_POST['method'] == 'cookie_write')				cookie_write($_POST["username"]);
else if($_POST['method'] == 'get_tool_access')		 	get_user_tool_access(); 


/******************************************************************************
 * Ldap Connect grabs username & password, and echo one of the following:
 * - OK   					 	-> authenticated successfully
 * - Authentication failed   	-> wrong username/password
 * - unable to connect to ldap
 ******************************************************************************/
function authenticate_ldap($username,$password,$ldapHost='COMPANY.COM')
{
	$ldapBaseDN = ',CN=Users,DC=net,DC=global,DC=company,DC=com';
	$filter = "(&(objectClass=user)(objectCategory=person)(cn=*))";

	$ldapConnectResult = ldap_connect($ldapHost);

	if ($ldapConnectResult) 
	{ 
		$ldapDN = "CN=" . $username . $ldapBaseDN;
		
		if(strlen($password) > -1)
			$binding_result=ldap_bind($ldapConnectResult,$ldapDN,$password);
		else
			$binding_result=false;
		
		if ($binding_result)
			echo "OK";
		else
			echo "Authentication Failed";
	}
	else
		return "unable to connect to ldap";
}


/*
 * Check cookie Validity. If valid, echo the username
 */
function cookie_check_validity()
{
	ini_set("display_errors",1);
	if(isset($_COOKIE['ldap_authenticated']) && $_COOKIE['ldap_authenticated'])
	{
		$username   = $_COOKIE['username'];
		$last_login = $_COOKIE['last_login'];
		if (($last_login + 259200) > time())	
		{
			cookie_write($username,false);
			echo "$username";
		}
		else echo 'expired';
	}
	else echo 'no_cookie';
}

/*
 * Set Cookie to expire after 3 days
 */
function cookie_write($username,$echo=true)
{
	setcookie('ldap_authenticated',true,time() + (86400 * 3),'/');
	setcookie('last_login',time(),time() + (86400 * 3),'/');
	setcookie('username',$username,time() + (86400 * 3),'/');
	
	$query_string = "SELECT `tool_access` FROM `dashboard_user_tool_access` WHERE  `ldap_username`='" . $username . "'";
	$query_result = mysql_query($query_string);
	$row = mysql_fetch_row($query_result);
	if(strlen($row[0]) > 3)
	{
		$access_list = explode(",",$row[0]);
		foreach($access_list as $tools_name)
			setcookie($tools_name,true,time() + (86400 * 3),'/');
	}
	//else
	//	echo "none";
	
	if($echo) echo 'done';
}


/*
 * Check Database and get the tool access in comma separated
 */
function get_user_tool_access()
{
	$username   = $_COOKIE['username'];
	$query_string = "SELECT `tool_access` FROM `dashboard_user_tool_access` WHERE  `ldap_username`='" . $username . "'";
	$query_result = mysql_query($query_string);
	$row = mysql_fetch_row($query_result);
	if(strlen($row[0]) > 3)
		echo $row[0];
	else
		//echo "autoscale,gooddata,tableau";
		echo "autoscale,gooddata";
	
}
?>
