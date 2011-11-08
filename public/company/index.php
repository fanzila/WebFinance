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
  // Check arguments
  if(empty($_GET['company_id']))
    throw new Exception('Missing argument');
  
  // Check permissions
  $company = new WebfinanceCompany($_GET['company_id']);
  $company->ValidatePermission($_SESSION['cybsso_user']['email']);

  // Display localized amounts and dates
  setlocale(LC_ALL, $_SESSION['cybsso_user']['language'] . '.utf8');

  $user = new WebfinanceUser($_SESSION['cybsso_user']['email']);
  $smarty->assign('this_company_id', $_GET['company_id']);
  $smarty->assign('companies', $user->GetCompanies());
  $smarty->assign('company_info', $company->GetInfo());
  $smarty->assign('invoices', $company->InvoicesGet());
}
catch(SoapFault $fault) {
  $smarty->assign('error', $fault->getMessage());
}
catch(Exception $fault) {
  $smarty->assign('error', $fault->getMessage());
}

$smarty->display('company/index.tpl');
?>
