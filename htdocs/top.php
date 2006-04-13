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
  header("Location: /login.php");
}

$User->getInfos();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel=stylesheet type=text/css href=/css/main.css />
  <title>Webfinance<?= ($title=="")?"":" - $title" ?></title>
  <script type="text/javascript" language="javascript" src="/js/preloader.js.php"></script>
  <?include "extra_js.php" ?>
</head>

<body>

<table border="0" cellspacing="10" cellpadding="0" style="height: 100%">
<tr>
  <td style="text-align: center" width=150 valign=top>
     <?include $GLOBALS['_SERVER']['DOCUMENT_ROOT']."/nav.php" ?>
    <img height="200" width="1" src="/imgs/blank.gif" /><br />

    <form action="/search.php" method="get">
    <input id="searchfield" type="text" name="q" style="width: 100px; margin-bottom: 5px;" class="bordered" />
    <input type="submit" value="<?= _('Search') ?>" class="bordered" style="width: 102px;" /><br/>
    </form>

    <img height="200" width="1" src="/imgs/blank.gif" /><br />
    <img src="/imgs/logo.gif" alt="NBi" />
  </td>
  <td valign=top>
