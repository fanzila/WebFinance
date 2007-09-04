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
// $Id: top_popup.php 550 2007-08-01 14:51:30Z gassla $
// Common topper

include_once("inc/main.php");

if ($GLOBALS['_SERVER']['HTTP_HOST'] != "backoffice.nbi.fr") {
  $_SESSION['debug'] = 1;
}

global $title;
$User = new User();

if (! $User->isLogued()) {
  header("Location: login.php");
}

$User->getInfos();

$css_theme = "/css/themes/main/main.css"; // Historic default
if(isset($User->prefs->theme)){
  $user_css_theme = "/css/themes/".$User->prefs->theme."/main.css";
  if (!file_exists(getWFDirectory().$user_css_theme)) {
    $User->prefs->theme = "main";
  }else
    $css_theme=$user_css_theme;
 }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="<?= $css_theme ?>" />
  <title>Webfinance<?= ($title=="")?"":" - $title" ?></title>
  <script type="text/javascript" language="javascript" src="/js/preloader.js.php"></script>
  <?include "extra_js.php" ?>
</head>

<body>
