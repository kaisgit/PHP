<html>
<head>
<title>Client Update Information - Splunk</title>

<script type='text/javascript'></script>
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
	margin-left: 800px; 
}
.runreport {
	font-family: 'Nunito', sans-serif;
	color: #ffffff;
	font-size: 16px;
	background: #cc3300;
	padding: 10px 20px 10px 20px;
	text-decoration: none;
	border: 0;
	margin-left: 0px; 
}
.runreport:hover {
	background: #ff0000;
	text-decoration: none;
}
.submit:hover {
	background: #3cb0fd;
	text-decoration: none;
}
</style>

</head>
<body onload="document.refresh();"> 
<script src="jquery.js"></script>
<script src="sc_usage.js"></script>
<link rel="stylesheet" href="style.css" type="text/css" media="screen"/>
 <!--   <center><button class="refresh_button" id="id" onClick="storage()">Update Dashboard</button>
    <div id="status_message"></div>
    </center> 
-->
    
<form method="post" action="update_client.php">

<?php

#error_reporting(E_ALL);

require_once("db_connect.php");

$query = "SELECT `Splunk Name`, `Client`, `report_end_date` FROM `Client` ORDER BY `report_end_date` DESC, `Splunk Name` ASC, `Client` ASC";
$result = mysql_query($query);

$data = array();
$clients = array();
$report_end_date = array();
$log_date_array = array();
$new = 0;
while ($row = mysql_fetch_assoc($result)) {
	$splunk_name = $row['Splunk Name'];
    $client = $row['Client'];
    $log_date = $row['report_end_date'];
    $log_date_array = explode(",",$log_date);
    $count = count($log_date_array);
    for($i=0;$i<$count;$i++)
    {
        if($log_date_array[$i] != "" AND $log_date_array[$i] != "0000-00-00" AND $log_date_array[$i] != " " AND $log_date_array[$i] != NULL)
        { $log_date_array[$i] = date("M Y",strtotime($log_date_array[$i])); }
    }
    $log_date = implode(", ",$log_date_array);
    if($log_date == "0000-00-00") { $log_date = ""; }
    if ($client == '<new client>') {
    	array_push($data, $splunk_name);
        $report_end_date[$splunk_name] = $log_date;
        $new++;
    } else {
		array_push($clients, $client);
	}
}
natcasesort($clients);
$clients = array_unique($clients);
?>


<table>
<caption>Client Update Information <?php echo "<font size=\"2\"> (".$new." new client ids) </font>"; ?> </caption>
<thead>
<tr>
	<th scope="col" class="clientid">Client ID</th>
	<th scope="col">Clients</th>
	<th scope="col" class="expand"></th>
    <th scope="col" style="width:150px">Report Month</th>
</tr>
</thead>

<?php

$count = 1;
foreach ($data as $rec) {
	
	print "<tr>";

	print "<script type='text/javascript'>\n";
	print "function showfield$count(name){\n";
	print "if(name=='Other')document.getElementById('div$count').innerHTML='<input type=\"text\" name=\"$rec\" />'\n";
	print "else document.getElementById('div" . $count . "').innerHTML=''\n";
	print "}\n";
	print "</script>\n";

	# Split big string into chunks otherwise string will cause UI to scroll to the right.
	print "<td>";
	$chunks = str_split($rec, 70);
	if (count($chunks) > 1) {
		foreach ($chunks as $item) {
			print "$item<br>";
		}
	} else {
		print "$rec";
	}
	print "</td>";

	print "<td>";
		print "<select name=\"$rec\" onchange='showfield" . $count . "(this.options[this.selectedIndex].value)'>\n";
		print "<option>Please Select:";
		print "<option value='Other'>OTHER</option>\n";
		foreach ($clients as $item) {
			print "<option value='" . $item . "'>" . $item . "</option>\n";
		}
		print "</select>\n";
	print "</td>";

	print "<td>\n";
	print "<div id='div" . $count . "'></div>\n";
	print "</td>\n";
    
    print "<td>".$report_end_date[$rec];
    print "</td>";
	print "</tr>";
	$count++;
}

?>

<tfoot>
    <tr>
      <td><input class="runreport" type="submit" name="submit[]" value="Run Report" onClick="return confirm('Are you sure you want to run the report?');" /></td>
      <td colspan="3"><input class="submit" type="submit" name="submit[]" value="Submit" /></td>
    </tr>
</tfoot>

</table>

</form> 

</body>
</html>
