<?php
require("../inc/main.php");

if(isset($_GET['action'])=='file' AND isset($_GET['id']))
  show_file($_GET['id']);

function show_file($id_transaction){
  $result = mysql_query("SELECT ".
			"file, ".
			"file_type as type, ".
			"file_name as name ".
			"FROM webfinance_transactions ".
			"WHERE id=".$id_transaction)
    or wf_mysqldie();
  if(mysql_num_rows($result)>0){
    $afile=mysql_fetch_assoc($result);
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