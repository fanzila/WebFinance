<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL and otheres
// Author : Cyril Gantin <cgantin@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$


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
$result = mysql_query("SELECT DISTINCT author FROM cvslog WHERE file like '$path%' AND date>=date_sub(NOW(), INTERVAL $deltatime DAY)") or die(mysql_error());
while (list($codeur) = mysql_fetch_array($result)) {
  $codeurs[] = $codeur;
}
mysql_free_result($result);
$totals = array();
foreach($codeurs as $id=>$codeur) {
  $totals[$id] = 0;
}

// Get bounds
$result = mysql_query("SELECT UNIX_TIMESTAMP(DATE(MIN(date))), UNIX_TIMESTAMP(DATE(MAX(date))) FROM cvslog WHERE file like '$path%' AND date>=date_sub(NOW(), INTERVAL $deltatime DAY)") or die(mysql_error());
list($start_date, $end_date) = mysql_fetch_array($result);
mysql_free_result($result);

$max = 0;
$cur_date = $start_date;
$count=0;
while ($cur_date <= $end_date) {
  $row = array();
  if ($count++ % 5 == 0) {
    $row[0] = strftime("%d/%m/%Y", $cur_date);
  } else {
    $row[0] = "";
  }
  $row[1] = $cur_date;

  $dt = strftime("%Y-%m-%d", $cur_date);
  foreach ($codeurs as $id=>$codeur) {
    $result = mysql_query("SELECT sum(added), sum(deleted), sum(added)+sum(deleted) FROM cvslog WHERE DATE(date) = '$dt' AND author='$codeur'") or die(mysql_error());
    $t = mysql_fetch_array($result);
    if ($t[2] == "") $t[2] = 0; 
    $totals[$id]+=$t[2];
    $row[] = $totals[$id];
    $max = max($totals[$id], $max);
  }

  $data[] = $row;
  $cur_date += 86400; // Tomorow is a good day
}

// print "<pre>";
// print "Max $max\n";
// print_r($data);

$plot->SetDataType('data-data');
$plot->SetDataValues($data);
if (abs($max) > 0) {
  $tmp_max = abs($max);
  $exp = 0;
  while ($tmp_max > 10) { $tmp_max /= 10; $exp++; }
  $tmp_max = ceil($tmp_max)+1;
  $nb_ticks = $tmp_max;
  while ($exp > 0) { $tmp_max *= 10; $exp--; }
  $tmp_max = $max/abs($max) * $tmp_max;

  $plot->SetPlotAreaWorld(null, 0, null, null);
  $plot->plot_min_y = 0;
  $plot->plot_max_y = $tmp_max;
} 
$plot->SetPlotType('area');
$plot->SetLineWidth(array(2, 1, 1, 1));
$plot->SetLineStyles('solid');
$plot->SetDataColors(array("#ceffce", "#6785c3", "#ffce90", "#ffff60" )); // Lime, NBI blue, salmon, and yellow
$plot->SetLegend($codeurs);
$plot->SetLegendPixels( 50, 30 );
$plot->SetXLabelAngle(90);
$plot->SetYTickIncrement(2000);
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
