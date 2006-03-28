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
// $Id$

include("../inc/backoffice.php");

$User = new User();

if ($GLOBALS['_SERVER']['REQUEST_METHOD'] == "POST") {
  extract($_POST);
  if ($action == "changepass") {
    if ($new_pass1 != $new_pass2) {
      $_SESSION['message'] = "Les deux nouveaux mot de passe ne concordent pas";
      header("Location: index.php");
      die();
    }
    $User->changePass($_SESSION['id_user'], $old_password, $new_pass1);
  } else if ($action == "userprefs") {
    $Prefs = new stdClass();
    foreach ($_POST as $n=>$v) {
      if (preg_match("/^pref_(.*)$/", $n, $matches)) {
        $prefname = $matches[1];
        $Prefs->$prefname = $v;
      }
    }
    $data = base64_encode(serialize($Prefs));
    $User->setPrefs($Prefs);
  }
}


$user = $User->getinfos($_SESSION['id_user']);

include("../top.php");

?>

<script type="text/javascript">
function checkForm(f) {
  f.submit();
}
</script>
<h2>Mon mot de passe</h2>

<div style="background: #ffcece;"><?= $_SESSION['message']; $_SESSION['message'] = ""; ?></div>

<form name="change_pass" action="index.php" method="post">
<input type="hidden" name="action" value="changepass" />
<table border="0" cellspacing="7" cellpadding="0">
<tr>
  <td>Ancien mot de passe</td>
  <td><input name="old_password" type="password" class="border" /></td>
</tr>
<tr>
  <td>Nouveau mot de passe</td>
  <td><input name="new_pass1" type="password" class="border" /></td>
</tr>
<tr>
  <td>Répéter</td>
  <td><input name="new_pass2" type="password" class="border" /></td>
</tr>
<tr>
  <td colspan="2" style="text-align: center;">
    <img onclick="checkForm(document.forms['change_pass']);" src="<?= '/imgs/boutons/'.urlencode(base64_encode("Enregistrer:off")).'.png' ?>" onmouseover="this.src='<?= '/imgs/boutons/'.urlencode(base64_encode("Enregistrer:on")).'.png' ?>';" onmouseout="this.src='<?= '/imgs/boutons/'.urlencode(base64_encode("Enregistrer:off")).'.png' ?>';" />
  </td>
</tr>
</table>
</form>

<h2>Options personnelles</h2>

<form name="user_prefs" action="index.php" method="post">
<input type="hidden" name="action" value="userprefs" />
<table border="0" cellspacing="0" cellpadding="5">
<tr>
  <td>Thème graphique</td>
  <td>
    <select name="pref_theme">
    <?php
    $themes = glob("../css/main*.css");
    foreach ($themes as $theme) {
      $theme = basename($theme);
      $theme = preg_replace("/\.css$/", "", $theme);
      printf('<option value="%s"%s>%s</option>',
             $theme, ($theme==$User->prefs->theme)?" selected":"", $theme );
    }
    ?>
    </select>
  </td>
</tr>
<tr>
  <td>Tri des entreprise</td>
  <td><select name="pref_tri_entreprise">
  <?php
  foreach (array('ca_total_ht' => 'CA total',
                 'ca_total_ht_year' => 'CA année',
                 'du' => 'A jour / Impayés',
                 'total_du_ht' => 'Encours',
                 'nom' => 'Raison sociale') as $v=>$n) {
    printf('<option value="%s"%s>%s</option>', $v, ($v==$User->prefs->tri_entreprise)?" selected":"", $n);
  }
  ?>
  </select></td>
</tr>
<tr>
  <td>Onglet par défaut dans la fiche entreprise</td>
  <td><select name="pref_default_onglet_fiche_contact">
  <?php
  foreach (array(
                  'contacts' => 'Contact & Adresse',
                  'facturation' => 'Facturation', 
                  'log' => 'Journal d\'évènements',
                  'other' => 'Divers'
                 ) as $v=>$n) {
    printf('<option value="%s"%s>%s</option>', $v, ($v==$User->prefs->default_onglet_fiche_contact)?" selected":"", $n);
  }
  ?>
  </select></td>
</tr>
<tr>
  <td colspan="2" style="text-align: center;">
    <input type="image" src="<?= '/imgs/boutons/'.urlencode(base64_encode("Enregistrer:off")).'.png' ?>" onmouseover="this.src='<?= '/imgs/boutons/'.urlencode(base64_encode("Enregistrer:on")).'.png' ?>';" onmouseout="this.src='<?= '/imgs/boutons/'.urlencode(base64_encode("Enregistrer:off")).'.png' ?>';" />
  </td>
</tr>
</table>
</form>

<?php
include("../bottom.php");

?>
