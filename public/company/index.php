<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
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
require_once('../../template/header.php');

try{
	# Check permissions
	$company = new WebfinanceCompany($_GET['company_id']);
	$company->ValidatePermission($_SESSION['cybsso_user']['email']);

	$company_info = $company->GetInfo();

	echo "<h1> $company_info[name] </h1>\n";
	# Show invoices and quotations

	?>
		<h2> Invoices </h2>
		<table border="1">
			 <tr>
			 <th> <?=_('Type');?> </th>
			 <th> <?=_('Reference');?> </th>
			 <th> <?=_('Date');?> </th>
			 <th> <?=_('Amount');?> </th>
			 <th> <?=_('Actions');?> </th>
			 </tr>

<?
	# Display localized amounts and dates
	setlocale(LC_ALL, $_SESSION['cybsso_user']['language'] . '.utf8');

	foreach($company->InvoicesGet() as $invoice) {
		echo "<tr> <td> $invoice[type] </td>";
		echo "<td> $invoice[invoice_reference] </td>";
		echo '<td>'. strftime('%x', $invoice['date']) . '</td>';
		echo '<td>'. money_format('%.2n', $invoice['amount']) . '</td>';
		echo '<td> ';
		if($invoice['paid'])
			echo 'paid.ico';
		else
			echo 'unpaid.ico';
		echo " <a href=\"download?invoice_id=$invoice[id]\">PDF</a> </td></tr>";
	}
	echo '</table>';

	# Link to the other companies, if any
	$user = new WebfinanceUser($_SESSION['cybsso_user']['email']);
	$companies = $user->GetCompanies();

	if(count($companies) > 1) {
?>
	<form>
		<select name="company_id">
<?
		foreach($companies as $company) {
			echo "<option value=\"$company[id]\"";
			if($company['id'] == $_GET['company_id'])
				echo ' selected';
			echo ">$company[name]</option>\n";
		}

?>
		</select>
 	    <input type="submit" name="Change company" value="Change company">
	</form>
<?
	}
}
catch(SoapFault $fault) {
	echo '<font color="red">'.$fault->getMessage() . '</font>';
}
catch(Exception $fault) {
	echo '<font color="red">'.$fault->getMessage() . '</font>';
}

require_once('../../template/footer.php');
?>
