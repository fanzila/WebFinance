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
<?php
require("../inc/main.php");

if(isset($_GET['action'])=='file' AND isset($_GET['id'])){
  if(isset($_GET['type']) AND $_GET['type'] != "")
    show_file($_GET['id'],$_GET['type']);
  else
    show_file($_GET['id'],"transactions");
 }



function show_file($id_transaction,$type){
  $result = mysql_query("SELECT ".
			"file, ".
			"file_type as type, ".
			"file_name as name ".
			"FROM webfinance_$type ".
			"WHERE id=".$id_transaction)
    or wf_mysqldie();
  if(mysql_num_rows($result)>0){
    $afile=mysql_fetch_assoc($result);
    mysql_free_result($result);
    $file_name=$afile['name'];
    $file_type=$afile['type'];
    $file=$afile['file'];
    header ('Content-type: $file_type');
    header ("Content-Disposition: attachment; filename=$file_name");
    echo $file;
    //echo base64_decode($afile['file']);
  }else
    echo "File not found";
  exit;
}


?>