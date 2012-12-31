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

require("../inc/main.php");
must_login();

$extension = end(explode(".", $_FILES["file"]["name"]));
if($extension != "pdf" AND $_FILES["error"] == false) 
	exit('Wrong file type (ONLY PDF)');

move_uploaded_file($_FILES["file"]["tmp_name"],
"invoice_docs/" . 'docs_'.$_POST['mail_tpl_lang'].'.pdf');

header("Location: preferences.php?tab=Invoice_docs&mail_tpl_lang=$_POST[mail_tpl_lang]");
exit;

?>
