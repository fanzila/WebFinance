<?php
/*
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

// $Id$

require("../inc/main.php");
must_login();

  $data = new stdClass();
  $data->PBX_SITE = $_GET['PBX_SITE'];
  $data->PBX_RANG = $_GET['PBX_RANG'];
  $data->PBX_IDENTIFIANT = $_GET['PBX_IDENTIFIANT'];
  $data = base64_encode(serialize($data));

if($_GET['id']>0)
  mysql_query("UPDATE webfinance_pref SET value='$data' WHERE id_pref=".$_GET['id']) or wf_mysqldie();
 else
   mysql_query("INSERT INTO webfinance_pref SET type_pref='paybox', value='$data' ")
     or wf_mysqldie();

header("Location: preferences.php?tab=Paybox");


?>
