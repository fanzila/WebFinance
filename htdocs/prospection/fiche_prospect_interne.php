<?php 
// 
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
// 
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
?>
  <b>Social :</b><br/>
  <input type="text" class="tva" name="vat_number" value="<?= $Client->data->vat_number ?>" class="vat_number" /><br>
  <input type="text" class="siren" name="siren" value="<?= $Client->data->siren ?>" class="siren" /><br>
  <?// Interne  ?>
  <b>Interne : </b><br/>
  <select style="font-size: 10px; width: 200px;" name="id_company_type"><?php
  $result = mysql_query("SELECT id_company_type,nom FROM webfinance_company_types ORDER BY nom");
  while ($t = mysql_fetch_object($result)) {
    printf('<option value="%s" %s>%s</option>'."\n", $t->id_company_type, ($Client->data->id_company_type == $t->id_company_type)?"selected":"", ucfirst($t->nom));
  }
  ?>
  </select><br/>
