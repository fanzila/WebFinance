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

require("main.php");
require("../top_popup.php");

function makeBar($color) {
  $html = "";
  for ($i =0 ; $i<64 ; $i++) {
    switch ($color) {
      case 'red' : $hcolor = sprintf("%02x0000", ($i+1)*4); break;
      case 'green' : $hcolor = sprintf("00%02x00", ($i+1)*4); break;
      case 'blue' : $hcolor = sprintf("0000%02x", ($i+1)*4); break;
    }
    $html .= sprintf('<div onclick="set_%s(\'%d\');" style="cursor: pointer; cursor: hand; float: left; width: 10px height: 16px; background: #%s">&nbsp;</div>', $color, $i, $hcolor );
  }
  return $html;
}

preg_match("/^(..)(..)(..)$/", $_GET['start'], $matches);
$html_color = $_GET['start'];

$matches[1] = sscanf($matches[1], "%x"); $matches[1] = $matches[1][0];
$matches[2] = sscanf($matches[2], "%x"); $matches[2] = $matches[2][0];
$matches[3] = sscanf($matches[3], "%x"); $matches[3] = $matches[3][0];
$red = $matches[1];
$green = $matches[2];
$blue = $matches[3];

?>

<script type="text/javascript">
function fromHex(i) {
	var Dec;

	switch (i.substr(0,1)) {
		case 'A' : 
		case 'a' : Dec = 10*16; break;
		case 'B' : 
		case 'b' : Dec = 11*16; break;
		case 'C' : 
		case 'c' : Dec = 12*16; break;
		case 'D' : 
		case 'd' : Dec = 13*16; break;
		case 'E' : 
		case 'e' : Dec = 14*16; break;
		case 'F' : 
		case 'f' : Dec = 15*16; break;
    default: Dec = i.substr(0,1)*16;
	}
	switch (i.substr(1, 2)) {
		case 'A' : 
		case 'a' : Dec += 10; break;
		case 'B' : 
		case 'b' : Dec += 11; break;
		case 'C' : 
		case 'c' : Dec += 12; break;
		case 'D' : 
		case 'd' : Dec += 13; break;
		case 'E' : 
		case 'e' : Dec += 14; break;
		case 'F' : 
		case 'f' : Dec += 15; break;
    default: Dec += parseInt(i.substr(1,2));
	}

	return Dec;
}
function toHex(i) {
  var Left = Math.floor(i/16);
  var Right = i%16;

  switch (Left) {
    case 10: Left = 'A'; break;
    case 11: Left = 'B'; break;
    case 12: Left = 'C'; break;
    case 13: Left = 'D'; break;
    case 14: Left = 'E'; break;
    case 15: Left = 'F'; break;
  }
  switch (Right) {
    case 10: Right = 'A'; break;
    case 11: Right = 'B'; break;
    case 12: Right = 'C'; break;
    case 13: Right = 'D'; break;
    case 14: Right = 'E'; break;
    case 15: Right = 'F'; break;
  }

  var Hex = ''+Left+Right;

  return Hex;
}
function updateSample() {
  r = document.getElementById('red_field'); 
  if (r.value > 255) { r.value = 255; } 
  if (r.value < 0) { r.value = 0; } 
  r = r.value;
  g = document.getElementById('green_field'); 
  if (g.value > 255) { g.value = 255; } 
  if (g.value < 0) { g.value = 0; } 
  g = g.value;
  b = document.getElementById('blue_field'); 
  if (b.value > 255) { b.value = 255; } 
  if (b.value < 0) { b.value = 0; } 
  b = b.value;

  var hcolor = '#'+toHex(r)+toHex(g)+toHex(b);
  s = document.getElementById('sample');
  s.style.background = hcolor;
  s.innerHTML = 'Couleur HTML : '+hcolor;

  field = window.parent.document.getElementById('<?= $_GET['input'] ?>');
  smpl = window.parent.document.getElementById('<?= $_GET['sample'] ?>');
  smpl.style.background = hcolor;
  field.value = hcolor;
}
function set_red(v) {
  f = document.getElementById('red_field');
  f.value = v*4;
  updateSample();
}
function set_green(v) {
  f = document.getElementById('green_field');
  f.value = v*4;
  updateSample();
}
function set_blue(v) {
  f = document.getElementById('blue_field');
  f.value = v*4;
  updateSample();
}
function setup() {
  smpl = window.parent.document.getElementById('<?= $_GET['sample'] ?>');
  c = smpl.style.backgroundColor;

  r = document.getElementById('red_field'); 
  g = document.getElementById('green_field'); 
  b = document.getElementById('blue_field'); 

  if (document.all) {
    matches = c.match(/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/);
		r.value = fromHex(matches[1]);
		g.value = fromHex(matches[2]);
		b.value = fromHex(matches[3]);
  } else {
    matches = c.match(/rgb\(([0-9]+), ([0-9]+), ([0-9]+)\)/);
    r.value = matches[1];
    g.value = matches[2];
    b.value = matches[3];
  }

  updateSample();
}
</Script>

<div id="main_container">

<form id="main_form" method="get">
<table border="0" cellspacing="5" cellpadding="0">
<tr height="30">
  <td><input onchange="updateSample();" style="text-align: center; width: 30px;" type="text" id="red_field" name="red" value="<?= $red ?>" /></td>
  <td width="100%"><?= makeBar('red'); ?></td>
</tr>
<tr height="30">
  <td><input onchange="updateSample();" style="text-align: center; width: 30px;" type="text" id="green_field" name="green" value="<?= $green ?>" /></td>
  <td width="100%"><?= makeBar('green'); ?></td>
</tr>
<tr height="30">
  <td><input onchange="updateSample();" style="text-align: center; width: 30px;" type="text" id="blue_field" name="blue" value="<?= $blue ?>" /></td>
  <td width="100%"><?= makeBar('blue'); ?></td>
</tr>
<tr>
  <td style="text-align: center; vertical-align: middle; height: 100px; background: #<?= $html_color ?>" id="sample" colspan="2">
  Couleur HTML : #<?= $html_color ?>
  </td>
</tr>
<tr>
  <td align="center" colspan="2"><input style="width: 200px;" type="button" onclick="popup = window.parent.document.getElementById('inpage_popup').style.display='none';" value="Valider" /></td>
</tr>
</table>
</form>

</div>
<script type="text/javascript">
setup();
</script>
<?php

?>
