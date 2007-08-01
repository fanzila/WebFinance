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
# $Id$
?>
<?

if( isset($_SESSION['id_user']) and is_numeric($_SESSION['id_user']) and $_SESSION['id_user']>0 ) {
  $User = new User();
  $User->getPrefs();
  $language = $User->prefs->lang;
} else {
  $language = 'en_US';
}

if (isset($language) and $language!='en_US') {
  $gettext_dictionnary_filename=$_SERVER['DOCUMENT_ROOT'] . '/../lang/' . substr($language,0,2) . '/LC_MESSAGES/webfinance';

  if (!file_exists("$gettext_dictionnary_filename.mo") or
      filemtime("$gettext_dictionnary_filename.po") > filemtime("$gettext_dictionnary_filename.mo")) {
    system("msgfmt $gettext_dictionnary_filename.po -o $gettext_dictionnary_filename.mo");
  }

  setlocale(LC_ALL, $language.".UTF-8")
    or die("local LC_ALL language failed $language");

  bindtextdomain('webfinance', $_SERVER['DOCUMENT_ROOT'] . '/../lang')
    or die("Set gettext bindtextdomain language failed");

  textdomain('webfinance')
    or die("Set gettext textdomain language failed");
}
?>
