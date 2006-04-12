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

require("inc/main.php");
$title = _('Search results');
require("top.php");

// Search fields 
$search_domains = array(
    'companies' => _('Companies'),
    'invoices' => _('Invoices'),
    'transactions' => _('Transactions'),
);

// Params can be received by GET or POST
extract($_GET);
extract($_POST);

// If comming from left nav search field search in every subcategories
if (!is_array($search_in)) {
  $search_in = array();
  foreach ($search_domains as $c=>$l) {
    $search_in[$c] = 'on';
  }
}

?>

<h1><?= _('Search results') ?></h1>

<form action="search.php" method="post" id="main_form">
<div id="advanced_search">
<b><?= _('Advanced search') ?></b>
<table border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td><?= _('Keywords') ?></td>
    <td><input type="text" name="q" value="<?= $q ?>" style="width: 200px;"/></td>
  </tr>
  <tr>
    <td><?= _('Search in') ?></td>
    <td>
      <?php
      foreach ($search_domains as $c=>$l) {
        printf('<input type="checkbox" name="search_in[%s]" %s>&nbsp;%s', $c, ($search_in[$c])?" checked":"", $l );
      }
      ?>
    </td>
  </tr>
  <tr>
    <td style="text-align: center;" colspan="2"><input type="submit" value="<?= _('Search again') ?>" /></td>
  </tr>
</table>
</div>
</form>

<?php
// Display some results

if ($search_in['invoices']) {
  $result = mysql_query("SELECT f.id_facture,id_client,sum(fl.qtt*fl.prix_ht) as total_facture,
                                f.extra_top, f.extra_bottom, f.commentaire 
                         FROM webfinance_invoices AS f, webfinance_invoice_rows fl
                         WHERE fl.id_facture=f.id_facture
                         AND (
                          f.extra_top LIKE '%$q%' OR 
                          f.extra_bottom LIKE '%$q%' OR 
                          f.commentaire LIKE '%$q%' 
                        ) GROUP BY f.id_facture") or die(mysql_error());

  if (mysql_num_rows($result)) {
    print "<h2>"._('Results found in invoices')."</h2>";
    print '<ul class="search_results">';


    while ($found = mysql_fetch_object($result)) {
      $invoice = new Facture();
      $data = $invoice->getInfos($found->id_facture);
      print "<pre>";
      print_r($data);
      print "</pre>";
    }
  }
}

?>

<?php
$Revision = '$Revision$';
require("bottom.php");
?>
