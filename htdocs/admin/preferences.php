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
//
include("../inc/main.php");
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

<?= $_SESSION['message']; $_SESSION['message']=""; ?>

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
$Revision = '$Revision$';
include("../bottom.php");
?>
