<?php

include('../../inc/main.php');
$roles = 'admin';
include('../../top.php');

extract($_GET);

if (!isset($path)) { $path='htdocs'; }
if (!isset($deltatime)) { $deltatime=100; }

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

?>

<html>
<body>
<table border="0" cellspacing="0" cellpadding="10">
<tr>
  <td><img alt="daily" src="daily.php?path=<?= $path ?>&deltatime=<?= $deltatime ?>" /></td>
  <td rowspan="2"><?php
  // $tree->realise();
  ?></td>
</tr>
<tr>
  <td><img alt="overall" src="overall.php?path=<?= $path ?>&deltatime=<?= $deltatime ?>" /></td>
</tr>
</table>
</body>
</html>

