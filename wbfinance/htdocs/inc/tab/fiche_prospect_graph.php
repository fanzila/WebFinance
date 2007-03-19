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
<img onmouseover="return escape('<?= ('Income by month for this client') ?>')" src="/graphs/client_income.php?nb_months=12&grid=1&width=720&height=250&id_client=<?= $_GET['id'] ?>" />
<img onmouseover="return escape('<?= ('Income by month for this client') ?>')" src="/graphs/client_debpt.php?nb_months=12&grid=1&width=720&height=250&id_client=<?= $_GET['id'] ?>" />
<?php
}
?>