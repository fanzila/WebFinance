<?php
/*
 Copyright (C) 2004-2006 NBI SARL, ISVTEC SARL

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
@ini_set('session.gc_maxlifetime',3600);
session_start();

//Get IE out of here
if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
	echo "Webfinance does not fit with Internet Explorer, please be kind with yourself and use a working browser like <a href='http://www.mozilla.org/'>Firefox</a>.";
	exit;
}

require_once("dbconnect.php");
require_once("WFO.php");
require_once("User.php");
require_once("Facture.php");
require_once("Client.php");
require_once("File.php");
require_once("FileTransaction.php");
require_once("TabStrip.php");
require_once("gettext.php");
require_once("Debug.php");

$_SESSION['debug'] = WF_DEBUG;
if(WF_DEBUG_ALL){
  $debug = new Debug($_SERVER['DOCUMENT_ROOT'].'/../logs/debug.log') ;
  $debug->start();
 }
if(WF_DEBUG){
      $mt_start=getMicroTime();
 }

function GetCompanyInfo() {

	// Get my company info (address...)
	$result = mysql_query('SELECT value ' .
							'FROM webfinance_pref '.
							"WHERE type_pref='societe' AND owner=-1");

	if (mysql_num_rows($result) != 1)
	  die(_("You didn't setup your company address and name. ".
			"<a href='../admin/societe'>Go to 'Admin' and " .
			"'My company'</a>"));

	list($value) = mysql_fetch_array($result);
	mysql_free_result($result);

	return unserialize(base64_decode($value));
} 

function parselogline($str) {
  if (preg_match("/(user|fa|client):([0-9]+)/", $str)) {
    while (preg_match("/(user|fa|client):([0-9]+)/", $str, $matches)) {
      switch ($matches[1]) {
        case "fa":
          $result = mysql_query("SELECT num_facture FROM webfinance_invoices WHERE id_facture=".$matches[2]);
          list($num_facture) = mysql_fetch_array($result);
          mysql_free_result($result);
          if (empty($num_facture)) {
            $str = preg_replace("/".$matches[0]."/", "<i>"._('invoice deleted')."</i>", $str);
          } else {
            $str = preg_replace("/".$matches[0]."/", '<a href="/prospection/edit_facture.php?id_facture='.$matches[2].'">'.$num_facture.'</a> <a href="/prospection/gen_facture.php?id='.$matches[2].'"><img src="/imgs/icons/pdf.png" valign="bottom"></a>', $str);
          }
          break;
        case "user":
          $result = mysql_query("SELECT login FROM webfinance_users  WHERE id_user=".$matches[2]);
          list($login) = mysql_fetch_array($result);
          mysql_free_result($result);
          $str = preg_replace("/".$matches[0]."/", '<a href="/admin/fiche_user.php?id='.$matches[2].'">'.$login.'</a>', $str);
          break;
        case "client":
          $result = mysql_query("SELECT nom FROM webfinance_clients WHERE id_client=".$matches[2]);
          list($client) = mysql_fetch_array($result);
          mysql_free_result($result);
          $str = preg_replace("/".$matches[0]."/", '<a href="/prospection/fiche_prospect.php?id='.$matches[2].'">'.$client.'</a>', $str);
          break;
      }
    }
  }
  return $str;
}



function makeCadre($html) {
    echo <<<EOF
        <table border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td class="cadre_tl">&nbsp;</td>
          <td class="cadre_t">&nbsp;</td>
          <td class="cadre_tr">&nbsp;</td>
        </tr>
        <tr>
          <td class="cadre_l">&nbsp;</td>
          <td class="cadre">
          $html
          </td>
          <td class="cadre_r">&nbsp;</td>
        </tr>
        <tr>
          <td class="cadre_bl">&nbsp;</td>
          <td class="cadre_b">&nbsp;</td>
          <td class="cadre_br">&nbsp;</td>
        </tr>
        </table>
EOF;

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

function random_int($length=15) {
  $rand='';

  for($i=0; $i<$length; $i++) {
    $rand.=rand(0,9);
  }

  return $rand;
 }

// Logs a message ala syslog
function logmessage($msg, $id_client = 'NULL', $id_facture = 'NULL') 
{
    $id = (empty($_SESSION['id_user']))?-1:$_SESSION['id_user'];
    $msg = preg_replace("/'/", "\\'", $msg );
    $msg = preg_replace('/"/', "\\'", $msg );
    
    $query = 
        sprintf("INSERT INTO webfinance_userlog " .
                " (log,date,id_user,id_client,id_facture) VALUES('%s', now(), %s, %s, %s) ", $msg, $id, $id_client,$id_facture);

    mysql_query($query) or wf_mysqldie();
}

// crée un champ date avec calendrier dans un formulaire
// Params :
//   $input_name => field name
//   $default_time => Unix timestamp of defatuls field value defaults to time()
//   $autosubmit => if true the selection of a date will close the popup and submit the form
//   $input_id => id of the input field defaults to input_name. You need to specify a plain string if input_name contains "[" or "]"
//   $extra_style => CSS override
function makeDateField($input_name, $defaulttime=null, $autosubmit=0, $input_id=null, $extra_style="") {

  if (!isset($defaulttime)) { $defaulttime = time(); }
  if (!isset($input_id)) { $input_id = $input_name; }

  if ($defaulttime == -1) {
    $nice_date = "";
    $date = "";
  } else {
    $nice_date = strftime('%d/%m/%Y', $defaulttime);
    $date = strftime('%Y%m%d', $defaulttime);
  }
  printf('<input type="text" id="%s" name="%s" class="date_field" value="%s" style="%s">'
        .'<img valign="top" src="/imgs/icons/calendrier.gif" onclick="inpagePopup(event, this, 200, 230, \'/calendar_popup.php?field=%s&jour=%s&autosubmit=%d\');" />',

        $input_id, $input_name, $nice_date, $extra_style, $input_id, $date, $autosubmit );
}

function wf_mysqldie($message="") {
  if ($_SESSION['debug'] == 1) {
    if (headers_sent()) {
      print '<div style="position: absolute; border: solid 5px red; background: #ffcece; left: 100px top: 100px;"><pre>';
    } else {
      header("Content-Type: text/plain; charset=utf8");
    }
    print "Page : ".$GLOBALS['_SERVER']['SCRIPT_NAME']."\n";
    print "Message : $message\n";
    print "Mysql error : \n";
    print mysql_error();
    if (headers_sent()) {
      print '</pre></div>';
    }
  }
  die();
}

function check_email($param){
  return preg_match('/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-Za-z]{2,4}$/',$param);
}

function getTVA(){
  $result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='taxe_TVA' OR type_pref='taxe_tva' ");
  list($tva) = mysql_fetch_array($result);
  if(!is_numeric($tva))
    $tva=19.6;
  $tva = preg_replace("/,/", ".", $tva); // 19,6 fails to insert as 19.6
  return $tva;
}

function getCurrency($id_bank){
  $result = mysql_query("SELECT value FROM webfinance_pref WHERE id_pref=$id_bank")
    or wf_mysqldie();
  list($value) = mysql_fetch_array($result);
  $account = unserialize(base64_decode($value));
  return array((isset($account->currency))?$account->currency:"",(isset($account->exchange))?$account->exchange:"");
}

//from osh
function format_price($price) {
  if(empty($price))
    return '0 &euro;';

  $price=trim($price);
  $price=str_replace(',', '.', $price);
  $price=money_format('%i', $price);
  $price=preg_replace('/[,\.]00 /', ' ', $price);
  $price=str_replace('EUR', '&euro;', $price);
  $price=str_replace(' ', '&nbsp;', $price);
  return $price;
}

//security
function must_login(){
  if(isset($_SESSION['id_user']) AND $_SESSION['id_user']>0 ){
    $User = new User();
    if ($User->isLogued()){
      return true;
      exit;
    }
  }
  $_SESSION['came_from']=$_SERVER['REQUEST_URI'];
  if(WF_DEBUG){
    echo 'Not logged. Debug mode, please <a href="/login.php">log in</a>';
    include("bottom.php");
    die();
  }
  header("Location: ../login.php");
  die();
}

function getMicroTime() {
	$microsecondes=microtime();
	list($micro,$time)=explode(' ',$microsecondes);
	return($micro+$time);
}

function getWFDirectory(){
# very basic, doesn't work with multiviews
  $dir = str_replace($_SERVER['PHP_SELF'],'/',$_SERVER['SCRIPT_FILENAME']);
  $pattern = '/\.php$|\.html$|\.html$/';

  if(is_dir($dir))
    return $dir."/";
  else if(is_dir( preg_replace($pattern,'/', $dir) ) )
    return preg_replace($pattern,'/', $dir);
  else
    return "";
}

/** 
  * Returns the content of the specified file. Allows us to assign this content to a var.
  */
function get_include_contents($filename) {
  if (is_file($filename)) {
   ob_start();
   include $filename;
   $contents = ob_get_contents();
   ob_end_clean();
   return $contents;
  }
  return false;
}

header("Content-Type: text/html; charset=utf-8");

// This array starts empty here and is filled by pages
$_SESSION['preload_images'] = array();
$extra_js = array();

?>
