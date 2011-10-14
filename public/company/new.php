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

require_once('../../htdocs/inc/sso.php');
require_once('../../htdocs/inc/smarty.php');

try{

  if(isset($_POST['Create_company'])) {
    // Add email to POST arguments
    $_POST['email'] = $_SESSION['cybsso_user']['email'];

    // Create company
    $company_id = WebfinanceCompany::Create($_POST);

    // Redirect to company page
    header("Location: ./?company_id=$company_id");
    exit;
  }
}
catch(SoapFault $fault) {
  $smarty->assign('error', $fault->getMessage());
}
catch(Exception $fault) {
  $smarty->assign('error', $fault->getMessage());
}

$smarty->assign('params', $_POST);
$smarty->assign('countries', CybPHP_Country::$countries);
$smarty->display('company/new.tpl');
?>
