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

if ($_SESSION['message'] != "") {
  if($_SESSION['error'] == "1"){
    $_SESSION['message'] = '<div class="post_error">'.$_SESSION['message']."</div>";
    $_SESSION['error'] = "";
  }else{
    $_SESSION['message'] = '<div class="post_message">'.$_SESSION['message']."</div>";
  }
}

$css_theme = "/css/themes/".$User->prefs->theme."/main.css";
if (! file_exists($GLOBALS['_SERVER']['DOCUMENT_ROOT'].$css_theme)) {
  $css_theme = "/css/themes/main/main.css"; // Historic default
  $User->prefs->theme = "main";
}

$search_button = '/imgs/boutons/'.urlencode(base64_encode(_('Search').":off:".$User->prefs->theme)).'.png';
$search_button_on = '/imgs/boutons/'.urlencode(base64_encode(_('Search').":on:".$User->prefs->theme)).'.png';

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
     <?include $GLOBALS['_SERVER']['DOCUMENT_ROOT']."/nav.php" ?>
    <img height="200" width="1" src="/imgs/blank.gif" /><br />

    <?php if ($User->isAuthorized('admin,employee,manager')) { ?>
    <form action="/search.php" method="get">
    <input id="searchfield" type="text" name="q" style="width: 120px; margin-bottom: 5px;" class="bordered" />
    <input type="image" src="<?= $search_button ?>" onmouseover="this.src='<?= $search_button_on ?>';" onmouseout="this.src='<?= $search_button ?>';" style="border: none;" /><br/>
    </form>
    <?php }?>

  </td>
  <td valign=top>
