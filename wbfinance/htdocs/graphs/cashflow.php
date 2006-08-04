<?php
require("../inc/main.php");
require_once("/usr/share/phplot/phplot_data.php");

must_login();

global $User;
$User->getInfos();

extract($_GET);
if (!isset($width)) { $width = 500; }
if (!isset($height)) { $height = 400; }
if (!isset($legend)) { $legend = 1; } // By default show legend
if (!isset($movingaverage)) { $movingaverage = 0; } // By default hide movingaverage
if (!isset($hidetitle)) { $hidetitle = 0; } // By default hide hidetitle

$query_account="";
$text="";
if(!empty($_GET['account'])){
  $query_account=" AND id_account=".$_GET['account'];
  $query=mysql_query("SELECT MIN(date) as min , MAX(date) as max, UNIX_TIMESTAMP(MIN(date)) as ts_min, UNIX_TIMESTAMP(MAX(date)) as ts_max ".
		     "FROM webfinance_transactions ".
		     "WHERE id_account=".$_GET['account'] )
    or wf_mysqldie();
}else
  $query=mysql_query("SELECT MIN(date) as min , MAX(date) as max, UNIX_TIMESTAMP(MIN(date)) as ts_min, UNIX_TIMESTAMP(MAX(date)) as ts_max ".
		     "FROM webfinance_transactions ")
    or wf_mysqldie();

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
$max=0;
$min=0;
$nb_day =  ($end_date_ts - $start_date_ts) / (3600 * 24);

//echo $nb_day;

if($nb_day>0){

  $var=explode("-",$start_date);

  $query_date_last_real=mysql_query("select UNIX_TIMESTAMP(max(date)) from webfinance_transactions where type='real' ". $query_account)
    or wf_mysqldie();

  $date_last_real=mysql_result($query_date_last_real, 0);

  $q = "SELECT amount, type, date, UNIX_TIMESTAMP(date) as ts_date , id_account, exchange_rate FROM webfinance_transactions ORDER BY date ";
  $res = mysql_query($q) or wf_mysqldie();
  $trs=array();
  while($row  = mysql_fetch_assoc($res)){
    if(empty($row['exchange_rate']))
      $row['exchange_rate']=1;

    $row['amount']=$row['amount']/$row['exchange_rate'];
    $trs[] = $row;
  }
  mysql_free_result($res);

  $q_real = "SELECT amount, type, date, UNIX_TIMESTAMP(date) as ts_date, id_account, exchange_rate FROM webfinance_transactions WHERE type='real' ORDER BY date ";
  $res_real = mysql_query($q_real) or wf_mysqldie();
  while($row  = mysql_fetch_assoc($res_real)){
    if(empty($row['exchange_rate']))
      $row['exchange_rate']=1;

    $row['amount']=$row['amount']/$row['exchange_rate'];
    $trs_real[] = $row;
  }
  mysql_free_result($res_real);

  for($step = 0; $step <= $nb_day ; $step++ ){
    $current_date = mktime(0, 0, 0, $var[1], $var[2]+$step, $var[0]);

    $tmp[0]=$current_date;

    //prev
    $x=0;
    $i=0;
    $sum = 0;
    if(count($trs)>0){
      foreach($trs as $tr){
	if($tr['ts_date'] <= $current_date)
	  $sum  = $sum + $tr['amount'];
      }
      $tmp[1]=$sum;
      $max = max($max,$sum);
      $min = min($min,$sum);
    }else{
      $tmp[1]="";
    }

    //real
    $x = 0;
    $i = 0;
    $sum = 0;
    if($date_last_real>=$current_date){
      foreach($trs_real as $tr){
	if($tr['ts_date'] <= $current_date)
	  $sum  = $sum + $tr['amount'];
      }
      $tmp[2]=$sum;
      $max = max($max,$sum);
      $min = min($min,$sum);

    }else{
      $tmp[2]="";
    }
    $data[] = $tmp;
  }

}else{
      $data=array( array('','',''));
      $max=1;
      $min=0;
      $nb_day=1;
}

//echo "<pre/>";
//print_r($data);

//Define the object
$graph2=& new PHPlot_Data($width,$height);

//Set titles
if ($hidetitle) {
  $title = "";
  $graph2->SetYTitle('');
} else {
  $title=utf8_decode(_("Cash flow / all history"));
  $graph2->SetYTitle('€'); // <-- this is possible only with UTF8-aware TTF fonts
}

$graph2->SetTitle($title);
$graph2->SetXTitle('');



// NB : Calculate the density of tick horizontaly and verticaly to not "flood"
// the graph. Try to be clever : take into account the width & height of the
// image, and the range of values.
//
// First verticaly
$range = $max + abs($min);

if($range != 0 AND abs($range)>1000 ){
  $ratioy = 1000*$height/$range; // $ratioy = nb of pixels per 1000 euro
  if ($ratioy > 20) {
    $graph2->SetYTickIncrement( 1000 );
  } else if ($ratioy > 9) {
    $graph2->SetYTickIncrement( 5000 );
  } else if ($ratioy > 5) {
    $graph2->SetYTickIncrement( 10000 );
  } else if($ratioy > 2) {
    $graph2->SetYTickIncrement( 15000 );
  }
 }

// Then horizontaly
if($nb_day>0){
  $ratiox = ($width / $nb_day);
  if ($ratiox > 15) {
    // 30 pixels per day is plenty to show the grid at day level. Labels are the
    // day date ex "2 feb 06"
    $moving_average_blur = 7; // Week
    $graph2->SetXTickIncrement( 1 );
    $graph2->SetXLabelAngle(60); // <-- this is possible only with TTF fonts
    for ($i=0 ; $i<count($data) ; $i++) {
      $data[$i][0] = strftime("%e %b %y", $data[$i][0]);
    }
  } elseif ($ratiox > 7) {
    // 7 pixels is enough to show the grid at week level
    $graph2->SetXTickIncrement( 7 );
    $moving_average_blur = 15; // 2 Weeks
    $graph2->SetXLabelAngle(30); // <-- this is possible only with TTF fonts

    for ($i=0 ; $i<count($data) ; $i++) {
      if ($i%7 == 0)
	$data[$i][0] = strftime(_("Week #%W"), $data[$i][0]); // Week number + year
      else
	$data[$i][0] = "";
    }
  } else { // Under 7 pixels per day we show the grid at month level
    $graph2->SetXTickIncrement( 30 );
    $old_ts = $data[0][0] - 86400*60; // Make sur we wrap to print the first label
    $moving_average_blur = 20; // 20 days
    $graph2->SetXLabelAngle(90); // <-- this is possible only with TTF fonts
    for ($i=0 ; $i<count($data) ; $i++) {
      if (strftime("%m%Y", $old_ts) != strftime("%m%Y", $data[$i][0])) {
	$old_ts = $data[$i][0];
	$data[$i][0] = utf8_decode(ucfirst(strftime("%b %Y", $data[$i][0])));
      } else {
	$old_ts = $data[$i][0];
	$data[$i][0] = "";
      }
    }
  }

 }

# Make a legend for the 2 functions:
//		$graph2->SetLineWidths(array('1','1'));

$graph2->SetLineStyles(array('solid','solid'));

if ($legend != 0) {
  $graph2->SetLegend(array(utf8_decode(_('prev')), utf8_decode(_('real')) ));
  //$graph2->SetLegendPixels(82, $height-100);
}

$graph2->SetDataColors(array( 'red' ,'blue', 'green'));
$graph2->SetDataType("text-data");
$graph2->SetDataValues($data);
$graph2->SetPlotType("lines");
$graph2->SetLineWidth( array(1, 1, 2) );

// NB : Find the vertical range and extend it for positive and negative values
// to the next "round" number so that the horizontal ticks fall on nice odd
// numbers.
if (abs($max) > 0) {
  $tmp_max = abs($max);
  $exp = 0;
  while ($tmp_max > 10) { $tmp_max /= 10; $exp++; }
  $tmp_max = ceil($tmp_max);
  while ($exp > 0) { $tmp_max *= 10; $exp--; }
  $tmp_max = $max/abs($max) * $tmp_max;
} else {
  $tmp_max = 0;
}

if (abs($min) > 0) {
  $tmp_min = abs($min);
  $exp = 0;
  while ($tmp_min > 10) { $tmp_min /= 10; $exp++; }
  $tmp_min = ceil($tmp_min);
  while ($exp > 0) { $tmp_min *= 10; $exp--; }
  $tmp_min = $min/abs($min) * $tmp_min;
} else {
  $tmp_min = 0;
}

if($tmp_min != $tmp_max ){
  $graph2->SetPlotAreaWorld(null, null, null, null);
  $graph2->plot_min_y = $tmp_min;
  $graph2->plot_max_y = $tmp_max;
}

if ($movingaverage) {
  $graph2->DoMovingAverage(0,$moving_average_blur,FALSE);
}

if (isset($User->prefs->graphgrid) && $User->prefs->graphgrid ) {
  $graph2->SetDrawXGrid(true);
  $graph2->SetDrawYGrid(true);
} else {
  $graph2->SetDrawXGrid(false);
  $graph2->SetDrawYGrid(false);
}

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

$graph2->DrawGraph();

// vim: sw=2 ts=2

?>
