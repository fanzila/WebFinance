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

class Client {
  var $id = -1;
  var $data = null;

  function _setInfos() {
    $result = mysql_query("SELECT * FROM client WHERE id_client=".$this->id) or die("Client:_setInfos ".mysql_error());
    $this->data = mysql_fetch_object($result);
    mysql_free_result($result);
  }

  function Client($id = null) {
    if (is_numeric($id)) {
      $this->id = $id;
      $this->_setInfos();
    }
  }
  
  function setId($id) {
    if (is_numeric($id)) {
      $this->id = $id;
      $this->_setInfos();
    }
  }
}
