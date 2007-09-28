<?php
/*
   This file is part of Webfinance.

    Webfinance is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Webfinance is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Webfinance; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

    Copyright (c) 2004-2006 NBI SARL
    Author : Nicolas Bouthors <nbouthors@nbi.fr>

    $Id: barGraph.php 531 2007-06-13 12:32:31Z thierry $
*/

/**
  * barGraph produces PNG bargraphs. By default, it will render each bar in a
  * gradient of the choosen color. Several getter/setter methods are available
  * to change the general look of the graph (display grid or not in the
  * background ...)
  * 
  * Typical usage can look like this
  * <pre>

  $bars = new BarGraph(600, 300);
  for ($i=0 ; $i<10 ; $i++) 
    $bars->addValue( $i, $i, "Sample $i");
  }
  $bars->realise();
  
  </pre>
  *
  * This class uses directly PHP's GD functions instead of some wrapper around
  * them like phplot
  *
  * @author Nicolas Bouthors <nbouthors@nbi.fr>
  */
class barGraph {
  var $format = "png";
  var $im;
  var $width, $height;
  var $data = Array();
  var $labels = Array();
  var $barlabels = Array();
  var $max = 0;
  var $font_size = 10;
  var $step = 40; # Bar width in pixel
  var $margin = 5;
  var $C_bar, $C_barframe, $C_text, $C_average, $C_grid;
  var $nb_shades = 50;
  var $ttf_font = "/usr/share/fonts/truetype/freefont/FreeSansBold.ttf";
  var $draw_grid = 1;

  /**
    * Internal method : converts a HTML triplet color like #ff0000 for red into a GD color
    */
  function _htmlColorToGD($html) {
    $html = preg_replace("/^0x/", "", $html);
    preg_match("/(..)(..)(..)/", $html, $matches);
    $bincolor[0] = hexdec("0x{$matches[1]}");
    $bincolor[1] = hexdec("0x{$matches[2]}");
    $bincolor[2] = hexdec("0x{$matches[3]}");
    $color = ImageColorAllocate($this->im, $bincolor[0], $bincolor[1], $bincolor[2]);
    return $color;
  }

  /**
   * Setup the initial colors of the graph. Default is black/white for axis ans
   * blue for bars
   */
  function _initColors() {
    $this->C_bar = $this->_htmlColorToGd("FF0000");
    $this->C_barframe = $this->_htmlColorToGd("000000");
    $this->C_barshadow = $this->_htmlColorToGd("7f7f7f");
    $this->C_text = $this->_htmlColorToGd("000000");
    $this->C_grid = $this->_htmlColorToGd("dddddd");
    $this->C_average = ImageColorAllocateAlpha($this->im, 74, 89, 65, 20 );
  }

  /**
    * Constructor.
    *
    * Params :
    *  $width : int Width in pixel of the generated PNG. Default is 300 pixels.
    *  $height : int Height in pixel. Default is 500
    *  $draw_grid : boolean, wether to draw the grid in the background or not
    */
  function barGraph($width=300, $height=500, $draw_grid=1) {
    $this->width = $width;
    $this->height = $height;
    $this->draw_grid = $draw_grid;

    $this->im = imagecreatetruecolor($this->width, $this->height) or die("Impossible d'initialiser la bibliothèque GD");
    $pen = imagecolorallocatealpha($this->im, 255, 255, 255, 0); # Mauve transparent miam ne marche qu'avec png
    imagefilledrectangle($this->im, 0,0, $this->width, $this->height, $pen );
    imagealphablending($this->im, TRUE);

    $this->_initColors();
  }

  /**
   * Adds a value to the pool. Use it in a loop when building your barGraph
   *
   * Params :
   *  $value int (or float) value of the new bar 
   *  $label is the X axis label. It will be rendered in a small font under the axis.
   *  $barlabel is the label to draw inside the bar itself. Il will be drawn in
   *            a larger font verticaly. If label is small it's drawn inside
   *            the bar, otherwise it's drawn on top of the bar
   */
  function addValue($value, $label="", $barlabel="") {
    if ($value > $this->max)
      $this->max = $value;

    array_push($this->data, $value);
    array_push($this->labels, $label);
    array_push($this->barlabels, $barlabel);
  }

  /** 
    * Chages the main color for the bars. Gradient will be calculated from this base color 
    */
  function setBarColor($red, $green, $blue) {
    $this->RVB_bar = array($red, $green, $blue);
    $this->shadecolors = array();
    for ($a=0 ; $a<$this->nb_shades ; $a++) {
      $this->shadecolors[$a] = ImageColorAllocate($this->im, $this->RVB_bar[0]-($this->nb_shades-$a*4), $this->RVB_bar[1]-($this->nb_shades-$a*4), $this->RVB_bar[2]-($this->nb_shades-$a*4));
    }
    $this->C_bar = ImageColorAllocate($this->im, $red, $green, $blue);
  }

  function setBarFrameColor($html_color) { $this->C_barframe = $this->_htmlColorToGd($html_color); }
  function setTextColor($html_color) { $this->C_text = $this->_htmlColorToGd($html_color); }

  /**
   * This function will build the graph and output the resulting PNG file to the browser
   */
  function realise() {
    header("Content-type: image/png");
    header("Pragma: no-cache");
    $blue = imagecolorallocate ($this->im, 0, 0, 255);
    $red  = imagecolorallocate ($this->im, 255, 0, 0);

    if (!count($this->data)) {
      $this->step = 20;
    } else {
      $this->step = $this->width / sizeof($this->data);
    }

    // Calculate average value
    $i = 0 ;
    $grand_total = 0;
    foreach ($this->data as $data) {
      $grand_total += $data;
      $i++;
    }
    if ($i>0) { $average = $grand_total/$i; } else { $average = 0; }
    if ($this->max > 0) {
      $y_average = 15 + ($this->height-15) - ($average*($this->height-15)/$this->max);
    } else {
      $y_average = 15 + ($this->height-15);
    }

    // Draw unit grid behind (ie before) bars
    if ($this->draw_grid) {
      if ($this->max < 1000) { 
        $step = 100;
      } else {
        $step = 1000;
      }
      $count = 1;
      while ($count*$step < $this->max) {
        $h = $this->height - ($step*$count)*(($this->height-15)/$this->max);
        imagerectangle($this->im, 0, $h, $this->width, $h, $this->C_grid );
        if ($count % 2 == 0) {
          imagettftext($this->im, 8, 0, 1, $h-2, $this->C_grid, $this->ttf_font, sprintf("%d%s\xe2\x82\xacHT",  $step*$count, ($step==1000)?"K":"") );
        }
        $count++;
      }
    }

    $i=0;
    foreach ($this->data as $data) {
      if ($this->max > 0) {
        $bar_height = ($data*($this->height-15)/$this->max);
      } else {
        $bar_height = 0;
      }
      $bar_height = ($bar_height<15)?15:$bar_height;

      $left = $i*$this->step;
      $top = $this->height-$bar_height;
      $right = $i*$this->step+$this->step-$this->margin;
      $bottom = $this->height-15;

      imagefilledrectangle($this->im, $left+2, $top-2, $right+2,$bottom, $this->C_barshadow);
      imagefilledrectangle($this->im, $left, $top, $right,$bottom, $this->C_bar);
      for ($a=0 ; $a<$this->nb_shades ; $a+=4) {
        imagefilledrectangle($this->im, $left, $top, $right,$bottom-$a, $this->shadecolors[$a/4]);
      }
      imagerectangle($this->im, $left, $top, $right,$bottom, $this->C_barframe);

      imagestring($this->im, 1, $i*$this->step+($this->step-strlen($this->labels[$i])*imagefontwidth(1))/2, $this->height-10, $this->labels[$i], $this->C_text);

      $dummy = imagecreate(10, 10);
      $bounding_box = imagettftext($dummy, $this->font_size, 90, $i*$this->step+15, $this->height-$bar_height+20, $this->C_text, $this->ttf_font, $this->barlabels[$i]);
      imagedestroy($dummy);

      $text_height = $bounding_box[0] - $bounding_box[4];
      $text_width  = $bounding_box[1] - $bounding_box[5];
      if ($bar_height-20 > $text_width) {
        $text_y = $this->height-$bar_height+$text_width+5;
      } else {
        $text_y = $this->height-$bar_height-5;
      }
      $text_x = $i*$this->step + $text_height + ($this->step - $this->margin*2 - $text_height)/2;
      $bounding_box = imagettftext($this->im, $this->font_size, 90, $text_x, $text_y, $this->C_text, $this->ttf_font, $this->barlabels[$i]);
      $i++;
    }

    imagerectangle($this->im, 0, $y_average, $this->width, $y_average, $this->C_average );
    imagettftext($this->im, 9, 0, 1, $y_average-2, $this->C_average, $this->ttf_font, sprintf("Moyenne : %.1fK\xe2\x82\xacHT", $average/1000) );
    // imagestring ($this->im, 1, 5, 5,  "A Simple Text String", $text_color);
    imagepng ($this->im);
    imagedestroy($this->im);
  }

  /**
   * Choose the TrueType font file to use to display values and legend information
   */ 
  function setFont($fontfile) {
    if (!file_exists($fontfile)) {
      return FALSE;
    }
    $this->ttf_font = $fontfile;
  }
}

?>
