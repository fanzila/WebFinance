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

require_once("../inc/main.php");
$User = new User();
$document = new WebfinanceDocument;

if(!$User->isAuthorized("manager,accounting,employee")){
  $_SESSION['came_from'] = $_SERVER['REQUEST_URI'];
  header("Location: /login.php");
  exit;
}

if (!isset($_POST['new_suivi_comment'], $_POST['new_suivi_type'],
    $_SESSION['id_user'], $_POST['company_id']))
  die('Too few argument');

mysql_query('INSERT INTO webfinance_suivi SET '.
  'type_suivi =' . mysql_real_escape_string($_POST['new_suivi_type']) . ' ,'.
  'id_objet   =' . mysql_real_escape_string($_POST['company_id']). ', '.
  "message ='". mysql_real_escape_string($_POST['new_suivi_comment']) . "',".
  'date_added = NOW(), ' .
  'date_modified = NOW(), ' .
  "added_by = $_SESSION[id_user]")
or die(mysql_error());

header("Location: fiche_prospect.php?onglet=followup&id=$_POST[company_id]");
exit;
?>
