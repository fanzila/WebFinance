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

CybPHP_Validate::ValidateInt($_GET['company_id']);
WebfinanceCompany::ValidateExists($_GET['company_id']);
WebfinanceDocument::ValidateFileName($_GET['file']);

$path = $document->GetCompanyDirectory($_GET['company_id']) . "/$_GET[file]";

$fp = fopen($path, 'r')
  or die("Unable to open $path");

header("Content-Type: application/pdf");
header("Content-Length: " . filesize($path));
header("Content-Disposition: attachment; filename=".basename($path));

fpassthru($fp);
fclose($fp);
exit;

?>
