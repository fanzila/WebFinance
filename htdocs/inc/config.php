<?php
//
// Copyright (C) 2011 Cyril Bouthors <cyril@bouthors.org>
//
// This program is free software: you can redistribute it and/or modify it under
// the terms of the GNU General Public License as published by the Free Software
// Foundation, either version 3 of the License, or (at your option) any later
// version.
//
// This program is distributed in the hope that it will be useful, but WITHOUT
// ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
// FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
// details.
//
// You should have received a copy of the GNU General Public License along with
// this program. If not, see <http://www.gnu.org/licenses/>.
//

# Load configuration
$filename=dirname(__FILE__)."/../../etc/wf.conf";
if (!file_exists($filename)) {
   $filename='/etc/webfinance/wf.conf';
 }

$perms = fileperms($filename);
if ($perms & 0x0004) {
  echo "<font color=\"red\">SECURITY WARNING: $filename contains a clear MySQL password and is world readable!<br>";
  echo "You can fix it with: chmod go-rwx $filename</font>";
}

if (!$handle = fopen($filename, 'r')) {
  echo "Cannot open file ($filename)";
  exit;
}

while (!feof($handle)) {
  $buffer = fgets($handle, 4096);

  if (preg_match('/=/', $buffer)) {
    list($variable, $value) = explode('=', $buffer);
    $variable=trim($variable);
    $value=trim($value);
	if(!defined('WF_' . $variable))
		define('WF_' . $variable, $value);
  }
}
fclose($handle);

if (!defined('WF_SQL_PASS')) {
  define('WF_SQL_PASS', '');
}
if(!defined('WF_DEBUG')){
  define('WF_DEBUG',1);
 }
if(!defined('WF_DEBUG_ALL')){
  define('WF_DEBUG_ALL',0);
 }

# Autoload PHP classes
set_include_path(
  get_include_path() . PATH_SEPARATOR .
  dirname(__FILE__).'/../../lib'
);

if(!function_exists('__autoload')) {
	function __autoload($class_name) {
		require_once str_replace('_', '/', $class_name) .'.php';
	}
}

?>
