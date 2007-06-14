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