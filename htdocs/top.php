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
// This file is part of Â« Webfinance Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$
// Common topper


include_once("inc/main.php");

global $title;
global $roles;

$User = new User();

if (! $User->isLogued()) {
  $_SESSION['came_from']=$_SERVER['REQUEST_URI'];

  if($_SESSION['debug']==1){
    echo 'Not logged. Debug mode, please <a href="/login.php">log in</a>';
    include("bottom.php");
    die();
  }
  header("Location: /login.php");
  die();
}

$user = $User->getInfos();

if(!$User->isAuthorized($roles)){
  header("Location: /welcome.php");
}

if (isset($_SESSION['message']) AND $_SESSION['message'] != "") {
  if(isset($_SESSION['error']) AND $_SESSION['error'] == 1){
    $_SESSION['message'] = '<div class="post_error">'.$_SESSION['message']."</div>";
    unset($_SESSION['error']);
  }else if(!preg_match('/^<div/',$_SESSION['message']))
    $_SESSION['message'] = '<div class="post_message">'.$_SESSION['message']."</div>";
 }

$css_theme = "/css/themes/main/main.css"; // Historic default
if(isset($User->prefs->theme)){
  $user_css_theme = "/css/themes/".$User->prefs->theme."/main.css";
  if (!file_exists(getWFDirectory().$user_css_theme)) {
    $User->prefs->theme = "main";
  }else
    $css_theme=$user_css_theme;
 }

$search_button = '/imgs/boutons/'.urlencode(_('Search')."_off_".$User->prefs->theme).'.png';
$search_button_on = '/imgs/boutons/'.urlencode(_('Search')."_on_".$User->prefs->theme).'.png';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="<?= $css_theme ?>" />
  <title><?= ($title=="")?"":"$title - " ?>Webfinance</title>
  <script type="text/javascript" language="javascript" src="/js/preloader.js.php"></script>
  <?include "extra_js.php" ?>
</head>

<body>

<table border="0" cellspacing="10" cellpadding="0" style="height: 100%">
<tr>
  <td style="text-align: center" width=150 valign=top>
  <? include(getWFDirectory()."/nav.php"); ?>
    <img height="200" width="1" src="/imgs/blank.gif" /><br />

    <?php if ($User->isAuthorized('admin,employee,manager')) { ?>
    <form action="search.php" method="get">
    <input id="searchfield" type="text" name="q" style="width: 120px; margin-bottom: 5px;" class="bordered" />
    <input type="image" src="<?= $search_button ?>" onmouseover="this.src='<?= $search_button_on ?>';" onmouseout="this.src='<?= $search_button ?>';" style="border: none;" /><br/>
    </form>
    <?php }?>

  </td>
  <td valign=top>
