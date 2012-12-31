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

require("../inc/main.php");
must_login();

$roles = "manager,admin,employee";

//array_push($extra_js, "/js/onglets.js");

// Onglet affiché par défaut
if (isset($_GET['tab']) AND !empty($_GET['tab'])) {
  $shown_tab = $_GET['tab'];
} else {
  $shown_tab = 'Mail_invoice';
}

include("../top.php");
include("nav.php");

$mail_tpl_lang = 'fr_FR';
if(isset($_GET['mail_tpl_lang'])) $mail_tpl_lang = $_GET['mail_tpl_lang'];
$language_form = "<br />&nbsp;&nbsp;<?=_('Language:')?><form><input type='hidden' name='tab' value='$shown_tab'><select onchange='this.form.submit ()' name='mail_tpl_lang'><option value='fr_FR'";
if($mail_tpl_lang == 'fr_FR') { $language_form .= "selected"; } 
$language_form .= ">French</option><option value='en_US'";
if($mail_tpl_lang == 'en_US') { $language_form .= "selected"; }
$language_form .= ">English</option></select></form><hr />";
?>

<script type="text/javascript">
var isModified = 0;

function formChanged() {
  f = document.getElementById('main_form');

  f.submit_button.style.background = '#009f00';
  f.submit_button.style.fontWeight = 'bold';
  f.submit_button.style.color = 'white';

  f.cancel_button.style.background = '#ff0000';
  f.cancel_button.style.fontWeight = 'bold';
  f.cancel_button.style.color = 'white';

  isModified = 1;
}

var onglet_shown='<?= $shown_tab ?>';

function focusOnglet(id) {
  if ((onglet_shown != '') && (onglet_shown != id)) {
    shown = document.getElementById('tab_'+onglet_shown);
    shown.style.display = 'none';

    oldtab = document.getElementById('handle_'+onglet_shown);
    oldtab.className = '';
  }
  toshow = document.getElementById('tab_'+id);
  if (toshow) {
    toshow.style.display='block';
    tab = document.getElementById('handle_'+id);
    tab.className = 'focus';

    onglet_shown = id;
  }
}

function mainFormChanged(f) {
  f.save_button.className = 'unsaved_button';
}
</script>

<?
if(isset($_SESSION['message'])) {
	echo $_SESSION['message'];
	unset($_SESSION['message']);
}
?>

<table width="600" border="0" cellspacing="0" cellpadding="0" class="fiche_prospect">
<tr class="onglets">
<?
$i=1;
foreach (glob("pref_*.php") as $pref) {
  preg_match("/pref_(.*).php$/", $pref, $matches);
  printf("<td id='handle_%s' onclick=\"focusOnglet('%s');\">%s</td>",$matches[1],$matches[1], $matches[1]);
  $i++;
}
?>
  <td style="background: none;" width="100%"></td>
</tr>
<tr style="vertical-align: top;">
<td colspan="<?=$i?>" class="onglet_holder">

<?
foreach (glob("pref_*.php") as $pref) {
  preg_match("/pref_(.*).php$/", $pref, $matches);
  printf('<div id="tab_%s" style="display: none;">',$matches[1]);
  include($pref);
  echo "</div>";
}
?>

<? // FIN ONGLETS ?>
</td>
</tr>
</table>

</form>

<script>
focusOnglet('<?= $shown_tab ?>');
</script>

<?php
$Revision = '$Revision: 531 $';
include("../bottom.php");
?>
