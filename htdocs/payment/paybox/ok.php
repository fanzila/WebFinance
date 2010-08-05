<?php
/*
 Copyright (C) 2004-2006 NBI SARL, ISVTEC SARL

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
?>
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
  exit;

 ?>
<span class="text">
<? echo _("Merci, votre transaction sera prise en compte dans quelques instants."); ?>
</span>
<?

}else {
	header("Location: deny.php");
	exit;
}

?>

<br/>
<a href="../../client/"><?=_('Back')?></a>

<?php

$Revision = '$Revision: 532 $';
include("../../bottom.php");

?>
