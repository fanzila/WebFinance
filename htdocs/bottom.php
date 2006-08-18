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
  </td>
</tr>
</table>
<?
if($_SESSION['debug']==1){
  echo '<div><pre>';

  echo '<b>GET:</b>';
  print_r($_GET);
  echo '<b>POST:</b>';
  print_r($_POST);
  echo '<b>SESSION:</b>';
  print_r($_SESSION);
  echo '<b>COOKIES:</b>';
  print_r($HTTP_COOKIE_VARS);
  echo '<b>$_SERVER:</b>';
  print_r($_SERVER);

  $mt_end=getMicroTime();

  echo "<b>Time elapsed</b>: ".round(($mt_end-$mt_start)*1000)." ms \n";
  //  echo "<b>Query number</b>: $query_number\n";

  echo "<b>mysql stats:</b>\n";
  $status = explode('  ', mysql_stat());
  print_r($status);

  echo "<b>mysql processes</b>:\n";
  $result = mysql_list_processes();
  while ($row = mysql_fetch_assoc($result)){
    printf("%s %s %s %s %s\n", $row["Id"], $row["Host"], $row["db"],
	   $row["Command"], $row["Time"]);
  }
  mysql_free_result($result);
  echo "<b>Locales:</b>\n";
  system("locale");

  echo '</pre></div>';

}
?>
<div id="revision"><?= $Revision ?></div>
<script type="text/javascript" language=javascript src=/js/wz_tooltip.js></script>
<script type="text/javascript" src="/js/inpage_popup.js"></script>
</body>

</html>
