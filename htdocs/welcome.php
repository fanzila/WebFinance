<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//

include("inc/main.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel=stylesheet type=text/css href=/css/themes/main/main.css />
  <title>Webfinance<?= ($title=="")?"":" - $title" ?></title>
  <script type="text/javascript" language="javascript" src="/js/preloader.js.php"></script>
  <?include "extra_js.php" ?>
</head>

<body>


<table border="0" cellspacing="10" cellpadding="0" style="height: 100%"

<tr>
  <td style="text-align: center" width=150 valign=top>
     <?include $GLOBALS['_SERVER']['DOCUMENT_ROOT']."/nav.php" ?>
    <img height="200" width="1" src="/imgs/blank.gif" /><br />
  </td>
  <td valign=top><?= _("Welcome to WebFinance ...") ?></td>
<?php
 
  $Revision = '$Revision$';
    include("bottom.php")

?>
