<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('/var/simplesaml/lib/_autoload.php');

switch ($_SERVER['HTTP_HOST']) {
	case 'toolz.localhost': 	
		$sso = new SimpleSAML_Auth_Simple('toolz');  
		break;
	case 'changeform.localhost': 
		$sso = new SimpleSAML_Auth_Simple('changeform');  
		break;
}

$sso->requireAuth();
$attributes = $sso->getAttributes();
print_r($attributes);
?>
