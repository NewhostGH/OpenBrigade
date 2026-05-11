<?php

include_once ("../../config.php");
check_all(14);
ini_set ('max_execution_time', 0);

$query="select count(1) from disponibilite";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$num1=intval($row[0]);

$query="select date_format(max(D_DATE), '%Y-%m') from disponibilite";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$last_month=$row[0];
if ( $last_month == '' ) exit;

echo "duplicate dispo of ".$last_month."<p>";
$query="insert into disponibilite (P_ID, D_DATE, PERIOD_ID)
select d1.P_ID, ADDDATE(d1.D_DATE, 30), d1.PERIOD_ID from disponibilite d1
where date_format(d1.D_DATE, '%Y-%m') ='".$last_month."'
and not exists (select 1 from disponibilite d2 where d1.P_ID=d2.P_ID and d2.D_DATE = ADDDATE(d1.D_DATE, 30) and d1.PERIOD_ID = d2.PERIOD_ID)";
$result=mysqli_query($dbc,$query);
//echo $query."<p>";

$query="select count(1) from disponibilite";
$result=mysqli_query($dbc,$query);
$row=mysqli_fetch_array($result);
$num2=intval($row[0]);

$inserted = $num2 - $num1;

echo $inserted." rows inserted";





