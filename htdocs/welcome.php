<?php
/*
 Copyright (C) 2004-2006 NBI SARL, ISVTEC SARL

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

include("inc/main.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel=stylesheet type=text/css href=/css/themes/main/main.css />
	  <title>Webfinance<?=(empty($title)?"":" - $title") ?></title>
  <script type="text/javascript" language="javascript" src="/js/preloader.js.php"></script>
  <?include "extra_js.php" ?>
</head>

<body>


<table border="0" cellspacing="10" cellpadding="0" style="height: 100%"

<tr>
  <td style="text-align: center" width=150 valign=top>
    <? include("nav.php"); ?>
    <img height="200" width="1" src="/imgs/blank.gif" /><br />
  </td>
  <td valign=top><?= _("Welcome to WebFinance ...") ?></td>
<?php

  $Revision = '$Revision: 549 $';
    include("bottom.php")

?>
