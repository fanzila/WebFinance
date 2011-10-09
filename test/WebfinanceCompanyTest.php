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

class WebfinanceCompanyTest extends PHPUnit_Framework_TestCase
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

	function testCreateOK() {
		$email = 'valid-user@company.com';
		$name = 'ACME';
		WebfinanceUser::Create($email);
		$company = array(
			'name'     => $name,
			'address1' => '1110 Gateway Drive',
			'address2' => '2nd address line',
			'address3' => '3rd address line',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		$company_id = WebfinanceCompany::Create($company);

		$this->assertGreaterThan(0, $company_id);
    }

	function testValidateAvailableOK() {
		WebfinanceCompany::ValidateAvailable('new company');
	}

	function testValidateAvailableAlreadyTaken() {
		$name = 'ACME';
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		$company = array(
			'name'     => $name,
			'address1' => '1110 Gateway Drive',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		WebfinanceCompany::Create($company);
        $this->setExpectedException('Exception');
		WebfinanceCompany::ValidateAvailable($name);
	}

	function testExistsOK() {
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		$company = array(
			'name'     => 'ACME',
			'address1' => '1110 Gateway Drive',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		$company_id = WebfinanceCompany::Create($company);
		WebfinanceCompany::ValidateExists($company_id);
	}

	function testValidatePermissionOK() {
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		$company = array(
			'name'     => 'ACME',
			'address1' => '1110 Gateway Drive',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		$company_id = WebfinanceCompany::Create($company);

		$company = new WebfinanceCompany($company_id);
		$company->ValidatePermission($email);
	}

	function testValidatePermissionDenied() {
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		$company = array(
			'name'     => 'ACME',
			'address1' => '1110 Gateway Drive',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		$company_id = WebfinanceCompany::Create($company);

		$company = new WebfinanceCompany($company_id);
        $this->setExpectedException('Exception');
		$company->ValidatePermission('unknown@user.com');
	}

	function testValidateInvoiceTypeUnknownType() {
        $this->setExpectedException('Exception');
		WebfinanceCompany::ValidateInvoiceType('unknown');
	}

	function testValidateInvoiceTypeOK() {
		WebfinanceCompany::ValidateInvoiceType('invoice');
		WebfinanceCompany::ValidateInvoiceType('quotation');
	}

	function testValidateInvoicePeriodUnknownPeriod() {
        $this->setExpectedException('Exception');
		WebfinanceCompany::ValidateInvoicePeriod('unknown');
	}

	function testValidateInvoicePeriodOK() {
		WebfinanceCompany::ValidateInvoicePeriod('none');
		WebfinanceCompany::ValidateInvoicePeriod('monthly');
		WebfinanceCompany::ValidateInvoicePeriod('quarterly');
		WebfinanceCompany::ValidateInvoicePeriod('yearly');
	}

	function testValidateInvoicePaymentMethodInvalid() {
        $this->setExpectedException('Exception');
		WebfinanceCompany::ValidateInvoicePaymentMethod('invalid');
	}

	function testValidateInvoicePaymentMethodOK() {
		WebfinanceCompany::ValidateInvoicePaymentMethod('unknown');
		WebfinanceCompany::ValidateInvoicePaymentMethod('direct_debit');
		WebfinanceCompany::ValidateInvoicePaymentMethod('check');
		WebfinanceCompany::ValidateInvoicePaymentMethod('wire_transfer');
	}

	function testValidateInvoiceDeliveryUnknownDelivery() {
        $this->setExpectedException('Exception');
		WebfinanceCompany::ValidateInvoiceDelivery('unknown');
	}

	function testValidateInvoiceDeliveryOK() {
		WebfinanceCompany::ValidateInvoiceDelivery('email');
		WebfinanceCompany::ValidateInvoiceDelivery('postal');
	}

	function testValidateInvoiceExistsUnknown() {
        $this->setExpectedException('Exception');
		WebfinanceCompany::ValidateInvoiceExists(8);
	}

	function testValidateInvoiceExistsOK() {
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		$company = array(
			'name'     => 'ACME',
			'address1' => '1110 Gateway Drive',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		$company_id = WebfinanceCompany::Create($company);

		$company = new WebfinanceCompany($company_id);

		$invoice = array(
			'type'                   => 'invoice',
			'paid'                   => false,
			'vat'                    => 19.6,
			'period'                 => 'monthly',
			'periodic_next_deadline' => '2012-01-04',
			'delivery'               => 'email',
			'payment_method'         => 'unknown',
			'items'  => array(
				0 => array(
					'description' => 'the item description',
					'price'       => 123.32,
					'quantity'    => 3,
				),
				1 => array(
					'description' => 'the second item description',
					'price'       => 23,
					'quantity'    => 1,
				),
			)
		);

		$invoice_id = $company->InvoiceCreate($invoice);

		WebfinanceCompany::ValidateInvoiceExists($invoice_id);
	}

	function testGenerateInvoiceReferenceIDOverFlow() {
		$prefix=date('Ymd');

		for($suffix=0; $suffix<=99; $suffix++) {
			$invoice_reference = sprintf('%d%.2d', $prefix, $suffix);

			CybPHP_MySQL::Query(
				'INSERT INTO webfinance_invoices SET '.
				"num_facture = '$invoice_reference'");
		}

        $this->setExpectedException('Exception');
		WebfinanceCompany::GenerateInvoiceReference();
	}

	function testGenerateInvoiceReferenceOK() {
		$invoice_reference = WebfinanceCompany::GenerateInvoiceReference();
		$this->assertEquals(date('Ymd') . 0, $invoice_reference);

		CybPHP_MySQL::Query(
			'INSERT INTO webfinance_invoices SET '.
			"num_facture = '$invoice_reference'");

		$invoice_reference = WebfinanceCompany::GenerateInvoiceReference();
		$this->assertEquals(date('Ymd') . 01, $invoice_reference);

		CybPHP_MySQL::Query(
			'INSERT INTO webfinance_invoices SET '.
			"num_facture = '$invoice_reference'");

		$invoice_reference = WebfinanceCompany::GenerateInvoiceReference();
		$this->assertEquals(date('Ymd') . 02, $invoice_reference);
	}

	function testInvoiceCreateOK() {
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		$company = array(
			'name'     => 'ACME',
			'address1' => '1110 Gateway Drive',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		$company_id = WebfinanceCompany::Create($company);

		$company = new WebfinanceCompany($company_id);

		$invoice = array(
			'type'                   => 'invoice',
			'paid'                   => false,
			'vat'                    => 19.6,
			'period'                 => 'monthly',
			'periodic_next_deadline' => '2012-01-04',
			'delivery'               => 'email',
			'payment_method'         => 'unknown',
			'items'  => array(
				0 => array(
					'description' => 'the item description',
					'price'       => 123.32,
					'quantity'    => 3,
				),
			)
		);

		$invoice_id = $company->InvoiceCreate($invoice);
		$this->assertGreaterThan(0, $invoice_id);
	}

	function testInvoiceUpdateOK() {
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		$company = array(
			'name'     => 'ACME',
			'address1' => '1110 Gateway Drive',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		$company_id = WebfinanceCompany::Create($company);

		$company = new WebfinanceCompany($company_id);

		$invoice = array(
			'type'                   => 'invoice',
			'paid'                   => false,
			'vat'                    => 19.6,
			'period'                 => 'monthly',
			'periodic_next_deadline' => '2012-01-04',
			'delivery'               => 'email',
			'payment_method'         => 'unknown',
			'items'  => array(
				0 => array(
					'description' => 'the item description',
					'price'       => 123.32,
					'quantity'    => 3,
				),
				1 => array(
					'description' => 'the second item description',
					'price'       => 23,
					'quantity'    => 1,
				),
			)
		);

		$invoice_id = $company->InvoiceCreate($invoice);

		$invoice['id'] = $invoice_id;

		$invoices = $company->InvoicesGet();
		$this->assertEquals($invoice['paid'], $invoices[0]['paid']);

		$invoice['paid'] = true;

		$company->InvoiceUpdate($invoice);

		$invoices = $company->InvoicesGet();
		$this->assertEquals($invoice['paid'], $invoices[0]['paid']);
	}

	function testInvoicesGetEmpty() {
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		$company = array(
			'name'     => 'ACME',
			'address1' => '1110 Gateway Drive',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		$company_id = WebfinanceCompany::Create($company);

		$company = new WebfinanceCompany($company_id);

		$invoice = array(
			'type'           => 'invoice',
			'paid'           => false,
			'vat'            => 19.6,
			'period'         => 'none',
			'delivery'       => 'email',
			'payment_method' => 'unknown',
			'items'          => array(
				0 => array(
					'description' => 'the item description',
					'price'       => 123.32,
					'quantity'    => 3,
				),
			)
		);

		$company->InvoiceCreate($invoice);

		$invoices = $company->InvoicesGet();

		$this->assertEquals($invoice['type'], $invoices[0]['type']);
		$this->assertEquals($invoice['vat'], $invoices[0]['vat']);
		$this->assertEquals($invoice['paid'], $invoices[0]['paid']);
		$this->assertEquals($invoice['period'], $invoices[0]['period']);
		$this->assertEquals($invoice['delivery'], $invoices[0]['delivery']);
		$this->assertEquals($invoice['items'], $invoices[0]['items']);
	}

	function testGetInfo() {
		$email = 'valid-user@company.com';
		WebfinanceUser::Create($email);
		$new_company = array(
			'name'     => 'ACME',
			'address1' => '1110 Gateway Drive',
			'zip_code' => 'CA 94404',
			'city'     => 'San Mateo',
			'country'  => 'US',
			'email'    => $email,
		);
		$company_id = WebfinanceCompany::Create($new_company);

		$company = new WebfinanceCompany($company_id);

		$company_info = $company->GetInfo();
		$this->assertEquals($new_company['name'], $company_info['name']);
	}
}
?>
