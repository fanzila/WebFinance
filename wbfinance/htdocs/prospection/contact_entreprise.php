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
<div style="overflow: auto; height: 300px;">
<table width="100%" border="0" cellspacing="0" cellpadding="2">
  <?php
  global $Client;
  // Liste les personnes contacts pour ce client
  $result = mysql_query("SELECT nom,prenom,fonction,mobile,tel,note,email FROM webfinance_personne WHERE client=".$_GET['id']." ORDER BY nom") or wf_mysqldie();
  $count = 1;
  while ($contact = mysql_fetch_object($result)) {
    $contact->note = preg_replace("!\r\n!", "<br/>", $contact->note );
    $class = ($count%2 == 0)?"odd":"even";
    if ($contact->email != "") $mail = sprintf('<a href="mailto:%s"><img class="icon" src="/imgs/icons/mail.gif" alt="%s" /></a>', $contact->email, $contact->email ); else $mail = "";
    if ($contact->tel != "") $tel = sprintf('<img style="vertical-align: middle;" src="/imgs/icons/tel.gif" alt="Tel" />&nbsp;%s<br/>', $contact->tel); else $tel = "";
    if ($contact->mobile != "") $mobile = sprintf('<img style="vertical-align: middle;" src="/imgs/icons/gsm.gif" alt="GSM" />&nbsp;%s<br/>', $contact->mobile); else $mobile = "";
    if ($contact->note != "") $note = sprintf('<img style="vertical-align: middle;" src="/imgs/icons/notes.gif" onmouseover="return escape(\'%s\')"/>', addslashes($contact->note)); else $note = "";
    print <<<EOF
      <tr onmouseover="this.className='row_over';" onmouseout="this.className='row_$class';" class="row_$class" valign="top">
        <td width="16">$mail</td>
        <td onclick="inpagePopup(event, this, 240, 250, 'edit_contact.php?id_personne=$contact->id_personne');" ><b>$contact->prenom $contact->nom</b></td>
        <td onclick="inpagePopup(event, this, 240, 250, 'edit_contact.php?id_personne=$contact->id_personne');" >$contact->fonction</td>
        <td onclick="inpagePopup(event, this, 240, 250, 'edit_contact.php?id_personne=$contact->id_personne');" >$mobile</td>
        <td onclick="inpagePopup(event, this, 240, 250, 'edit_contact.php?id_personne=$contact->id_personne');" >$tel</td>
        <td onclick="inpagePopup(event, this, 240, 250, 'edit_contact.php?id_personne=$contact->id_personne');" >$note</td>
      </tr>
EOF;
    $count++;
  }
  mysql_free_result($result);
  ?>
</table>
</div>
