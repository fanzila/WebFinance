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
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<br/>
<?php
$result = mysql_query("SELECT count(*) FROM webfinance_invoices WHERE id_client=".$_GET['id']);
list($has_invoices) = mysql_fetch_array($result);
mysql_free_result($result);
if ($has_invoices) {
?>
<img onMouseOut="UnTip();" onmouseover="Tip('<?= ('Income by month for this client') ?>')" src="/graphs/client_income.php?nb_months=12&grid=1&width=720&height=250&id_client=<?= $_GET['id'] ?>" />
<img onMouseOut="UnTip();" onmouseover="Tip('<?= ('Income by month for this client') ?>')" src="/graphs/client_debpt.php?nb_months=12&grid=1&width=720&height=250&id_client=<?= $_GET['id'] ?>" />
<?php
}
?>