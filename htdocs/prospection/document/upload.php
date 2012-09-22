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

if(!isset($_FILES['file']['name'], $_POST['description'], $_POST['client_id'],
  $_SESSION['id_user']))
  die('Too few argument');

if($_FILES['file']['error'] !== 0)
  die('Unknown upload error from PHP');

$_POST['client_id']   = mysql_real_escape_string($_POST['client_id']);
$_POST['description'] = mysql_real_escape_string($_POST['description']);
$_SESSION['id_user']  = mysql_real_escape_string($_SESSION['id_user']);

$filename = basename($_FILES['file']['name']);
$main_doc_dir = '../../../document';
$client_doc_dir = "$main_doc_dir/client-$_POST[client_id]";
$upload_file = "$main_doc_dir/client-$_POST[client_id]/$filename";

// Check $filename syntax
if(preg_match('/(\.\.|\/)/', $filename))
  die("Invalid file name syntax: $filename");

// Check if main document directory is writable
if(!is_writable($main_doc_dir))
  die("Directory is not writable: ". $main_doc_dir);

// Create client directory if needed
if(!file_exists($client_doc_dir))
  if(!mkdir($client_doc_dir))
    die("Unable to create directory $client_doc_dir");

// Check if destination file already exists
if(file_exists($upload_file))
  die("File $upload_file alread exists");

// Move the uploaded file to the final destination
if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_file))
  die("Failed uploading $upload_file");

$result = mysql_query(
  'INSERT INTO document ' .
  "SET id_client = $_POST[client_id], " .
  'date = NOW(), ' .
  "filename = '" . mysql_real_escape_string($filename) . "', ".
  "description = '$_POST[description]'")
or die(mysql_error());

$document_id = mysql_insert_id();

// Log user action
logmessage(_('Upload document').
  " doc:$document_id for client: $_POST[client_id]", $_POST['client_id']);

header("Location: ../fiche_prospect.php?onglet=documents&id=$_POST[client_id]");
exit;
?>
