<?php
	session_start();
?>

<html>
<head>
<title>Component Details Update - Splunk</title>

<script type='text/javascript'></script>
<link rel="stylesheet" type="text/css" href="css/table.css">

<style type="text/css">
table thead th {
	width: 250px;
}
table thead td.col2 {
	width: 330px;
}
table thead td.col3 {
	width: 240px;
}
table, thead, select {
	font-size: 18px;
}
input {
	font-size: 14px;
}
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
.notfound {
	width: 850px;
}
</style>

</head>
<body>

<?php

#$id = $_GET[id];
#$sid = substr($id, 4);

$sid = substr($_GET[id], 4);

$decode = base64_decode($sid);

require_once("db_connect.php");

$query = "SELECT * FROM ec2_components WHERE id = $decode order by component_name_in_log";
$result = mysql_query($query);

while ($row = mysql_fetch_assoc($result)) {
    $id = $row['id'];
    $name = $row['component_name_in_log'];
    $unique = $row['unique_component_name'];
    $group = $row['group_name'];	

	$components = array("$id","$name","$unique","$group");
}

?>

<form method="post" action="component_update.php">

<table>
<caption>Component Details Update</caption>

<thead>

<?php

$unique = array();
$group = array();
foreach ($_SESSION['components'] as $pointer) {
	$unique[] = $pointer[unique_component_name];
	$group[] = $pointer[group];
}

$unique = array_unique(array_filter($unique));
$group = array_unique(array_filter($group));

sort($unique, $group);

if ($components) {
    print "<script type='text/javascript'>\n";
    print "function showfield1(name){\n";
    print "if(name=='Other')document.getElementById('div1').innerHTML='<input type=\"text\" name=\"unique\" />'\n";
    print "else document.getElementById('div1').innerHTML=''\n";
    print "}\n";
    print "</script>\n";

    print "<tr><th>Component Name in Log</th><td class=\"col2\">$components[1]</td><td class=\"col3\"></td></tr>\n";
    print "<tr><th>Unique Component Name</th>\n";
	print "<td>";
		print "<select name='unique' onchange='showfield1(this.options[this.selectedIndex].value)'>\n";
		print "<option>Please Select:\n";
		print "<option value='Other'>OTHER</option>\n";

		foreach ($unique as $item) {
			if ($components[2] == $item) {
				print "<option value='" . $item . "' SELECTED>" . $item . "</option>\n";
			} else {
				print "<option value='" . $item . "'>" . $item . "</option>\n";
			}
		}

		print "</select>\n";
	print "</td>\n";
	print "<td><div id=\"div1\"></div></td>\n\n";

    print "<script type='text/javascript'>\n";
    print "function showfield2(name){\n";
    print "if(name=='Other')document.getElementById('div2').innerHTML='<input type=\"text\" name=\"group\" />'\n";
    print "else document.getElementById('div2').innerHTML=''\n";
    print "}\n";
    print "</script>\n";

    print "<tr><th>Group Name</th>\n";
	print "<td>";
		print "<select name='group' onchange='showfield2(this.options[this.selectedIndex].value)'>\n";
		print "<option>Please Select:\n";
		print "<option value='Other'>OTHER</option>\n";

		foreach ($group as $item) {
			if ($components[3] == $item) {
				print "<option value='" . $item . "' SELECTED>" . $item . "</option>\n";
			} else {
				print "<option value='" . $item . "'>" . $item . "</option>\n";
			}
		}
		print "</select>\n";
	print "</td>\n";
	print "<td><div id=\"div2\"></div></td>\n\n";

	print "</tr>";
	print "<tfoot>";
	print "<tr>";
	print "<td colspan=3><input class=\"submit\" type=\"submit\" value=\"Submit\" /></td>";
	print "</tr>";
	print "<tfoot>";
} else {
	print "<tr><td class=\"notfound\">Record not found.</td></tr>";
}

?>

</thead>

</table>

<input type="hidden" name="id" value="<?php print $components[0]; ?>">

</form>
