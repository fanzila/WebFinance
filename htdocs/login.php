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
// $Id: login.php 551 2007-08-02 05:16:27Z gassla $
include("inc/main.php");

if(isset($_POST['user_login'],$_POST['user_password'])){
  $_POST['login']=$_POST['user_login'];
  $_POST['password']=$_POST['user_password'];
  $User = new User();
  $test = $User->login($_POST);
  if($test>0) {
    if ($_POST['came_from'] != "" AND !preg_match('/login.php$/i',$_POST['came_from']) AND !preg_match('/passwd.php$/i',$_POST['came_from']) ) {
      header("Location: ".$_POST['came_from']);
    } else {
      header("Location: /");
    }
  }else{
    header("Location: /login?err=1");
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
<?php
  $came_from="/";
  if(isset($_SESSION['came_from']))
    $came_from=$_SESSION['came_from'];

$err_msg="";
if(isset($_GET['err'])){
  switch ($_GET['err']){
    case 1:
      $err_msg="<center><span style='color: rgb(255, 0, 0);'>"._('Invalid Login or password!') . "</span>";
      break;
    }
 }
?>
    <?=$err_msg?>
    <input type="hidden" name="came_from" value="<?=$came_from ?>" />
    <table border="0" cellspacing="0" cellpadding="10" style="border: solid 1px black; margin: auto auto auto auto; background: white;">
      <tr><td><?= _("Login") ?></td><td><input type="text" size="20" style="border: solid 1px #777;" name="user_login" id="login" value="" /></td></tr>
      <tr><td><?= _("Password") ?></td><td><input type="password" style="border: solid 1px #777;" size="20" name="user_password" /></td></tr>
      <tr><td colspan="2" style="text-align:center"><input value="<?= _('Login') ?>" type="submit" /></td></tr>
    </table>
    </form>
</div>
<center><a href="passwd.php"><?= _("Forgot password")?></a></center>
<?
$Revision = '$Revision: 551 $';
include("bottom.php");
?>
