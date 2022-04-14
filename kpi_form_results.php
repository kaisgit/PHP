<?php
    session_start();
?>

<html>
<title>Change Logging Results</title>
<head>
    <script src="sorttable.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="css/table_results.css">
</head>
<body >

<?php
	require("kpi_form_search.php");
?>

<div id="table">
		<table class="sortable" width="99%">
		<thead>
		<tr>
    		<th>ID</th>
    		<th>Start</th>
    		<th>Time</th>
    		<th>Service Name</th>
    		<th>Environment</th>
    		<th>Executor Name</th>
    		<th>Status</th>
    		<th align="left">Change Description</th>
		</tr>
		</thead>
		<tbody>

<?php
	if (isset($_SESSION['kpi_form'])) {
		$summary = $_SESSION['kpi_form'];

		foreach ($summary as $id=>$value) {
				print "<tr>\n";
				print "<td><a href='kpi_form_result_details.php?form_id=$id'>" . $id . "</a></td>";
				print "<td>" . $value['date'] . "</td>";
				print "<td>" . $value['time'] . "</td>";
				print "<td nowrap>" . $value['service_name'] . "</td>";
				print "<td>" . $value['env'] . "</td>";
				print "<td>" . $value['assigned_to'] . "</td>";
				print "<td>" . $value['status'] . "</td>";
				print "<td nowrap align='left'>" . $value['desc'] . "</td>";
				print "</tr>";
		}
	}
?>

		</tbody>
	</table>
</div>

</body>
</html>
