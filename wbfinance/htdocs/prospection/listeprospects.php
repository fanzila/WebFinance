<?php 
// 
// This file is part of « Backoffice NBI »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU LGPL v2.0
//
?>
<table border=0 cellspacing=0 cellpadding=3 style="border: solid 1px black;">
<tr align=center class=row_header> 
  <td>Nom prospect</td>
  <td>Tel</td>
  <td>Fax</td>
</tr>
<?php

$result = mysql_query("SELECT id_client,nom,tel,fax FROM client WHERE state='prospect' ORDER BY nom") or nbi_mysqldie();
while ($client = mysql_fetch_object($result)) {
  $count++;
  print "<tr align=center class=row_".(($count%2 == 0)?"even":"odd").">\n"
       ."  <td><a href=index.php?file=fiche_prospect&id=".$client->id_client.">".$client->nom."</a></td>\n"
       ."  <td width=150>".$client->tel."</td>\n"
       ."  <td width=150>".$client->fax."</td>\n"
       ."</tr>\n";
}
mysql_free_result($result);


?>
</table>
