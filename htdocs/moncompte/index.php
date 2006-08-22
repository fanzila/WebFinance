<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<?php
// $Id$

require("../inc/main.php");
must_login();

$User = new User();

if ($GLOBALS['_SERVER']['REQUEST_METHOD'] == "POST") {
  extract($_POST);
  if ($action == "changepass") {
    if ($new_pass1 != $new_pass2) {
      $_SESSION['message'] = _("The passwords don't match");
      $_SESSION['error'] = 1;
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
    header("Location: index.php");
    die();
  }
}

$roles="any";
$title = _('My account');
include("../top.php");

$user = $User->getInfos($_SESSION['id_user']);

?>

<script type="text/javascript">
function checkForm(f) {
  f.submit();
}
</script>

<?= $_SESSION['message']; $_SESSION['message'] = ""; ?>

<h2><?=_('My account') ?></h2>
<table border="0" cellspacing="7" cellpadding="0">
<tr>
  <td><?=_('Name') ?>:</td>
  <td><?=$user->first_name?> <?=$user->last_name?></td>
</tr>
<tr>
  <td><?=_('Email') ?>:</td>
  <td><?=$user->email?></td>
</tr>
<tr>
  <td><?=_('Login') ?>:</td>
  <td><?= $user->login ?></td>
</tr>
<tr>
  <td><?=_('Role')?>s:</td>
  <td><?=$user->role?></td>
</tr>
</table>


<h2><?= _('My password') ?></h2>

<form id="main_form" name="change_pass" action="index.php" method="post">
<input type="hidden" name="action" value="changepass" />
<table border="0" cellspacing="7" cellpadding="0">
<tr>
  <td><?= _('Old password') ?></td>
  <td><input name="old_password" type="password" /></td>
</tr>
<tr>
  <td><?= _('New password') ?></td>
  <td><input name="new_pass1" type="password" /></td>
</tr>
<tr>
  <td><?= _('Repeat') ?></td>
  <td><input name="new_pass2" type="password" /></td>
</tr>
<tr>
  <td colspan="2" style="text-align: center;">
    <input type="submit" value="<?= _("Save") ?>" />
  </td>
</tr>
</table>
</form>

<h2><?=_('My preferences')?></h2>

<form id="main_form" name="user_prefs" action="index.php" method="post">
<input type="hidden" name="action" value="userprefs" />
<table border="0" cellspacing="0" cellpadding="5">
<tr>
  <td><?= _("Language") ?></td>
  <td>
    <select name="pref_lang">
    <?php
      $choices = array("Français" => "fr_FR",
                       "English" => "en_US");
      foreach ($choices as $n=>$v) {
        printf('<option value="%s"%s>%s</option>', $v, ($v==$User->prefs->lang)?" selected":"", $n );
      }
    ?>
    </select>
  </td>
  <td><?= _('Skin') ?></td>
  <td>
    <select name="pref_theme">
    <?php
    $themes = glob("../css/themes/*");
    foreach ($themes as $theme) {
      if (basename($theme)!="CVS" && is_dir($theme)) {
        $theme = basename($theme);
        $theme = preg_replace("/\.css$/", "", $theme);
        printf('<option value="%s"%s>%s</option>',
               $theme, ($theme==$User->prefs->theme)?" selected":"", $theme );
      }
    }
    ?>
    </select>
  </td>
</tr>
<tr>
  <td><?= _('Sort companies by') ?></td>
  <td><select name="pref_tri_entreprise">
  <?php
  foreach (array('ca_total_ht' => _('Total Income'),
                 'ca_total_ht_year' => _('Year Income'),
                 'du' => _('Up to date / has unpaid'),
                 'total_du_ht' => _('Owed'),
                 'nom' => _('Company name')) as $v=>$n) {
    printf('<option value="%s"%s>%s</option>', $v, ($v==$User->prefs->tri_entreprise)?" selected":"", $n);
  }
  ?>
  </select></td>

  <td colspan="2"><input type="checkbox" name="pref_graphgrid" <?= ($User->prefs->graphgrid)?"checked":"" ?> /> <?= _('Show grids in graphs') ?></td>
</tr>
<tr>
  <td><?= _('Default focused tab in company view') ?></td>
  <td><select name="pref_default_onglet_fiche_contact">
  <?php
  foreach (array(
                  'contacts' => _('Contact & Addresses'),
                  'facturation' => _('Billing'),
                  'log' => _('Client log'),
                  'other' => _('Miscellaneous'),
                  'graph' => _('Graphics')
                 ) as $v=>$n) {
    printf('<option value="%s"%s>%s</option>', $v, ($v==$User->prefs->default_onglet_fiche_contact)?" selected":"", $n);
  }
  ?>
  </select></td>
</tr>
<tr>
  <td><?=_('Transactions')?></td>
  <td>
  <?php
      if(empty($User->prefs->transactions_per_page))
	$User->prefs->transactions_per_page=50;
   ?>
      <select name="pref_transactions_per_page">
  <?php
      for($i=10; $i <=100; $i=$i+10){
	printf('<option value="%s" %s>%s</option>', $i, ($i==$User->prefs->transactions_per_page)?"selected":"", $i, $tr_disp);
      }
  ?>
  </select>&nbsp;<?=_('per')?> page
</td>
</tr>
<tr>
  <td colspan="2" style="text-align: center;">
    <input type="submit" value="<?= _('Save') ?>" />
  </td>
</tr>
</table>
</form>

<?php
$Revision = '$Revision$';
include("../bottom.php");

?>
