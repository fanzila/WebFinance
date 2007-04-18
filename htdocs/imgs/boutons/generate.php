<?php

include("../../inc/main.php");

if (!isset($_GET['data'])) {
  die();
}

$font_size = 8;

$theme = (isset($User->prefs->theme) ? isset($User->prefs->theme) : 'main');
$data = base64_decode($_GET['data']);
list($text, $style) = explode(":", $data);

if(file_exists($_GET['data'])) {
  header('Location: ./'. $_GET['data'].'.png');
  exit;
}

if (!file_exists("../../css/themes/$theme/$style.png")) {
  die("No base image '$style.png' for this theme !");
}
$base_image = "../../css/themes/$theme/$style.png";

if (!file_exists("../../css/themes/$theme/buttonfont.ttf")) {
  die("No font for this theme");
}
$font = "../../css/themes/$theme/buttonfont.ttf";

// Load the background PNG
$img = imagecreatefrompng($base_image);
// Allocate black
$black = imagecolorallocate($img, 255, 0, 0, 127);
// Blends the font into the background
imagealphablending($img, TRUE);

list($width, $height, $ignored1, $ignored2) = getimagesize($base_image);
$bounding_box = imageftbbox($font_size, 0, $font, $text);

$text_width = abs($bounding_box[0] - $bounding_box[4]);
$text_height  = abs($bounding_box[1] - $bounding_box[5]);

$text_x = ($width - $text_width) / 2;
$text_y = $height - (3 + ($height - $text_height) / 2);

imagettftext($img, $font_size, 0, $text_x, $text_y, $black, $font, $text);

header("Content-Type: image/png");
imagepng($img);
imagepng($img, $_GET['data'].".png");

?>
