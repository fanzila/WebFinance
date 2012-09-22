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

require_once("../../inc/main.php");
$User = new User();

if(!$User->isAuthorized("manager,accounting,employee")){
  $_SESSION['came_from'] = $_SERVER['REQUEST_URI'];
  header("Location: /login.php");
  exit;
}

$result = mysql_query(
  "SELECT CONCAT('../../../document/client-', id_client, '/', filename) " .
  'FROM document '.
  "WHERE id = $_GET[id] ")
or die(mysql_error());

list($path) = mysql_fetch_row($result);

if(!file_exists($path))
  die("$path: no such file or directory");

$fp = fopen($path, 'rb');

header("Content-Type: application/pdf");
header("Content-Length: " . filesize($path));
header("Content-Disposition: attachment; filename=".basename($path));

fpassthru($fp);
fclose($fp);
exit;

?>
