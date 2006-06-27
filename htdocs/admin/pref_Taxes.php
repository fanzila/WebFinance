<script type="text/javascript">
   function confirmDeleteTaxe(id,txt) {
  if (confirm(txt)) {
    window.location = 'save_taxes.php?action=delete&id='+id;
  }
}
</script>

<form action="save_taxes.php" method="post">
<input type="hidden" name="action" value="taxe"/>

<table border="0" cellspacing="0" cellpadding="3" class="framed">
<tr style="text-align: center;" class="row_header">
  <td><?= _('Taxe') ?></td>
  <td><?= _('Value') ?></td>
  <td><?= _('Actions') ?></td>
</tr>

<?php
   $txt = _('Confirm ?');
$result = mysql_query("SELECT id_pref, type_pref, value FROM webfinance_pref WHERE type_pref RLIKE '^taxe_'")
   or wf_mysqldie();
while ($c = mysql_fetch_assoc($result)) {
  extract($c);

  $taxe_name=preg_replace('/^taxe_/','',$type_pref);

 print <<<EOF
  <tr class="row_even">
   <td><input type="text" name="taxes[$id_pref][taxe]" value="$taxe_name" style="width: 100px;" /></td>
   <td><input type="text" name="taxes[$id_pref][value]" value="$value" style="width: 100px;" /></td>
   <td align="center"><a href="javascript:confirmDeleteTaxe($id_pref,'$txt');"><img src="/imgs/icons/delete.gif" /></a>
  </tr>
EOF;
}

?>
<tr style="background: #ceffce;">
  <td><input type="text" name="taxes[new][taxe]" value="" style="width: 100px;" /></td>
  <td><input type="text" name="taxes[new][value]" value="" style="width: 100px;" /></td>
  <td></td>
</tr>
<tr class="row_even">
  <td style="text-align: center;" colspan="3"><input type="submit" value="<?= _('Save') ?>" /></td>
</table>

</form>
