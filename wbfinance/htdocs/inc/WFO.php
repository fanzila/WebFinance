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
}

?>
