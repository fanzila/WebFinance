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
//
/// Transaction.php
/// 
/// Transaction object aims to manage a transaction (operation in a bank
/// account) namely
// 

class Transaction {
  var $id = -1;

  function _getInfos() {
    $result = mysql_query("SELECT id_account, id_category, text, amount, type, document, date, date_update, comment, lettrage, id_invoice
                           FROM webfinance_transactions 
                           WHERE id=".$this->id) or wf_mysqldie("Transaction::_getInfos");
    if (mysql_num_rows($result)) {
      $data = mysql_fetch_assoc($result);
      foreach ($data as $n=>$v) { $this->$n = $v; }
      mysql_free_result($result);
    }
    
  function Transaction($id = null) {
    if (is_numeric($id)) {
      $this->id = $id;
      $this->_getInfos();
    }
  }

  function setId($id) {
    if (is_numeric($id)) {
      $this->id = $id;
      $this->_getInfos();
    }
  }
}
