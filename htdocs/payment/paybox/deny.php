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

if(isset($_GET['ref']) ){
  //"PBX_RETOUR" => "amount:M;ref:R;auto:A;trans:T;pbxtype:P;card:C;soletrans:S;error:E",

  extract($_GET);

  if($error>=100 AND $error<200)
    printf("<span class='text'>%s</span>", _("Paiement refusé par le centre d'autorisation"));
  else if(isset($error)){

    $_SESSION['error']=1;

    switch($error){
    case 0:
      printf("<span class='text'>%s</span>", _("The transaction accepted")); //error in PBX_EFFECTUE or PAYBOX :)
      $_SESSION['message']=_("The transaction accepted");
      break;
    case 3:
      printf("<span class='text'>%s</span>", _("Erreur Paybox"));
      $_SESSION['message']=_("Erreur Paybox");
      break;
    case 4:
      printf("<span class='text'>%s</span>", _("Numéro de porteur ou cryptogramme visuel invalide"));
      $_SESSION['message']=_("Numéro de porteur ou cryptogramme visuel invalide");
      break;
    case 6:
      printf("<span class='text'>%s</span>", _("Accès refusé ou site/rang/identifiant incorrect"));
      $_SESSION['message']=_("Accès refusé ou site/rang/identifiant incorrect");
      break;
    case 8:
      printf("<span class='text'>%s</span>", _("date de fin de validité incorrect"));
      $_SESSION['message']=_("date de fin de validité incorrect");
      break;
    case 11:
      printf("<span class='text'>%s</span>", _("Montant incorrect"));
      $_SESSION['message']=_("Montant incorrect");
      break;
    case 15:
      printf("<span class='text'>%s</span>", _("Erreur Paybox"));
      $_SESSION['message']=_("Erreur Paybox");
      break;
    case 16:
      printf("<span class='text'>%s</span>", _("Abonnée déjà existant..."));
      $_SESSION['message']=_("Abonnée déjà existant...");
      break;
    case 21:
      printf("<span class='text'>%s</span>", _("Bin de carte non autorisée"));
      $_SESSION['message']=_("Bin de carte non autorisée");
      break;
    }
  }else{
      printf("<span class='text'>%s</span>", _("PAYBOX INPUT ERROR") ." ". $error);
      $_SESSION['message']=_("PAYBOX INPUT ERROR") ." ". $error;
  }
  mysql_query("UPDATE webfinance_paybox SET ".
	      "state='deny', ".
	      "payment_type='$pbxtype' , ".
	      "transaction_sole_id='$soletrans', ".
	      "error_code='$error'  ".
	      "WHERE reference='$ref'") or wf_mysqldie();
 }else{
?>
  <span class="text">
    <? echo _("Wrong arguments"); ?>
  </span>
<?
 }
?>
<br/>
<a href="../../client/"><?=_('Back')?></a>

<?php
$Revision = '$Revision: 532 $';
include("../../bottom.php");
?>
