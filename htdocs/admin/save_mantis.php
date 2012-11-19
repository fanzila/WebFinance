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

require("../inc/main.php");
must_login();

if(!isset($_POST['login'], $_POST['password']))
  exit('Missing argument');

$_POST['login']    = mysql_escape_string($_POST['login']);
$_POST['password'] = mysql_escape_string($_POST['password']);

mysql_query('begin')
  or die(mysql_error());

# Delete previous entries
mysql_query('delete from webfinance_pref '.
  "where type_pref in ('mantis_login', 'mantis_password')")
  or die(mysql_error());

# Set login
mysql_query('insert into webfinance_pref '.
  "set type_pref = 'mantis_login', ".
  " value = '$_POST[login]'")
  or die(mysql_error());

# Set password
mysql_query('insert into webfinance_pref '.
  "set type_pref = 'mantis_password', ".
  " value = '$_POST[password]'")
  or die(mysql_error());

mysql_query('commit')
  or die(mysql_error());

header("Location: preferences.php?tab=Mantis");
exit;

?>
