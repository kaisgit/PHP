<style>
	#formstyle fieldset { border: 1px #AAA solid; padding: 10px; margin-top: 20px; width: 99%; }
	#formstyle legend { font-family: "arial" sans-serif; font-size: 16px; text-decoration: bold; }
    #formstyle table { margin: 1em; border-collapse: collapse; width="100%"; }
    #formstyle table tr:nth-child(odd) td { background: #EEFAF6; }
    #formstyle table tr:nth-child(even) td { background: #D4F7EB; }
    #formstyle td { padding: .7em; border: 1px #ccc solid; }
    #formstyle th { padding: .7em; border: 1px #ccc; }
	#formstyle label { font-family: Arial; font-size: 14px; font-weight: none; }
	#formstyle input { font-family: Arial; font-size: 12px; }
	#formstyle button { font-size: 16px; }
</style>

<?php
	session_start();
?>
    <!-- BEGIN OF DATEPICKER: http://jqueryui.com/datepicker/ -->
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
 
<?php
    date_default_timezone_set('America/Los_Angeles');

    $now = date('m/d/Y',strtotime('today'));

    if ($_SESSION['datefrom']) {
		$datefrom = $_SESSION['datefrom'];
	} elseif ($_GET['datefrom']) {
		$datefrom = $_GET['datefrom'];
    } else {
		$datefrom = date("m/d/Y", strtotime("$now -1 month"));
    }

    if ($_SESSION['dateto']) {
		$dateto = $_SESSION['dateto'];
	} elseif ($_GET['dateto']) {
		$dateto = $_GET['dateto'];
    } else {
    	$dateto = $now;
    }

    if ($_SESSION['serviceid']) {
		$serviceid = $_SESSION['serviceid'];
    } else {
		$serviceid = $_GET['serviceid'];
	}
	
?>

<form id="formstyle" method="get" action="kpi_form_cgi.php">
<fieldset>
    <legend>Change Management Dashboard Search:</legend>
    <table width="800px">
	<tr>
	    <td><label for="from">From: </label></td>
	    <td><label for="to">To: </label></td>
	    <td><label for="to">Service name: </label></td>
	    <td><label for="to">Risk: </label></td>
	    <td><label for="to">Impact: </label></td>
		<td></td>
	</tr>
	<tr>
	    <td><input id="from" type="text" name="datefrom" value="<?php print $datefrom; ?>"></td>
	    <td><input type="text" id="to" name="dateto" value="<?php print $dateto; ?>"></td>
	    <td><select name="serviceid" style="width: 400px;">
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
		<td><select name="f_risk">
				<option value="">All</option>
				<option value="high">High</option>
				<option value="medium">Medium</option>
				<option value="low">Low</option>
			</select>
		</td>
		<td><select name="f_impact">
				<option value="">All</option>
				<option value="high">High</option>
				<option value="medium">Medium</option>
				<option value="low">Low</option>
			</select>
		</td>
	    <td><button type="submit">Search</button></td>
	</tr>
    </table>
</fieldset>
</form>

<br /><br />
