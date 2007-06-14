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
// 
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI, ISVTEC 
// See CREDITS file
// 
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$

/** 
  * WFO stands for WebFinance Object
  *
  * It aims to be the standard base from witch we shall derive every object of the project.
  * 
  * WFO handles some basic aspects of the project like playing a SQL query in the DB and failing nicely
  */
class WFO {
  function WFO() {
  }

  /** 
   * Plays the SQL query passed in parameter like mysql_query() does.
   * 
   * If query fails a formated HTML error message is outputed, but only when
   * debug mode is active.
   *
   * TODO : When debug mode is off, should log query and error to syslog or similar
   *        Split queries between master and slave mysql depending on the presence of SELECT at start
   *
   * @author Cyril Gantin <cgantin@nbi.fr>
   */
  function SQL($q, $die=TRUE) {
    if ($result = mysql_query($q)) {
      return $result;
    }
    
    if (!$_SESSION['debug']) {
      return;
    }
   
    $mysql_error_in = addslashes(preg_replace("/.* near '(.*)' at.*/", '\1', mysql_error()));
    $mysql_error_in = preg_replace("!(\(|\))!", '\\1', $mysql_error_in); // Escape parenthesis for further use in preg 
    $q = preg_replace("/(.*)($mysql_error_in)(.*)/", '\1<b>\2</b>\3', $q); 
    $message = sprintf("Page : %s\nQuery : %s\nMysql error: %s\n", $_SERVER['SCRIPT_URL'], $q, mysql_error());

    // backtrace
    $trace = debug_backtrace();
    foreach ($trace as $t) {
      $message .= sprintf("trace : %s%s%s(%s) [%s:%d]\n", $t['class'], $t['type'], $t['function'], implode(', ', $t['args']), $t['file'], $t['line']);
    }
    
    if (headers_sent()) {
      printf('<div style="position: absolute; border: solid 5px red; background: #ffcece; left: 100px top: 100px;"><pre>%s', $message);
      print "</pre></div>";
    } else {
      header("Content-Type: text/html; charset=utf8");
      printf("<pre>%s</pre>", $message);
    }

    if ($die) die();  
  }

  /**
    * Strips format added by javascript and others in the HTML interface.
    *
    * print FormatisObject::stripMonetaryFormat('33,12 €'); 
    * outputs 33.12
    *
    * Both this function and makeMonetaryFormat exist because sprintf and such
    * change the decimal separator symbol depending on the locale set in the
    * application. Therefore sprintf and sscanf should not be used in WF to
    * format amounts if we want to preserve the multi-currency behavior AND the
    * localised interface.
    */
  function stripMonetaryFormat($val) {
    $r = preg_replace("/,/", ".", $val);
    $r = preg_replace("/ /", "", $r);
    $r = preg_replace("/[^0-9.]/", "", $r);
    return $r;
  }

  /**
    * This function does the oposit of stripMonetaryFormat : it takes a number
    * (typicaly a DECIMAL field from a DB) and converts it to a string
    * representing the ammount
    *
    * Todo : handle currency symbols.
    *
    */
  function makeMonetaryFormat($val) {
    return number_format($val, 2, ',', ' ')." €";
  }
}

?>
