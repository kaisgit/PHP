<?php
    session_start();
?>

<html>
<title>Change Logging Results</title>
<head>
    <link rel="stylesheet" type="text/css" href="css/table_results.css">
	<style type="text/css">
	#table table {
    	padding: 0;
		width: 100%;
		border-collapse: collapse;
	}
	#table table th.name {
		font-family: "Tribuchet MS", Arial, Helvetica, sans-serif;
		color: #333;
		font-size: 13px;
		text-decoration: bold;
		text-align: right;
		width: 150px;
		background: #D4F7EB;
		padding: 13px 5px;
		border: 1px solid #EEFAF6;
	}
	#table table td:first-child {
		text-align: right;
    	padding-left:20px;
    	border-left: 0;
	}
	#table .tablewidth {
		text-align: left;
		border-top: 1px solid #EEE;
		border-right: 1px solid #EEE;
		border-bottom: 1px solid #EEE;
		padding-left: 15px;
	}
	</style>
</head>
<body>

<?php
	require("kpi_form_search.php");
?>

<div id="table">
<table>
<?php
	$form_id = $_GET['form_id'];

	if (isset($_SESSION['kpi_form'])) {
		$summary = $_SESSION['kpi_form'];

		foreach ($summary as $id=>$value) {
			if ($form_id == $id) {

				print "<tr><th class='name'>ID:</td>";
				print "<td class='tablewidth'>$id</td></tr>";

            	print "<tr><th class='name'>Start Time:</td>";
				print "<td class='tablewidth'>" . $value['start'] . "</td></tr>";

            	print "<tr><th class='name'>End Time:</td>";
				print "<td class='tablewidth'>" . $value['end'] . "</td></tr>";

            	print "<tr><th class='name'>Service Name:</td>";
				print "<td class='tablewidth'>" . $value['service_name'] . "</td></tr>";

            	print "<tr><th class='name'>Initiator:</td>";
				print "<td class='tablewidth'>";
				$filter = "(&(mail=" . $value['assigner'] . ")(objectClass=person))";
				$assigner = search_ldap($filter);
				print (($assigner == null) ? $value['assigner'] : $assigner);
				print "</td></tr>";

				print "<tr><th class='name'>Executor:</td>";
				print "<td class='tablewidth'>";
				$filter = "(&(mail=" . $value['assigned_to'] . ")(objectClass=person))";
				$assigned_to = search_ldap($filter);
				print (($assigned_to == null) ? $value['assigned_to'] : $assigned_to);
				print "</td></tr>";

				print "<tr><th class='name'>Risk:</td>";
				print "<td class='tablewidth' >" . $value['risk'] . "</td></tr>";

				print "<tr><th class='name'>Impact:</td>";
				print "<td class='tablewidth'>" . $value['impact'] . "</td></tr>";

				print "<tr><th class='name'>Priority:</td>";
				print "<td class='tablewidth'>" . $value['priority'] . "</td></tr>";

				print "<tr><th class='name'>CSO:</td>";
				print "<td class='tablewidth'>";
				print (($value['cso'] == 1) ? "yes" : "no");
				print "</td></tr>";

				print "<tr><th class='name'>Change Type:</td>";
				print "<td class='tablewidth'>" . $value['change_type'] . "</td></tr>";

				print "<tr><th class='name'>Description:</td>";
				print "<td class='tablewidth'>" . $value['desc'] . "</td></tr>";
			}
		}
	}
?>
</table>
</div>

</body>
</html>


<?php
	
function search_ldap($filter){
	$filter_attributes = array("displayname", "mail", "title","telephonenumber");
	$base_dn = "cn=users,dc=localhost,dc=global,dc=alocalhost,dc=com";
	$ldap_bind_user  = "CN=toolz2,CN=Users,DC=localhost,DC=global,DC=localhost,DC=com";
	$ldap_bind_pass = "";
	$ldap_connection = ldap_connect( "ldaps://localhost") or die(">>Could not connect to LDAP server<<");
	ldap_bind($ldap_connection, $ldap_bind_user, $ldap_bind_pass);

	$ldap_sr = ldap_search($ldap_connection, $base_dn, $filter, $filter_attributes) or die(json_encode(array("Unable to search ldap server")));
	$result = ldap_get_entries($ldap_connection, $ldap_sr);

	ldap_close($ldap_connection);

	$fullname = ($result[0][displayname][0]);
	#print_r($result[0][mail][0]);
	#print_r($result[0][telephonenumber][0]);

	return $fullname;
}

?>
