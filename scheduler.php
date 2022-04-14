<?php
date_default_timezone_set('America/Los_Angeles');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title>Project Status Email Scheduler</title> 
<link rel="stylesheet" href="../storage/style.css">
 <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<style type="text/css">
.input{	
}
.input-wide{
	width: 500px;
}
</style>
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
 <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>

<script type='text/javascript'>
function check(value)
{
    if(value === "weekly")
    { 
        document.getElementById("freq_label").style.display = "block";
        document.getElementById("next").style.display = "none";
    }
    else if(value === "monthly")
    {
        document.getElementById("freq_label").style.display = "none";
        document.getElementById("next").style.display = "block";
    }
    else
    {
        document.getElementById("freq_label").style.display = "none";
        document.getElementById("next").style.display = "none";
    }
}
    
function selected_dates_monthly(val_4)
{
    if(val_4 === '1')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1' selected> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '2')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2' selected> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '3')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3' selected> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '4')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4' selected> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '5')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5' selected> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '6')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6' selected> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '7')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7' selected> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '8')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8' selected> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '9')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9' selected> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '10')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10' selected> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '11')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11' selected> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '12')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12' selected> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '13')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13' selected> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '14')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14' selected> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '15')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15' selected> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '16')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16' selected> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '17')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17' selected> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '18')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18' selected> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '19')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19' selected> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '20')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20' selected> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '21')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21' selected> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '22')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22' selected> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '23')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23' selected> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '24')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24' selected> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '25')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25' selected> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '26')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26' selected> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '27')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27' selected> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '28')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28' selected> 28<option value='29'> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '29')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29' selected> 29<option value='30'> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '30')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30' selected> 30<option value='31'> 31</select>";
    }
    else if(val_4 === '31')
    { document.getElementById("next").innerHTML="On day:<select name='monthly'><option> Please select:<option value='1'> 1<option value='2'> 2<option value='3'> 3<option value='4'> 4<option value='5'> 5<option value='6'> 6<option value='7'> 7<option value='8'> 8<option value='9'> 9<option value='10'> 10<option value='11'> 11<option value='12'> 12<option value='13'> 13<option value='14'> 14<option value='15'> 15<option value='16'> 16<option value='17'> 17<option value='18'> 18<option value='19'> 19<option value='20'> 20<option value='21'> 21<option value='22'> 22<option value='23'> 23<option value='24'> 24<option value='25'> 25<option value='26'> 26<option value='27'> 27<option value='28'> 28<option value='29'> 29<option value='30'> 30<option value='31' selected> 31</select>";
    }
}

function selected_days_weekly(val_4)
{ 
    if(val_4.indexOf('mon') != -1) { var for_mon = "<input type='checkbox' name='weekly[]' value='mon' checked> Monday<br>"; }
                            else { var for_mon = "<input type='checkbox' name='weekly[]' value='mon'> Monday<br>"; }
    if(val_4.indexOf('tue') != -1) { var for_tue = "<input type='checkbox' name='weekly[]' value='tue' checked> Tuesday<br>"; }
                            else { var for_tue = "<input type='checkbox' name='weekly[]' value='tue'> Tuesday<br>"; }
    if(val_4.indexOf('wed') != -1) { var for_wed = "<input type='checkbox' name='weekly[]' value='wed' checked> Wednesday<br>"; }
                            else { var for_wed = "<input type='checkbox' name='weekly[]' value='wed'> Wednesday<br>"; }
    if(val_4.indexOf('thu') != -1) { var for_thu = "<input type='checkbox' name='weekly[]' value='thu' checked> Thursday<br>"; }
                            else { var for_thu = "<input type='checkbox' name='weekly[]' value='thu'> Thursday<br>"; }
    if(val_4.indexOf('fri') != -1) { var for_fri = "<input type='checkbox' name='weekly[]' value='fri' checked> Friday<br>"; }
                            else { var for_fri = "<input type='checkbox' name='weekly[]' value='fri'> Friday<br>"; }
    if(val_4.indexOf('sat') != -1) { var for_sat = "<input type='checkbox' name='weekly[]' value='sat' checked> Saturday<br>"; }
                            else { var for_sat = "<input type='checkbox' name='weekly[]' value='sat'> Saturday<br>"; }
    if(val_4.indexOf('sun') != -1) { var for_sun = "<input type='checkbox' name='weekly[]' value='sun' checked> Sunday"; }
                            else { var for_sun = "<input type='checkbox' name='weekly[]' value='sun'> Sunday"; }
    
    document.getElementById("freq_label").innerHTML="Frequency:<br>"+for_mon+for_tue+for_wed+for_thu+for_fri+for_sat+for_sun;

}
    
function edit_confirmation(id,val2,val3,val4,val5,val6,val7)
{
    document.getElementById("col1").innerHTML="<input style='height:20px;width:100px;display:none;' type='text' id='job_id' name='job_id' rows='5' cols='10' value='" + id + "'>";
    document.getElementById("col2").innerHTML="<input style='height:20px;width:300px;' type='text' id='recipient1' name='recipient1' rows='5' cols='50' value='" + val2 + "'><br>(separated by semi colons)";
    if(val3 === "daily")
    {
    document.getElementById("col3").innerHTML="<select id='dayid' name='repeat_event' onchange='check(value)'><option value=' '>Please select:</option><option value='daily' selected='selected'>Daily</option><option value='weekly'>Weekly</option><option value='monthly'>Monthly</option></select>";
    check(val3);
    }
    else if(val3 === "weekly")
    {
    document.getElementById("col3").innerHTML="<select id='dayid' name='repeat_event' onchange='check(value)'><option value=' '>Please select:</option><option value='daily'>Daily</option><option value='weekly' selected='selected'>Weekly</option><option value='monthly'>Monthly</option></select>";
    check(val3);
    selected_days_weekly(val4);
    }
    else if(val3 === "monthly")
    {
    document.getElementById("col3").innerHTML="<select id='dayid' name='repeat_event' onchange='check(value)'><option value=' '>Please select:</option><option value='daily' selected='selected'>Daily</option><option value='weekly'>Weekly</option><option value='monthly' selected='selected'>Monthly</option></select>";
    check(val3);
    selected_dates_monthly(val4);
    }
    document.getElementById("col4").innerHTML="<input style='height:20px;width:150px' type='text' id='from' name='from' value='" + val5 + "'>";
    document.getElementById("col5").innerHTML="<input style='height:20px;width:150px;' type='text' id='to' name='to' value='" + val6 + "'><br>(leave empty if NA)";
    if(val7 === "00:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00' selected> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "01:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1' selected> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "02:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2' selected> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "03:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3' selected> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "04:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4' selected> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "05:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5' selected> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "06:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6' selected> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "07:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7' selected> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "08:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8' selected> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "09:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9' selected> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "10:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10' selected> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "11:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11' selected> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "12:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12' selected> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "13:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13' selected> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "14:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14' selected> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "15:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15' selected> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "16:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16' selected> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "17:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17' selected> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "18:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18' selected> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "19:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19' selected> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "20:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20' selected> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "21:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21' selected> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "22:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22' selected> 10:00 PM PT<option value='23'> 11:00 PM PT</select>"; }
    else if(val7 === "23:00:00") { document.getElementById("col6").innerHTML="<select name='time' id='time'><option value='00'> 12:00 AM PT<option value='1'> 1:00 AM PT<option value='2'> 2:00 AM PT<option value='3'> 3:00 AM PT<option value='4'> 4:00 AM PT<option value='5'> 5:00 AM PT<option value='6'> 6:00 AM PT<option value='7'> 7:00 AM PT<option value='8'> 8:00 AM PT<option value='9'> 9:00 AM PT<option value='10'> 10:00 AM PT<option value='11'> 11:00 AM PT<option value='12'> 12:00 PM PT<option value='13'> 1:00 PM PT<option value='14'> 2:00 PM PT<option value='15'> 3:00 PM PT<option value='16'> 4:00 PM PT<option value='17'> 5:00 PM PT<option value='18'> 6:00 PM PT<option value='19'> 7:00 PM PT<option value='20'> 8:00 PM PT<option value='21'> 9:00 PM PT<option value='22'> 10:00 PM PT<option value='23' selected> 11:00 PM PT</select>"; }s

}
function delete_confirmation(id)
{
    var x;
	var r=confirm("Are you sure you want to delete?");
	if (r==true) 
    { 
        delete_from_db(id); 
    }
    else
    {
        document.getElementById('delete').checked = false;
        document.getElementById('edit').checked = false;
    }
}
function delete_from_db(id)
{
	$.ajax({
		type: "post",
		cache: false,
		url: "ajaxtophp.php",
		data: { method: "delete_db", job_id: id},
        success: function(response) 
		{
			if($.trim(response) == "success")
			{
                //location.reload();
				$("#status").hide()
					.html("<strong>Schedule deleted</strong><br>")
					.css("color","green")
					.fadeIn(1000);
                
                //Remove the row
				$("#row_" + id).animate({'backgroundColor':'#fb6c6c'},300);
				$("#row_" + id).slideUp(300);
				$("#row_" + id).remove();
			}
			else
			{
				$("#status").hide()
					.html("<strong>Error: Schedule not deleted</strong>")
					.css("color","red")
					.fadeIn(1000);
			}
            
		}
		
	});
}
</script>
<script>
  $(function() 
  {
    $( "#from" ).datepicker
    ({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 1,
        onClose: function( selectedDate ) 
        {
        $( "#to" ).datepicker( "option", "minDate", selectedDate );
        }
    });
    $( "#to" ).datepicker
    ({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 1,
      onClose: function( selectedDate ) 
        {
        $( "#from" ).datepicker( "option", "maxDate", selectedDate );
        }
    });
  });
  </script>
    <style>
    body {
	margin:0px 0px; padding:0px;
	text-align:center;
        background-color:#fff;
	}
	
	#Content {
	width:100%;
	margin:50px auto;
	text-align:left;
	padding:15px 0px 15px 15px;
	border:1px dashed #333;
	background-color:#eee;
	}
    h1 {
    padding: 15px 5px 5px 5px;
    }
    pad  {
    position:absolute;
	top:50px;
	left:5px;
    }
    #wrapper {
    width: 90%;
    margin: 0 auto;
    position: relative;
	}
	
	.desktop {
	min-height: 500px;
	}
    
    td {
    font-size: small;
    }
    </style>
</head>
<body class="desktop">
<?php require_once "lib/mysqli_dbconnect.php";
$sql = "select max(`job_id`) from `scheduler2`";
$result = $mysqli->query($sql);
while ($row=$result->fetch_assoc()) 
{
    $new_job_id = $row['max(`job_id`)'];
    if(isset($new_job_id) AND $new_job_id != NULL) {$new_job_id = $new_job_id+1;}
    else { $new_job_id = 1000; }
}
?>
<div id="wrapper">
    
<h1 align="left">Project Status Email Scheduler</h1> 
<pad>
<div class="steps" id="Content">
<h3>Set multiple delivery schedules:</h3>
    <br>
    <br>
<form method="post" enctype="multipart/form-data" action="scheduler_submit2.php">
<table>
<tr>
    <td style="display:none;">Job ID:</td>
    <td>Recipients:</td>
    <td style="padding-left:20px;">Repeat Event:</td>
    <td style="padding-left:20px;">Start Date:</td>
    <td style="padding-left:20px;">End Date:</td>
    <td style="padding-left:20px;">Time:</td>
</tr>
<tr>
    <td valign="top" id="col1" style="padding-right:10px;display:none;"><input style="height:20px;width:60px;" type="text" id="job_id" name="job_id" rows="5" cols="10" value="<?php echo $new_job_id; ?>"></td>
    <td valign="top" id="col2"><input style="height:20px;width:300px;" type="text" id="recipient1" name="recipient1" rows="5" cols="50"><br>(separated by semi colons)</td>
    <td valign="top" style="padding-left:20px;" id="col3">
        <select id="dayid" name="repeat_event" onchange="check(value)">
			<option value=" ">Please select:</option>
        	<option value="daily">Daily</option>
        	<option value="weekly">Weekly</option>
        	<option value="monthly">Monthly</option>
    	</select>
    </td>
    <td valign="top" style="padding-left:20px;" id="col4">
        
        <input style="height:20px;width:150px" type="text" id="from" name="from">
    </td>
    <td valign="top" style="padding-left:20px;" id="col5">
       
        <input style="height:20px;width:150px;" type="text" id="to" name="to">
        <br>(leave empty if NA)
    </td>
    <td valign="top" style="padding-left:20px;" id="col6">
    <select name="time" id="time">
			<option value="00"> 12:00 AM PT
			<option value="1"> 1:00 AM PT
			<option value="2"> 2:00 AM PT
			<option value="3"> 3:00 AM PT
			<option value="4"> 4:00 AM PT
			<option value="5"> 5:00 AM PT
			<option value="6"> 6:00 AM PT
			<option value="7"> 7:00 AM PT
			<option value="8"> 8:00 AM PT
			<option value="9"> 9:00 AM PT
			<option value="10"> 10:00 AM PT
			<option value="11"> 11:00 AM PT
			<option value="12"> 12:00 PM PT
			<option value="13"> 1:00 PM PT
			<option value="14"> 2:00 PM PT
			<option value="15"> 3:00 PM PT
			<option value="16"> 4:00 PM PT
			<option value="17"> 5:00 PM PT
			<option value="18"> 6:00 PM PT
			<option value="19"> 7:00 PM PT
			<option value="20"> 8:00 PM PT
			<option value="21"> 9:00 PM PT
			<option value="22"> 10:00 PM PT
			<option value="23"> 11:00 PM PT
		</select>
    </td>
</tr>
<tr>
    <!-- <td valign="top"><br><input type="checkbox" name="delete" value="on">&nbsp;Check to delete<br><br></td> -->
    <td></td>
    <td class="freq_label" id="freq_label" style="padding-left:20px;display:none;">Frequency:
        <br><input type="checkbox" name="weekly[]" value="mon"> Monday
        <br><input type="checkbox" name="weekly[]" value="tue"> Tuesday
        <br><input type="checkbox" name="weekly[]" value="wed"> Wednesday
        <br><input type="checkbox" name="weekly[]" value="thu"> Thursday
        <br><input type="checkbox" name="weekly[]" value="fri"> Friday
        <br><input type="checkbox" name="weekly[]" value="sat"> Saturday
        <br><input type="checkbox" name="weekly[]" value="sun"> Sunday
    </td>
    <td class="next" id="next" style="display:none;">
    On day:
        <select name="monthly">
            <option> Please select:
            <option value="1"> 1
            <option value="2"> 2
            <option value="3"> 3
            <option value="4"> 4
            <option value="5"> 5
            <option value="6"> 6
            <option value="7"> 7
            <option value="8"> 8
            <option value="9"> 9
            <option value="10"> 10
            <option value="11"> 11
            <option value="12"> 12
            <option value="13"> 13
            <option value="14"> 14
            <option value="15"> 15
            <option value="16"> 16
            <option value="17"> 17
            <option value="18"> 18
            <option value="19"> 19
            <option value="20"> 20
            <option value="21"> 21
            <option value="22"> 22
            <option value="23"> 23
            <option value="24"> 24
            <option value="25"> 25
            <option value="26"> 26
            <option value="27"> 27
            <option value="28"> 28
            <option value="29"> 29
            <option value="30"> 30
            <option value="31"> 31
        </select>
    </td>
</tr>
<!-- <tr>
<td style="padding-bottom:30px;"></td>
</tr> -->
</table>

<input type="submit" name="submit" style="width:60px; height:25px;" value="Submit">
</form>

</pad>

<?php 
require_once "lib/mysqli_dbconnect.php";
$sql = "select * from `scheduler2`";
$result = $mysqli->query($sql);
$row_cnt = $result->num_rows;
echo '<center><div id="status"></div><h3>Scheduled Emails</h3>';
echo '<table id="scheduledlist" style="table-layout:fixed;border-collapse: collapse;border: 1px solid black;width:1100px;">';
	if ($row_cnt >= 1) {
        
        echo '<tr style="border: 1px solid black;">';
        echo '<th style="border: 1px solid black;padding: 10px;display:none;width:1%;">Job ID</th>';
        echo '<th style="border: 1px solid black;padding: 10px;width:44%;word-wrap:break-word;">Recipient</th>';
        echo '<th style="border: 1px solid black;padding: 1px;width:7%;word-wrap:break-word;">Frequency</th>';
        echo '<th style="border: 1px solid black;padding: 10px;width:10%;word-wrap:break-word;">Days</th>';
        echo '<th style="border: 1px solid black;padding: 10px;width:8%;word-wrap:break-word;">Start Date</th>';
        echo '<th style="border: 1px solid black;padding: 10px;width:8%;word-wrap:break-word;">End Date</th>';
        echo '<th style="border: 1px solid black;padding: 10px;width:7%;word-wrap:break-word;">Time (PT)</th>';
        echo '<th style="border: 1px solid black;padding: 10px;width:15%;word-wrap:break-word;">Check to edit/delete</th>';
        echo '</tr>';
        while ($row=$result->fetch_assoc()) 
        {
            echo '<tr style="border: 1px solid black;" id="row_'.$row['job_id'].'">';
            echo '<td style="border: 1px solid black;padding: 10px;display:none;width:1%;">'.$row['job_id'].'</td>';
            echo '<td style="border: 1px solid black;padding: 10px;width:44%;word-wrap:break-word;">'.$row['recipient'].'</td>';
            echo '<td style="border: 1px solid black;padding: 5px;width:7%;word-wrap:break-word;">'.$row['repeat_event'].'</td>';
            echo '<td style="border: 1px solid black;padding: 7px;width:10%;word-wrap:break-word;">'.$row['frequency'].'</td>';
            echo '<td style="border: 1px solid black;padding: 7px;width:8%;word-wrap:break-word;">'.$row['start_date'].'</td>';
            echo '<td style="border: 1px solid black;padding: 7px;width:8%;word-wrap:break-word;">'.$row['end_date'].'</td>';
            echo '<td style="border: 1px solid black;padding: 1px;width:7%;word-wrap:break-word;">'.$row['hr'].'</td>';
            echo '<td style="border: 1px solid black;padding: 7px;text-align:center;width:15%;word-wrap:break-word;" valign="center"><input type="radio" name="edit" id="edit" value="edit" onclick="edit_confirmation('.$row['job_id'].',\''.$row['recipient'].'\',\''.$row['repeat_event'].'\',\''.$row['frequency'].'\',\''.$row['start_date'].'\',\''.$row['end_date'].'\',\''.$row['hr'].'\')">&nbsp;Edit&nbsp;<input type="radio" name="delete" id="delete" class="delete" value="delete" onClick="delete_confirmation('.$row['job_id'].')">&nbsp;Delete</td>';
            //onclick="delete_confirmation('.$row['job_id'].')"
            echo '</tr>';
        }
    }
    else
    {
        echo '<tr><br>No Scheduled Emails</tr>';
    }
echo '</table>';
echo '<br><br><div><input type="submit" name="submit" style="width:100px; height:30px;" value="Clear Selection" onclick="location.reload();"></div>';
echo '</center>';
?>

</div> <!-- steps/Content -->
</div> <!--wrapper-->
</body>
</html>
<?php
/*
if (isset($_POST['submit'])) {
$date1 = $_POST['datetimepicker1'];
$recipient1 = $_POST['recipient1'];

if(!empty($date1))
{
    if(!empty($recipient1))
    {
        $at_time = date("g:i A m/d/Y",strtotime($date1));
        $filename = "/var/www/html/cron.txt";
        $content = file_get_contents($filename);
        $content = "php /var/www/html/workfront_email.php --recipients ".$recipient1;
        file_put_contents($filename, $content);
        //echo get_current_user();
        //$output = shell_exec("at ".$at_time." -f /var/www/html/cron.txt 2>&1; echo $?");
        shell_exec($content);
        //echo "HI";
        //print_r ($output);
        echo "<br><b><font color=\"green\"> Project status will be sent to ".$recipient1." on ".$date1."</font></b>";
    }
    else
    {
         echo "<br><b><font color=\"red\"> Please enter recipients and try again.</font></b>";
    }
} 
if(!empty($date2))
{
    if(!empty($recipient2))
    {
        $at_time = date("g:i A m/d/Y",strtotime($date2));
        $filename = "/var/www/html/cron.txt";
        $content = file_get_contents($filename);
        $content = "php /var/www/html/workfront_email.php --recipients ".$recipient2;
        file_put_contents($filename, $content);
        shell_exec("at ".$at_time." -f /var/www/html/cron.txt");
        echo "<br><b><font color=\"green\"> Project status will be sent to ".$recipient2." on ".$date2."</font></b>";
    }
    else
    {
         echo "<br><b><font color=\"red\"> Please enter recipients and try again.</font></b>";
    }
} 
if(!empty($date3))
{
    if(!empty($recipient3))
    {
        $at_time = date("g:i A m/d/Y",strtotime($date3));
        $filename = "/var/www/html/cron.txt";
        $content = file_get_contents($filename);
        $content = "php /var/www/html/workfront_email.php --recipients ".$recipient3;
        file_put_contents($filename, $content);
        shell_exec("at ".$at_time." -f /var/www/html/cron.txt");
        echo "<br><b><font color=\"green\"> Project status will be sent to ".$recipient3." on ".$date3."</font></b>";
    }
    else
    {
         echo "<br><b><font color=\"red\"> Please enter recipients and try again.</font></b>";
    }
} 
if(!empty($date4))
{
    if(!empty($recipient4))
    {
        $at_time = date("g:i A m/d/Y",strtotime($date4));
        $filename = "/var/www/html/cron.txt";
        $content = file_get_contents($filename);
        $content = "php /var/www/html/workfront_email.php --recipients ".$recipient4;
        file_put_contents($filename, $content);
        shell_exec("at ".$at_time." -f /var/www/html/cron.txt");
        echo "<br><b><font color=\"green\"> Project status will be sent to ".$recipient4." on ".$date4."</font></b>"; 
    }
    else
    {
         echo "<br><b><font color=\"red\"> Please enter recipients and try again.</font></b>";
    }
}
if(empty($date1) AND empty($date2) AND empty($date3) AND empty($date4))
{
    echo "<br><b><font color=\"red\"> Please enter date and try again.</font></b>"; 
}
}
*/
?>
