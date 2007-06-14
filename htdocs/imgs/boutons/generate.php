<?php
/*
 Copyright (C) 2004-2006 NBI SARL, ISVTEC SARL

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
*/
?>
<?php

include("../../inc/main.php");

if (!isset($_GET['text'], $_GET['style'], $_GET['theme'])) {
  die('missing argument');
}

$font_size = 8;

$filename=$_GET['text'].'_'.$_GET['style'].'_'.$_GET['theme'].'.png';

if(file_exists($filename)) {
  header("Location: ./$filename");
  exit;
}

$base_image = '../../css/themes/'.$_GET['theme'].'/'.$_GET['style'].'.png';
if (!file_exists($base_image)) {
  die("No base image '$base_image' for this theme !");
}

$font = '../../css/themes/'.$_GET['theme'].'/buttonfont.ttf';
if (!file_exists($font)) {
  die("No font for this theme ($font)");
}

// Load the background PNG
$img = imagecreatefrompng($base_image);
// Allocate black
$black = imagecolorallocate($img, 0, 0, 0);
// Blends the font into the background
imagealphablending($img, TRUE);

list($width, $height, $ignored1, $ignored2) = getimagesize($base_image);
$bounding_box = imageftbbox($font_size, 0, $font, $_GET['text']);

$text_width = abs($bounding_box[0] - $bounding_box[4]);
$text_height  = abs($bounding_box[1] - $bounding_box[5]);

$text_x = ($width - $text_width) / 2;
$text_y = $height - (3 + ($height - $text_height) / 2);

imagettftext($img, $font_size, 0, $text_x, $text_y, $black, $font, $_GET['text']);

imagepng($img, $filename);

header("Location: ./$filename");

?>
