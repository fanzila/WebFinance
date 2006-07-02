<?php

include("../../inc/main.php");
require_once("/usr/share/phplot/phplot_data.php");

if (!$User->isAuthorized('admin')) {
  die();
}

extract($_GET);
if (!isset($width))  { $width = 600; }
if (!isset($height)) { $height = 400; }
if (!isset($path))   { $path = 'htdocs'; }

$plot =& new PHPlot($width, $height);

// Who's done it ?
$result = mysql_query("SELECT DISTINCT author FROM cvslog WHERE file like '$path%' AND date>=date_sub(NOW(), INTERVAL $deltatime DAY)");
while (list($codeur) = mysql_fetch_array($result)) {
  $codeurs[] = $codeur;
  $current_values[$codeur] = 0;
}
mysql_free_result($result);
$totals = array();
foreach($codeurs as $id=>$codeur) {
  $totals[$id] = 0;
}

// Get bounds
$result = mysql_query("SELECT UNIX_TIMESTAMP(DATE(MIN(date))), UNIX_TIMESTAMP(DATE(MAX(date))) FROM cvslog WHERE file like '$path%' AND date>=date_sub(NOW(), INTERVAL $deltatime DAY)");
list($start_date, $end_date) = mysql_fetch_array($result);
mysql_free_result($result);

$max = 0;
$min = 0;
$cur_date = $start_date;
$count=0;
while ($cur_date <= $end_date) {
  $row = array();
  if ($count++ % 5 == 0) {
    $row[0] = strftime("%d %B", $cur_date);
  } else {
    $row[0] = "";
  }
  $row[1] = $cur_date;

  $dt = strftime("%Y-%m-%d", $cur_date);
  $added_all = 0;
  $deleted_all = 0;

  // Added lines
  $result = mysql_query("SELECT sum(added) FROM cvslog WHERE DATE(date) = '$dt' AND file LIKE '$path%'");
  list($total_added) = mysql_fetch_array($result);
  if ($total_added == "") { $total_added = 0; } // SUM returns null when no row matches
  $max = max($total_added, $max);
//   print "$max -- $dt<br/>";
  foreach ($codeurs as $id=>$codeur) {
    $result = mysql_query("SELECT sum(added) FROM cvslog WHERE DATE(date) = '$dt' AND author='$codeur' AND file LIKE '$path%'");
    $t = mysql_fetch_array($result);
    if ($t[0] == "") $t[0] = 0; 

    $row[] = $total_added;
    $total_added -= $t[0];
  }

  // Deleted lines
  $result = mysql_query("SELECT sum(deleted) FROM cvslog WHERE DATE(date) = '$dt' AND file LIKE '$path%'");
  list($total_deleted) = mysql_fetch_array($result);
  if ($total_deleted == "") { $total_deleted = 0; } // SUM returns null when no row matches
  $min = min($total_deleted, $min);
  foreach ($codeurs as $id=>$codeur) {
    $result = mysql_query("SELECT sum(deleted) FROM cvslog WHERE DATE(date) = '$dt' AND author='$codeur' AND file LIKE '$path%'");
    $t = mysql_fetch_array($result);
    if ($t[0] == "") $t[0] = 0; 

    $row[] = $total_deleted;
    $total_deleted -= $t[0];
  }

  $data[] = $row;
  $cur_date += 86400; // Tomorow is a good day
}
// print "<pre>";
// print_r($data);

$plot->SetDataType('data-data');
$plot->SetDataValues($data);
if (abs($max) > 0 || abs($min) > 0) {
  $tmp_max = 0;
  $tmp_min = 0;
  if (abs($max) > 0) {
    $tmp_max = abs($max);
    $exp = 0;
    while ($tmp_max > 10) { $tmp_max /= 10; $exp++; }
    $tmp_max = ceil($tmp_max);
    while ($exp > 0) { $tmp_max *= 10; $exp--; }
    $tmp_max = $max/abs($max) * $tmp_max;
  }

  if (abs($min) > 0) {
    $tmp_min = abs($min);
    $exp = 0;
    while ($tmp_min > 10) { $tmp_min /= 10; $exp++; }
    $tmp_min = ceil($tmp_min);
    while ($exp > 0) { $tmp_min *= 10; $exp--; }
    $tmp_min = $min/abs($min) * $tmp_min;
  }

  $plot->SetPlotAreaWorld(null, 0, null, null);
  $plot->plot_max_y = $tmp_max;
  $plot->plot_min_y = $tmp_min;
} 
$plot->SetPlotType('area');
$plot->SetLineWidth(array(2, 1, 1, 1));
$plot->SetLineStyles('solid');
$colors_defined = array("thierry" => "#ffce90", "cyril" => "#ceffce", "nico" => "#6785c3", "cyb" => "#ffff60" );
$colors_used = array();
foreach ($codeurs as $id=>$c) {
  $colors_used[] = $colors_defined[$c];
}
$plot->SetDataColors($colors_used); // Lime, NBI blue, salmon, and yellow
$plot->SetLegend($codeurs);
$plot->SetLegendPixels( 50, $height-190 );
$plot->SetXLabelAngle(90);
$plot->SetYTickIncrement(250);
$plot->SetTitle("Solde de lignes par jour");

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
