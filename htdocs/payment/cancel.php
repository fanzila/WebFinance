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
?>
<?php
include("../inc/main.php");

$societe = GetCompanyInfo();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link rel="stylesheet" type="text/css" href="/css/themes/main/main.css" />
  <title>Paiement <?=$societe->raison_sociale?></title>
</head>
<body>
<br /><br />
<table width="500" align="center" border="0" cellspacing="5" cellpadding="5" style="border: 1px solid gray;">
  <tr>
    <td align="center"><?php

	$result = mysql_query("SELECT type_pref,value FROM webfinance_pref WHERE owner=-1 AND type_pref='logo'") or die(mysql_error());
	$logo = mysql_fetch_object($result);

	if (mysql_num_rows($result)) {
	  printf('<img src="data:image/png;base64,%s" /><br/>', $logo->value);
	}

	?></td>
  </tr>
  <tr><td height="30"></td></tr>
  <tr><td align="center"><h2>Votre paiement a été abandonné.</h2></td></tr>
</table>
</body>
</html>
