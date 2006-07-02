<?php

include("../../inc/main.php");
require_once("/usr/share/phplot/phplot_data.php");
require_once("/usr/share/phplot/rgb.inc.php");

if (!$User->isAuthorized('admin')) {
  die();
}

extract($_GET);
if (!isset($width))  { $width = 600; }
if (!isset($height)) { $height = 400; }
if (!isset($path))   { $path = 'htdocs'; }

$plot =& new PHPlot($width, $height);

$path = (isset($_GET['path']))?$_GET['path']:"htdocs/";

// Who's done it ?
// We ORDER by contribution here, so that logic below builds the $data array in the right order for series to apear stacked
$result = mysql_query("SELECT DISTINCT author, SUM(added)-SUM(deleted) as total_contribution
                               FROM cvslog 
                               WHERE file like '$path%'
                               GROUP BY author 
                               ORDER BY total_contribution DESC");
while (list($codeur) = mysql_fetch_array($result)) {
  $codeurs[] = $codeur;
  $current_values[$codeur] = 0;
}
mysql_free_result($result);

// Get bounds
$result = mysql_query("SELECT UNIX_TIMESTAMP(DATE(MIN(date))), UNIX_TIMESTAMP(DATE(MAX(date))) FROM cvslog WHERE file like '$path%' AND date>=date_sub(NOW(), INTERVAL $deltatime DAY)");
list($start_date, $end_date) = mysql_fetch_array($result);
mysql_free_result($result);

$initial_row = array();
$current_values[] = strftime("%d %B", $start_date);
$current_values[] = $start_date;

$t = 0;
$row = array();
$row[] = strftime("%e %B", $start_date);
$row[] = $start_date;
$total = 0;
foreach ($codeurs as $id=>$c) {
  $res = mysql_query("SELECT SUM(added)+SUM(deleted) FROM cvslog WHERE UNIX_TIMESTAMP(date) < $start_date AND file LIKE '$path%' AND author='$c'");
  while (list($delta) = mysql_fetch_array($res)) {
    if ($delta == "") { $delta = 0; }
    $total += $delta;
    $current_values[$c] += $delta;
    $row[] = $total;
    $t += $total;
  }
}
$data[] = $row;
// print_r($current_score);

$max = $t;
$min = 0;
$cur_date = $start_date;
$count=0;
$cur_date += 86400; // Start counting at d+1
while ($cur_date <= $end_date) {
  $row = array();
  if ($count++ % 5 == 0) {
    $row[0] = strftime("%e %B", $cur_date);
  } else {
    $row[0] = "";
    $row[0] = strftime("%e %B", $cur_date); //TODEL
  }
  $row[1] = $cur_date;

  $dt = strftime("%Y-%m-%d", $cur_date);
  $added_all = 0;
  $deleted_all = 0;

  // Positive lines (total)
  $day_total = 0;
  foreach ($codeurs as $id=>$codeur) {
    $result = mysql_query("SELECT sum(added)+sum(deleted) FROM cvslog WHERE DATE(date) = '$dt' AND author='$codeur' AND file LIKE '$path%'");
    list($delta) = mysql_fetch_array($result);
    if ($delta == "") $delta = 0; // SUM returns null when no row matches

    $current_values[$codeur] += $delta;
    $day_total += $current_values[$codeur];
    $row[] = $day_total;
    $max = max($day_total, $max);
  }

  $data[] = $row;
  $cur_date += 86400; // Tomorow is a good day
}
// print "<pre>";
// print_r($data);
// DIe();

$plot->SetDataType('data-data');
$plot->SetDataValues($data);
if (abs($max) > 0) {
  $tmp_max = 0;
  if (abs($max) > 0) {
    $tmp_max = abs($max);
    $exp = 0;
    while ($tmp_max > 10) { $tmp_max /= 10; $exp++; }
    $tmp_max = ceil($tmp_max);
    while ($exp > 0) { $tmp_max *= 10; $exp--; }
    $tmp_max = $max/abs($max) * $tmp_max;
  }

  $plot->SetPlotAreaWorld(null, 0, null, null);
  $plot->plot_max_y = $tmp_max;
  $plot->plot_min_y = 0;
} 


$plot->SetDataType('data-data');
$plot->SetDataValues($data);

if (abs($max) > 0) {
  $tmp_max = abs($max);
  $exp = 0;
  while ($tmp_max > 10) { $tmp_max /= 10; $exp++; }
  $tmp_max = ceil($tmp_max);
  $nb_ticks = $tmp_max;
  while ($exp > 0) { $tmp_max *= 10; $exp--; }
  $tmp_max = $max/abs($max) * $tmp_max;

  $plot->SetPlotAreaWorld(null, 0, null, null);
  $plot->plot_min_y = 0;
  $plot->plot_max_y = $tmp_max;
} 
$plot->SetPlotType('lines');
$colors_defined = array("thierry" => "#ffce90", "cyril" => "#ceffce", "nico" => "#6785c3", "cyb" => "#ffff60" );
$colors_used = array();
foreach ($codeurs as $id=>$c) {
  $colors_used[] = $colors_defined[$c];
}
$plot->SetDataColors($colors_used); // Lime, NBI blue, salmon, and yellow
$plot->SetLegend($codeurs);
$plot->SetLegendPixels( 50, 30 );
$plot->SetXLabelAngle(90);
$plot->SetTitle("Total lignes de code");

// Use TTF
$ttf_dir = "/usr/share/fonts/truetype/freefont";
$fonts = array(
    'title_font' => array('size' => 13, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
    'legend_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSans.ttf'),
    'generic_font' => array('size' => 9, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
    'x_label_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSans.ttf'),
    'y_label_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSans.ttf'),
    'x_title_font' => array('size' => 10, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
    'y_title_font' => array('size' => 10, 'font'=>$ttf_dir.'/FreeSansBold.ttf')
);
foreach ($fonts as $object=>$fontdata) {
  $plot->$object = $fontdata;
}
$plot->use_ttf = TRUE;

// draw the graph
$plot->DrawGraph();

?>
