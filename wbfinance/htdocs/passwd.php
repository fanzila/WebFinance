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
  if($User->existsLogin($_POST['login'])){
    $q = mysql_query("SELECT id_user, email FROM webfinance_users WHERE login='".$_POST['login']."'")
      or wf_mysqldie();
    list($id_user,$email)=mysql_fetch_array($q);
    if(is_numeric($id_user)){
      $pass = $User->random_password();
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
 }

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
      <tr><td><?= _("Login") ?></td><td><input type="text" size="20" style="border: solid 1px #777;" name="login" id="login" value="" /></td></tr>
      <tr><td colspan="2" style="text-align:center"><input value="<?= _('Send') ?>" type="submit" /></td></tr>
    </table>
    </form>
</div>
<center><a href="login.php"><small><?= _("Login")?></small></a></center>
</body>
</html>
