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

if(!isset($_FILES['file']['name'], $_POST['company_id'], $_SESSION['id_user']))
  die('Too few argument');

WebfinanceDocument::ValidateFileName($_FILES['file']['name']);
CybPHP_Validate::ValidateInt($_POST['company_id']);
WebfinanceCompany::ValidateExists($_POST['company_id']);
CybPHP_Validate::ValidateInt($_SESSION['id_user']);

if($_FILES['file']['error'] !== 0)
  die('Unknown upload error from PHP');

$filename = basename($_FILES['file']['name']);
$doc_dir = $document->GetCompanyDirectory($_POST['company_id']);
$upload_file = "$doc_dir/$filename";

// Check $filename syntax
if(preg_match('/(\.\.|\/)/', $filename))
  die("Invalid file name syntax: $filename");

// Check if document directory is writable
if(!is_writable($doc_dir))
  die("Directory is not writable: $doc_dir");

// Check if destination file already exists
if(file_exists($upload_file))
  die("File $upload_file already exists");

// Move the uploaded file to the final destination
if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_file))
  die("Failed uploading $upload_file");

// Log user action
logmessage(_('Upload document').
  " $filename for client:$_POST[company_id]", $_POST['company_id']);

// Fetch company information
$company = new Client($_POST['company_id']);

// Fetch user information
$user = $User->getInfos($_SESSION['id_user']);

// Fetch document URL
$document_url = 'http://';
if($_SERVER['SERVER_PORT'] == 443)
  $document_url = 'https://';
$document_url .= $_SERVER['HTTP_HOST'] .
  "/prospection/document/download.php?company_id=$_POST[company_id]&file=" .
  urlencode($filename);

// Récupérer les info sur la société
$result = mysql_query('SELECT value '.
          'FROM webfinance_pref '.
          "WHERE type_pref='societe' AND owner=-1")
  or wf_mysqldie();
list($value) = mysql_fetch_array($result);
$societe = unserialize(base64_decode($value));

mail('cyril.bouthors@isvtec.com',
  "$company->nom: new file \"$filename\" by $user->login",
  $document_url,
  "From: $societe->raison_sociale <$societe->email>");

header("Location: ../fiche_prospect.php?onglet=documents&id=$_POST[company_id]");
exit;
?>
