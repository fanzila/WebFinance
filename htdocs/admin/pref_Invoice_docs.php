<?php
/*
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
<?=$language_form?>
<form onchange="formChanged()" id="main_form" action="save_invoice_docs.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="action" value="mail_quote_<?=$mail_tpl_lang?>" />
	<input type="hidden" name="mail_tpl_lang" value="<?=$mail_tpl_lang?>" />
<br />
<a href="invoice_docs/docs_<?=$mail_tpl_lang?>.pdf">> View current doc</a><br /><br />
<label for="file">Filename:</label><input type="file" name="file" id="file" /> <b>(ONLY PDF)</b>
<br /><br />
<input type="submit" value="<?= _("Save") ?> <?=$mail_tpl_lang?> version" />

</form>
