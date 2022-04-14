<?php
session_start();

$start_time = $_GET['datefrom'];
$end_time = $_GET['dateto'];
$serviceid = $_GET['serviceid'];
$f_risk = $_GET['f_risk'];
$f_impact = $_GET['f_impact'];

require_once('/var/www/html/lib/db_connect.php');
$connection = new DbConnection();

#$converted_start = date("Y-m-d h:i:s", $start_time/1000);
#$converted_end = date("Y-m-d h:i:s", $end_time/1000);
$converted_start = date("Y-m-d 00:00:00", strtotime($start_time));
$converted_end = date("Y-m-d 23:59:59", strtotime($end_time));

#$query_string = "SELECT c.form_id, c.start, c.end, s.id, s.name, 'STAGE' as stage, c.assigner, 'SUCCESS' as status, c.desc FROM changeform c, service_name s WHERE c.service_id = s.id AND c.risk = 'low' AND c.impact = 'low' AND s.id = 171 AND c.start >= '2015-02-01 00:00:00' and c.start <= '2015-03-06 00:00:00'";

if ($serviceid == 'All') {
	$serviceid_sqlcheck = null;
} else {
	$serviceid_sqlcheck = "AND s.id = $serviceid\n"; 
}

$query_string = "SELECT
					c.form_id, c.start, c.end, s.id as service_id, s.name as service_name, 'STAGE' as stage,
					c.assigner, c.assigned_to, c.risk, c.impact, c.priority, c.cso, c.change_type, 'SUCCESS' as status, c.desc 
				FROM changeform c, service_name s
				WHERE
					c.service_id = s.id
					AND c.risk = '" . $f_risk . "'
					AND c.impact = '" . $f_impact . "'
					$serviceid_sqlcheck
					AND c.start >= '" . $converted_start . "'
					AND c.start <= '" . $converted_end . "'
				ORDER BY c.start DESC";

$result = $connection->query($query_string);

$kpi_form = array();
while($row = $result->fetch_assoc()) {

    $date = date("Y-m-d", strtotime($row['start'])); 
    $time = date("h:i a", strtotime($row['start'])); 

    $id = $row['form_id'];
    $start = $row['start'];
    $end = $row['end'];
    $service_id = $row['service_id'];
    $service_name = $row['service_name'];
    $env = $row['stage'];
    $assigner = $row['assigner'];
    $assigned_to = $row['assigned_to'];
    $risk = $row['risk'];
    $impact = $row['impact'];
    $priority = $row['priority'];
    $cso = $row['cso'];
    $change_type = $row['change_type'];
    $status = $row['status'];
    $desc = $row['desc'];

	$kpi_form[$id]['start'] = $start;
	$kpi_form[$id]['end'] = $end;
	$kpi_form[$id]['date'] = $date;
	$kpi_form[$id]['time'] = $time;
	$kpi_form[$id]['service_id'] = $service_id;
	$kpi_form[$id]['service_name'] = $service_name;
	$kpi_form[$id]['env'] = $env;
	$kpi_form[$id]['assigner'] = $assigner;
	$kpi_form[$id]['assigned_to'] = $assigned_to;
	$kpi_form[$id]['risk'] = $risk;
	$kpi_form[$id]['impact'] = $impact;
	$kpi_form[$id]['priority'] = $priority;
	$kpi_form[$id]['cso'] = $cso;
	$kpi_form[$id]['change_type'] = $change_type;
	$kpi_form[$id]['status'] = $status;
	$kpi_form[$id]['desc'] = $desc;
}

$_SESSION['kpi_form'] = $kpi_form;

header("Location: kpi_form_results.php?datefrom=$start_time&dateto=$end_time&serviceid=$serviceid&f_risk=$f_risk&f_impact=$f_impact");

?>
