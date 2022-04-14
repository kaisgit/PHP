<?php
$db_host = "toolz.com";  
$db_username = "";  
$db_pass = "";  
$db_name = "cloudops_db"; 

mysql_connect("$db_host","$db_username","$db_pass") or die ("could not connect to mysql");
mysql_select_db("$db_name") or die ("no database");   

if(!isset($_POST['method'])) die('uknown_method');
if($_POST['method'] == 'delete_db'){	delete_row($_POST["job_id"]); }

function delete_row($job_id)
{
    $query = "DELETE FROM `scheduler2` WHERE `job_id` = ".$job_id;
    $result = mysql_query($query);
    if(!mysql_error()) { echo "success"; }
}
?>
