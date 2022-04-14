<?php
	session_start();
?>
    <!-- BEGIN OF DATEPICKER: http://jqueryui.com/datepicker/ http://marcgrabanski.com/jquery-ui-datepicker/ -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/jquery-1.10.2.js"></script>
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
	<script type="text/javascript">
    $(function() {
      $( "#from" ).datepicker({
        defaultDate: "-1M",
        changeMonth: true,
        numberOfMonths: 1,
        onClose: function( selectedDate ) {
          $( "#to" ).datepicker( "option", "minDate", selectedDate );
        }
      });
      $( "#to" ).datepicker({
        defaultDate: "-1M",
        changeMonth: true,
        numberOfMonths: 1,
        onClose: function( selectedDate ) {
          $( "#from" ).datepicker( "option", "maxDate", selectedDate );
        }
      });
    });
    </script>
    <!-- END OF DATEPICKER -->

	<style type="text/css">
        fieldset { border: 1px #AAA solid; padding: 10px; margin-top: 20px; width: 99%; }
        legend { font-family: "arial" sans-serif; font-size: 16px; text-decoration: bold; }
        table { margin: 1em; border-collapse: collapse; width="100%"; }
        table tr:nth-child(odd) td { background: #EEFAF6; }
        table tr:nth-child(even) td { background: #D4F7EB; }
        td { padding: .7em; border: 1px #ccc solid; }
        th { padding: .7em; border: 1px #ccc; }
        label { font-family: Arial; font-size: 14px; font-weight: none; }
        input { font-family: Arial; font-size: 12px; }
        button { font-size: 16px; }

		.block {
			width: 305px;
		}
	</style>
 
<?php
    date_default_timezone_set('America/Los_Angeles');

    $now = date('m/d/Y',strtotime('today'));

    if ($_SESSION['datefrom']) {
		$datefrom = $_SESSION['datefrom'];
    } else {
		$datefrom = date("m/d/Y", strtotime("$now -1 day"));
    }
    if ($_SESSION['dateto']) {
		$dateto = $_SESSION['dateto'];
    } else {
    	$dateto = $now;
    }
    if ($_SESSION['serviceid']) {
		$serviceid = $_SESSION['serviceid'];
    }
?>

<form method="get" action="kpi_dashboard_cgi.php">
<fieldset class="toptable">
    <legend>CHANGE MANAGEMENT DASHBOARD SEARCH:</legend>
    <table width="800px">
	<tr>
	    <td><label for="from">From: </label></td>
	    <td><label for="to">To: </label></td>
	    <td><label for="service_type">Service Type: </label></td>
	    <td><label for="service_name">Service Name: </label></td>
	    <td><label for="impact">Impact: </label></td>
	    <td><label for="risk">Risk: </label></td>
	    <td><label for="priority">Priority: </label></td>
		<td></td>
	</tr>
	<tr>
	    <td>
			<div class="block">
				<input id="from" type="text" name="datefrom" value="<?php print $datefrom; ?>">
				<select name="from_hh">
					<option>HH</option>
<?php
	foreach (range(01, 12) as $hour) {
		echo "<option>" . sprintf("%02d", $hour) . "</option>";
	}
?>
				</select> :
				<select name="from_mm">
					<option>MM</option>
<?php
	foreach (range(00, 60, 15) as $min) {
		echo "<option>" . sprintf("%02d", $min) . "</option>";
	}
?>
				</select>
				<select name="from_meridian">
					<option>AM</option>
					<option>PM</option>
				</select>
			</div>
		</td>

	    <td>
			<div class="block">
				<input type="text" id="to" name="dateto" value="<?php print $dateto; ?>">
				<select name="to_hh">
					<option>HH</option>
<?php
	foreach (range(01, 12) as $hour) {
		echo "<option>" . sprintf("%02d", $hour) . "</option>";
	}
?>
				</select> :
				<select name="to_mm">
					<option>MM</option>
<?php
	foreach (range(00, 60, 15) as $min) {
		echo "<option>" . sprintf("%02d", $min) . "</option>";
	}
?>
				</select>
				<select name="to_meridian">
					<option>AM</option>
					<option>PM</option>
				</select>
			</div>
		</td>

	    <td><select name="service_type">
				<option>Business Service</option>
			</select>
		</td>
	    <td><select name="serviceid" style="width: 400px">
<?php
    $headers = array(
        "X-Api-Key:3TMd2X7T4395C2U3j737FL2rZ69mzJ6T",
        "Content-Type: application/json"
        );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://localhost/api/1.0/names.json");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $res = json_decode(curl_exec($ch),true);

    usort($res['data']['names'], function ($elem1, $elem2) {
	return strcmp($elem1['NAME'], $elem2['NAME']);
    });

    foreach ($res["data"]["names"] as $service) {
        $id = $service["ID"];
        $name = $service["NAME"];

	if ($serviceid == $id) {
       	    print '<option value="' . $id . '" selected>' . $name . '</option>';
	    print "\n";
	} else {
       	    print '<option value="' . $id . '">' . $name . '</option>';
	    print "\n";
	}
    }
?>
	    </select></td>
	    <td><select name="impact">
				<option>All</option>
			</select>
		</td>
	    <td><select name="risk">
				<option>All</option>
			</select>
		</td>
	    <td><select name="priority">
				<option>Priority</option>
			</select>
		</td>
	    <td><button type="submit">Search</button></td>
	</tr>
    </table>
</form><br /><br />
