<?php
session_start();
?>

<html>
<head>
<title>Component Update Information - Splunk</title>

<script type='text/javascript'></script>
<link rel="stylesheet" type="text/css" href="css/table.css">

</head>
<body>

<?php

#error_reporting(E_ALL);

require_once("db_connect.php");

$query = "SELECT * FROM ec2_components order by `group_name`";
$result = mysql_query($query);

$components = array();
#$name = $unique = $group = array();
while ($row = mysql_fetch_assoc($result)) {
	$id = $row['id'];
   	$name = $row['component_name_in_log'];
   	$unique = $row['unique_component_name'];
   	$group = $row['group_name'];

	$components[] = array(
						'id' => "$id",
						'component_name_in_log' => "$name",
						'unique_component_name' => "$unique",
						'group' => "$group");
}

$_SESSION['components'] = $components;

?>

<table>
<caption>Component Update Information</caption>
<thead>
<tr>
	<th scope="col">Component Name in Log</th>
	<th scope="col">Unique Component Name</th>
	<th scope="col">Group Name</th>
	<th scope="col"></th>
</tr>
</thead>

<?php

foreach ($components as $key=>$value) {
	if ( empty($value[unique_component_name]) || empty($value[group]) ) {
		print "<tr>";
		print "<td>$value[component_name_in_log]</td>";
		print "<td>$value[unique_component_name]</td>";
		print "<td>$value[group]</td>";
		print "<td style='text-align: center'><a href='component_details.php?id=" . rand(1000,9999) . base64_encode($value[id]) . "'>EDIT</a></td>";
		print "</tr>";
	}
}

?>

</table>


</body>
</html>
