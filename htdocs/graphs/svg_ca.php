<?php
// 
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$
//
// Generates an income graph based on the invoices in the database. Renders a SVG graphic.

require("../inc/main.php");
header("Content-Type: image/svg+xml; charset=utf8");

// -----------------------------------------------------------------------------------------
// Data definitions 
// -----------------------------------------------------------------------------------------
// Defines for the look of the graph
$top_color = "#6785c3";
$bottom_color = "#fefeff";
$height = isset($_GET['height'])?$_GET['height']:400;
$width = isset($_GET['width'])?$_GET['width']:600;
$margin = 10;
if (isset($_GET['bar_width'])) { $bar_width = $_GET['bar_width']; } // Else : bars stretch to fill given $width
$bar_sep = 5;
$nb_months = isset($_GET['nb_months'])?$_GET['nb_months']:12;
// Fill the data graphed
$bar_data = array();

for ($i=$nb_months-1 ; $i>=0; $i--) {
  $result = mysql_query("SELECT date_format(date_sub(now(), INTERVAL $i MONTH), '%m/%y') as mois_shown, date_format(date_sub(now(), INTERVAL $i MONTH), '%Y%m') as mois");
  list($mois_shown, $mois) = mysql_fetch_array($result);
  mysql_free_result($result);

  $result = mysql_query("SELECT sum(fl.prix_ht*fl.qtt) as total, count(f.id_facture) as nb_factures,
                                date_format(f.date_facture, '%Y%m') as groupme, date_format(f.date_facture, '%m/%y') as mois
                         FROM webfinance_invoices as f, webfinance_invoice_rows as fl
                         WHERE fl.id_facture=f.id_facture
                         AND f.type_doc = 'facture'
                         AND date_format(f.date_facture,'%Y%m') = '$mois' GROUP BY groupme") or wf_mysqldie();
  $billed = mysql_fetch_object($result);
  $bar = new stdClass();
  $bar->value = $billed->total;
  $bar->legend = $mois_shown;
  $bar->label = preg_replace("/\./", ",", sprintf("%.1f", $billed->total/1000))."K\xe2\x82\xac";
  array_push($bar_data, $bar);
}


// Start of svg rendering. Here live dragons :)

print '<?xml version="1.0" encoding="UTF-8" standalone="no"?>'; // Has to be printed since parser gets <? for himself
?>

<!-- Created with Inkscape (http://www.inkscape.org/) -->
<svg
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:cc="http://web.resource.org/cc/"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:svg="http://www.w3.org/2000/svg"
   xmlns="http://www.w3.org/2000/svg"
   xmlns:xlink="http://www.w3.org/1999/xlink"
   xmlns:sodipodi="http://inkscape.sourceforge.net/DTD/sodipodi-0.dtd"
   xmlns:inkscape="http://www.inkscape.org/namespaces/inkscape"
   width="<?= $width ?>px"
   height="<?= $height ?>px"
   id="svg2"
   sodipodi:version="0.32"
   inkscape:version="0.43"
   sodipodi:docbase="/tmp"
   sodipodi:docname="graphbar.svg">
  <defs id="defs4">
    <linearGradient inkscape:collect="always" id="linearGradient2182">
      <stop style="stop-color:<?= $bottom_color ?>;stop-opacity:1" offset="0" id="stop2186" /> <!-- color top -->
      <stop style="stop-color:<?= $top_color ?>;stop-opacity:1" offset="1" id="stop2184" /> <!-- color bottom --> 
    </linearGradient>
    <linearGradient
       inkscape:collect="always"
       xlink:href="#linearGradient2182"
       id="nbi_gradient"
       x1="<?= $margin + $bar_width / 2 ?>"
       y1="<?= $height-$margin ?>"
       x2="<?= $margin + $bar_width / 2 ?>"
       y2="<?= $height - $margin - 100 ?>"
       gradientUnits="userSpaceOnUse" />
  </defs>
  <sodipodi:namedview
     id="base"
     pagecolor="#ffffff"
     bordercolor="#666666"
     borderopacity="1.0"
     inkscape:pageopacity="0.0"
     inkscape:pageshadow="2"
     inkscape:zoom="3.959798"
     inkscape:cx="183.98938"
     inkscape:cy="665.81483"
     inkscape:document-units="px"
     inkscape:current-layer="layer1"
     inkscape:window-width="1280"
     inkscape:window-height="721"
     inkscape:window-x="0"
     inkscape:window-y="24" />
  <metadata
     id="metadata7">
    <rdf:RDF>
      <cc:Work
         rdf:about="">
        <dc:format>image/svg+xml</dc:format>
        <dc:type
           rdf:resource="http://purl.org/dc/dcmitype/StillImage" />
      </cc:Work>
    </rdf:RDF>
  </metadata>
<?php 

$max_value = 0;
foreach ($bar_data as $bar) { if ($bar->value > $max_value) { $max_value = $bar->value; } }
if (!isset($bar_width))  {
  $bar_width = floor(($width - $margin*2 - $bar_sep * count($bar_data)) / count($bar_data));
}

foreach ($bar_data as $bar) { // BARS
  $thisbar_height = floor(($bar->value*($height-$margin*2))/$max_value);
?>
    <rect
       style="fill:url(#nbi_gradient);fill-opacity:1.0;fill-rule:evenodd;stroke:#000000;stroke-width:0.5;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1;stroke-miterlimit:4;stroke-dasharray:none"
       id="janvier2006"
       width="<?= $bar_width ?>"
       height="<?= $thisbar_height ?>"
       x="<?= $margin + ($bar_width + $bar_sep) * $count++ ?>"
       y="<?= $height - $margin - $thisbar_height ?>" />
<?php } // END BARS?>
</svg>

<?php
// EOF
?>
