<?php

class File {

  function File($id_file=-1){
    if($id>0){
      $result = mysql_query("SELECT id_file, fk_id , wf_type , file_name , file_type FROM webfinance_files WHERE id_file=$id_file ") or die(mysql_error());
      list($this->id,$this->fk_id,$this->wf_type,$this->name , $this->type) = mysql_fetch_array($result);
    }
  }

  function getFile($id_file){

    $result = mysql_query("SELECT id_file, fk_id , wf_type , file , file_name as name , file_type as type FROM webfinance_files WHERE id_file=$id_file")
      or die(mysql_error());

    if(mysql_num_rows($result)>0){
      $afile=mysql_fetch_assoc($result);
      mysql_free_result($result);
      $file_name=$afile['name'];
      $file_type=$afile['type'];
      $file=$afile['file'];
      header ('Content-type: $file_type');
      header ("Content-Disposition: attachment; filename=$file_name");
      echo $file;
      mysql_free_result($result);
    }else{
      echo "File not found";
    }
  }

  function getFiles($id_fk){
    $files = array();
    if(is_numeric($id_fk)){
      $result = mysql_query("SELECT id_file ,  file_name as name , file_type as type FROM webfinance_files WHERE fk_id=$id_fk ") or die(mysql_error());
      while( $file = mysql_fetch_object($result)){
	$files[]=$file;
      }
      mysql_free_result($result);
    }
    return $files;
  }

  function getAllFiles($id_fk){
    $files = array();
    if(is_numeric($id_fk)){
      $result = mysql_query("SELECT id_file , file , file_name as name , file_type as type FROM webfinance_files WHERE fk_id=$id_fk ") or die(mysql_error());
      while( $file = mysql_fetch_object($result)){
	$files[]=$file;
      }
      mysql_free_result($result);
    }
    return $files;
  }


  //$file = $_FILES['file']
  function addFile( $file, $fk_id , $wf_type='transaction'){
    if(is_uploaded_file($file['tmp_name']) && is_numeric($fk_id)){
        $file_type=addslashes($file['type']);
	$file_name=addslashes($file['name']);
	$file_blob = file_get_contents($file['tmp_name']);
	$file=addslashes($file_blob);
	mysql_query("INSERT INTO webfinance_files SET fk_id=$fk_id , wf_type = '$wf_type' , file_type='$file_type' , file_name = '$file_name' , file='$file' ")
	  or die(mysql_error());
	return mysql_insert_id();
    }else
      return 0;
  }

  function deleteFile($id_file){
    if(is_numeric($id_file)){
      mysql_query("DELETE FROM webfinance_files WHERE id_file=$id_file") or die(mysql_error());
    }
  }

}

?>