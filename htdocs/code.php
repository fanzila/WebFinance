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
*/
?>
<?php
include("inc/main.php");

$text=$_SESSION['code']; //on récupére le code à générer

$im = imagecreatefromjpeg("./imgs/secret.jpg");
$id = imagecreatefromjpeg("./imgs/secret.jpg");
$grey = imagecolorallocate($im, 128, 128, 128);
$black = imagecolorallocate($im, 0, 0, 0);
$font = "./css/themes/main/buttonfont.ttf";
for($i=0;$i<5;$i++) {
  $angle=mt_rand(10,30);
  if(mt_rand(0,1)==1)
    $angle=-$angle;
  imagettftext($im, 14, $angle, 11+(20*$i), 21, $grey, $font, substr($text,$i,1));
      imagettftext($im, 14, $angle, 10+(20*$i), 20, $black, $font, substr($text,$i,1));
 }

imagecopymerge ( $im, $id, 0, 0, 0, 0, 120, 30, 50 );

header("Content-type: image/png");

imagepng($im);
imagedestroy($im);
imagedestroy($id);

?>
