<?php

require_once('splunk_cloudability/functions.php');

date_default_timezone_set ("Etc/UCT");	//Splunk follows UTC
set_time_limit(7200); //2 hours maximum
if (FALSE)
{
    error_reporting(E_ALL);
    ini_set('display_errors', FALSE);
}
$link = connect_to_db();
$error_count = 0;

error_reporting(0);

######################################################################################################################
#	SET DEFAULT SPLUNK COMPONENTS & INITIALIZATING ARRAYS
#
$needed_splunk_components = array("imageserver","norriscontainer","servicecontainer","syncservicecontainer","norris");

$set_components = array("ImageServer","NorrisContainer","ServiceContainer","SyncServiceContainer","Norris");  // Search v2 = NorrisContainer

$country = array('us'=>'ue1',
				 'ap'=>'an1',
				 'eu'=>'ew1');

######################################################################################################################
#	GET START DATE AND END DATE OF REPORT
#
$last_month = date("Y-m",strtotime("-1 month"));
$start_date = $last_month."-19";

$this_month = date("Y-m");
$end_date = $this_month."-18";

######################################################################################################################
#  MAIN
#
cloudability($start_date,$end_date);

require_once('splunk_cloudability/splunk.php');
splunk($start_date,$end_date);

calc_horizontal_and_vertical_totals();
calc_allocation_common_infra();
calc_allocation_mongoDB();
calc_aws();
calc_final_aws();

######################################################################################################################
#  CLOUDABILITY CALCULATIONS
#
function cloudability($start_date,$end_date)
{
	global $indiv_clients, $grouped_clients;
	global $set_components;
    global $start_date, $end_date;
	$grand_total_weight = array();
	$cloudability = array();

	# add (weight >= 0) to remove any negative number.
	$query = "SELECT
				distinct region, sum(weight) as grand_total_weight
			FROM
				export_from_cloudability
			WHERE
				start_date >= '".$start_date."' and end_date <= '".$end_date."' AND
				weight >= 0
			GROUP BY
				region";

	$result = mysql_query($query);
	if(!$result) { $error_message = mysql_error()."<br><br> Query Failed: " . $query . "<br><br>"; $error_count++; }

	while ($row = mysql_fetch_assoc($result)) {
        $region = $row['region'];
        $total_weight = $row['grand_total_weight'];

		$grand_total_weight[$region] = $total_weight;
	}

    $query = "SELECT
				env, region, e.group_name, c.group_name as comp_group_name, sum(weight) as total_weight
			FROM
				export_from_cloudability e LEFT OUTER JOIN ec2_components c ON e.group_name = c.component_name_in_log
			WHERE
				start_date >= '".$start_date."' and end_date <= '".$end_date."' AND
				weight >= 0 
			GROUP BY
				env, region, e.group_name, c.group_name";

    $result = mysql_query($query);
	if(!$result) { $error_message = mysql_error()."<br><br> Query Failed: " . $query . "<br><br>"; $error_count++; }

	while ($row = mysql_fetch_assoc($result)) {
    	$env = $row['env'];
    	$region = $row['region'];
    	$group_name = $row['group_name'];
    	$comp_group_name = $row['comp_group_name'];
    	$total_weight = $row['total_weight'];

		if (empty($comp_group_name)) {
			$comp_group_name = "N/A";
		}

		$cloudability[$region][] = array('env' => $env,
										'group_name' => $group_name,
										'comp_group_name' => $comp_group_name,
										'total_weight' => $total_weight,
										'weight_percentage' => ($total_weight/$grand_total_weight[$region])*100);
	}

	foreach ($cloudability as $region=>$arr_value) {
		foreach ($arr_value as $item) {
			#print "$region :: $item[env] :: $item[group_name] :: $item[comp_group_name] :: $item[total_weight] :: $item[weight_percentage]\n";

			$comp_group_name = $item['comp_group_name'];

			if ($comp_group_name == "N/A") {
				$comp_group_name = "Common Infra";
			} elseif ($comp_group_name == "Search v2" or $comp_group_name == "NorrisContainer" ) {
				#$comp_group_name = 'NorrisContainer';
				$comp_group_name = 'Norris';
			} elseif ($comp_group_name == "Sync Service" ) {
				$comp_group_name = 'SyncServiceContainer';
			}

			if (in_array($comp_group_name, $set_components)) {
				$grouped_clients[$region][$comp_group_name]['sum_of_weight_perc'] += $item['weight_percentage'];
				$grouped_clients[$region]['grand_total'] += $item['weight_percentage'];
			}
			$indiv_clients[$region][$comp_group_name]['sum_of_weight_perc'] += $item['weight_percentage'];
			$indiv_clients[$region]['grand_total'] += $item['weight_percentage'];
		}
	}
}

########################################################################################################################################################
# 1. Get the horizontal total for the 4 clients (ImageServer, Search v2(Norris), ServiceContainer, SyncServiceContainer) for use to calculate the "Common Infra".
# 2. Get the vertical total for the 3 clients (ImageServer, ServiceContainer, SyncServiceContainer) for use to calculate "MongoDB". Search V2(Norris) is not used
#    for mongoDB.
#
function calc_horizontal_and_vertical_totals() {
	global $grouped_clients, $percentageByComponent_FORALL;
	global $country;
	global $final_four_components, $clients_by_components;
	global $grandtotal_by_each_client;

	foreach ($grouped_clients as $region=>$arr_clients) {
		foreach ($arr_clients as $client=>$arr_value) {
			foreach ($percentageByComponent_FORALL[$country[$region]][$client] as $key=>$value) {

				$sum_weight_perc = ($arr_value['sum_of_weight_perc'] * $value) / 100;

				$final_four_components[$country[$region]][$client][$key] = $sum_weight_perc;

				# Calc each component's HORIZONTAL total (ImageServer + Search v2(Norris) + ServiceContainer + SyncServiceContainer)
				#
				$clients_by_components[$region][$key][$client] = $sum_weight_perc;
				$clients_by_components[$region][$key]['horizontal_total_on_clients'] += $sum_weight_perc;

				# Calc client's VERTICAL total for 3 of 4 clients for mongodb. Norris(Search V2) is not used for mongodb.
				# (ImageServer, ServiceContainer, SyncServiceContainer)
				#
				if (($client == "ImageServer") || ($client == "ServiceContainer") || ($client == "SyncServiceContainer")) {
					$grandtotal_by_each_client[$region][$client]['vertical_total_on_clients'] += $sum_weight_perc;
				}
			}
		}
	}
}

########################################################################################
# Calculate the "Common Infra" in the "Allocation 80" tab of excel.
#
function calc_allocation_common_infra() {
	global $indiv_clients, $grouped_clients;
	global $clients_by_components;

	foreach ($clients_by_components as $region=>$arr_clients) {
		foreach ($arr_clients as $key=>$value) {

			# Calculate Common Infra and add to array.
			#
			$common_infra = ($value['horizontal_total_on_clients'] * $indiv_clients[$region]['Common Infra']['sum_of_weight_perc']) / $grouped_clients[$region]['grand_total'];
			$clients_by_components[$region][$key]['common_infra'] = $common_infra;

			# Add "Common Infra" to the existing horizontal total which is now the sum of:
			# Common Infra + (ImageServer + Search v2(Norris) + ServiceContainer + SyncServiceContainer)
			#
			$clients_by_components[$region][$key]['horizontal_total_on_clients'] += $common_infra;
		}
	}
    //print_r($clients_by_components);
}

########################################################################################
# Calculate the "MongoDB" in the "Allocation 80" tab of excel.
#
function calc_allocation_mongoDB() {
	global $clients_by_components, $indiv_clients;
	global $grandtotal_by_each_client;
	global $sum_all;

	foreach ($clients_by_components as $region=>$arr_clients) {
		foreach ($arr_clients as $client=>$arr_value) {

			# Calculate MongoDB. Norris(Search V2) is not used for mongodb
			# (ImageServer, ServiceContainer, SyncServiceContainer)
			#
			$ImageServer = ($arr_value['ImageServer'] / $grandtotal_by_each_client[$region]['ImageServer']['vertical_total_on_clients']);
			$ServiceContainer = ($arr_value['ServiceContainer'] / $grandtotal_by_each_client[$region]['ServiceContainer']['vertical_total_on_clients']);
			$SyncServiceContainer = ($arr_value['SyncServiceContainer'] / $grandtotal_by_each_client[$region]['SyncServiceContainer']['vertical_total_on_clients']);

			$total_avg = ($ImageServer + $ServiceContainer + $SyncServiceContainer) / 3;

			$mongoDB = ($indiv_clients[$region]['MongoDB']['sum_of_weight_perc'] * $total_avg);

			# 1. Add MongoDB to array.
			# 2. Add "MongoDB" to the existing horizontal total which is now the sum of:
			#    MongoDB + (Common Infra + ImageServer + Search v2(Norris) + ServiceContainer + SyncServiceContainer)
			#
			if ($mongoDB > 0) {
				$clients_by_components[$region][$client]['mongoDB'] = $mongoDB;
				$clients_by_components[$region][$client]['horizontal_total_on_clients'] += $mongoDB;
			}

			$sum_all[$region]['sum_vertical_total'] += $clients_by_components[$region][$client]['horizontal_total_on_clients'];
		}
	}
}

######################################################################################################################
# Calculate AWS.
#
function calc_aws() {
	global $clients_by_components, $sum_all;
	global $clients_by_components_aws;

	$required_clients = array("CC","a.com - Blue Heron","DPS","DPS - SCS","Hammersmith","Edge CC","Archon","Agora","CCV","Marketing Cloud","CC Storage","Image Service","Community Platform","Luca","Dreamweaver","Extract");

	foreach ($clients_by_components as $region=>$arr_clients) {

		$sharedcloud = $clients_by_components[$region]['SharedCloud']['horizontal_total_on_clients'];
		foreach ($arr_clients as $client=>$arr_clients) {

			# sum of vertical total minus "shared cloud". Donot count "shared cloud" in the total sum.
			# sum of horizontal total for each client / (sum of vertical total for each region - shared cloud)
			#
			$aws = $clients_by_components[$region][$client]['horizontal_total_on_clients'] / ($sum_all[$region]['sum_vertical_total'] - $sharedcloud);

			$clients_by_components_aws[$client][$region] = $aws; 
			$database_clients[] = $client;
		}
	}

	$merged_clients = array_unique(array_merge($required_clients, $database_clients));

	foreach ($merged_clients as $client) {
		if (!$clients_by_components_aws[$client]) {
			$clients_by_components_aws[$client] = array("us"=>"0.00",
														  "eu"=>"0.00",
														  "ap"=>"0.00");
		}
	}
}


//print_r($grouped_clients);
//print_r($indiv_clients);
//echo "1::::\n";
//print_r($final_four_components);
//echo "2::::\n";
//print_r($clients_by_components);
//print_r($clients_by_components_aws);
//echo "3::::\n";
//print_r($grandtotal_by_each_client);
//print_r($sum_all);
//print_r($clients_by_components_aws);

mysql_close($link);

?>
