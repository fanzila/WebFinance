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

require_once('../htdocs/inc/config.php');

class WebfinanceUserTest extends PHPUnit_Framework_TestCase
{

    protected function setUp() {
		mysql_connect(WF_SQL_HOST, WF_SQL_LOGIN, WF_SQL_PASS)
			or die(mysql_error());

		// On fait en sorte que tous les warnings SQL se transforment en erreurs,
		// pour eviter les varchar overflow, etc.
		mysql_query("SET SESSION sql_mode='STRICT_TRANS_TABLES'")
			or die(mysql_error());

		// Drop database if needed
		mysql_query('DROP DATABASE IF EXISTS ' . WF_SQL_BASE)
			or die(__FILE__.':'.__LINE__.':' . mysql_error());

		// Create database
		mysql_query('CREATE DATABASE ' . WF_SQL_BASE)
			or die(__FILE__.':'.__LINE__.':' . mysql_error());

		// Select the MySQL database
		mysql_select_db(WF_SQL_BASE)
			or die(__FILE__.':'.__LINE__.':' . mysql_error());

		// Create table(s)
		foreach(explode(';', file_get_contents('../sql/schema.sql')) as $query) {
			if(preg_match('/^\s+$/', $query))
				continue;
			mysql_query($query)
				or die(__FILE__.':'.__LINE__.':' . mysql_error());
		}
	}

	protected function TearDown() {
		// Drop database if needed
		mysql_query('DROP DATABASE IF EXISTS ' . WF_SQL_BASE)
			or die(__FILE__.':'.__LINE__.':' . mysql_error());

		// Close connection
		mysql_close()
			or die(__FILE__.':'.__LINE__.':' . mysql_error());
	}


	function testValidateExistsDoesNotExists() {
        $this->setExpectedException('Exception');
		WebfinanceUser::ValidateExists('unknown-user@company.com');
    }

	function testValidateExistsOK() {
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		WebfinanceUser::ValidateExists($email);
    }

	function testCreateOK() {
		WebfinanceUser::Create('valid-user@company.com');
    }

	function testGetCompaniesNoCompanyOK() {
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		$user = new WebfinanceUser($email);
		$companies = $user->GetCompanies();
		$this->assertEquals(array(), $companies);
    }

	function testGetCompaniesSeveralCompaniesOK() {
		$email = 'valid-user@company.com';
		$name = 'ACME';
		WebfinanceUser::Create($email);
		$company = array(
			'name'     => $name,
			'address1' => '1110 Gateway Drive',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		$company_id = WebfinanceCompany::Create($company);
		$user = new WebfinanceUser($email);
		$companies = $user->GetCompanies();
		$this->assertType('array', $companies);
		$expected = array(
			array(
				'id'   => $company_id,
				'name' => $name,
			)
		);
		$this->assertEquals($expected, $companies);
    }

	function testGetIdFromEmailOK() {
		$email = 'valid-user@company.com';
		$user_id = WebfinanceUser::Create($email);
		$this->assertEquals($user_id, WebfinanceUser::GetIdFromEmail($email));
    }
	
}
?>
