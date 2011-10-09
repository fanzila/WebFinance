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

    $Id: dbconnect.php 539 2007-08-01 12:15:10Z gassla $
*/

require_once('config.php');

$dbi = mysql_connect(WF_SQL_HOST,WF_SQL_LOGIN, WF_SQL_PASS)
  or die("Could not connect to mysql : ".mysql_error());
mysql_select_db(WF_SQL_BASE) or die("Could not select database : ".mysql_error());

?>
