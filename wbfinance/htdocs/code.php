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
