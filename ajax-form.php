<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US" xml:lang="en-US">
<head>
	<title>Ajax / PHP Form Example</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="This is an example form that uses Ajax to submit data, and PHP to retrieve it."/>
	<meta name="keywords" content="ajax, form, example, php" />
	<!-- JQUERY -->
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
	<script type="text/javascript">
	// JQUERY: Plugin "autoSumbit"
	(function($) {
		$.fn.autoSubmit = function(options) {
			return $.each(this, function() {
				// VARIABLES: Input-specific
				var input = $(this);
				var column = input.attr('name');
	
				// VARIABLES: Form-specific
				var form = input.parents('form');
				var method = form.attr('method');
				var action = form.attr('action');

				// VARIABLES: Where to update in database
				var where_val = form.find('#where').val();
				var where_col = form.find('#where').attr('name');
	
				// ONBLUR: Dynamic value send through Ajax
				input.bind('blur', function(event) {
					// Get latest value
					var value = input.val();
					// AJAX: Send values
					$.ajax({
						url: action,
						type: method,
						data: {
							val: value,
							col: column,
							w_col: where_col,
							w_val: where_val
						},
						cache: false,
						timeout: 10000,
						success: function(data) {
							// Alert if update failed
							if (data) {
								alert(data);
							}
							// Load output into a P
							else {
								$('#notice').text('Updated');
								$('#notice').fadeOut().fadeIn();
							}
						}
					});
					// Prevent normal submission of form
					return false;
				})
			});
		}
	})(jQuery);
	// JQUERY: Run .autoSubmit() on all INPUT fields within form
	$(function(){
		$('#ajax-form INPUT').autoSubmit();
	});
	</script>
	<!-- STYLE -->
	<style type="text/css">
		INPUT { margin-right: 1em }
	</style>
</head>
<body>

<!-- CONTENT -->
<h1>My Ajax Form</h1>

<?php

/*
 * DATABASE CONNECTION
 */

// DATABASE: Connection variables

$db_host = "toolz.com"; 
$db_username = "";  
$db_password = "";  
$db_name = "ec2_count"; 

// DATABASE: Try to connect
if (!$db_connect = mysql_connect($db_host, $db_username, $db_password))
	die('Unable to connect to MySQL.');
if (!$db_select = mysql_select_db($db_name, $db_connect))
	die('Unable to select database');

/*
 * DATABASE QUERY
 */

// DATABASE: Get current row
$result = mysql_query("SELECT * FROM Client");
$row = mysql_fetch_assoc($result);

?>
<form id="ajax-form" class="autosubmit" method="POST" action="./ajax-update.php">
	<fieldset>
		<legend>Update user information</legend>

		<label>Company:</label>
			<input name="user_company" value="<?php echo $row['user_company'] ?>" />

		<label>Name:</label>
			<input name="user_name" value="<?php echo $row['user_name'] ?>" />

		<label>E-mail:</label>
			<input name="user_email" value="<?php echo $row['user_email'] ?>" />

		<input id="where" type="hidden" name="user_id" value="<?php echo $row['user_id'] ?>" />
	</fieldset>
</form>

<p id="notice"></p>

</body>
</html>
