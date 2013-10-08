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
include("../inc/main.php");
must_login();

$roles = 'manager,employee';
include("../top.php");

if(!isset($_GET['id']) or !is_numeric($_GET['id'])) {
  echo "Invalid id";
  exit(1);
}

$res = mysql_query(
  'DELETE '.
  'FROM direct_debit_row '.
  "WHERE invoice_id = $_GET[id] AND state='todo'")
  or die(mysql_error());

?>

Successfully deleted. <br/>

<a href="./">Back</a>
