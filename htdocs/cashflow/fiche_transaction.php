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

require("../inc/main.php");
require("../top_popup.php");

$result = mysql_query("SELECT * FROM webfinance_transactions WHERE id=".$_GET['id']) or die(mysql_error());
$transaction = mysql_fetch_object($result);
mysql_free_result($result);

?>

<form id="main_form" method="post" action="save_transaction.php">
<input type="hidden" name="id_transaction" value="<?= $transaction->id ?>" />

Montant : <input type="text" name="amount" value="<?= $transaction->amount ?>" />
Catégorie : <select name="id_category"><?php
$result = mysql_query("SELECT id,name FROM webfinance_categories ORDER BY name");
while (list($id,$name) = mysql_fetch_array($result)) {
  printf('<option value="%d"%s>%s</option>', $id, ($id==$transaction->id_category)?" selected":"", $name );
}
mysql_free_result($result);
?></select>

<input type="submit" value="Enregistrer" />



<?php
$Revision = '$Revision$';
require("../bottom.php");
?>
