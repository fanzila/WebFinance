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

// Make a handy class that simplifies calling the debugging code
// multiple times within the same script.

class Debug {
  // Define variables that store the old error reporting and logging states
  var $old_error_level;
  var $old_display_level;
  var $old_error_logging;
  var $old_error_log;

  // For storing the path to the temporary log file
  var $debug_log;

  function Debug ($log = 'debug.log') {
    $this->debug_log = $log;
  }

  function start () {
    // Show all errors
    $this->old_error_level = error_reporting (E_ALL);

    // Make sure that the errors get displayed
    $this->old_display_level = ini_set ('display_errors', 1);

    // Make sure that error logging is enabled
    $this->old_error_logging = ini_set ('log_errors', 1);

    // Make sure that the errors get logged to a special log file
    $this->old_log_setting = ini_set ('error_log', $this->debug_log);
  }

  function stop () {
    // Use the stored error and display settings to
    // restore the previous state
    error_reporting ($this->old_error_level);
    ini_set ('display_errors', $this->old_display_level);
    ini_set ('log_errors', $this->old_error_logging);
    ini_set ('error_log', $this->debug_log);
  }
 }
?>
