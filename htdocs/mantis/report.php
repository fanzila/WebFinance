<?php
  /*
   * Copyright (C) 2013 Cyril Bouthors <cyril@bouthors.org>
   *
   * This program is free software: you can redistribute it and/or modify it
   * under the terms of the GNU General Public License as published by the
   * Free Software Foundation, either version 3 of the License, or (at your
   * option) any later version.
   *
   * This program is distributed in the hope that it will be useful, but
   * WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
   * Public License for more details.
   *
   * You should have received a copy of the GNU General Public License along
   * with this program. If not, see <http://www.gnu.org/licenses/>.
   *
   */

$roles='manager,accounting,employee';
require_once('../../lib/WebfinanceMantis.php');
require_once("../inc/main.php");

$User = new User();

if (! $User->isLogued()) {
  $_SESSION['came_from']=$_SERVER['REQUEST_URI'];

  if($_SESSION['debug']==1){
    echo 'Not logged. Debug mode, please <a href="/login.php">log in</a>';
    include("bottom.php");
    die();
  }
  header("Location: /login.php");
  die();
}

$user = $User->getInfos();

if(!$User->isAuthorized($roles)){
  header("Location: /welcome.php");
  exit;
}

$mantis = new WebfinanceMantis;

$pdf_file = $mantis->createReport($_GET['year'], $_GET['month'],
            $_GET['id_client'], 'inline');

unlink($pdf_file);

?>
