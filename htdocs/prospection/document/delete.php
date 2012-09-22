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
$document = new WebfinanceDocument;

if(!$User->isAuthorized("manager,accounting,employee")){
  $_SESSION['came_from'] = $_SERVER['REQUEST_URI'];
  header("Location: /login.php");
  exit;
}

if(!isset($_GET['company_id'], $_GET['filename'], $_SESSION['id_user']))
  die('Too few argument');

CybPHP_Validate::ValidateInt($_GET['company_id']);
WebfinanceCompany::ValidateExists($_GET['company_id']);
WebfinanceDocument::ValidateFileName($_GET['file']);
CybPHP_Validate::ValidateInt($_SESSION['id_user']);

$doc_dir = $document->GetCompanyDirectory($_GET['company_id']);

// Check if destination directory is writable
if(!is_writable($doc_dir))
  die("Directory is not writable: ". $doc_dir);

// Delete document from filesystem
if(!unlink("$doc_dir/$_GET[filename]"))
  die("Unable to delete $doc_dir/$_GET[filename]");

// Log user action
logmessage(_('Delete document')." $_GET[filename] for client:$_GET[company_id]",
  $_GET['company_id']);

header("Location: ../fiche_prospect.php?onglet=documents&id=$_GET[company_id]");
exit;
?>
