<?php 
// 
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU GPL v2.0
//

global $Client;
?>

<table width="100%" border="0" cellspacing="0" cellpadding="5">
<tr class="row_header">
  <td><?=_('Hour') ?></td>
  <td><?= _('Events') ?></Td>
  <td><?= _('Who') ?></td>
</tr>
<?php
  //client
  $clause=" log REGEXP 'client:".$_GET['id']."$|client:".$_GET['id']." ' OR";

//invoices
$result = mysql_query("SELECT id_facture FROM webfinance_invoices WHERE id_client=".$_GET['id'])
  or wf_mysqldie();
while( list($id) = mysql_fetch_array($result))
  $clause .=" log REGEXP 'fa:$id$|fa:$id ' OR";

$clause = preg_replace('/OR$/','',$clause);

//echo $clause;

$result = mysql_query("SELECT id_userlog, log, date, wf_userlog.id_user, date_format(date,'%d/%m/%Y %k:%i') as nice_date, login ".
		      "FROM webfinance_userlog wf_userlog, webfinance_users wf_users WHERE wf_users.id_user=wf_userlog.id_user ".
		      "AND ($clause) ".
		      "ORDER BY date DESC")
  or wf_mysqldie();

$count=1;
while ($log = mysql_fetch_object($result)) {
  $class = ($count%2)==0?"odd":"even";
  $message = parselogline($log->log);

  print <<<EOF
    <tr class="row_$class">
    <td>$log->nice_date</td>
    <td>$message</td>
    <td>$log->login</td>
    </tr>
EOF;

    $count++;

}
mysql_free_result($result);
?>
</table>


