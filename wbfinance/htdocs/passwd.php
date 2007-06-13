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
?>
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
include("inc/main.php");
$User = new User();
if(isset($_POST['login'])){
  if($_POST['code']==$_SESSION['code']){
    if($User->existsLogin($_POST['login'])){
      $q = mysql_query("SELECT id_user, email FROM webfinance_users WHERE login='".$_POST['login']."'")
	or wf_mysqldie();
      list($id_user,$email)=mysql_fetch_array($q);
      if(is_numeric($id_user)){
	$client_res = mysql_query("SELECT password FROM webfinance_clients WHERE id_user=".$id_user) or wf_mysqldie();
	if(mysql_num_rows($client_res)==1){
	  list($pass) = mysql_fetch_array($client_res);
	}else{
	  $pass = $User->random_password();
	}
	if($User->setPass($id_user,$pass)){
	  $_SESSION['id_user'] = $id_user;
	  $User->sendInfo($id_user,$pass);
	  $_SESSION['id_user'] = -1;
	  echo _("User information sent to")." ".$email;
	}else{
	  echo _("Set new password: fails");
	}
      }else{
	echo _("The id_user isn't correct");
      }

    }else{
      echo _("This login doesn't exist!");
    }
  }else{
    echo _("Invalid code");
  }
}

$chars = "abBCDEFcdefghijkLmnPQRSTUVWXYpqrstxyz123456789";
$code="";
srand((double)microtime()*1000000); //  Génération aléatoire du code
for($i=0; $i<5;$i++){
  $code.= $chars[rand()%strlen($chars)];
 }
$_SESSION['code'] = $code;


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <link rel="stylesheet" type="text/css" href="/css/themes/main/main.css" />
  <title>Webfinance <?= _("Password") ?></title>
</head>
<body>
<div style="margin-top: 15%; width: 100%; text-align: center;">
    <form action="passwd.php" method="post">
    <table border="0" cellspacing="0" cellpadding="10" style="border: solid 1px black; margin: auto auto auto auto;">
      <tr><td><?= _("Login") ?></td><td><input type="text" size="22" style="border: solid 1px #777;" name="login" id="login" value="" /></td></tr>
      <tr><td></td><td><img src="code.php">&nbsp;<a href="passwd.php"><img src="/imgs/icons/reload.png" alt="<?=_('The image is unreadable')?>" title="<?=_('The image is unreadable')?>" /></a></td></tr>
      <tr><td>Code</td><td><input type="text" size="22" style="border: solid 1px #777;" name="code" id="login" value="" /></td></tr>
      <tr><td colspan="2" style="text-align:center"><input value="<?= _('Send') ?>" type="submit" /></td></tr>
    </table>
    </form>
</div>
<center><a href="login.php"><?= _("Login")?></a></center>
</body>
</html>
