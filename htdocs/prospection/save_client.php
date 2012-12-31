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

require("../inc/main.php");
must_login();

global $User;
$user = $User->getInfos();
if (!$User->isAuthorized("admin,manager")) {
  $_SESSION['message'] = _('You are not allowed to modify this information');
  $_SESSION['error'] = 1;
  header("Location: /prospection/fiche_prospect.php?id=".$_POST['id_client']);
  die();
}

if (isset($_GET['action']) && $_GET['action'] == "delete") {

  $Client = new Client($_GET['id']);
  $document = new WebfinanceDocument;
  $document_dir = $document->GetCompanyDirectory($_GET['id']);
  $files = $document->ListByCompany($_GET['id']);

  if($Client->exists()){

    $q = mysql_query("SELECT id_facture FROM webfinance_invoices WHERE id_client=".$_GET['id'])
      or die(mysql_error());
    $clause= "WHERE (";

    while(list($id_inv) = mysql_fetch_array($q) )
      $clause .= " id_invoice=$id_inv OR";

    $clause = preg_replace('/OR$/',") AND type<>'real'",$clause);

    if(mysql_num_rows($q)>0)
      mysql_query("DELETE FROM webfinance_transactions $clause")
        or die(mysql_error());
    mysql_free_result($q);

    mysql_query("DELETE FROM webfinance_clients WHERE id_client=".$_GET['id'])
      or die(mysql_error());

    $User->delete($Client->id_user);

    # Remove each document
    foreach($files as $filename => $file)
      unlink("$document_dir/$filename")
        or die("Unable to unlink $document_dir/$filename");

    rmdir($document_dir)
      or die("Unable to remove directory $document_dir");

    $_SESSION['message'] = _('The company and related objects have been deleted');

  }else
    $_SESSION['message'] = _("This client doesn't exist");

  header("Location: /prospection/");
  die();

 }

if(isset($_GET['action']) && $_GET['action'] == "send_info"){

  extract($_GET);

  $Client = new Client($id);

  if($Client->id_user>0 AND $User->exists($Client->id_user)){
    $User->sendInfo($Client->id_user,$Client->password);
    $_SESSION['tmp_message'] .= "<br/>".$_SESSION['message'];

    $_SESSION['message']=$_SESSION['tmp_message'];
    $_SESSION['tmp_message']="";
    logmessage(_('Send info ')." ".$client.":".$id, $id);
  }else{
    $_SESSION['message'] = _('Login isn\'t correct!');
    $_SESSION['error'] = 1;
  }
  header("Location: /prospection/fiche_prospect.php?id=$id");
  die();

 }

$id_current_user=$_SESSION['id_user'];

extract($_POST);

#bugfix for register_globals=on
$_SESSION['id_user']=$id_current_user;

$emails = "";

$email = array_unique($email);

foreach($email as $mail){
  if(check_email($mail))
    $emails .= $mail.",";
}
$emails = preg_replace('/,$/', '', $emails);

if(!empty($login)){

  if(empty($password)){
    $password = $User->randomPass();
  }

  $prenom="";
  if(isset($_POST['prenom']))
    $prenom=$_POST['prenom'];

  $user_data=array(
		 "id_user"=>$_POST['id_user'],
		 "login"=>$login,
		 "first_name"=>$prenom,
		 "last_name"=>$_POST['nom'],
		 "password"=>$_POST['password'],
		 "email"=>$emails,
		 "role"=>array("client"),
		 "disabled"=>"off",
		 "admin"=>"off"
		 );

  if($User->exists($id_user)){
    $User->saveData($user_data);
  }else{
    $id_user = $User->createUser($user_data);
  }

 }

$document = new WebfinanceDocument;
$old_document_dir = $document->GetCompanyDirectory($id_client);

$q = sprintf("UPDATE webfinance_clients SET ".
	     "nom='%s' , addr1='%s' , addr2='%s' , addr3='%s' , cp='%s' , ".
	     "ville='%s' , rcs='%s' , capital='%s' ,pays='%s', tel='%s' , fax='%s' , web='%s', ".
	     " email='%s', ".
	     "vat_number='%s', siren='%s' , id_company_type='%d' , id_user=%d , password='%s', rib_titulaire='%s', rib_banque='%s', rib_code_banque='%s', rib_code_guichet='%s', " .
		 "rib_code_compte='%s', rib_code_cle='%s', id_mantis='%s', language='%s'
          WHERE id_client=%d",

			 mysql_real_escape_string($nom),
			 mysql_real_escape_string($addr1),
			 mysql_real_escape_string($addr2),
			 mysql_real_escape_string($addr3),
			 mysql_real_escape_string($cp),
			 mysql_real_escape_string($ville),
			 mysql_real_escape_string($rcs),
		   	 mysql_real_escape_string($capital),
			 mysql_real_escape_string($pays),
			 mysql_real_escape_string($tel),
			 mysql_real_escape_string($fax),
			 mysql_real_escape_string($web),
			 mysql_real_escape_string($emails),
			 mysql_real_escape_string($vat_number),
			 mysql_real_escape_string($siren),
			 mysql_real_escape_string($id_company_type),
			 mysql_real_escape_string($_POST['id_user']),
			 mysql_real_escape_string($password),
			 mysql_real_escape_string($rib_titulaire),
			 mysql_real_escape_string($rib_banque),
			 mysql_real_escape_string($rib_code_banque),
			 mysql_real_escape_string($rib_code_guichet),
			 mysql_real_escape_string($rib_code_compte),
			 mysql_real_escape_string($rib_code_cle),
			 mysql_real_escape_string($id_mantis),
			 mysql_real_escape_string($clt_language),
             mysql_real_escape_string($id_client)
	);

mysql_query($q) or die(mysql_error());

# Rename document directory if needed
$new_document_dir = $document->GetCompanyDirectory($id_client);

if($old_document_dir != $new_document_dir) {
  rename($old_document_dir, $new_document_dir)
    or die("Unable to rename $old_document_dir to $new_document_dir");

  # Rename Mantis project
  $mantis_project = array(
    'name' => $nom,
    # private = 50 according to mantis/ticket/core/constant_inc.php
    'view_state' => array( 'id' => 50),
  );
  $mantis = new WebfinanceMantis;
  $return = $mantis->updateProject($id_mantis, $mantis_project);

}

if(isset($_SESSION['message']))
  $_SESSION['message'] .= "<br/>"._('Update customer');
else
  $_SESSION['message'] = _('Update customer');

logmessage(_('Update customer')." client:$id_client ($nom)",$id_client);

header("Location: /prospection/fiche_prospect.php?id=$id_client&onglet=".$focused_onglet);
exit;
?>