<?php
/*
 Copyright (C) 2004-2012 NBI SARL, ISVTEC SARL

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

<br/>
    <div style="overflow: auto; height: 500px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="1">

<tr>
   <td style="border-bottom: solid 1px #777;" colspan="4">
          <b style="font-size: 16px;">Contracts</b>
   </td>
</tr>

<form method="POST" action="/prospection/contract/download.php">
  <input type="hidden" name="company_id" value="<?=$_GET[id]?>" />

<tr>
    <td>
      <select name="template">

<? 
$contract = new WebfinanceContract;
foreach($contract->ListTemplates() as $template) { ?>

      <option value="<?=$template?>"><?=ucfirst($template)?></option>

<? } ?>

 </td>
</tr>

<tr> <td> <input type="submit" name="download" value="Download"/> </td> </tr>
</select>

</form>

 </table>


</div>
