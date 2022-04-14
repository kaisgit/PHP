<?php
    session_start();

	error_reporting(E_ALL ^ E_NOTICE);
	
	date_default_timezone_set('America/Los_Angeles');

	$start_time = $_GET['datefrom'];	// mm/dd/yyyy
	$end_time = $_GET['dateto'];
	$serviceid = $_GET['serviceid'];

	$converted_start = date("Y-m-d 00:00:00", strtotime($start_time));	// yyyy-mm-dd 00:00:00
	$converted_end = date("Y-m-d 23:59:59", strtotime($end_time));

	$_SESSION['datefrom'] = $_GET['datefrom'];
	$_SESSION['dateto'] = $_GET['dateto'];
	$_SESSION['serviceid'] = $_GET['serviceid'];

	if ($serviceid == "All") {
		$sql_serviceid = null;
	} else {
		$sql_serviceid = "AND service_id = $serviceid ";
	}

	##################################################################################
	# INCIDENTS CAUSED BY CHANGE from QoS
	##################################################################################
	$summary = array();

	$headers = array(
        "X-Api-Key: 3TMd2X7T4395C2U3j737FL2rZ69mzJ6T",
        "Content-Type: application/json"
        );

	$date_start_millisecs = strtotime($start_time)*1000;
	$date_end_millisecs = strtotime($end_time)*1000;

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://localhost/api/1.0/historical/ticket.json?ids=$serviceid&caused_by_change=true&range_start=$date_start_millisecs&range_end=$date_end_millisecs");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$res = json_decode(curl_exec($ch),true);

	foreach ($res['data']['tickets'] as $rec) {
		$start = $rec['IMPACT_START']/1000;
		$date_start = date("Y-m-d", $start);

		$start_week = date('mdY', strtotime('Sunday Last Week', strtotime($date_start)));
		$end_week = date('mdY', strtotime('Saturday This Week', strtotime($date_start)));

		$summary["$start_week $end_week"]['incidents_caused_by_change'] += 1;
	}

	$incidents_caused_by_change_total = count($res['data']['tickets']);

	#print "<pre>";
	#print_r($summary);
	#print "</pre>";

	##################################################################################
	# TOTAL CHANGE VOLUME from CHANGEFORM DATABASE
	##################################################################################
	require_once('/var/www/html/lib/db_connect.php');
	$connection = new DbConnection();

	$query_string = "SELECT count(*) as total,
			start,
			date_format(date_add(start, INTERVAL(1-DAYOFWEEK(start)) DAY), '%b %d %Y') as start_week,
			date_format(date_add(start, INTERVAL(7-DAYOFWEEK(start)) DAY), '%b %d %Y') as end_week,
			concat(date_format(date_add(start, INTERVAL(1-DAYOFWEEK(start)) DAY), '%m%d%Y'),
				' ',
				date_format(date_add(start, INTERVAL(7-DAYOFWEEK(start)) DAY), '%m%d%Y')) as id
		FROM changeform
		WHERE
			start >= '$converted_start' AND
			start <= '$converted_end'
			$sql_serviceid
		GROUP BY start_week, end_week
		ORDER BY start";

	$result = $connection->query($query_string);

	$final_summary = array();
	while($row = $result->fetch_assoc()) {
        $total = $row['total'];
		$total_change_volume += $total;
        $start = $row['start'];
        $start_week = $row['start_week'];
        $end_week = $row['end_week'];
        $id = $row['id'];

		if ($summary[$id]) {

			$percent_rate = (($summary[$id][incidents_caused_by_change] / $total)*100);
			$final_summary[$id]['percent_rate'] = ceil($percent_rate);

			# UNCOMMENT TO DEBUG
			#print "Incidents Caused By Change / total change volume * 100 <br>";
			#print $summary[$id][incidents_caused_by_change] . " / " . $total . " * 100 = "  . $percent_rate . "<p>";

			$final_summary[$id]['incidents_caused_by_change'] = $summary[$id][incidents_caused_by_change];
			$final_summary[$id]['total_change_volume'] = $total;
			$final_summary[$id]['start_week'] = $start_week;
			$final_summary[$id]['end_week'] = $end_week;
		} else {
			$final_summary[$id]['percent_rate'] = 0; 
			$final_summary[$id]['incidents_caused_by_change'] = 0;
			$final_summary[$id]['total_change_volume'] = $total;
			$final_summary[$id]['start_week'] = $start_week;
			$final_summary[$id]['end_week'] = $end_week;
		}
	}

	$_SESSION['data'] = $final_summary;

	$change_incident_rate = round(($incidents_caused_by_change_total / $total_change_volume)*100) . "%";

	$_SESSION['change_incident_rate'] = $change_incident_rate;
	$_SESSION['incidents_caused_by_change_total'] = $incidents_caused_by_change_total;
	$_SESSION['total_change_volume'] = $total_change_volume;

	#print "<pre>";
	#print_r($final_summary);
	#print "</pre>";

	##################################################################################
	# CHANGE BY TYPE
	##################################################################################
	$query_string = "SELECT
						distinct change_type,
						count(*) as subtotal,
						(select count(*) from changeform) as total
					FROM changeform
					WHERE
						start >= '$converted_start' AND
						start <= '$converted_end'
						$sql_serviceid
					GROUP BY change_type";

	$result = $connection->query($query_string);

	while($row = $result->fetch_assoc()) {
		$change_type = $row['change_type'];
		$subtotal = $row['subtotal'];
		$total = $row['total'];

		$changebytype[$change_type] = $subtotal;
	}

	$_SESSION['changebytype'] = $changebytype;

	##################################################################################
	# CHANGE BY PRIORITY
	##################################################################################
	$query_string = "SELECT
						distinct priority,
						count(*) as subtotal,
						(select count(*) from changeform) as total
					FROM changeform
					WHERE
						start >= '$converted_start' AND
						start <= '$converted_end'
						$sql_serviceid
					GROUP BY priority";
	$result = $connection->query($query_string);

	while($row = $result->fetch_assoc()) {
		if ($row['priority'] == "") {
			$row['priority'] = "Null";
		}
		$priority = $row['priority'];
		$subtotal = $row['subtotal'];

		$changebypriority[$priority] = $subtotal;
	}

	$_SESSION['changebypriority'] = $changebypriority;

	header('Location: kpi_dashboard_results.php');

?>
