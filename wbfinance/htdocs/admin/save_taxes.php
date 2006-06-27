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

if ($_GET['action'] == "delete") {
  mysql_query("DELETE FROM webfinance_pref WHERE id_pref=".$_GET['id']);
  $_SESSION['message'] = _('Taxe deleted');
  header("Location: preferences.php?tab=Taxes");
}

foreach ($_POST['taxes'] as $id=>$data) {
  if ($id == "new") {
    if (!empty($data['taxe']) ) {
      $q = sprintf("INSERT INTO webfinance_pref SET type_pref='taxe_%s', value='%s'" , $data['taxe'], $data['value']);
      $_SESSION['message'] = _('Taxe added');
    }
  } else {
    $q = sprintf("UPDATE webfinance_pref SET type_pref='taxe_%s', value='%s' WHERE id_pref=%d " , $data['taxe'], $data['value'] , $id );
    $_SESSION['message'] = _('Taxe updated');
  }
  if(isset($q) AND !empty($q))
    mysql_query($q) or wf_mysqldie();
}

header("Location: preferences.php?tab=Taxes");


?>
