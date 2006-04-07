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

include("../inc/main.php");
include("../top.php");
include("nav.php");

if ((isset($_GET['id_client'])) && (preg_match("/^[0-9]+$/", $_GET['id_client']))) {
  $result = mysql_query("SELECT * FROM webfinance_clients WHERE id_client=".$_GET['id_client']) or nbi_mysqldie("Fetching client information");
  $client = mysql_fetch_object($result);
  mysql_free_result($result);

  $action = "save_client";
  $_SESSION['return_to'] = $GLOBALS['HTTP_SERVER_VARS']['HTTP_REFERER'];
} else {

  $action = "add_client";
  $_SESSION['return_to'] = "/prospection/index.php?file=listeprospects";
}

?>
<form action=/include/save_prospect.php method=post>

<input type=hidden name=action value="<?= $action ?>">
<input type=hidden name=id_client value="<?= $client->id_client ?>">

<table border=0 cellspacing=5 cellpadding=3>
  <tr><td>Nom de l'entreprise</td><td><input type=text size=30 name=name value="<?= $client->nom ?>"></td></tr>
  <tr><td valign=top>Addresse</td><td>
    <input type=text size=30 name=addr1 value="<?= $client->addr1 ?>"><br>
    <input type=text size=30 name=addr2 value="<?= $client->addr2 ?>"><br>
    <input type=text size=30 name=addr3 value="<?= $client->addr3 ?>"><br>
    <input type=text size=5 name=cp value="<?= $client->cp ?>">&nbsp;<input type=text size=10 name=ville value="<?= $client->ville ?>"><br><input type=text size=20 name=pays value="<?= $client->pays ?>"></td></tr>
  <tr><td>Téléphone</td><td><input type=text size=15 name=tel value="<?= $client->tel ?>"></td></tr>
  <tr><td>Fax</td><td><input type=text size=15 name=fax value="<?= $client->fax ?>"></td></tr>
  <tr><td>VAT Reg</td><td><input type=text size=15 name=vat_number value="<?= $client->vat_number ?>"></td></tr>
  <tr><td><select name="state">
  <?php
  foreach (array('prospect', 'client', 'archive') as $state) {
    printf('<option value="%s" %s>%s</option>', $state, ($state==$client->state)?"selected":"", $state);
  }
  ?></select></td></tr>
  <?php if (!isset($_GET['id_client'])) { ?>
  <tr><td colspan=2>
    Notes sur le premier contact : <br>
    <textarea rows=10 cols=50 name=comment></textarea>
  </td></tr>
  <?php } ?>
</table>

<input type=submit value="Enregistrer">
<input type="button" value="Annule et retour" onclick="window.location='fiche_prospect.php?id=<?= $_GET['id_client'] ?>';">

</form>

<?php

include("../bottom.php");

?>
