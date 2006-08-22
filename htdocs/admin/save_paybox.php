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
