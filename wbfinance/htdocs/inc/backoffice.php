<?php
//
// This file is part of « Backoffice NBI »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU LGPL v2.0
//
?>
<?php

// $Id$

session_start();
require($GLOBALS['_SERVER']['DOCUMENT_ROOT']."/inc/dbconnect.php");
require($GLOBALS['_SERVER']['DOCUMENT_ROOT']."/inc/User.php");
require($GLOBALS['_SERVER']['DOCUMENT_ROOT']."/inc/Facture.php");
require($GLOBALS['_SERVER']['DOCUMENT_ROOT']."/inc/Client.php");
setlocale(LC_ALL, "fr_FR.UTF-8");

function parselogline($str) {
  if (preg_match("/(user|fa|client):([0-9]+)/", $str)) {
    while (preg_match("/(user|fa|client):([0-9]+)/", $str, $matches)) {
      switch ($matches[1]) {
        case "fa":
          $result = mysql_query("SELECT num_facture FROM webcash_invoices WHERE id_facture=".$matches[2]);
          list($num_facture) = mysql_fetch_array($result);
          mysql_free_result($result);
          if (empty($num_facture)) {
            $str = preg_replace("/".$matches[0]."/", '<i>facture supprimée</i>', $str);
          } else {
            $str = preg_replace("/".$matches[0]."/", '<a href="/prospection/edit_facture.php?id_facture='.$matches[2].'">'.$num_facture.'</a> <a href="/prospection/getfacture.php?id='.$matches[2].'"><img src="/imgs/icons/pdf.png" valign="bottom"></a>', $str);
          }
          break;
        case "user":
          $result = mysql_query("SELECT login FROM webcash_users  WHERE id_user=".$matches[2]);
          list($login) = mysql_fetch_array($result);
          mysql_free_result($result);
          $str = preg_replace("/".$matches[0]."/", '<a href="/admin/fiche_user.php?id='.$matches[2].'">'.$login.'</a>', $str);
          break;
        case "client":
          $result = mysql_query("SELECT nom FROM webcash_clients WHERE id_client=".$matches[2]);
          list($client) = mysql_fetch_array($result);
          mysql_free_result($result);
          $str = preg_replace("/".$matches[0]."/", '<a href="/prospection/fiche_prospect.php?id='.$matches[2].'">'.$client.'</a>', $str);
          break;
      }
    }
  }
  return $str;
}

function randomPass() {
  $passwd = "";

  $passwd .= chr(96+rand(1,26));
  $passwd .= chr(96+rand(1,26));
  $passwd .= rand(0,9);
  $passwd .= rand(0,9);
  $passwd .= chr(96+rand(1,26));
  $passwd .= chr(96+rand(1,26));
  $passwd .= rand(0,9);
  $passwd .= rand(0,9);

  print $passwd;
}

// Logs a message ala syslog
function logmessage($msg) {
  $id = (empty($_SESSION['id_user']))?-1:$_SESSION['id_user'];
  $msg = preg_replace("/'/", "\\'", $msg );
  $msg = preg_replace('/"/', "\\'", $msg );
  mysql_query("INSERT INTO webcash_userlog (log,date,id_user) VALUES('$msg', now(), $id)") or die(mysql_error());
}

header("Content-Type: text/html; charset=utf-8");

// This array starts empty here and is filled by pages
$_SESSION['preload_images'] = array();
$extra_css = array();
$extra_js = array();


?>