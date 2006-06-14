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

  echo '</pre></div>';
}
?>
<div id="revision"><?= $Revision ?></div>
<script type="text/javascript" language=javascript src=/js/wz_tooltip.js></script>
<script type="text/javascript" src="/js/inpage_popup.js"></script>
</body>

</html>
