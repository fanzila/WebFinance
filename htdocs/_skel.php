<?php
/*

Webfinance: manage invoices, clients contacts, cash-flow history and
previsions

Copyright (C) 2004-2006 NBI SARL, ISVTEC SARL

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2, or (at your option)
any later version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc. 51 Franklin St, Fifth Floor, Boston, MA 02110-1301
USA

$Id: _skel.php 487 2006-09-22 07:28:24Z nico $
*/

require_once("../../inc/main.php");
$User = new User();
$document = new WebfinanceDocument;

if(!$User->isAuthorized("manager,accounting,employee")){
  $_SESSION['came_from'] = $_SERVER['REQUEST_URI'];
  header("Location: /login.php");
  exit;
}

require("../top.php");

?>

HTML content here

<?php
$Revision = '$Revision: 487 $';
require("../bottom.php");
// -*- indent-tabs-mode: nil -*-
// vim: ts=4 sw=4 et
?>
