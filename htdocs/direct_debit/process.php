<?php
/*
 Copyright (C) 2004-2011 NBI SARL, ISVTEC SARL

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
require("../inc/main.php");
require('../../lib/sepa.php');
must_login();

$roles = 'manager,employee';

// Check if invoices are planned to be debited
$req = mysql_query(
  'SELECT COUNT(*) '.
  'FROM direct_debit_row '.
  "WHERE state='todo'")
  or die(mysql_error());
list($res) = mysql_fetch_row($req);

if($res == 0) {
  echo "No invoice to be debited";
  exit(1);
}

// Check SEPA format
if(GenerateSepa() === false)
  die('Unable to build SEPA file');

// Create new direct debit
mysql_query('INSERT INTO direct_debit '.
  'SET date=NOW()')
or die(mysql_error());
$id = mysql_insert_id();

// Mark invoices as debited
mysql_query('UPDATE direct_debit_row '.
  "SET state = 'done', ".
  "    debit_id = $id " .
  "WHERE state='todo'")
or die(mysql_error());

header('Location: ./');
exit;

?>
