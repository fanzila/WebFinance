<?php
  /*
   * Copyright (C) 2013 Cyril Bouthors <cyril@boutho.rs>
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

require_once("../../inc/main.php");
$User = new User();
$document = new WebfinanceDocument;

if(!$User->isAuthorized("manager,accounting,employee")){
  $_SESSION['came_from'] = $_SERVER['REQUEST_URI'];
  header("Location: /login.php");
  exit;
}

if(!isset($_POST['company_id'], $_POST['template']))
{
  echo "Missing POST arguments company_id and template";
  exit;
}

// Fetch company information
$company = new Client($_POST['company_id']);

$temp_file = tempnam(sys_get_temp_dir(), 'webfinance_contract');
$template_file = "$_POST[template].md";
$template_dir = realpath(dirname(__FILE__) . '/../../../contract');
$subtitle = "Contrat d'infogérance ISVTEC";

if(!file_exists("$template_dir/$template_file"))
{
  echo "Unable to find $template_dir/$template_file";
  exit;
}

if(empty($company->nom))
  die("Invalid company name");

if(empty($company->business_entity))
  die("Invalid business entity");

if(empty($company->contract_signer_role))
  die("Invalid contract signer role");

if(empty($company->contract_signer))
  die("Invalid contract signer");

if(empty($company->capital))
  die("Invalid capital");

if(empty($company->rcs))
  die("Invalid RCS");

# Generate address
$address = "$company->addr1";
if(!empty($company->addr2))
  $address .= ", $company->addr2";
if(!empty($company->addr3))
  $address .= ", $company->addr3";
$address .= ", $company->cp, $company->ville, $company->pays";

# Generate date
$date = strftime('%x');

$stdout = shell_exec("$template_dir/build.sh " .
          "--template-file=$template_file ".
          "--output=$temp_file ".
          "--company=\"$company->nom\" ".
          "--business_entity=\"$company->business_entity\" ".
          "--contract_signer_role=\"$company->contract_signer_role\" ".
          "--contract_signer=\"$company->contract_signer\" ".
          "--capital=\"$company->capital\" ".
          "--address=\"$address\" ".
          "--rcs=\"$company->rcs\" ".
          "--date=\"$date\" ".
          " 2>&1");

if(!empty($stdout))
{
  print_r($stdout);
  exit;
}

$fp = fopen($temp_file, 'r')
  or die("Unable to open $temp_file");

$final_filename="Contract-$_POST[template]-$company->nom.pdf";

header("Content-Type: application/pdf");
header("Content-Length: " . filesize($temp_file));
header("Content-Disposition: attachment; filename=$final_filename");

fpassthru($fp);
fclose($fp);
unlink($temp_file);
exit;


/* template smarty .md */

?>
