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

global $Client;
?>
  <b>Social :</b><br/>
  <input type="text" class="tva" name="vat_number" value="<?= $Client->vat_number ?>" class="vat_number" /><br>
  <input type="text" class="siren" name="siren" value="<?= $Client->siren ?>" class="siren" />&nbsp;<?= $Client->link_societe ?><br>
  <?// Interne  ?>
  <b>Interne : </b><br/>
  <select style="font-size: 10px; width: 200px;" name="id_company_type"><?php
  $result = mysql_query("SELECT id_company_type,nom FROM webfinance_company_types ORDER BY nom");
  while ($t = mysql_fetch_object($result)) {
    printf('<option value="%s" %s>%s</option>'."\n", $t->id_company_type, ($Client->id_company_type == $t->id_company_type)?"selected":"", ucfirst($t->nom));
  }
  ?>
  </select><br/>
