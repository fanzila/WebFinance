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

$css_theme = "/css/themes/".$User->prefs->theme."/main.css";
if (! file_exists($GLOBALS['_SERVER']['DOCUMENT_ROOT'].$css_theme)) {
  $css_theme = "/css/main.css"; // Historic default
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
