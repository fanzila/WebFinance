<?php

if (!empty($_SERVER['DOCUMENT_ROOT']))
  $filename=$_SERVER['DOCUMENT_ROOT'].'/../etc/wf.conf';
 else
   $filename='/etc/webfinance/wf.conf';

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

$dbi = mysql_pconnect(WF_SQL_HOST,WF_SQL_LOGIN, WF_SQL_PASS)
  or die("Could not connect to mysql : ".mysql_error());
mysql_select_db(WF_SQL_BASE) or die("Could not select database : ".mysql_error());

?>
