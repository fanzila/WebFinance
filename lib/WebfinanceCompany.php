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

/**
 * This class handles Webfinance companies
 */

class WebfinanceCompany {

	private $_company_id = null;

	function __construct($company_id = null) {
		CybPHP_Validate::ValidateInt($company_id);
		WebfinanceCompany::ValidateExists($company_id);
		$this->_company_id = $company_id;
	}

	/**
	 * Create a new company
	 *
	 * @param company array. An array containing the company information,
	 * example:
	 * \code
	 * array(
	 *    'name'     => 'ACME',
	 *    'address1' => '1110 Gateway Drive',
	 *    'address2' => '',
	 *    'address3' => '',
	 *    'zip_code' => 'CA 94404',
	 *    'city'     => 'San Mateo',
	 *    'country'  => 'US', // ISO 3166-1 alpha-2 country code
	 *    'email'    => 'client@acme.com',
	 * )
	 * \endcode
	 *
	 * @return company_id int. The ID of the newly created company.
	 *
	 */
	static function Create(array $company = array()) {
		CybPHP_Validate::ValidateCompanyName($company['name']);
		CybPHP_Validate::ValidateAddress($company['address1']);
		CybPHP_Validate::ValidateZipCode($company['zip_code']);
		CybPHP_Validate::ValidateCity($company['city']);
		CybPHP_Validate::ValidateCountry($company['country']);
		CybPHP_Validate::ValidateEmail($company['email']);
		WebfinanceUser::ValidateExists($company['email']);
		WebfinanceCompany::ValidateAvailable($company['name']);

		if(empty($company['address2']))
			$company['address2'] = '';
		else
			CybPHP_Validate::ValidateAddress($company['address2']);

		if(empty($company['address3']))
			$company['address3'] = '';
		else
			CybPHP_Validate::ValidateAddress($company['address3']);

		foreach(array('name', 'address1', 'address2', 'address3', 'zip_code',
					  'city', 'country', 'email') as $value)
			$company[$value] = mysql_escape_string($company[$value]);

		$user_id = WebfinanceUser::GetIdFromEmail($company['email']);

		CybPHP_MySQL::Query('BEGIN');

		CybPHP_MySQL::Query('INSERT INTO webfinance_clients SET '.
							"nom          = '$company[name]', ".
							'date_created = NOW(), ' .
							"addr1        = '$company[address1]', ".
							"addr2        = '$company[address2]', ".
							"addr3        = '$company[address3]', ".
							"cp           = '$company[zip_code]', ".
							"ville        = '$company[city]', ".
							"pays         = '$company[country]', ".
							"email        = '$company[email]'");

		$company_id = mysql_insert_id();

		CybPHP_MySQL::Query('INSERT INTO webfinance_clients2users SET '.
							"id_client = $company_id, " .
							"id_user   = $user_id");

		CybPHP_MySQL::Query('COMMIT');

		return $company_id;							
	}

	/**
	 * Validate that the named company is available
	 *
	 * @param company_name string. The name of the company
	 *
	 */
	static function ValidateAvailable($company_name = '') {
		CybPHP_Validate::ValidateCompanyName($company_name);
		$company_name = mysql_escape_string($company_name);

		$result = CybPHP_MySQL::Query('SELECT nom '.
									  'FROM webfinance_clients '.
									  "WHERE nom = '$company_name' ".
									  'LIMIT 1');

		if(mysql_num_rows($result) == 1)
			throw new Exception('Company name is not available');
	}

	/**
	 * Validate that the invoice exists
	 *
	 * @param invoice_id int. The ID of the invoice
	 *
	 */
	static function ValidateInvoiceExists($invoice_id = null) {
		CybPHP_Validate::ValidateInt($invoice_id);
		$invoice_id = mysql_escape_string($invoice_id);

		$result = CybPHP_MySQL::Query('SELECT id_facture '.
									  'FROM webfinance_invoices '.
									  "WHERE id_facture = $invoice_id ".
									  'LIMIT 1');

		if(mysql_num_rows($result) != 1)
			throw new Exception('Invoice not found');
	}

	/**
	 * Validate invoice type
	 *
	 * @param invoice_type string. The type of the invoice
	 *
	 */
	static function ValidateInvoiceType($invoice_type = '') {
		if($invoice_type != 'invoice' and $invoice_type != 'quotation')
			throw new Exception('Invalid invoice type');
	}

	/**
	 * Validate invoice period
	 *
	 * @param invoice_period string. The period of the invoice
	 *
	 */
	static function ValidateInvoicePeriod($invoice_period = '') {
		if(!preg_match('/^(none|monthly|quarterly|yearly)$/', $invoice_period))
			throw new Exception('Invalid invoice period');
	}

	/**
	 * Validate invoice payment method
	 *
	 * @param invoice_payment_method string. The payment method of the invoice
	 *
	 */
	static function ValidateInvoicePaymentmethod($invoice_payment_method = '') {
		if(!preg_match('/^(unknown|direct_debit|check|wire_transfer)$/',
					   $invoice_payment_method))
			throw new Exception('Invalid invoice payment method');
	}

	/**
	 * Validate invoice delivery
	 *
	 * @param invoice_delivery string. The delivery type of the invoice
	 *
	 */
	static function ValidateInvoiceDelivery($invoice_delivery = '') {
		if(!preg_match('/^(email|postal)$/', $invoice_delivery))
			throw new Exception('Invalid invoice delivery');
	}

	/**
	 * Validate that the company exists
	 *
	 * @param company_id int. The ID name of the company
	 *
	 */
	static function ValidateExists($company_id = null) {
		CybPHP_Validate::ValidateInt($company_id);

		$result = CybPHP_MySQL::Query('SELECT id_client '.
									  'FROM webfinance_clients '.
									  "WHERE id_client = $company_id ".
									  'LIMIT 1');

		if(mysql_num_rows($result) != 1)
			throw new Exception('Company does not exist');
	}

	/**
	 * Validate that the user has permission to access this company
	 *
	 * @param company_name string. The name of the company
	 *
	 */
	function ValidatePermission($email = '') {
		CybPHP_Validate::ValidateInt($this->_company_id);
		CybPHP_Validate::ValidateEmail($email);

		$result = CybPHP_MySQL::Query(
			'SELECT c.id_client '.
			'FROM webfinance_clients c '.
			'JOIN webfinance_clients2users c2u ON c2u.id_client = c.id_client '.
			'JOIN webfinance_users u ON u.id_user = c2u.id_user '.
			"WHERE u.email = '$email' AND ".
			"c.id_client = $this->_company_id");

		if(mysql_num_rows($result) != 1)
			throw new Exception('Permission denied');
	}

	/**
	 * Create a new invoice
	 *
	 * @param invoice array. The array defining the invoice to create, example:
	 *
	 * \code
	 * array(
	 *    'type'                   => 'invoice', // or 'quotation'
	 *    'paid'                   => false, // or true
	 *    'vat'                    => 19.6,  // %
	 *    'period'                 => 'none', // 'monthly', 'quarterly', 'yearly'
	 *    'periodic_next_deadline' => 2012-01-04, // YYYY-MM-DD
	 *    'delivery'               => 'email', // or 'postal'
	 *    'payment_method'         => 'unknown', // 'direct_debit', 'check' or
	 *                                           // 'wire_transfer'
	 *    'items'  => array(
	 *       0 => array(
	 *          'description' => 'the item description',
	 *          'price'       => 123.32, // the unit price, excluding VAT
	 *          'quantity'    => 3,
	 *       ),
	 *       1 => array(
	 *          'description' => 'the second item description',
	 *          'price'       => 23,
	 *          'quantity'    => 1,
	 *       ),
	 *    )
	 * )
	 * \endcode
	 *
	 * @return invoice_id int. The ID of the invoice
	 *
	 */
	function InvoiceCreate(array $invoice = array()) {
		CybPHP_Validate::ValidateInt($this->_company_id);
		WebfinanceCompany::ValidateInvoiceType($invoice['type']);
		WebfinanceCompany::ValidateInvoiceDelivery($invoice['delivery']);
		WebfinanceCompany::ValidateInvoicePaymentMethod(
			$invoice['payment_method']);
		WebfinanceCompany::ValidateInvoicePeriod($invoice['period']);
		if($invoice['period'] != 'none')
			CybPHP_Validate::ValidateDate($invoice['periodic_next_deadline']);
		CybPHP_Validate::ValidateFloat($invoice['vat']);
		CybPHP_Validate::ValidateBool($invoice['paid']);

		# Define empty items if needed
		if(empty($invoice['items']))
			$invoice['items'] = array();

		# Define periodic_next_deadline if needed
		if(empty($invoice['periodic_next_deadline']))
			$invoice['periodic_next_deadline'] = '0000-00-00';

		foreach($invoice['items'] as &$item) {
			CybPHP_Validate::ValidateFloat($item['price']);
			CybPHP_Validate::ValidateInt($item['quantity']);
			$item['description'] = mysql_escape_string($item['description']);
		}

		$type_translation = array(
			'invoice'   => 'facture',
			'quotation' => 'devis',
		);
		$invoice['type'] = $type_translation[$invoice['type']];
		$invoice['paid']= ($invoice['paid'] ? 1 : 0);

		$invoice['reference'] = WebfinanceCompany::GenerateInvoiceReference();

		CybPHP_MySQL::Query('BEGIN');

		CybPHP_MySQL::Query(
			'INSERT INTO webfinance_invoices SET '.
			'date_created           = NOW(), '.
			"type_doc               = '$invoice[type]', ".
			"is_paye                = $invoice[paid], ".
			"tax                    = $invoice[vat], ".
			"period                 = '$invoice[period]', ".
			"periodic_next_deadline = '$invoice[periodic_next_deadline]', ".
			"delivery               = '$invoice[delivery]', ".
			"payment_method         = '$invoice[payment_method]', ".
			"num_facture            = '$invoice[reference]', ".
			"id_client              = $this->_company_id");

		$invoice_id = mysql_insert_id();

                $ordre = 1;
		foreach($invoice['items'] as $item) {
			CybPHP_MySQL::Query('INSERT INTO webfinance_invoice_rows SET '.
								"id_facture  = $invoice_id, ".
								"description = '$item[description]', ".
								"qtt         = $item[quantity], ".
								"prix_ht     = $item[price], ".
								"ordre       = $ordre");
                        $ordre++;
                }

		CybPHP_MySQL::Query('COMMIT');

		return $invoice_id;
	}


	/**
	 * Update an invoice
	 *
	 * @param invoice array. The array defining the invoice to create, example:
	 *
	 * \code
	 * array(
	 *    'id'                     => 4, // the invoice ID
	 *    'type'                   => 'invoice', // or 'quotation'
	 *    'paid'                   => false, // or true
	 *    'vat'                    => 19.6,  // %
	 *    'period'                 => 'none', // 'monthly', 'quarterly', 'yearly'
	 *    'periodic_next_deadline' => 2012-01-04, // YYYY-MM-DD
	 *    'delivery'               => 'email', // or 'postal'
	 *    'payment_method'         => 'unknown', // 'direct_debit', 'check' or
	 *                                           // 'wire_transfer'
	 *    'items'  => array(
	 *       0 => array(
	 *          'description' => 'the item description',
	 *          'price'       => 123.32, // the unit price, excluding VAT
	 *          'quantity'    => 3,
	 *       ),
	 *       1 => array(
	 *          'description' => 'the second item description',
	 *          'price'       => 23,
	 *          'quantity'    => 1,
	 *       ),
	 *    )
	 * )
	 * \endcode
	 *
	 */
	function InvoiceUpdate(array $invoice = array()) {
		CybPHP_Validate::ValidateInt($this->_company_id);
		CybPHP_Validate::ValidateInt($invoice['id']);
		WebfinanceCompany::ValidateInvoiceExists($invoice['id']);
		WebfinanceCompany::ValidateInvoiceType($invoice['type']);
		WebfinanceCompany::ValidateInvoicePeriod($invoice['period']);
		WebfinanceCompany::ValidateInvoiceDelivery($invoice['delivery']);
		WebfinanceCompany::ValidateInvoicePaymentMethod(
			$invoice['payment_method']);
		if($invoice['period'] != 'none')
			CybPHP_Validate::ValidateDate($invoice['periodic_next_deadline']);
		CybPHP_Validate::ValidateFloat($invoice['vat']);
		CybPHP_Validate::ValidateBool($invoice['paid']);

		# Define empty items if needed
		if(empty($invoice['items']))
			$invoice['items'] = array();

		# Define periodic_next_deadline if needed
		if(empty($invoice['periodic_next_deadline']))
			$invoice['periodic_next_deadline'] = '0000-00-00';

		foreach($invoice['items'] as &$item) {
			CybPHP_Validate::ValidateFloat($item['price']);
			CybPHP_Validate::ValidateInt($item['quantity']);
			$item['description'] = mysql_escape_string($item['description']);
		}

		$type_translation = array(
			'invoice'   => 'facture',
			'quotation' => 'devis',
		);
		$invoice['type'] = $type_translation[$invoice['type']];
		$invoice['paid']= ($invoice['paid'] ? 1 : 0);

		$invoice['reference'] = WebfinanceCompany::GenerateInvoiceReference();

		CybPHP_MySQL::Query('BEGIN');

		CybPHP_MySQL::Query(
			'UPDATE webfinance_invoices SET '.
			'date_created           = NOW(), '.
			"type_doc               = '$invoice[type]', ".
			"is_paye                = $invoice[paid], ".
			"tax                    = $invoice[vat], ".
			"period                 = '$invoice[period]', ".
			"periodic_next_deadline = '$invoice[periodic_next_deadline]', ".
			"delivery               = '$invoice[delivery]', ".
			"payment_method         = '$invoice[payment_method]', ".
			"num_facture            = '$invoice[reference]' ".
			"WHERE id_facture       = $invoice[id]");

		$invoice_id = mysql_insert_id();

		CybPHP_MySQL::Query('DELETE FROM webfinance_invoice_rows '.
							"WHERE id_facture = $invoice_id");

                $ordre=1;
		foreach($invoice['items'] as $item) {
			CybPHP_MySQL::Query('INSERT INTO webfinance_invoice_rows SET '.
								"id_facture  = $invoice_id, ".
								"description = '$item[description]', ".
								"qtt         = $item[quantity], ".
								"prix_ht     = $item[price], ".
								"ordre       = $ordre");
                        $ordre++;
                }

		CybPHP_MySQL::Query('COMMIT');
	}

	/**
	 * Get all the invoices of a company
	 *
	 * @return invoices array. Array containing the invoices, if any. Example:
	 *
	 * \code
	 *
	 * \endcode
	 *
	 */
	function InvoicesGet() {
		CybPHP_Validate::ValidateInt($this->_company_id);

		$result = CybPHP_MySQL::Query(
			'SELECT i.id_facture AS id, '.
			'i.num_facture AS invoice_reference, '.
			'i.type_doc AS type, '.
			'UNIX_TIMESTAMP(i.date_created) AS date, '.
			'ROUND(SUM(ir.prix_ht*ir.qtt*(100+i.tax)/100), 2) AS amount, '.
			'i.is_paye AS paid, '.
			'i.period, '.
			'i.periodic_next_deadline, '.
			'i.delivery, '.
			'i.payment_method, '.
			'i.tax AS vat '.
			'FROM webfinance_invoices i '.
			'JOIN webfinance_invoice_rows ir ON ir.id_facture = i.id_facture '.
			'JOIN webfinance_clients c ON c.id_client = i.id_client '.
			"WHERE c.id_client = $this->_company_id ".
			'GROUP BY ir.id_facture');

		$type_translation = array(
			'facture' => 'invoice',
			'devis'   => 'quotation',
		);

		$invoices = array();
		while ($invoice = mysql_fetch_assoc($result)) {
			$invoice['paid'] = ($invoice['paid'] == 1 ? true : false);

			$invoice['type'] = $type_translation[$invoice['type']];

			$result_item = CybPHP_MySQL::Query(
				'SELECT description, prix_ht AS price, qtt AS quantity '.
				'FROM webfinance_invoice_rows '.
				"WHERE id_facture = $invoice[id]");

			while ($invoice_item = mysql_fetch_assoc($result_item)) {
				$invoice['items'][] = $invoice_item;
			}
			
			$invoices[] = $invoice;
		}

		return $invoices;
	}

	static function GenerateInvoiceReference() {
		$prefix = date('Ymd');

		for($suffix=0; $suffix<=99; $suffix++) {
			$invoice_reference = sprintf('%d%.2d', $prefix, $suffix);

			$result = CybPHP_MySQL::Query(
				'SELECT num_facture '.
				'FROM webfinance_invoices '.
				"WHERE num_facture='$invoice_reference'");

			if(mysql_num_rows($result)==0)
				return $invoice_reference;
		}

		throw new Exception('Unable to allocate invoice reference');
	}

	function GetInfo() {
		CybPHP_Validate::ValidateInt($this->_company_id);

		$result = CybPHP_MySQL::Query(
			'SELECT nom AS name '.
			'FROM webfinance_clients '.
			"WHERE id_client = $this->_company_id");

		return mysql_fetch_assoc($result);
	}
	
}

?>
