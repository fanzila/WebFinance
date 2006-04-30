<?php
require("../inc/main.php");
require_once("/usr/share/phplot/phplot_data.php");

global $User;
$User->getInfos();

extract($_GET);
if (!isset($width)) { $width = 500; }
if (!isset($height)) { $height = 400; }
if (!isset($legend)) { $legend = 1; } // By default show legend
if (!isset($movingaverage)) { $movingaverage = 0; } // By default hide movingaverage
if (!isset($hidetitle)) { $hidetitle = 0; } // By default hide hidetitle

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
if(diff_date($end_date,$_GET['end_date'])>0)
  $end_date=$_GET['end_date'];
}
if(isset($_GET['start_date']) AND !empty($_GET['start_date'])){
if(diff_date($start_date,$_GET['start_date'])>0)
  $start_date=$_GET['start_date'];
}

$data=array();
$max=1;
$min=-1;
$nb_day=diff_date($start_date,$end_date);

if($nb_day>0){

      $var=explode("-",$start_date);

      // $nb_day = $nb_day+30;
      $query_date_last_real=mysql_query("select UNIX_TIMESTAMP(max(date)) from webfinance_transactions where type='real' ". $query_account)
      or wf_mysqldie();

      $date_last_real=mysql_result($query_date_last_real, 0);

      for($step = 0; $step < $nb_day ; $step++) {

        $current_date=date("Y-m-d" , mktime(0, 0, 0, $var[1],1+$step, $var[0]) );

        $date_ex=explode("-",$current_date);

        $tmp=array();

        $tmp[0] = mktime(0, 0, 0, $var[1],1+$step, $var[0]);

        // prevs
        $query_sold=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE date<='$current_date' ".$query_account )
          or wf_mysqldie();
        $res=mysql_result($query_sold, 0);
        if(empty($res))
          $res=0;
        $tmp[1]=$res;
        $max=max($max,mysql_result($query_sold, 0));
        $min=min($min,mysql_result($query_sold, 0));

        // real
        if(mktime(0, 0, 0, $var[1],1+$step, $var[0]) <= $date_last_real) {
          $query_sold=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE date<='$current_date' AND type='real' ".$query_account )
            or wf_mysqldie();
          $res=mysql_result($query_sold, 0);
          if(empty($res))
            $res=0;
          $tmp[2]=$res;
          $max=max($max,mysql_result($query_sold, 0));
        }

        $data[]=$tmp;
      }
}else{
      $data=array( array('','',''));
}

// echo "<pre/>";
// print_r($data);

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
$ratioy = 1000*$height/$range; // $ratioy = nb of pixels per 1000 euro
if ($ratioy > 20) {
  $graph2->SetYTickIncrement( 1000 );
} else if ($ratioy > 9) {
  $graph2->SetYTickIncrement( 5000 );
} else if ($ratioy > 5) {
  $graph2->SetYTickIncrement( 10000 );
} else {
  $graph2->SetYTickIncrement( 15000 );
}

// Then horizontaly
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
  $graph2->SetXLabelAngle(0); // <-- this is possible only with TTF fonts
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

# Make a legend for the 2 functions:
//		$graph2->SetLineWidths(array('1','1'));

$graph2->SetLineStyles(array('solid','solid'));

if ($legend != 0) {
  $graph2->SetLegend(array(utf8_decode(_('prev')), utf8_decode(_('real')) ));
  $graph2->SetLegendPixels(82, $height-100);
}

$graph2->SetDataColors(array( 'red' ,'blue', 'green'));
$graph2->SetDataType("text-data");
$graph2->SetDataValues($data);
$graph2->SetPlotType("lines");
$graph2->SetLineWidth( array(2, 2, 2) );

// NB : Find the vertical range and extend it for positive and negative values
// to the next "round" number so that the horizontal ticks fall on nice odd
// numbers. 
$tmp_max = abs($max);
$exp = 0;
while ($tmp_max > 10) { $tmp_max /= 10; $exp++; }
$tmp_max = ceil($tmp_max);
while ($exp > 0) { $tmp_max *= 10; $exp--; }
$tmp_max = $max/abs($max) * $tmp_max;

$tmp_min = abs($min);
$exp = 0;
while ($tmp_min > 10) { $tmp_min /= 10; $exp++; }
$tmp_min = ceil($tmp_min);
while ($exp > 0) { $tmp_min *= 10; $exp--; }
$tmp_min = $min/abs($min) * $tmp_min;

$graph2->SetPlotAreaWorld(null, null, null, null);
$graph2->plot_min_y = $tmp_min;
$graph2->plot_max_y = $tmp_max;

if ($movingaverage) {
  $graph2->DoMovingAverage(1,$moving_average_blur,FALSE);
}

if (isset($User->prefs->graphgrid) && $User->prefs->graphgrid == "on") {
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
    'title_font' => array('size' => 13, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
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
