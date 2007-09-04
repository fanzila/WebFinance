<?php
/*
 Copyright (C) 2004-2006 NBI SARL, ISVTEC SARL

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
?>
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
<?
require("../nbi/functions.php");
must_login();
?>
<?php
connect();
ini_set('session.gc_maxlifetime',3600);
session_start();
if (!nbi_is_logued()) {
  nbi_redirect("/login.php");
}
if ($GLOBALS['HTTP_SERVER_VARS']['REQUEST_METHOD'] != "POST") {
  die();
}
$user = nbi_get_user_info();

if ($_POST['action'] == "create") {
  $q = sprintf("INSERT INTO webfinance_suivi (type_suivi,id_objet,message,date_added,added_by,rappel) VALUES(%d, %d, '%s', now(), %d, %s)",
               $_POST['type_suivi'], $_POST['id_client'], $_POST['message'], $user->id_user,
               ($_POST['deltadays']!=0)?"date_add(now(), INTERVAL ".$_POST['deltadays']." DAY)":"NULL");

  mysql_query($q) or wf_mysqldie();;
} elseif ($_POST['action'] == "save") {
} else {
  die("Don't know what to do with posted data");
}
nbi_redirect("index.php?file=fiche_prospect&id=".$_POST['id_client']);
?>
