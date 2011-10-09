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
?>

<h1> Create company </h1>

<?
try{

	// print_r($_POST);exit;
	if(isset($_POST['Create_company'])) {
		# Add email to POST arguments
		$_POST['email'] = $_SESSION['cybsso_user']['email'];

		# Create company
		$company_id = WebfinanceCompany::Create($_POST);

		# Redirect to company page
		header("Location: ./?company_id=$company_id");
		exit;
	}
}
catch(SoapFault $fault) {
	echo '<font color="red">'.$fault->getMessage() . '</font>';
}
catch(Exception $fault) {
	echo '<font color="red">'.$fault->getMessage() . '</font>';
}

# Display HTML form
?>

<form method="POST">
  Company name:
	  <input type="text" name="name"
	  value="<?=(isset($_POST['name'])?$_POST['name']:'');?>" /> <br/>

  Address:
	  <input type="text" name="address1"
	  value="<?=(isset($_POST['address1'])?$_POST['address1']:'');?>" /> <br/>

	  <input type="text" name="address2"
	  value="<?=(isset($_POST['address2'])?$_POST['address2']:'');?>" /> <br/>

	  <input type="text" name="address3"
	  value="<?=(isset($_POST['address3'])?$_POST['address3']:'');?>" /> <br/>

  Zip code:
	  <input type="text" name="zip_code"
	  value="<?=(isset($_POST['zip_code'])?$_POST['zip_code']:'');?>" /> <br/>

  City:
	  <input type="text" name="city"
	  value="<?=(isset($_POST['city'])?$_POST['city']:'');?>" /> <br/>

  Country:
	<select name="country">
	<?
	foreach(CybPHP_Country::$countries as $code => $country) {
		echo "<option value=\"$code\"";
		if(isset($_POST['country']) and $_POST['country'] == $code)
			echo ' selected';
		echo ">$country</option>\n";
	}
    ?>
	</select> <br/>

	<input type="submit" name="Create company" value="Create company">

</form>

<?
require_once('../../template/footer.php');
