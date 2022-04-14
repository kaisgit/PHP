<?php
//----------------//
// SKMS WEB API   //
// Object: CmrDao //
// Method: search //
//----------------//
require_once(dirname(__FILE__) . '/SkmsWebApiClient.php');
require_once(dirname(__FILE__) . '/SkmsWebApiCredentials.php');
require_once(dirname(__FILE__) . '/db_connect.php');
date_default_timezone_set('America/Denver'); //MST because skms uses MST

$cmrs = get_cmrs(); //assuming change happens every 2 days, if not check tomorrow. 
$api->disableSslChainVerification();
$total_num_of_pages = 1;

$parameters = "cmr_id,service.name,service.service_type.name,service.direct_depends_on_service.name,service.direct_depends_on_service.service_type.name,service.direct_depended_on_service.name,service.direct_depended_on_service.service_type.name";
foreach($cmrs as $index => $cmr) {
for($i = 1; $i<=$total_num_of_pages; $i++){
$param_arr = Array(
	// Searches for load balancer pools where the name starts with 'sitecat' : change_executor_user.full_name
	"query" => "SELECT ".$parameters." WHERE cmr_id IN (".$cmr.") PAGE ".$i.", 100",
);
if($api->sendRequest('CmrDao', 'search', $param_arr) == true) {
    $response_arr = null;
	$response_arr = $api->getResponseArray();
    
    //get num of pages for reading through pages
    if(isset($response_arr['data']['paging_info']) && is_array($response_arr['data']['paging_info']))
    {
        if(isset($response_arr['data']['paging_info']['last_page'])) { $total_num_of_pages = $response_arr['data']['paging_info']['last_page']; }
    }
	if(isset($response_arr['data']['results']) && is_array($response_arr['data']['results'])) {
		$result_counter = 0;
		foreach($response_arr['data']['results'] as $row) {
			++$result_counter;
        
            $cmr_id = NULL;
            $service_name = NULL;
            $service_type = NULL;
            
            $direct_depends_on_service_name = NULL;
            $direct_depends_on_service_type = NULL;
            
            $direct_depended_on_service_name = NULL;
            $direct_depended_on_service_type = NULL;
            
            if(isset($row['cmr_id'])){ $cmr_id = $row['cmr_id']; }
            
            if(isset($row['service']))
            {
                foreach($row['service'] as $key)
                {
                    if(isset($key['name'])) { $service_name = format_data($key['name']); } 
                    if(isset($key['service_type']))
                    {
                        foreach($key['service_type'] as $key2 => $value2)
                        {
                            if($key2 == "name") { $service_type = format_data($value2); }
                        }
                    }
                    if($service_name != NULL) { $serviceandtype = $service_name."@".$service_type; }
                    $count = 0;
                    if(isset($key['direct_depends_on_service']))
                    {
                        foreach($key['direct_depends_on_service'] as $key2)
                        {
                            $direct_depends_on_service_name = NULL;
                            $direct_depends_on_service_type = NULL;
                            $dserviceanddtype = NULL;
                            
                            if(isset($key2['name'])) { $direct_depends_on_service_name = format_data($key2['name']); } 
                            if(isset($key2['service_type']))
                            {
                                foreach($key2['service_type'] as $key3 => $value3)
                                {
                                    if($key3 == "name") { $direct_depends_on_service_type = format_data($value3); }   
                                }
                            }
                            if($direct_depends_on_service_name != NULL) { $dserviceanddtype = $direct_depends_on_service_name."@".$direct_depends_on_service_type; }
                            $cmr_details[$cmr_id][$serviceandtype][$count] = $dserviceanddtype;
                            ++$count;
                        }
                    }
                    $count = 0;
                    if(isset($key['direct_depended_on_service']))
                    {
                        foreach($key['direct_depended_on_service'] as $key2)
                        {
                            $direct_depended_on_service_name = NULL;
                            $direct_depended_on_service_type = NULL;
                            $dserviceanddtype = NULL;
                            
                            if(isset($key2['name'])) { $direct_depended_on_service_name = format_data($key2['name']); } 
                            if(isset($key2['service_type']))
                            {
                                foreach($key2['service_type'] as $key3 => $value3)
                                {
                                    if($key3 == "name") { $direct_depended_on_service_type = format_data($value3); }   
                                }
                            }
                            if($direct_depended_on_service_name != NULL) { $dserviceanddtype = $direct_depended_on_service_name."@".$direct_depended_on_service_type; }
                            $cmr_details2[$cmr_id][$serviceandtype][$count] = $dserviceanddtype;
                            ++$count;
                        }
                    }
                }
            }
		}
	} else {
		echo "No data was returned.\n";
	}
} else {
 	echo "ERROR:\n";
 	echo "   STATUS: " . $api->getResponseStatus() . "\n";
 	echo "   TYPE: " . $api->getErrorType() . "\n";
 	echo "   MESSAGE: " . $api->getErrorMessage() . "\n";
 	if(preg_match('/unable to json decode/i', $api->getErrorMessage())) {
 		echo "RESPONSE STRING:\n";
 		echo $api->getResponseString() . "\n";
 	}
    }
}
}
$link = connect_to_skms_db();
foreach($cmr_details as $cmrid=>$cmridvalue)
{
    foreach($cmridvalue as $servicesandtypes=>$servicesandtypesvalue)
    {
        $temp = explode("@",$servicesandtypes);
        $service = $temp[0];
        $type = str_replace(" ","_",strtolower($temp[1]));
        if($service == 'CCV Ingest') { $type = "business_service"; }
        if($type != '' AND $type != NULL) 
    {
        $num_of_depends = count($servicesandtypesvalue);
        
        foreach($servicesandtypesvalue as $count=>$value)
        {
            $dtemp = explode("@",$value);
            $dservice = $dtemp[0];
            $dtype = str_replace(" ","_",strtolower($dtemp[1]));
            if($dservice == 'CCV Ingest') { $dtype = "business_service"; }
            if(isset($cmr_details2[$cmrid][$servicesandtypes]))
            { $num_of_further_depends = count($cmr_details2[$cmrid][$servicesandtypes]); }
            else { $num_of_further_depends = 0; }
            for($i=0; $i<$num_of_further_depends; $i++)
            {
                $dtemp2 = explode("@",$cmr_details2[$cmrid][$servicesandtypes][$i]);
                $ddservice = $dtemp2[0];
                $ddtype = str_replace(" ","_",strtolower($dtemp2[1]));
                if($ddservice == 'CCV Ingest') { $ddtype = "business_service"; }
                if(stripos($ddtype,"business") !== FALSE) { $solution_check = 0; $capability_check = 0; $bs_check = 1; $ts_check = 0; } 
                elseif(stripos($ddtype,"technical") !== FALSE) { $solution_check = 0; $capability_check = 0; $bs_check = 0; $ts_check = 1; } 
                elseif(stripos($ddtype,"capability") !== FALSE) { $solution_check = 0; $capability_check = 1; $bs_check = 0; $ts_check = 0; }
                elseif(stripos($ddtype,"solution") !== FALSE) { $solution_check = 1; $capability_check = 0; $bs_check = 0; $ts_check = 0; }
                else { $solution_check = 0; $capability_check = 0; $bs_check = 0; $ts_check = 0; }
                if($ddtype == $type AND $dtype != $type) 
                {
                    if($dtype != '' AND $dtype != NULL AND $type != '' AND $type != NULL ) 
                    {
                        $num_rows = check_if_few_exists($cmrid,$dservice,$dtype,$service,$type); 
                        if($num_rows<1)
                        {
                            $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$type."`,`".$dtype."`) VALUES";
                            $query .= "('".$cmrid."','".$service."','".$dservice."')";
                            execute($query);
                        }
                    }
                }
                elseif($ddtype != $type AND $dtype == $type) 
                {
                    if($ddtype != '' AND $ddtype != NULL AND $type != '' AND $type != NULL ) 
                    {
                        $num_rows = check_if_few_exists($cmrid,$ddservice,$ddtype,$service,$type); 
                        if($num_rows<1)
                        {
                            $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$type."`,`".$ddtype."`) VALUES";
                            $query .= "('".$cmrid."','".$service."','".$ddservice."')";
                            execute($query);
                        }
                    }
                    if($ddtype != '' AND $ddtype != NULL AND $dtype != '' AND $dtype != NULL ) 
                    {
                        if(isset($cmr_details2[$cmrid][$dservice]))
                        {
                            $num_rows = check_if_few_exists($cmrid,$ddservice,$ddtype,$dservice,$dtype); 
                            if($num_rows<1)
                            {
                                $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$dtype."`,`".$ddtype."`) VALUES";
                                $query .= "('".$cmrid."','".$dservice."','".$ddservice."')";
                                execute($query);
                            }
                        }
                        else
                        {
                            if(isset($cmr_details2[$cmrid][$ddservice]))
                            {
                                foreach($cmr_details2[$cmrid][$ddservice] as $c => $v)
                                {
                                    $dtemp4 = explode("@",$v);
                                    $dddservice4 = $dtemp4[0];
                                    $dddtype4 = str_replace(" ","_",strtolower($dtemp4[1]));
                                    if($dddservice4 == 'CCV Ingest') { $dddtype4 = "business_service"; }
                                    if($dddtype4 != $dtype AND $dddtype4 != $ddtype AND $dddtype4 != NULL AND $dddtype4 != '')
                                    {
                                        $num_rows = check_if_exists($cmrid,$dddservice4,$dddtype4,$ddservice,$ddtype,$dservice,$dtype); 
                                        if($num_rows<1)
                                        {
                                            $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$dddtype4."`,`".$dtype."`,`".$ddtype."`) VALUES";
                                            $query .= "('".$cmrid."','".$dddservice4."','".$dservice."','".$ddservice."')";
                                            execute($query);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                elseif($ddtype != $type AND $dtype == $ddtype) 
                {
                    if($ddtype != '' AND $ddtype != NULL AND $type != '' AND $type != NULL ) 
                    {
                        if(isset($cmr_details2[$cmrid][$ddservice]))
                        {
                            foreach($cmr_details2[$cmrid][$ddservice] as $c => $v)
                            {
                                $dtemp4 = explode("@",$v);
                                $dddservice4 = $dtemp4[0];
                                $dddtype4 = str_replace(" ","_",strtolower($dtemp4[1]));
                                if($dddservice4 == 'CCV Ingest') { $dddtype4 = "business_service"; }
                                if($dddtype4 != $type and $dddtype4 != $ddtype and $dddtype4 != NULL and $dddtype4 != '') { $dddtype5 = $dddtype4; }
                                else { $dddtype5 = NULL; }
                            }
                            if(isset($dddtype5) AND $dddtype5 != NULL) 
                            {
                                $num_rows = check_if_exists($cmrid,$dservice,$dtype,$service,$type,$dddservice4,$dddtype5); 
                                if($num_rows<1)
                                {
                                    $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$ddtype."`,`".$type."`,`".$dddtype5."`) VALUES";
                                    $query .= "('".$cmrid."','".$ddservice."','".$service."','".$dddservice4."')";
                                    execute($query);
                                }
                            }
                            else
                            {
                                $num_rows = check_if_few_exists($cmrid,$ddservice,$ddtype,$service,$type); 
                                if($num_rows<1)
                                {
                                    $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$ddtype."`,`".$type."`) VALUES";
                                    $query .= "('".$cmrid."','".$ddservice."','".$service."')";
                                    execute($query);
                                }
                            }
                        }
                        elseif(isset($cmr_details2[$cmrid][$dservice]))
                        {    
                            $num_rows = check_if_few_exists($cmrid,$ddservice,$ddtype,$service,$type); 
                            if($num_rows<1)
                            {
                                $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$type."`,`".$ddtype."`) VALUES";
                                $query .= "('".$cmrid."','".$service."','".$ddservice."')";
                                execute($query);
                            }
                        }
                    }
                }
                elseif($ddtype == $type AND $dtype == $type) 
                {
                    $should_insert = NULL;
                    foreach($cmr_details[$cmrid][$servicesandtypes] as $x=>$y) //dtype and type
                    {
                        $dtemp2 = explode("@",$y);
                        $dytype = str_replace(" ","_",strtolower($dtemp2[1]));
                        if($ts_check == 1)
                        {
                            if($dytype != "technical_service")
                            {
                                 $should_insert = 0; 
                            }
                            else
                            {
                                if($should_insert != 0) { $should_insert = 1; }
                            }
                        }
                        elseif($bs_check == 1)
                        {
                            if($dytype != "business_service")
                            {
                                 $should_insert = 0; 
                            }
                            else
                            {
                                if($should_insert != 0) { $should_insert = 1; }
                            }
                        }
                        elseif($capability == 1)
                        {
                            if($dytype != "capability")
                            {
                                 $should_insert = 0; 
                            }
                            else
                            {
                                if($should_insert != 0) { $should_insert = 1; }
                            }
                        }
                        elseif($solution == 1)
                        {
                            if($dytype != "solution")
                            {
                                 $should_insert = 0; 
                            }
                            else
                            {
                                if($should_insert != 0) { $should_insert = 1; }
                            }
                        }
                        if(isset($cmr_details[$cmrid][$y])) { $should_insert_d = 0; } else { $should_insert_d = 1; }
                    }
                    if($should_insert == 1)
                    {
                        if($type != '' AND $type != NULL) 
                        {
                            $num_rows = check_if_this_exists($cmrid,$service,$type); 
                            if($num_rows<1)
                            {
                                $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$type."`) VALUES";
                                $query .= "('".$cmrid."','".$service."')";
                                execute($query);
                            }
                        } 
                    }
                    if($should_insert_d == 1)
                    {
                        if($dtype != '' AND $dtype != NULL) 
                        {
                            $num_rows = check_if_this_exists($cmrid,$dservice,$dtype); 
                            if($num_rows<1)
                            {
                                $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$dtype."`) VALUES";
                                $query .= "('".$cmrid."','".$dservice."')";
                                execute($query);
                            }
                        } 
                    }
                }   
                else
                {
                    if($type != '' AND $type != NULL AND $dtype != '' AND $dtype != NULL AND ($ddtype == '' OR $ddtype == NULL))
                    { 
                        $num_rows = check_if_few_exists($cmrid,$service,$type,$dservice,$dtype); 
                        if($num_rows<1)
                        {
                            $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$type."`,`".$dtype."`) VALUES ('".$cmrid."','".$service."','".$dservice."')";
                            execute($query);
                        }
                    }
                    elseif($type != '' AND $type != NULL AND ($dtype == '' OR $dtype == NULL) AND $ddtype != '' AND $ddtype != NULL)
                    { 
                        $num_rows = check_if_few_exists($cmrid,$service,$type,$ddservice,$ddtype); 
                        if($num_rows<1)
                        {
                            $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$type."`,`".$ddtype."`) VALUES ('".$cmrid."','".$service."','".$ddservice."')";
                            execute($query);
                        }
                    }
                    elseif($type != '' AND $type != NULL AND $dtype != '' AND $dtype != NULL AND $ddtype != '' AND $ddtype != NULL) 
                    { 
                        $num_rows = check_if_exists($cmrid,$service,$type,$dservice,$dtype,$ddservice,$ddtype); 
                        if($num_rows<1)
                        {
                            $query = "INSERT INTO `service_hierarchy`(`cmr_id`,`".$type."`,`".$dtype."`,`".$ddtype."`) VALUES ('".$cmrid."','".$service."','".$dservice."','".$ddservice."')";
                            execute($query);
                        }
                    }
                }
                
            }
        }
    }
    }
    fill_empty($cmrid);
    delete_unwanted($cmrid);
}
mysql_close($link);
function format_data($text)
{
	$text = str_replace("," , "," , $text);
	$text = str_replace("–" , "-" , $text); //replace double hyphen with single hyphen
	$text = str_replace('"',"",$text); 
	$text = str_replace("'", " ", $text);
	$text = str_replace(array("\n", "\r"), ' ', $text); //remove break lines
	$text = preg_replace('/  */', ' ', $text); // Replace sequences of spaces with a single space
	$text = str_replace('“',"'",$text); 
	$text = str_replace('”',"'",$text); 
	ini_set('mbstring.substitute_character', 32); 
  	$text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
  
  	return $text;
}

function get_cmrs()
{
    $two_days_ago = date("Y-m-d",strtotime("-2 days"));
    $link = connect_to_skms_db();
    $query = "SELECT distinct(`id`) FROM `cmr_api_dump` where `type` = 'cmr' and `creation_datetime` >= '".$two_days_ago."'";
    $query = "SELECT distinct(`id`) FROM `cmr_api_dump` where `type` = 'cmr' and `creation_datetime` >= '2015-02-01'";
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
    $cmrs = array();
    $i = 0;
    while ($row = mysql_fetch_assoc($result)) 
    {
        ++$i;
        $cmrs[$i] = $row['id']; 
    }
    
    mysql_close($link);
    return $cmrs;
}

function check_if_exists($cmr_id,$service,$type,$dservice,$dtype,$ddservice,$ddtype)
{
    $query = "select * from `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND `".$type."` = '".$service."'";
    $query .= " AND `".$dtype."` = '".$dservice."'";
    $query .= " AND `".$ddtype."` = '".$ddservice."'";
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
    $num_rows = mysql_num_rows($result);
    return $num_rows;
}

function check_if_few_exists($cmr_id,$service,$type,$dddservice,$dddtype)
{
    $query = "select * from `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND `".$type."` = '".$service."'";
    $query .= " AND `".$dddtype."` = '".$dddservice."'";
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
    $num_rows = mysql_num_rows($result);
    return $num_rows;
}

function check_if_this_exists($cmr_id,$service,$type)
{
    $query = "select * from `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND `".$type."` = '".$service."'";
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
    $num_rows = mysql_num_rows($result);
    return $num_rows;
}

function execute($query)
{
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
    return $result;
}

function fill_empty($cmr_id)
{
    $solution = NULL;
    $cloud = NULL;
    $capability = NULL;
    $bs = NULL;
    $ts = NULL;
    
    $query = "SELECT * FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND `technical_service` IS NOT NULL";
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
    while ($row = mysql_fetch_assoc($result)) 
    {
        $bs = $row['business_service'];
        $ts = $row['technical_service'];
        
        $query2 = "SELECT distinct(`capability`) FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND ";
        $query2 .= "`business_service` = '".$bs."' AND `technical_service` = '".$ts."' AND `capability` IS NOT NULL";
        $result2 = mysql_query($query2);
        if(!$result2) { echo $query2.mysql_error().PHP_EOL; }
        $num_rows2 = mysql_num_rows($result2);
        if($num_rows2 > 0) 
        {
            while ($row2 = mysql_fetch_assoc($result2)) 
            {
                $capability = $row2['capability'];
            
                $query3 = "SELECT distinct(`solution`) FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND ";
                $query3 .= "`capability` = '".$capability."' AND `solution` IS NOT NULL";
                $result3 = mysql_query($query3);
                if(!$result3) { echo $query3.mysql_error().PHP_EOL; }
                $num_rows3 = mysql_num_rows($result3);
                if($num_rows3 > 0) 
                {
                    while ($row3 = mysql_fetch_assoc($result3)) 
                    {
                        $solution = $row3['solution'];
                        
                        $query5 = "UPDATE `service_hierarchy` SET `solution` = '".$solution."' WHERE `cmr_id` = '".$cmr_id."' AND ";
                        $query5 .= "`business_service` = '".$bs."' AND `capability` = '".$capability."' AND `technical_service` = '".$ts."'";
                        execute($query5);
                        
                        $query4 = "SELECT distinct(`cloud`) FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND ";
                        $query4 .= "`solution` = '".$solution."' AND `cloud` IS NOT NULL";
                        $result4 = mysql_query($query4);
                        if(!$result4) { echo $query4.mysql_error().PHP_EOL; }
                        $num_rows4 = mysql_num_rows($result4);
                        if($num_rows4 > 0) 
                        {
                            while ($row4 = mysql_fetch_assoc($result4)) 
                            {
                                $cloud = $row4['cloud'];
                                
                                $query6 = "UPDATE `service_hierarchy` SET `cloud` = '".$cloud."' WHERE `cmr_id` = '".$cmr_id."' AND ";
                                $query6 .= "`business_service` = '".$bs."' AND `capability` = '".$capability."' AND `technical_service` = '".$ts."' AND `solution` = '".$solution."'";
                                execute($query6);
                            }
                        }
                    }
                }
            }
        }
    }
    
    $solution = NULL;
    $cloud = NULL;
    $capability = NULL;
    $bs = NULL;
    $ts = NULL;

    $query = "SELECT distinct(`business_service`) FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND `business_service` IS NOT NULL";
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
    while ($row = mysql_fetch_assoc($result)) 
    {
        $bs = $row['business_service'];
        
        $query2 = "SELECT distinct(`capability`) FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND ";
        $query2 .= "`business_service` = '".$bs."' AND `capability` IS NOT NULL";
        $result2 = mysql_query($query2);
        if(!$result2) { echo $query2.mysql_error().PHP_EOL; }
        $num_rows2 = mysql_num_rows($result2);
        if($num_rows2 > 0) 
        {
            while ($row2 = mysql_fetch_assoc($result2)) 
            {
                $capability = $row2['capability'];
            
                $query3 = "SELECT distinct(`solution`) FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND ";
                $query3 .= "`capability` = '".$capability."' AND `solution` IS NOT NULL";
                $result3 = mysql_query($query3);
                if(!$result3) { echo $query3.mysql_error().PHP_EOL; }
                $num_rows3 = mysql_num_rows($result3);
                if($num_rows3 > 0) 
                {
                    while ($row3 = mysql_fetch_assoc($result3)) 
                    {
                        $solution = $row3['solution'];
                        
                        $query5 = "UPDATE `service_hierarchy` SET `solution` = '".$solution."' WHERE `cmr_id` = '".$cmr_id."' AND ";
                        $query5 .= "`business_service` = '".$bs."' AND `capability` = '".$capability."'";
                        execute($query5);
                        
                        $query4 = "SELECT distinct(`cloud`) FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND ";
                        $query4 .= "`solution` = '".$solution."' AND `cloud` IS NOT NULL";
                        $result4 = mysql_query($query4);
                        if(!$result4) { echo $query4.mysql_error().PHP_EOL; }
                        $num_rows4 = mysql_num_rows($result4);
                        if($num_rows4 > 0) 
                        {
                            while ($row4 = mysql_fetch_assoc($result4)) 
                            {
                                $cloud = $row4['cloud'];
                                
                                $query6 = "UPDATE `service_hierarchy` SET `cloud` = '".$cloud."' WHERE `cmr_id` = '".$cmr_id."' AND ";
                                $query6 .= "`business_service` = '".$bs."' AND `capability` = '".$capability."' AND `solution` = '".$solution."'";
                                execute($query6);
                            }
                        }
                    }
                }
            }
        }
    }
    
    $solution = NULL;
    $cloud = NULL;
    $capability = NULL;
    $bs = NULL;
    $ts = NULL;

    $query = "SELECT distinct(`capability`) FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND `capability` IS NOT NULL";
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
    while ($row = mysql_fetch_assoc($result)) 
    {
        $capability = $row['capability'];
        $query3 = "SELECT distinct(`solution`) FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND ";
        $query3 .= "`capability` = '".$capability."' AND `solution` IS NOT NULL";
        $result3 = mysql_query($query3);
        if(!$result3) { echo $query3.mysql_error().PHP_EOL; }
        $num_rows3 = mysql_num_rows($result3);
        if($num_rows3 > 0) 
        {
            while ($row3 = mysql_fetch_assoc($result3)) 
            {
                $solution = $row3['solution'];
                $query5 = "UPDATE `service_hierarchy` SET `solution` = '".$solution."' WHERE `cmr_id` = '".$cmr_id."' AND ";
                $query5 .= "`capability` = '".$capability."'";
                execute($query5);
                        
                $query4 = "SELECT distinct(`cloud`) FROM `service_hierarchy` where `cmr_id` = '".$cmr_id."' AND ";
                $query4 .= "`solution` = '".$solution."' AND `cloud` IS NOT NULL";
                $result4 = mysql_query($query4);
                if(!$result4) { echo $query4.mysql_error().PHP_EOL; }
                $num_rows4 = mysql_num_rows($result4);
                if($num_rows4 > 0) 
                {
                    while ($row4 = mysql_fetch_assoc($result4)) 
                    {
                        $cloud = $row4['cloud'];       
                        $query6 = "UPDATE `service_hierarchy` SET `cloud` = '".$cloud."' WHERE `cmr_id` = '".$cmr_id."' AND ";
                        $query6 .= "`capability` = '".$capability."' AND `solution` = '".$solution."'";
                        execute($query6);
                    }
                }
            }
        }        
    }
}

function delete_unwanted($cmr_id)
{
    $query = "SELECT * FROM `service_hierarchy` where `business_service` IS NULL and `technical_service` IS NULL AND `cmr_id` = '".$cmr_id."'";
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
    $num_rows = mysql_num_rows($result);
    if($num_rows > 0) 
    {
    while ($row = mysql_fetch_assoc($result)) 
    {   
        $capability = $row['capability'];
        $solution = $row['solution'];
        $cloud = $row['cloud'];
        
        $query2 = "SELECT * FROM `service_hierarchy` where `cloud` = '".$cloud."' AND `solution` = '".$solution."' AND `capability` = '".$capability."' AND (`business_service` IS NOT NULL OR `technical_service` IS NOT NULL)";
        $query2 .= " AND `cmr_id` = '".$cmr_id."'";
        $result2 = mysql_query($query2);
        if(!$result2) { echo $query2.mysql_error().PHP_EOL; }
        $num_rows2 = mysql_num_rows($result2);
        if($num_rows2 > 0) 
        {
            $query = "DELETE FROM `service_hierarchy` where `cloud` = '".$cloud."' AND `solution` = '".$solution."' AND `capability` = '".$capability."' AND `business_service` IS NULL and `technical_service` IS NULL AND `cmr_id` = '".$cmr_id."'";
            execute($query);
        }
    }
    }
        
    $query = "SELECT * FROM `service_hierarchy` where `technical_service` IS NULL AND `cmr_id` = '".$cmr_id."'";
    $result = mysql_query($query);
    if(!$result) { echo $query.mysql_error().PHP_EOL; }
    $num_rows = mysql_num_rows($result);
    if($num_rows > 0) 
    {
    while ($row = mysql_fetch_assoc($result)) 
    {   
        $bs = $row['business_service'];
        $capability = $row['capability'];
        $solution = $row['solution'];
        $cloud = $row['cloud'];
        
        $query2 = "SELECT * FROM `service_hierarchy` where `cloud` = '".$cloud."' AND `solution` = '".$solution."' AND `capability` = '".$capability."' AND `business_service` = '".$bs."' AND `technical_service` IS NOT NULL";
        $query2 .= " AND `cmr_id` = '".$cmr_id."'";
        $result2 = mysql_query($query2);
        if(!$result2) { echo $query2.mysql_error().PHP_EOL; }
        $num_rows2 = mysql_num_rows($result2);
        if($num_rows2 > 0) 
        {
            $query = "DELETE FROM `service_hierarchy` where `cloud` = '".$cloud."' AND `solution` = '".$solution."' AND `capability` = '".$capability."' AND `business_service` = '".$bs."' and `technical_service` IS NULL AND `cmr_id` = '".$cmr_id."'";
            execute($query);
        }
    }
    }  
}


?>
