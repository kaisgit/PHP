<?php

$db_host = "localhost";
$db_username = "";
$db_pass = "";
$db_name = "cloudops_db";

$mysqli = new mysqli("$db_host","$db_username","$db_pass","$db_name");

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
?>
