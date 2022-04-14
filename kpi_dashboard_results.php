<?php
    session_start();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Change Management Dashboard</title>

    <!-- INITIALIZE GOOGLE CHART API -->
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});

    <!-- ############### BEGIN: GOOGLE COMBO CHART ############### -->

	google.setOnLoadCallback(drawVisualization);
	function drawVisualization() {
		var change_incident_rate_data = google.visualization.arrayToDataTable([['Week', 'Total Change Volume', 'Incidents Caused by Change', 'Change Incident Rate'],
<?php
		if (isset($_SESSION['data'])) {
			$summary = $_SESSION['data'];

			foreach ($summary as $rec) {
	    		$start_year = date("Y", strtotime($rec['start_week']));
	    		$end_year = date("Y", strtotime($rec['end_week']));

	    		if ($start_year == $end_year) {
        			$start_week = date("M j", strtotime($rec['start_week']));
        			$end_week = date("j, Y", strtotime($rec['end_week']));
        			$week = $start_week . "-" . $end_week;
					print "['" . $week . "',";
	    		} else {
        			$start_week = date("M j, Y", strtotime($rec['start_week']));
        			$end_week = date("M j, Y", strtotime($rec['end_week']));
        			$week = $start_week . "-" . $end_week;
					print "['" . $week . "',";
	    		}

	    		print $rec['total_change_volume'] . ",";
	    		print $rec['incidents_caused_by_change'] . ",";
	    		print $rec['percent_rate'];
	    		print "],\n";
        	}
		}
?>
		]);

		var change_incident_rate_options = {
	   		title : 'Change Management',
	   		vAxis: {title: "Total Change Volume"},
	   		hAxis: {title: "Week"},
	   		legend: {position: "bottom"},
	   		backgroundColor: '#FAFAFA',
	   		seriesType: "bars",
	   		series: {2: {type: "line"}}
		};

		var change_incident_rate_chart = new google.visualization.ComboChart(document.getElementById('combo_chart_div'));
		change_incident_rate_chart.draw(change_incident_rate_data, change_incident_rate_options);
	}
    <!-- ############### END OF GOOGLE COMBO CHART ############### -->

    <!-- ############### BEGIN: CHANGE BY TYPE CHART ############### -->

    google.setOnLoadCallback(drawChart_change_by_type);
    function drawChart_change_by_type() {
		var change_by_type_data = google.visualization.arrayToDataTable([['CHANGE BY TYPE','SUBTOTAL'],
<?php
		if (isset($_SESSION['changebytype'])) {
			$changebytype = $_SESSION['changebytype'];

			foreach ($changebytype as $key=>$value) {
				 print "['" . $key . "'," . $value . "],\n";
			}
		}
?>
		]);

		var change_by_type_options = {
			title: 'CHANGE BY TYPE',
			sliceVisibilityThreshold: 1/10000
		};

		var change_by_type_chart = new google.visualization.PieChart(document.getElementById('piechart1'));
		change_by_type_chart.draw(change_by_type_data, change_by_type_options);
	}
    <!-- ############### END OF CHANGE BY TYPE CHART ############### -->

    <!-- ############### BEGIN: PRIORITY CHART ############### -->

	google.setOnLoadCallback(drawChart_change_by_priority);
	function drawChart_change_by_priority() {
		var change_by_priority_data = google.visualization.arrayToDataTable([['CHANGE BY PRIORITY','SUBTOTAL'],
<?php
		if (isset($_SESSION['changebypriority'])) {
			$priority = $_SESSION['changebypriority'];

			foreach ($priority as $key=>$value) {
				 print "['" . ucfirst($key) . "'," . $value . "],\n";
        	}
    	}
?>
    	]);
		var change_by_priority_options = {
			title: 'CHANGE BY PRIORITY',
			sliceVisibilityThreshold: 1/10000,
		};

		var change_by_priority_chart = new google.visualization.PieChart(document.getElementById('piechart2'));
		change_by_priority_chart.draw(change_by_priority_data, change_by_priority_options);
	}
    <!-- ############### END OF PRIORITY CHART ############### -->

	<!-- ############### BEGIN: DIAGNOSTIC TIME DURATION (MINS) ############### -->

      google.load("visualization", "1", {packages:["gauge"]});
      google.setOnLoadCallback(drawChart_gauge1);
      function drawChart_gauge1() {

        var data_gauge1 = google.visualization.arrayToDataTable([
          ['Label', 'Value'],
          ['', 29.50],
        ]);

        var options_gauge1 = {
          width: 400, height: 300,
          redFrom: 36, redTo: 48,
          yellowFrom: 30, yellowTo: 36,
	  greenFrom: 0, greenTo: 30,
          minorTicks: 5,
	  max: 48
        };

        var chart_gauge1 = new google.visualization.Gauge(document.getElementById('chart_div_gauge1'));

        chart_gauge1.draw(data_gauge1, options_gauge1);

        setInterval(function() {
          data_gauge1.setValue(0, 1, 40 + Math.round(60 * Math.random()));
          chart_gauge1.draw(data_gauge1, options_gauge1);
        }, 13000);
      }
	<!-- ############### END OF DIAGNOSTIC TIME DURATION (MINS) ############### -->
	</script>

	<style type="text/css">
	.smallbox {
    	border: 1px solid #DDD;
    	display: inline-block;
    	vertical-align: top;
    	width: 220px;
    	margin: 20px 20px 0px 20px;
    	font-family: Arial, Helvetica;
    	font-size: 15px;
    	color: #222222;
    	text-decoration: bold;
    	text-align: center;
    	border-radius: 0 0 5px 5px;
	}
	.boxtop {
    	background-color: #D4F7EB;
    	padding: 10px;
    	border-radius: 0;
	}
	.boxbottom {
    	background-color: #FFF;
    	padding-top: 15px;
    	padding-bottom: 10px;
    	border-radius: 0 0 5px 5px;
	}
	.combo_chart {
    	border: 1px solid #DDD;
		width: 1300px;
		height: 400px;
		margin-top: 35px;
		margin-left: 20px;
	}
	.piechart {
		margin-top: 35px;
	}
	table.piechart1 {
    	border: 1px solid #FFF;
	}
	table.piechart2 {
    	border: 1px solid #FFF;
	}
	.separator {
		width: 40px;
    	border: 1px solid #FFF;
		background-color: #FFF;
	}
	</style>
</head>
<body>
 
<?php
	require("kpi_dashboard_search.php");

    $change_incident_rate = $_SESSION['change_incident_rate'];
    $incidents_caused_by_change_total = $_SESSION['incidents_caused_by_change_total'];
    $total_change_volume = $_SESSION['total_change_volume'];
?>

    <div class="smallbox">
        <div class="boxtop">Change Success Rate</div>
        <div class="boxbottom">66%</div>
    </div>
    <div class="smallbox">
        <div class="boxtop">Incidents Caused By Change</div>
        <div class="boxbottom">
			<?php print $incidents_caused_by_change_total; ?>
        </div>
    </div>
    <div class="smallbox">
        <div class="boxtop">Unauthorized Change Rate</div>
        <div class="boxbottom">21%</div>
    </div>
    <div class="smallbox">
        <div class="boxtop">Change Volume</div>
        <div class="boxbottom">50</div>
    </div>
    <div class="smallbox">
        <div class="boxtop">Change Volume Rate</div>
        <div class="boxbottom">93%</div>
    </div>

	<div class="combo_chart">
		<div id="combo_chart_div" style="width: 1300px; height: 400px;"></div>
	</div>

	<table class="piechart">
		<tr>
			<td><div id="piechart1" style="width: 600px; height: 400px;"></td>
			<td class="separator" style="background-color: #FFF;"></td>
			<td><div id="piechart2" style="width: 600px; height: 400px;"></td>
		</tr>
	</table>


<!--
<b>DIAGNOSTIC TIME DURATION (MINS)</b>
<div id="chart_div_gauge1" style="width: 400px; height: 300px;"></div>
-->


</body>
</html>

<?php
	session_destroy();
?>
