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

class Transaction extends WFO {
  var $id = -1;
  var $id_category = 1;
  var $text = "";
  var $date = null;
  var $amount = null;

  function _getInfos() {
    if(is_numeric($this->id) && $this->id>0){
      $result = $this->SQL("SELECT id_account, id_category, text, amount, type, document, date, date_update, comment, lettrage, id_invoice
                           FROM webfinance_transactions
                           WHERE id=".$this->id);
      if (mysql_num_rows($result)) {
	$data = mysql_fetch_assoc($result);
	foreach ($data as $n=>$v) {
	  $this->$n = $v;
	}
	mysql_free_result($result);
      }
    }
  }

  function Transaction($id = null) {
    if (is_numeric($id) && $id>0) {
      $this->id = $id;
      $this->_getInfos();
    }
  }

  function setId($id) {
    if (is_numeric($id) && $id>0) {
      $this->id = $id;
      $this->_getInfos();
    }
  }

  /**
   * return an array of related invoices
   * return array()
   */
  function getInvoices($id){
    $id_invoices = array();
    if (is_numeric($id) && $id>0) {
      $result = $this->SQL("SELECT id_invoice FROM webfinance_transaction_invoice WHERE id_transaction=".$id);
      while(list($id_invoice) = mysql_fecth_assoc($result)){
	$id_invoices[] = $id_invoice;
      }
      mysql_free_result($result);
    }
    return $id_invoices;
  }

  /** Returns an array containing the id of possible matching invoices for this
      transaction.

      The idea of this method is mainly to help the importation of bank
      transaction from banking internet sites. We are given an amount and a
      text for a transaction and we try to find the invoices that are paid by
      this transaction.

      This might be tricky :
        1) invoices with the same amount are not uncommon
        2) some clients pay several invoices with the same transfer or check
        3) checks can be sent to the bank by pack of 2 or more
      So we must not assume the amount of the transaction will match closely
      with the invoice. It can also match for two or more invoices.

      FIXME : we need to return something to caller to identify "accuracy" of
      find : are we positive that the returned id_invoices match the
      transaction (case : only one unpaid invoice matches this amount) ? Is it
      only a guess (case : two unpaid invoices have exactly the same amount) ?
      The caller needs this info to choose UI to display.

  */
  function findRelatedInvoice() {
    if ($this->amount == null) {
      return array();
    }

    $matches = array();
    $this->_getInfos(); // Populate $this if empty

    /**
    First the simple case : user has already explicitly linked this transaction
    with an invoice. We just return that invoice id. Over
    */
    if ($this->id_invoice) {
      array_push($matches, $this->id_invoice);
      return $matches;
    }

    /**
     Now whe have to guess or find the invoice(s)... try to be clever.

     1) Common case : client pays one invoice by bank transfer. Text of imported transaction
        contains the name of the client and amount matches only one invoice. We
        search unpaid invoices where client name matches the transaction
        text.

        Societe Générale (and maybe some others) truncate texts comming from
        other banks to 19 chars so the match on the name is done on the same
        length. FIXME maybe there is a more elegant way  to do this trimming
        (like increasingly trim chars from the name until it maches ?)
    */
    $result = $this->SQL("SELECT fl.id_facture,1.196*sum(fl.qtt*fl.prix_ht) AS total, CONCAT('%', left(c.nom,19), '%') as re_clientname, c.nom
                           FROM webfinance_invoice_rows AS fl, webfinance_invoices AS f, webfinance_clients AS c
                           WHERE c.id_client=f.id_client
                           AND f.type_doc = 'facture'
                           AND f.is_paye = 0
                           AND f.id_facture=fl.id_facture
                           GROUP BY f.id_facture
                           HAVING '".$this->text."' LIKE re_clientname AND total=".$this->amount);
    if (mysql_num_rows($result) == 1) {
      // Only one it must be THE one return it
      array_push( $matches, mysql_fetch_field($result, 0) );
      return $result;
    } elseif (mysql_num_rows($result) > 1) {
      // More than one, we have equaly possible candidates store them and search deeper
      while ($id = mysql_fetch_field($result, 0)) {
        array_push($matches, $id);
      }
    }

    /**
      2) Less simple but still common case : a client pays two or more invoices
         with one bank transfer. We count unpaid invoices where client name
         matches the text of the transaction. Then we add the 2 oldest invoices
         and see if it matches transaction, if not we try with 3 oldest invoices,
         4 oldest invoices... The first matching combination is considered good

         This assumes the clients pays the oldest invoices first :)
    */
    $result = $this->SQL("SELECT count(*), CONCAT('%', left(c.nom,10), '%') as re_clientname, c.nom
                           FROM webfinance_invoice_rows AS fl, webfinance_invoices AS f, webfinance_clients AS c
                           WHERE c.id_client=f.id_client
                           AND f.type_doc = 'facture'
                           AND f.is_paye = 0
                           AND f.id_facture=fl.id_facture
                           GROUP BY f.id_client
                           HAVING '".$this->text."' LIKE re_clientname");
    list($nb,$foo,$bar) = mysql_fetch_array($result);
    mysql_free_result($result);
    for ($i = 2; $i<=$nb ; $i++) {
      $result = $this->SQL("SELECT SUM(total),id_client FROM (
                               SELECT fl.id_facture,1.196*sum(fl.qtt*fl.prix_ht) AS total, CONCAT('%', left(c.nom,10), '%') as re_clientname, c.nom, c.id_client
                               FROM webfinance_invoice_rows AS fl, webfinance_invoices AS f, webfinance_clients AS c
                               WHERE c.id_client=f.id_client
                               AND f.type_doc = 'facture'
                               AND f.is_paye = 0
                               AND f.id_facture=fl.id_facture
                               GROUP BY f.id_facture
                               HAVING '".$this->text."' LIKE re_clientname
                               ORDER BY f.date_facture DESC LIMIT $i
                             ) AS t GROUP BY id_client");
      list($tmp_total, $id_client) = mysql_fetch_array($result);
      mysql_free_result($result);
      // print "DEBUG : Oldest $i invoices totalize $tmp_total<br/>";

      if ($tmp_total == $this->amount) {
        // The total of oldest $i invoices equals this transaction. Chances are
        // very good that this transaction pays for this $i invoices. Now we
        // have to find back the id of this invoices (the SUM in last query
        // makes it impossible to have IDs AND total amount)
        $result = $this->SQL("SELECT f.id_facture
                               FROM webfinance_invoices AS f
                               WHERE f.id_client=$id_client
                               ORDER BY f.date_facture DESC LIMIT $i");
        while (list($id) = mysql_fetch_array($result) ) {
          array_push($matches, $id);
        }
        return $matches;
        mysql_free_result($result);
      }
    }

    /**
      3) Another common case : Several clients pays their invoices by check. We
         send those check all at once to the bank. The transaction apearing in
         the account only shows the total of all checks. Try to find the
         corresponding invoices... The logic is the same as for 2) but we don't
         restrict our search to one client : we do not match on the text of the
         transaction (FIXME : Might also be a match like '.*Remise de
         cheques.*' would work for me)
     */

    // FIXME : copy paste and adapt above logic

    return $matches;
  }

  function save() {
    // print_r($this);
    if (!is_numeric($this->id) || $this->id<1) {
      if ($this->text == "") {
	die("Transaction::save Cannot add a transaction without a text");
      }
      if ($this->date == "") {
	$this->date = strftime("%Y-%m-%d");
      }
      $q = sprintf("INSERT INTO webfinance_transactions (id_account, id_category, text, amount, type, document, date, comment, lettrage, id_invoice)
                                                  VALUES(%d,         %d,          '%s', '%s',   '%s', '%s',     '%s', '%s',    %d,       %d)",
                    $this->id_account, $this->id_category, $this->text, $this->amount, $this->type, $this->document, $this->date, $this->comment, $this->lettrage, $this->id_invoice );
    } else {
      $q = sprintf("UPDATE webfinance_transactions SET id_account=%d, id_category=%d, text='%s', amount='%s', type='%s', document='%s', date='%s', comment='%s', lettrage=%d, id_invoice=%d WHERE id=%",
                    $this->id_account, $this->id_category, $this->text, $this->amount, $this->type, $this->document, $this->date, $this->comment, $this->lettrage, $this->id_invoice );
    }
    $this->SQL($q);
  }
}
