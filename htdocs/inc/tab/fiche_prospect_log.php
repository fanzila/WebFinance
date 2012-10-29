<?php
/*
 Copyright (C) 2004-2012 NBI SARL, ISVTEC SARL

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

global $Client;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="5">
     <!-- Ugly, close the previous bloody global form. I mean *global* form! -->
     </form>
<?php

// Suivi

// Ajout d'un élément de suivi
$ts_select = '<select name="new_suivi_type">';
$result = mysql_query("SELECT id_type_suivi,name FROM webfinance_type_suivi ORDER BY id_type_suivi DESC");
while (list($id,$ts) = mysql_fetch_array($result)) {
  $ts_select .= sprintf('<option value="%d">%s</option>', $id, $ts);
}
$ts_select .= "</select>";

print <<<EOF
<tr><td colspan="3">
     <form method="POST" action="save_suivi.php">

$ts_select<br/>

<textarea name="new_suivi_comment" style="width: 600px; height: 90px; border: solid 1px #ccc;">
</textarea>

       <input type="hidden" name="company_id" value="$_GET[id]" />
       <input type="submit" name="Go" value="Go"/>
     </form>

</td></tr>
EOF;

// Affichage de l'existant
$q = "SELECT s.id_suivi, s.message, ts.name as type_suivi,
             UNIX_TIMESTAMP(s.date_added) as ts_date_added
      FROM webfinance_suivi s, webfinance_type_suivi ts
      WHERE ts.id_type_suivi=s.type_suivi
      AND s.id_objet=".$Client->id."
      ORDER BY s.date_added DESC";

$result = mysql_query($q) or die($q." ".mysql_error());

$count = 1;
while ($log = mysql_fetch_object($result)) {
  $class = ($count%2)?"even":"odd";
  $date = strftime("%e %b %y", $log->ts_date_added);
  $date = preg_replace("/([^0-9])0/", '\\1', $date); // year >= 2000 this app is not expected to still exist in y3K :)
  $txt_msg = nl2br($log->message);
  print <<<EOF
<tr class="$class" valign="top">
  <td nowrap align="center"><b>$date</b></td>
  <td>$txt_msg</td>
  <td nowrap class="type_suivi_$log->id_type_suivi">$log->type_suivi</td>
</tr>
EOF;

}
mysql_free_result($result);

?>
</table>