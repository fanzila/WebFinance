<?php
include("../../inc/main.php");
$title = _("Paybox");
$roles="manager,accounting,employee,client";
include("../../top.php");

// PBX_RETOUR  "montant:M;ref:R;auto:A;trans:T;pbxtype:P;card:C;soletrans:S;error:E",
extract($_GET);

if(isset($auto) AND isset($ref)){
  mysql_query("UPDATE webfinance_paybox SET state='pending', autorisation='$auto' WHERE reference='$ref'") or wf_mysqldie();
  $_SESSION['message'] = _("Merci, votre transaction sera prise en compte dans quelques instants.");
  header("Location: ../../client/");

 ?>
<span class="text">
<? echo _("Merci, votre transaction sera prise en compte dans quelques instants."); ?>
</span>
<?

 }else
  header("Location: deny.php");

?>

<br/>
<a href="../../client/"><?=_('Back')?></a>

<?php

$Revision = '$Revision$';
include("../../bottom.php");

?>
