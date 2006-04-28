<?php
require("../inc/main.php");
require_once("/usr/share/phplot/phplot.php");

global $User;
$User->getInfos();

extract($_GET);
if (!isset($width)) { $width = 500; }
if (!isset($height)) { $height = 400; }
// if (!isset($nb_months)) { $nb_months = 24; }

/*
 * return positive if $start_date < $end_date
 */
function diff_date( $start_date,$end_date){

	$tmp=explode("-",$start_date);
	$date2 = mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);

	$tmp=explode("-",$end_date);
	$date = mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);

	return floor(($date - $date2) / (3600 * 24));
}

function cmp_data($array1, $array2){
	$sum1=array_sum($array1);
	$sum2=array_sum($array2);
	if ($sum1==$sum2)
        return 0;
    if($sum1<0 AND $sum2<0)
    		return ($sum1 < $sum2) ? -1 : 1;
    	else
    		return ($sum1 < $sum2) ? 1 : -1;
}

$query_account="";
$text="";
if(!empty($_GET['account'])){
$query_account=" AND id_account=".$_GET['account'];
$query=mysql_query("SELECT MIN(date) as min , MAX(date) as max ".
       "FROM webfinance_transactions ".
       "WHERE id_account=".$_GET['account'] )
  or wf_mysqldie();
}else
$query=mysql_query("SELECT MIN(date) as min , MAX(date) as max FROM webfinance_transactions ") or wf_mysqldie();

$res=mysql_fetch_assoc($query);
if(!empty($res['min']) AND !empty($res['max'])){
$end_date=$res['max'];
$start_date=$res['min'];
}else{
$start_date=date("Y-m-d");
$end_date=date("Y-m-d" , mktime(0, 0, 0, date("m")+1, date("d"), date("Y")) );
}

if(isset($_GET['end_date']) AND !empty($_GET['end_date'])){
if(diff_date($start_date,$_GET['end_date'])>0)
  $end_date=$_GET['end_date'];
}

$data=array();
$max=1;
$min=-1;
$nb_day=diff_date($start_date,$end_date);

if($nb_day>0){

$var=explode("-",$start_date);

$nb_day = $nb_day+30;
$query_date_last_real=mysql_query("select UNIX_TIMESTAMP(max(date)) from webfinance_transactions where type='real' ". $query_account)
or wf_mysqldie();

$date_last_real=mysql_result($query_date_last_real, 0);

for($step = 0; $step < $nb_day ; $step++) {

  $current_date=date("Y-m-d" , mktime(0, 0, 0, $var[1],1+$step, $var[0]) );

  $date_ex=explode("-",$current_date);

  $tmp=array();

  if($date_ex[2]==1)
    $tmp[]=utf8_decode( strftime("%B %y", mktime(0, 0, 0, $var[1],1+$step, $var[0]) ));
  else
    $tmp[]='';

  // prevs
  $query_sold=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE date<='$current_date' ".$query_account )
    or wf_mysqldie();
  $res=mysql_result($query_sold, 0);
  if(empty($res))
    $res=0;
  $tmp[]=$res;
  $max=max($max,mysql_result($query_sold, 0));

  // real
  if(mktime(0, 0, 0, $var[1],1+$step, $var[0]) <= $date_last_real) {
    $query_sold=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE date<='$current_date' AND type='real' ".$query_account )
      or wf_mysqldie();
    $res=mysql_result($query_sold, 0);
    if(empty($res))
      $res=0;
    $tmp[]=$res;
    $max=max($max,mysql_result($query_sold, 0));
  }

  $data[]=$tmp;
}
}else{
$data=array( array('','',''));
}

//		echo "<pre/>";
//		print_r($data);

//Define the object
$graph2=& new PHPlot($width,$height);

//Set titles
$title=utf8_decode(_("Cash flow / all history"));

$graph2->SetTitle($title);
$graph2->SetXTitle('');
//$graph2->SetNumXTicks($nb_day/40);
$graph2->SetYTitle(_('Amount (Euro)')); 

if($max>10000)
$graph2->SetYTickIncrement( round($max/10,-3) );


$graph2->SetXLabelAngle(90);

# Make a legend for the 2 functions:
//		$graph2->SetLineWidths(array('1','1'));

$graph2->SetLineStyles(array('solid','solid'));

$graph2->SetLegend(array(utf8_decode(_('prev')), utf8_decode(_('real')) ));

$graph2->SetDataColors(array( 'green' ,'red'));

$graph2->SetDataType("text-data");

$graph2->SetDataValues($data);

$graph2->SetPlotType("lines");


//Draw it
if (isset($User->prefs->graphgrid) && $User->prefs->graphgrid == "on") { 
$graph2->SetDrawXGrid(true); 
$graph2->SetDrawYGrid(true); 
} else {
$graph2->SetDrawXGrid(false); 
$graph2->SetDrawYGrid(false); 
}
$graph2->DrawGraph();

// vim: sw=6

?>
