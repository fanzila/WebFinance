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

if(!isset($_GET['id'], $_SESSION['id_user']))
  die('Too few argument');

$_GET['id'] = mysql_real_escape_string($_GET['id']);

// Fetch document filename
$result = mysql_query('SELECT filename, id_client '.
       'FROM document '.
       "WHERE id=$_GET[id] " .
       'LIMIT 1')
  or die(mysql_error());

// Check if document exists
if(mysql_num_rows($result) !== 1)
  die('Unable to fetch document ID $_GET[id] from SQL.');

// Fetch database information
list($filename, $client_id) = mysql_fetch_row($result);

// Check if destination directory is writable
$upload_dir = "../../../document/client-$client_id/";
if(!is_writable($upload_dir))
  die("Directory is not writable: ". $upload_dir);

// Delete document from filesystem
if(!unlink("$upload_dir/$filename"))
  die("Unable to delete $upload_dir/$filename");

// Delete document from SQL
mysql_query('DELETE FROM document '.
  "WHERE id = $_GET[id] " .
  'LIMIT 1')
or die(mysql_error());

// Log user action
logmessage(_('Delete document')." $filename for client:$client_id", $client_id);

header("Location: ../fiche_prospect.php?onglet=documents&id=$client_id");
exit;
?>
