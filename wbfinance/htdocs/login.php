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
if ($User->login($_POST)) {
  if ($_POST['came_from'] != "" AND !preg_match('/login.php$/i',$_POST['came_from']) AND !preg_match('/passwd.php$/i',$_POST['came_from']) ) {
    header("Location: ".$_POST['came_from']);
  } else {
    header("Location: /");
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <link rel="stylesheet" type="text/css" href="/css/themes/main/main.css" />
  <title>Webfinance <?= _("Login screen") ?></title>
</head>
<body>
<div style="margin-top: 15%; width: 100%; text-align: center;">
    <form action="login.php" method="post">
    <input type="hidden" name="came_from" value="<?= $GLOBALS['_SERVER']['HTTP_REFERER'] ?>" />
    <table border="0" cellspacing="0" cellpadding="10" style="border: solid 1px black; margin: auto auto auto auto;">
      <tr><td><?= _("Login") ?></td><td><input type="text" size="20" style="border: solid 1px #777;" name="login" id="login" value="" /></td></tr>
      <tr><td><?= _("Password") ?></td><td><input type="password" style="border: solid 1px #777;" size="20" name="password" /></td></tr>
      <tr><td colspan="2" style="text-align:center"><input value="<?= _('Login') ?>" type="submit" /></td></tr>
    </table>
    </form>
</div>
<center><a href="passwd.php"><small><?= _("Forgot password")?></small></a></center>
</body>
</html>
