<html>
<head>
<title>Component Details Update - Splunk</title>

<link rel="stylesheet" type="text/css" href="css/table.css">

<style type="text/css">
.submit {
    font-family: 'Nunito', sans-serif;
    color: #ffffff;
    font-size: 16px;
    background: #3498db;
    padding: 10px 20px 10px 20px;
    text-decoration: none;
    border: 0;
    margin-left: 850px;
}
.submit:hover {
    background: #3cb0fd;
    text-decoration: none;
}
.message {
	width: 850px;
}
</style>

</head>
<body>

<table>
<caption>Component Update</caption>

<thead>

<tr><td class="message">

<?php

	if ( $_POST[unique] == "Please Select:" || $_POST[unique] == 'Other' OR empty($_POST[unique])) {
        if($_POST[group] == "Please Select:" OR empty($_POST[group]) OR $_POST[group] == "Other") {
		print "Please select a Unique Component Name or a Group Name."; 
        }
        else
        {
            require_once("db_connect.php");

            # must connect to database to use mysql_real_escape_string()
            #
            $id = $_POST[id];
            $group = mysql_real_escape_string($_POST[group]);

            $query = "UPDATE ec2_components SET group_name = '$group' WHERE id = $id";
            $result = mysql_query($query);

            if ($result) {
                print "Update Successful. <a href='/splunk/component_index.php'>Back to Main.</a>";
            } else {
                print "Update Failed. Please contact the administrator.";
            }
        }
	} elseif ($_POST[group] == "Please Select:" || $_POST[group] == "Other" OR empty($_POST[group])) {
		require_once("db_connect.php");

		# must connect to database to use mysql_real_escape_string()
		#
		$id = $_POST[id];
    	$unique = mysql_real_escape_string($_POST[unique]);

		$query = "UPDATE ec2_components SET unique_component_name = '$unique' WHERE id = $id";
		$result = mysql_query($query);

		if ($result) {
			print "Update Successful. <a href='/splunk/component_index.php'>Back to Main.</a>";
		} else {
			print "Update Failed. Please contact the administrator.";
		} 
	} else {
		require_once("db_connect.php");

		# must connect to database to use mysql_real_escape_string()
		#
		$id = $_POST[id];
    	$unique = mysql_real_escape_string($_POST[unique]);
    	$group = mysql_real_escape_string($_POST[group]);

		$query = "UPDATE ec2_components SET unique_component_name = '$unique', group_name = '$group' WHERE id = $id";
		$result = mysql_query($query);

		if ($result) {
			print "Update Successful. <a href='/splunk/component_index.php'>Back to Main.</a>";
		} else {
			print "Update Failed. Please contact the administrator.";
		}
	}

session_destroy();

?>

</td></tr>

</thead>

</table>
