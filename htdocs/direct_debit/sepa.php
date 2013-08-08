<?php
/*
This file is part of Webfinance.

Copyright (c) Pierre Doleans <pierre@doleans.net>

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
require('../../lib/sepa.php');
must_login();

if(empty($_GET['debit_id']) or !is_numeric($_GET['debit_id']))
  die('Invalid $_GET[debit_id] ' . $_GET['debit_id'] );

$sepa_file = GenerateSepa($_GET['debit_id']);

if($sepa_file === false)
  die('Error while building SEPA file');

# Send SEPA file to browser
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename = "'.$sepa_file.'"');
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($sepa_file));
readfile($sepa_file);
unlink($sepa_file);

?>