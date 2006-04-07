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
<?include "nbi/functions.php" ?>
<?include "nbi/tabs.php" ?>
<?php
connect();
session_start();
if (!nbi_is_logued()) {
  nbi_redirect("login.php");
}
nbi_nullordigit('id_site');

$tab = new nbi_tabs(2);
$tab->set_title(0, "Général");
$tab->set_title(1, "Mysql");

$result = mysql_query("SELECT *,date_format('%Y', date_created) as nice_date_created FROM site WHERE id_site=".$_SESSION['id_site']) or nbi_mysqldie();
$site = mysql_fetch_object($result);
print_r($site);
$tab->set_content(0, $sites);
?>

<html>

<body>

<?= $tab->print_html() ?>
</body>

</html>
