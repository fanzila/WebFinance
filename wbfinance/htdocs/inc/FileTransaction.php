<?php

class FileTransaction extends File{

  function getType(){
    return 'transaction';
  }

  function FileTransaction($id_file=-1){
    parent::File($id_file);
  }

  function getFiles($id_fk){
    return parent::getFiles($id_fk , $this->getType());
  }

  //$file = $_FILES['file']
  function addFile( $file, $fk_id){
    return parent::addFile($file,$fk_id,$this->getType());
  }

  function deleteAllFiles($fk_id){
    parent::deleteAllFiles($fk_id,$this->getType());
  }

}

?>