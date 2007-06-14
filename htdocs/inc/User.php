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
?>
<?php
//
// This file is part of Â« Webfinance Â»
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
<?php

class User {
  function User() {
  }

  function getInfo($id_user = "") {
    if ($id_user == "") {
      $id_user = $_SESSION['id_user'];
    }
    $result = mysql_query("SELECT id_user, last_name, first_name, login,
                                  email, disabled, last_login, creation_date,
                                  role, modification_date,
                                  date_format(creation_date,'%d/%m/%Y') as nice_creation_date,
                                  date_format(modification_date,'%d/%m/%Y') as nice_modification_date
                           FROM webfinance_users WHERE id_user=$id_user")
      or wf_mysqldie();

    $user = mysql_fetch_object($result);
    $this->userData = $user;
    mysql_free_result($result);
    $this->getPrefs();
    return $user;
  }

  function getInfos($id_user="") {
    return $this->getInfo($id_user);
  }

  // nbi_login
  function login($data) {
    if (isset($data['login']) && isset($data['password']) && (preg_match("/^[a-zA-Z0-9]+$/", $data['login'])) && (preg_match("/^[a-zA-Z0-9\/\.\-]+$/", $data['password']))) {
      $result = mysql_query("SELECT count(id_user) FROM webfinance_users WHERE login='".$data['login']."' AND md5('".$data['password']."')=password AND disabled=0");
      list($exists) = mysql_fetch_array($result);
      mysql_free_result($result);
      if ($exists) {
        $result = mysql_query("SELECT id_user FROM webfinance_users WHERE login='".$data['login']."' AND md5('".$data['password']."')=password");
        list($id_user) = mysql_fetch_array($result);
        mysql_free_result($result);
        $_SESSION['id_user'] = $id_user;

        $result = mysql_query("UPDATE webfinance_users SET last_login=now() WHERE id_user=$id_user");
        logmessage("Connected");
        return $id_user;
      }
    } else {
      return 0;
    }
 }

  function logout($url = "/") {
    logmessage("Disconnect");
    $_SESSION['id_user'] = -1;
    session_destroy();
    header("Location: ". $url);
    die();
  }

  function isLogued() {
    if (!is_array($_SESSION))
      return 0;
    if(isset($_SESSION['id_user']) AND $_SESSION['id_user']>0 )
      return $this->exists($_SESSION['id_user']);
    else
      return 0;
  }

  function random_password() {
    $passwd .= chr(97+rand(1,25));
    $passwd .= chr(97+rand(1,25));
    $passwd .= rand(0,9);
    $passwd .= rand(0,9);
    $passwd .= chr(97+rand(1,25));
    $passwd .= chr(97+rand(1,25));
    $passwd .= rand(0,9);
    $passwd .= rand(0,9);

    return $passwd;
  }

//   function isAdmin($id_user) {
//     $result = mysql_query("SELECT admin=1 FROM webfinance_users WHERE id_user=$id_user");
//     list($is_admin) = mysql_fetch_array($result);
//     mysql_free_result($result);
//
//     return $is_admin;
//   }

  function isAuthorized($roles, $id_user=null){
    if ($id_user == null)
      $id_user = $_SESSION['id_user'];

    if ($roles == "any") // The special "any" role is granted to all users (and non users for that matter)
      return true;

    $req=mysql_query("SELECT role FROM webfinance_users  WHERE id_user=$id_user") or wf_mysqldie();
    list($user_roles)=mysql_fetch_array($req);

    $user_roles=explode(",",$user_roles);
    foreach($user_roles as $role){
      if(preg_match("!(,|^)$role(,|$)!",$roles)) {
        return true;
      }
    }
    return false;
  }


  function hasRole($role,$id_user) {
    $result = mysql_query("SELECT COUNT(*) FROM webfinance_users WHERE id_user=$id_user AND role RLIKE '(^|,)$role(,|$)' ") or wf_mysqldie();
    list($hasRole) = mysql_fetch_array($result);
    mysql_free_result($result);

    return $hasRole;
  }

  function saveData($data=null) {
    if (!is_array($data))
      return false;

    if (! $this->isAuthorized('admin,manager') ) {
      $_SESSION['message'] = _('Sorry, you are not administrator');
      $_SESSION['error'] = 1;
      return false;
    }

    extract($data);

    if (is_array($data['role'])) {
      $roles=implode(",",$data['role']);
    } else {
      $roles = '';
    }

    if( !($this->existsLogin($login)) OR ($this->existsLogin($login) == $id_user) ){

      if(empty($password)){
	$q = sprintf("UPDATE webfinance_users SET first_name='%s', last_name='%s', login='%s', email='%s', disabled=%d, role='%s',
                         modification_date=now()
                  WHERE id_user=%d",
		     $first_name, $last_name, $login, $email, ($disabled == "on")?1:0, $roles,
		     $id_user );
      }else{
	$q = sprintf("UPDATE webfinance_users SET first_name='%s', last_name='%s', login='%s', email='%s', disabled=%d, role='%s',
                         password=md5('%s'), modification_date=now()
                  WHERE id_user=%d",
		     $first_name, $last_name, $login, $email, ($disabled == "on")?1:0, $roles, $password,
		     $id_user );
      }
      mysql_query($q) or wf_mysqldie();
      logmessage("Modified user:$id_user ($last_name $first_name)");
      $_SESSION['message'] = _("Data saved");

    }else{
      $_SESSION['message'] =  _("Sorry, this user already exists!");
      $_SESSION['error'] = 1;
    }
    return $id_user;

  }

  function existsLogin($login){
    $result = mysql_query("SELECT id_user FROM webfinance_users WHERE login='$login'") or wf_mysqldie();
    if(mysql_num_rows($result)>0){
      list($exists) = mysql_fetch_array($result);
      return $exists;
    }else{
      return 0;
    }

  }

  function exists($id_user){
    $result = mysql_query("SELECT count(*) FROM webfinance_users WHERE id_user=$id_user") or wf_mysqldie();
    list($exists) = mysql_fetch_array($result);
    return $exists;
  }

  function createUser($data=null) {
    if (! $this->isAuthorized('admin,manager') ) {
      $_SESSION['message'] = _("You aren't the Administrator");
      $_SESSION['error'] = 1;
      return false;
    }
    extract($data);
    $roles=implode(",",$data['role']);

    if($this->existsLogin($login)){
      $_SESSION['message'] =  _("Sorry, this user already exists!");
      $_SESSION['error'] = 1;
      return -1;
    }else{

      if(empty($password))
	$password=$this->randomPass();

      $q = sprintf("INSERT INTO webfinance_users (login, first_name, last_name, password, email, role, disabled,  modification_date, creation_date) ".
		   "VALUES('%s', '%s', '%s', md5('%s'), '%s','%s',  %d, now(), now() )",
		   $login, $first_name, $last_name, $password, $email, $roles, ($disabled == "on")?1:0 );
      mysql_query($q) or wf_mysqldie();

      $new_id_user=mysql_insert_id();

      logmessage("Created new user user:$new_id_user ($last_name $first_name)");
      $_SESSION['message'] = _("User added");

      return $new_id_user;
    }
  }

  function delete($id_user) {
    if (! $this->isAuthorized('admin,manager')) {
      $_SESSION['message'] =  _("You aren't the Administrator");
      $_SESSION['error'] = 1;
      return false;
    }
    $result = mysql_query("SELECT login,first_name,last_name FROM webfinance_users WHERE id_user=$id_user");
    list($login, $prenom, $nom) = mysql_fetch_array($result);
    mysql_free_result($result);
    logmessage("Deleted user $login ($prenom $nom)");
    mysql_query("DELETE FROM webfinance_users WHERE id_user=$id_user");
    $_SESSION['message'] = _("User deleted");
  }

  function randomPass() {
    $passwd .= chr(97+rand(1,25));
    $passwd .= chr(97+rand(1,25));
    $passwd .= rand(0,9);
    $passwd .= rand(0,9);
    $passwd .= chr(97+rand(1,25));
    $passwd .= chr(97+rand(1,25));
    $passwd .= rand(0,9);
    $passwd .= rand(0,9);

    return $passwd;
  }

  function changePass($id_user, $old_pass, $new_pass) {
    $result = mysql_query("SELECT count(*) FROM webfinance_users WHERE id_user=$id_user AND password=md5('$old_pass')");
    list($ok) = mysql_fetch_array($result);
    mysql_free_result($result);

    if ($ok) {
      mysql_query("UPDATE webfinance_users SET password=md5('$new_pass') WHERE id_user=$id_user");
      logmessage("Changed password for user:$id_user");
      $_SESSION['message'] = _('Password changed');
    } else {
      $_SESSION['message'] = _('Wrong password');
      $_SESSION['error'] = 1;
      return false;
    }
  }

  function setPass($id_user, $new_pass) {
    mysql_query("UPDATE webfinance_users SET password=md5('$new_pass') WHERE id_user=$id_user")
      or wf_mysqldie();
    logmessage("Changed password for user:$id_user");
    $_SESSION['message'] = _('Password changed');
    return true;
  }

  // Expects an object
  function setPrefs($prefs) {
    $data = base64_encode(serialize($prefs));
    $result = mysql_query("SELECT count(*) FROM webfinance_pref WHERE owner=".$_SESSION['id_user']." AND type_pref='user_pref'") or wf_mysqldie();
    list($has_pref) = mysql_fetch_array($result);
    mysql_free_result($result);
    if ($has_pref) {
      mysql_query("UPDATE webfinance_pref SET value='$data' WHERE owner=".$_SESSION['id_user']." AND type_pref='user_pref'") or wf_mysqldie();
    } else {
      mysql_query("INSERT INTO webfinance_pref (value,owner,type_pref) VALUES('$data', ".$_SESSION['id_user'].",'user_pref')") or wf_mysqldie();
    }
    $_SESSION['message'] = _('The data has been saved');
  }

  // Expects an object
  function getPrefs() {
    $result = mysql_query("SELECT value FROM webfinance_pref WHERE owner=".$_SESSION['id_user']." AND type_pref='user_pref'") or wf_mysqldie();
    list($data) = mysql_fetch_array($result);
    $this->prefs = unserialize(base64_decode($data));

    if (!isset($this->prefs->theme)) {
      $this->prefs->theme = "main";
    }
    if(!isset($this->prefs->graphgrid)){
      $this->prefs->graphgrid = 0;
    }
  }

  function sendInfo($id_user,$passwd){
    require("/usr/share/php/libphp-phpmailer/class.phpmailer.php");
    //récupérer les info de l'utilisateur
    $user = $this->getInfo($id_user);

    //récupérer les info sur la société
    $result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='societe' AND owner=-1")
      or wf_mysqldie();
    list($value) = mysql_fetch_array($result);
    mysql_free_result($result);
    $societe = unserialize(base64_decode($value));

    $result = mysql_query("SELECT value FROM webfinance_pref WHERE type_pref='mail_user'") or wf_mysqldie();
    list($data) = mysql_fetch_array($result);
    $pref = unserialize(base64_decode($data));

    $patterns=array(
		    '/%%COMPANY%%/' ,
		    '/%%URL_COMPANY%%/',
		    '/%%FIRST_NAME%%/' ,
		    '/%%LAST_NAME%%/' ,
		    '/%%LOGIN%%/' ,
		    '/%%PASSWORD%%/');
    $replacements=array(
			$societe->raison_sociale ,
			$societe->wf_url,
			$user->first_name,
			$user->last_name,
			$user->login,
			$passwd );

    //subject
    if(isset($pref->subject) && !empty($pref->body)){
      $subject = preg_replace($patterns, $replacements, stripslashes(utf8_decode($pref->subject)) );
    }else
      $subject= $societe->raison_sociale.": "._('your account informations');

    //body
    if(isset($pref->body) AND !empty($pref->body) ){
      $body = preg_replace($patterns, $replacements,  stripslashes(utf8_decode($pref->body)) );
    }else{
      $body = _('You receive this mail because you have an account ...')."\n";
      $body .= _('Name').": ".$user->first_name." ".$user->last_name."\n";
      $body .= _('Login').": ".$user->login."\n";
      $body .= _('Password').": ".$passwd."\n";
    }

    //compléter l'entête de l'email
    $mail = new PHPMailer();
    if(preg_match('/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-Za-z]{2,4}$/',$societe->email) )
      $mail->From = $from;
    else
      $mail->From = $societe->email;

    $mail->CharSet = "UTF-8";

    $mail->FromName = $societe->raison_sociale;

    $mail->AddAddress($user->email, $user->first_name." ".$user->last_name );

    $mail->Subject = $subject;
    $mail->Body = $body;

    $mail->WordWrap = 80;

    if(!$mail->Send()){
      echo _("User information was not sent");
      echo "Mailer Error: " . $mail->ErrorInfo;
      $_SESSION['message']=_('User information was not sent');
      $_SESSION['error'] = 1;
    }else
      $_SESSION['message']=_('User information sent');

  }

}

?>
