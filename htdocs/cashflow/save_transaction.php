<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$

require("../inc/main.php");

extract($_POST);

$q = sprintf("UPDATE webfinance_transactions SET ".
	     "id_category=%d, ".
	     "text='%s', ".
	     "amount=%s, ".
	     "type='%s', ".
	     "date='%s', ".
	     "comment='%s' ".
	     "WHERE id=%d",
             $id_category, $text, $amount, $type, $date, $comment, $id_transaction);
mysql_query($q) or die(mysql_error());

?>
<script>
popup = window.parent.document.getElementById('inpage_popup');
popup.style.display = 'none';
// Reload parent window to update contacts
</script>
