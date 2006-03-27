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
  <b>Social :</b><br/>
  <input type="text" class="tva" name="vat_number" value="<?= $Client->data->vat_number ?>" class="vat_number" /><br>
  <input type="text" class="siren" name="siren" value="<?= $Client->data->siren ?>" class="siren" /><br>
  <?// Interne  ?>
  <b>Interne : </b><br/>
  <select style="font-size: 10px; width: 200px;" name="state"><?php
  $choices = array('client', 'prospect', 'archive', 'fournisseur');
  foreach ($choices as $c) {
    printf('<option value="%s" %s>%s</option>'."\n", $c, ($Client->data->state == $c)?"selected":"", ucfirst($c));
  }
  ?>
  </select><br/>
