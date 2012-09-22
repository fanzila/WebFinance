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

$document = new WebfinanceDocument;
?>
<script type="text/javascript" language="javascript"
  src="/js/ask_confirmation.js"></script>

<br/>
    <div style="overflow: auto; width: 700px; height: 500px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="1">

<tr>
   <td style="border-bottom: solid 1px #777;" colspan="3">
          <b style="font-size: 16px;">Documents</b>
   </td>
</tr>

<?php
    $query = 'SELECT id, date, filename, description '.
    'FROM document '.
    "WHERE id_client = $_GET[id] ".
    'ORDER BY date';

$result = mysql_query($query)
  or die("$query ".mysql_error());

foreach($document->ListByCompany($_GET['id']) as $filename => $file)
{
?>

<tr class="facture_line" onmouseover="return escape(\'%s\');" valign="middle">
    <td nowrap><?=date('Y-m-d', $file['mtime'])?></td>
    <td nowrap><?=round($file['size']/1024)?>kB</td>
 <td nowrap><?=$filename?></td>

 <td width="100%" style="text-align: right;" nowrap><a href="/prospection/document/download.php?company_id=<?=$_GET[id]?>&file=<?=urlencode($filename)?>"><img src="/imgs/icons/pdf.png" border="0"></a><a href="/prospection/document/delete.php?company_id=<?=$_GET[id]?>&filename=<?=urlencode($filename)?>" onclick="return ask_confirmation('Are you sure you want to delete this file?')"><img src="/imgs/icons/delete.png" border="0"></a></td>
</tr>

<?
}
?>

 </table>

<br/>
<br/>

 <table width="100%" border="0" cellspacing="0" cellpadding="1">
<tr>
   <td style="border-bottom: solid 1px #777;" colspan="2">
          <b style="font-size: 16px;">Upload</b>
   </td>
</tr>
<tr>
  <td>
     <!-- Ugly, close the previous bloody global form. I mean *global* form! -->
     </form>
     <form method="POST" action="/prospection/document/upload.php"
       enctype="multipart/form-data">
       <input type="file" name="file" />
       <input type="hidden" name="company_id" value="<?=$_GET[id]?>" />
       <input type="submit" name="upload" value="Upload"/>
     </form>
  </td>
</tr>
 </table>


</div>
