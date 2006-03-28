<?php 
// 
// This file is part of « Backoffice NBI »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU LGPL v2.0
//
?>
<?php

header("Content-type: image/png");
header("Pragma: no-cache");
#header("Content-type: text/plain");
include("inc/dbconnect.php");
include("inc/backoffice.php");

// $Id$

# header("Content-type: text/plain");

class barGraph {
  var $format = "png";
  var $im;
  var $width, $height;
  var $data = Array();
  var $labels = Array();
  var $barlabels = Array();
  var $max = 0;
  var $font_size = 12;
  var $step = 40; # Bar width in pixel 
  var $margin = 5;
  var $C_bar, $C_barframe, $C_text;

  function _htmlColorToGD($html) {
    $html = preg_replace("/^0x/", "", $html);
    preg_match("/(..)(..)(..)/", $html, $matches);
    $bincolor[0] = hexdec("0x{$matches[1]}");
    $bincolor[1] = hexdec("0x{$matches[2]}");
    $bincolor[2] = hexdec("0x{$matches[3]}");
    $color = ImageColorAllocate($this->im, $bincolor[0], $bincolor[1], $bincolor[2]);
    return $color;
  }
  function _initColors() {
    $this->C_bar = $this->_htmlColorToGd("FF0000");
    $this->C_barframe = $this->_htmlColorToGd("000000");
    $this->C_text = $this->_htmlColorToGd("000000");
  }

  /* Constructeur */
  function barGraph($width=300, $height=500) {
    $this->width = $width;
    $this->height = $height;

    $this->im = imagecreate ($this->width, $this->height) or die("Impossible d'initialiser la bibliothèque GD");
    $pen = imagecolorallocatealpha($this->im, 255, 255, 255, 127); # Mauve transparent miam ne marche qu'avec png
    imagefilledrectangle($this->im, 0,0, $this->width, $this->height, $pen );
    imagealphablending($this->im, TRUE);

    $this->_initColors();
  }

  function addValue($value, $label="", $barlabel="") {
    if ($value > $this->max) 
      $this->max = $value;

    array_push($this->data, $value);
    array_push($this->labels, $label);
    array_push($this->barlabels, $barlabel);
  }

  /* Chage les couleurs des barres */
  function setBarColor($html_color) { $this->C_bar = $this->_htmlColorToGd($html_color); }
  function setBarFrameColor($html_color) { $this->C_barframe = $this->_htmlColorToGd($html_color); }
  function setTextColor($html_color) { $this->C_text = $this->_htmlColorToGd($html_color); }

  function realise() {
    $blue = imagecolorallocate ($this->im, 0, 0, 255);
    $red  = imagecolorallocate ($this->im, 255, 0, 0);

    $this->step = $this->width / sizeof($this->data);

    $i = 0 ;
    foreach ($this->data as $data) {
      $bar_height = ($data*($this->height-15)/$this->max);
      $bar_height = ($bar_height<15)?15:$bar_height;

      imagefilledrectangle($this->im, $i*$this->step,$this->height-$bar_height, $i*$this->step+$this->step-$this->margin,$this->height-15, $this->C_bar);
      imagerectangle($this->im, $i*$this->step,$this->height-$bar_height, $i*$this->step+$this->step-$this->margin,$this->height-15, $this->C_barframe);
      imagestring($this->im, 1, $i*$this->step+($this->step-strlen($this->labels[$i])*imagefontwidth(1))/2, $this->height-10, $this->labels[$i], $this->C_text);

      $dummy = imagecreate(10, 10);
      $bounding_box = imagettftext($dummy, $this->font_size, 90, $i*$this->step+15, $this->height-$bar_height+20, $this->C_text, "../client_data/ttf/arialnb.ttf", $this->barlabels[$i]);
      imagedestroy($dummy);
      
      $text_height = $bounding_box[0] - $bounding_box[4];
      $text_width  = $bounding_box[1] - $bounding_box[5];
      if ($bar_height-15 > $text_width) {
        $text_y = $this->height-$bar_height+$text_width+5;
      } else {
        $text_y = $this->height-$bar_height-5;
      }
      $text_x = $i*$this->step + $text_height + ($this->step - $this->margin - $text_height)/2;
      $bounding_box = imagettftext($this->im, $this->font_size, 90, $text_x, $text_y, $this->C_text, "../client_data/ttf/arialnb.ttf", $this->barlabels[$i]);
      $i++;
    }

    // imagestring ($this->im, 1, 5, 5,  "A Simple Text String", $text_color);
    imagepng ($this->im);
    imagedestroy($this->im);
  }
}

if (is_numeric($_GET['width'])) 
  $width = $_GET['width'];
else 
  $width = 700;

if (is_numeric($_GET['height'])) 
  $height = $_GET['height'];
else 
  $height = 300;

$bar = new barGraph($width, $height);
$result = mysql_query("SELECT sum(fl.prix_ht*fl.qtt) as total, count(f.id_facture) as nb_factures, 
                               date_format(f.date_facture, '%Y%m') as groupme, date_format(f.date_facture, '%m/%y') as mois 
                       FROM facture as f, facture_ligne as fl 
                       WHERE fl.id_facture=f.id_facture
                       AND f.is_paye=0
                       AND f.type_doc = 'facture' 
                       GROUP BY groupme") or die(mysql_error());
$bar->setBarColor("ff6060"); # NBI blue
while ($billed = mysql_fetch_object($result)) {
  $billed->total = sprintf("%d", $billed->total);
  $bar->addValue($billed->total, $billed->mois, preg_replace("/\./", ",", sprintf("%.1f", $billed->total/1000))."K\xe2\x82\xac");
}

$bar->realise();

?>
