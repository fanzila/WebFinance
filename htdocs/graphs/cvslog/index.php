<?php
/*
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

include('../../inc/main.php');
$roles = 'admin';
$chainage = "Statistiques de développement";
include('../../top.php');

extract($_GET);

if (!isset($path)) { $path='htdocs'; }
if (!isset($deltatime)) { $deltatime=25; }

// $tree = new Tree("files");
// 
// function recurse($path, $parent) {
//   global $tree;
// 
//   print "$parent<br/>";
//   $files = glob("$path/*");
//   foreach ($files as $f) {
//     if (!preg_match("/CVS/", $f)) {
//       $tree->addNode( addslashes($f), basename($f), addslashes($parent) );
//     }
//     if (is_dir($f)) {
//       // recurse($f, preg_replace("!/!", "_", $f));
//     }
//   }
// }
// 
// recurse("../../", "../../");

$res = mysql_query("SELECT DATEDIFF(NOW(),MIN(date)) FROM cvslog");
list($days_since_start) = mysql_fetch_array($res);

?>

<form method="get" onchange="this.submit();" />
<table border="0" cellspacing="0" cellpadding="10">
<tr>
  <td><img alt="daily" src="daily.php?path=<?= $path ?>&deltatime=<?= $deltatime ?>" /></td>
  <td rowspan="2" style="vertical-align: top;">
  <a class="search" href="?deltatime=7">Last week</a><br/>
  <a class="search" href="?deltatime=30">Last month</a><br/>
  <a class="search" href="?deltatime=<?= $days_since_start ?>">Since start</a><br/>
  <table border="0" cellspacing="0" cellpadding="3">
  <tr class="row_header">
    <td>Kodeur</td>
    <td>+</td>
    <td>-</td>
    <td>Delta</td>
  </tr>
  <?
  $res = mysql_query("select author,sum(added), sum(deleted), sum(added)+sum(deleted) from cvslog  where datediff(now(), date)<$deltatime group by author");
  while ($codeur = mysql_fetch_array($res)) {
    printf('<tr class="row_listing"><td>%s</td><td>+%d</td><td>-%d</td><td>%d</td></tr>'."\n", 
           $codeur[0], $codeur[1], $codeur[2], $codeur[3] );
  }
  print "</table>";
  
  // $tree->realise();
  ?></td>
</tr>
<tr>
  <td><img alt="overall" src="overall.php?path=<?= $path ?>&deltatime=<?= $deltatime ?>" /></td>
</tr>
</table>
</body>
</html>

