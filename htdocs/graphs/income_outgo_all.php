<?php
require("../inc/main.php");
require_once("/usr/share/phplot/phplot_data.php");

global $User;
$User->getInfos();

extract($_GET);

if (!isset($width))
  $width = 500;
if (!isset($height))
  $height = 400;
if (!isset($hidetitle))
  $hidetitle = 1;

$query_account="";
$text="";
if(!empty($_GET['account'])){
  $query_account=" AND id_account=".$_GET['account'];
  $query=mysql_query("SELECT ".
		     "MIN(date) as min , ".
		     "UNIX_TIMESTAMP(MIN(date)) as ts_min  , ".
		     "MAX(date) as max , ".
		     "UNIX_TIMESTAMP(MAX(date)) as ts_max ".
		     "FROM webfinance_transactions WHERE id_account=".$_GET['account'] )
    or wf_mysqldie();
 }else{
  $query=mysql_query("SELECT MIN(date) as min , ".
		     "UNIX_TIMESTAMP(MIN(date)) as ts_min , ".
		     "MAX(date) as max , ".
		     "UNIX_TIMESTAMP(MAX(date)) as ts_max ".
		     "FROM webfinance_transactions ")
    or wf_mysqldie();
 }

$res=mysql_fetch_assoc($query);
if($res['ts_min']>0 AND $res['ts_max']>0 AND ($res['ts_max'] - $res['ts_min'])>=(3600 * 24) ){
  $end_date=$res['max'];
  $end_date_ts=$res['ts_max'];
  $start_date=$res['min'];
  $start_date_ts=$res['ts_min'];
}else{
  $start_date=date("Y-m-d");
  $start_date_ts=mktime();
  $end_date_ts=mktime(0, 0, 0, date("m")+1,0, date("Y"));
  $end_date=date("Y-m-d" , $end_date_ts );
}

//echo "s_d: $start_date e_d: $end_date";

if(isset($_GET['end_date']) AND !empty($_GET['end_date'])){
  $tmp=explode("-",$_GET['end_date']);

  if( mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]) < $end_date_ts){
    $end_date=$_GET['end_date'];
    $end_date_ts=mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);
  }
}
if(isset($_GET['start_date']) AND !empty($_GET['start_date'])){
  $tmp=explode("-",$_GET['start_date']);

  if( mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]) > $start_date_ts ){
    $start_date = $_GET['start_date'];
    $start_date_ts = mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);
  }
}

$data=array();
$max=1;
$min=-1;
$nb_day =  ($end_date_ts - $start_date_ts) / (3600 * 24);

if($nb_day>0){

  $var=explode("-",$start_date);

  $nb_month = ($nb_day/30) + 1;

  $begin_date=date("Y-m-d" , mktime(0, 0, 0, $var[1],1, $var[0]));
  $begin_date_ts = mktime(0, 0, 0, $var[1],1, $var[0]);

  $q_trs_neg = mysql_query("SELECT amount , ".
			   "UNIX_TIMESTAMP(date) as ts_date ".
			   "FROM webfinance_transactions WHERE amount<0 ")
    or die(mysql_error());

  $trs_neg = array();
  while($row = mysql_fetch_assoc($q_trs_neg))
    $trs_neg[] = $row;
  mysql_free_result($q_trs_neg);

  $q_trs_pos = mysql_query("SELECT amount , ".
			   "UNIX_TIMESTAMP(date) as ts_date ".
			   "FROM webfinance_transactions WHERE amount>0 ")
    or die(mysql_error());

  $trs_pos = array();
  while($row = mysql_fetch_assoc($q_trs_pos))
    $trs_pos[] = $row;
  mysql_free_result($q_trs_pos);

  for($step = 0; $step < $nb_month ; $step++ ){
    $end_date=date("Y-m-d" , mktime(0, 0, 0, $var[1]+$step,0, $var[0]) );
    $end_date_ts = mktime(0, 0, 0, $var[1]+$step,0, $var[0]);

    $begin=explode("-",$begin_date);

    $tmp[0]=date("M y", mktime(0, 0, 0, $begin[1], 1, $begin[0]) );

    //outgo
    $sum = 0;
    foreach($trs_neg as $tr){
      if($tr['ts_date'] <= $end_date_ts AND $tr['ts_date'] >= $begin_date_ts )
	$sum  = $sum + $tr['amount'];
    }
    $tmp[1]= $sum*-1 ;
    $max = max($max,$tmp[1]);
    $min = min($min,$sum);

    //income
    $sum = 0;
    foreach($trs_pos as $tr){
      if($tr['ts_date'] <= $end_date_ts AND $tr['ts_date'] >= $begin_date_ts )
	$sum  = $sum + $tr['amount'];
    }
    $tmp[2]=$sum*1;
    $max = max($max,$sum);
    $min = min($min,$sum);

    $data[] = $tmp;

    $end=explode("-",$end_date);
    $begin_date=date("Y-m-d" , mktime(0, 0, 0, $end[1]+1, 1 , $end[0]) );
    $begin_date_ts=mktime(0, 0, 0, $end[1]+1, 1 , $end[0]);
  }

 }else{
  $data=array(array('','',''));
 }

//Define the object
$graph2=& new PHPlot_Data($width,$height);

//Set titles

//Set titles
if ($hidetitle) {
  $title = "";
  $graph2->SetYTitle('');
} else {
  $title=utf8_decode(_("Income & Outgo / all history"));
  $graph2->SetYTitle('€'); // <-- this is possible only with UTF8-aware TTF fonts
}


$graph2->SetTitle($title);
$graph2->SetXTitle('');

$graph2->SetNumXTicks($nb_day);


// NB : Calculate the density of tick horizontaly and verticaly to not "flood"
// the graph. Try to be clever : take into account the width & height of the
// image, and the range of values.
//
// First verticaly
$range = $max;

if($range != 0 AND abs($range)>100 ){
  $ratioy = 1000*$height/$range; // $ratioy = nb of pixels per 1000 euro
  if ($ratioy > 20 ) {
    $graph2->SetYTickIncrement( 1000 );
  } else if ($ratioy > 9) {
    $graph2->SetYTickIncrement( 2500 );
  } else if ($ratioy > 5) {
    $graph2->SetYTickIncrement( 5000 );
  } else if($ratioy > 2) {
    $graph2->SetYTickIncrement( 10000 );
  }
 }

$graph2->SetXLabelAngle(90);

# Make a legend for the 2 functions:
$graph2->SetLegend(array(utf8_decode(_('Outgo')), utf8_decode(_('Income'))));

$graph2->SetDataColors(array('orange', 'green'));

$graph2->SetDataType("text-data");

$graph2->SetDataValues($data);

$graph2->SetPlotType("bars");

//$graph2->SetDrawXGrid(true);

$graph2->SetXTickIncrement(0.5);


// NB : Base apearance fonts : use TTF for unicode support and nice looks.
//
// This is a hack around crippled phplot's object interface that makes it
// impossible to specify a correct filepath for the fonts used with it's
// getter/setter methods. We access directly the object's internal properties
// just before rendering.
$ttf_dir = "/usr/share/fonts/truetype/freefont";
$fonts = array(
    'title_font' => array('size' => 13, 'font'=>$GLOBALS['_SERVER']['DOCUMENT_ROOT']."/css/themes/".$User->prefs->theme."/buttonfont.ttf"),
    'legend_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
    'generic_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
    'x_label_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSans.ttf'),
    'y_label_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSans.ttf'),
    'x_title_font' => array('size' => 10, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
    'y_title_font' => array('size' => 10, 'font'=>$ttf_dir.'/FreeSansBold.ttf')
);
foreach ($fonts as $object=>$fontdata) {
  $graph2->$object = $fontdata;
}
$graph2->use_ttf = TRUE;


//Draw it
$graph2->DrawGraph();




?>
