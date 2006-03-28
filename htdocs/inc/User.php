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

class User {
  function User() {
  }

  function getInfo($id_user = "") {
    if ($id_user == "") {
      $id_user = $_SESSION['id_user'];
    }
    $result = mysql_query("SELECT *,date_format(creation_date,'%d/%m/%Y') as nice_creation_date,
                                  date_format(modification_date,'%d/%m/%Y') as nice_modification_date
                           FROM user WHERE id_user=$id_user") or nbi_mysqldie();


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
    if ((preg_match("/^[a-zA-Z0-9]+$/", $data['login'])) && (preg_match("/^[a-zA-Z0-9]+$/", $data['password']))) {
      $result = mysql_query("SELECT count(id_user) FROM user WHERE login='".$data['login']."' AND md5('".$data['password']."')=password AND disabled=0");
      list($exists) = mysql_fetch_array($result);
      mysql_free_result($result);
      if ($exists) {
        $result = mysql_query("SELECT id_user FROM user WHERE login='".$data['login']."' AND md5('".$data['password']."')=password");
        list($id_user) = mysql_fetch_array($result);
        mysql_free_result($result);
        $_SESSION['id_user'] = $id_user;

        $result = mysql_query("UPDATE user SET last_login=now() WHERE id_user=$id_user");
        logmessage("Connexion");
        return $id_user;
      }
    } else {
      return 0;
    }
  }

  function logout($url = "") {
    logmessage("Déconnexion");
    $_SESSION['id_user'] = -1;
    session_destroy();
    header("Location: /");
    die();
  }

  function isLogued() {
    if (!is_array($_SESSION)) { return 0; }
    return ($_SESSION['id_user'] > 0);
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

  function isAdmin($id_user) {
    $result = mysql_query("SELECT admin=1 FROM user WHERE id_user=$id_user");
    list($is_admin) = mysql_fetch_array($result);
    mysql_free_result($result);

    return $is_admin;
  }

  function saveData($data=null) {
    if (!is_array($data)) 
      return false;

    if (! $this->isAdmin($_SESSION['id_user'])) {
      $_SESSION['message'] = "Vous n'êtes pas administrateur";
      return false;
    }
    extract($data);

    $q = sprintf("UPDATE user SET first_name='%s', last_name='%s', login='%s', email='%s', disabled=%d, admin=%d,
                         modification_date=now()
                  WHERE id_user=%d",

                  $first_name, $last_name, $login, $email, ($disabled == "on")?1:0, ($admin == "on")?1:0, 
                  $id_user );
    mysql_query($q) or die(mysql_error());
    logmessage("Modification de l'utilisateur user:$id_user");
    $_SESSION['message'] = "Données enregistrées";
  }

  function createUser($data=null) {
    if (! $this->isAdmin($_SESSION['id_user'])) {
      $_SESSION['message'] = "Vous n'êtes pas administrateur";
      return false;
    }
    extract($data);

    $q = sprintf("INSERT INTO user (login, first_name, last_name, password, email, disabled, admin, modification_date, creation_date) 
                  VALUES('%s', '%s', '%s', md5('%s'), '%s', %d, %d, now(), now() )",
                  $login, $first_name, $last_name, $this->randomPass(), $email, ($disabled == "on")?1:0, ($admin == "on")?1:0 );
    mysql_query($q) or die(mysql_error());
    $result = mysql_query("SELECT id_user FROM user WHERE creation_date>date_sub(now(), INTERVAL 1 SECOND)");
    list($new_id_user) = mysql_fetch_array($result);
    mysql_free_result($result);

    logmessage("Création d'un nouvel utilisateur user:$new_id_user");
    $_SESSION['message'] = "Utilisateur créé";

    return $new_id_user;
  }

  function delete($id_user) {
    if (! $this->isAdmin($_SESSION['id_user'])) {
      $_SESSION['message'] = "Vous n'êtes pas administrateur";
      return false;
    }
    $result = mysql_query("SELECT login,first_name,last_name FROM user WHERE id_user=$id_user");
    list($login, $prenom, $nom) = mysql_fetch_array($result);
    mysql_free_result($result);
    logmessage("Suppression de l'utilisateur $login ($prenom $nom)");
    mysql_query("DELETE FROM user WHERE id_user=$id_user");
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
    $result = mysql_query("SELECT count(*) FROM user WHERE id_user=$id_user AND password=md5('$old_pass')");
    list($ok) = mysql_fetch_array($result);
    mysql_free_result($result);

    if ($ok) {
      mysql_query("UPDATE user SET password=md5('$new_pass') WHERE id_user=$id_user");
      logmessage("Mot de passe pour user:$id_user changé");
      $_SESSION['message'] = "Mot de passe modifié";
    } else {
      $_SESSION['message'] = "Ancien mot de passe incorrect";
      return false;
    }
  }

  // Expects an object
  function setPrefs($prefs) {
    $data = base64_encode(serialize($prefs));
    $result = mysql_query("SELECT count(*) FROM pref WHERE owner=".$_SESSION['id_user']." AND type_pref='user_pref'") or die(mysql_error());
    list($has_pref) = mysql_fetch_array($result);
    mysql_free_result($result);
    if ($has_pref) {
      mysql_query("UPDATE pref SET value='$data' WHERE owner=".$_SESSION['id_user']." AND type_pref='user_pref'") or die(mysql_error());
    } else {
      mysql_query("INSERT INTO pref (value,owner,type_pref) VALUES('$data', ".$_SESSION['id_user'].",'user_pref')") or die(mysql_error());
    }
    $_SESSION['message'] = "Vos préférences sont enregistrées";
  }

  // Expects an object
  function getPrefs() {
    $result = mysql_query("SELECT value FROM pref WHERE owner=".$_SESSION['id_user']." AND type_pref='user_pref'") or die(mysql_error());
    list($data) = mysql_fetch_array($result);
    $this->prefs = unserialize(base64_decode($data));
  }
}

?>
