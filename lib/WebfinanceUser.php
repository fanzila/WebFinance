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
 * This class handles Webfinance users
 */

class WebfinanceUser {

	private $_email = '';

	function __construct($email = '') {
		CybPHP_Validate::ValidateEmail($email);
		$this->_email = mysql_escape_string($email);
	}

	/**
	 * Check if user exists
	 *
	 * @param email string. The email address to check
	 *
	 * @return status bool. True if user exists, false otherwise
	 */
	static function ValidateExists($email = '') {
		CybPHP_Validate::ValidateEmail($email);
		$email = mysql_escape_string($email);

		$r = CybPHP_MySQL::Query('SELECT email '.
								 'FROM webfinance_users '.
								 "WHERE email = '$email'");

		if(mysql_num_rows($r) != 1)
			throw new Exception('User does not exist');
	}

	/**
	 * Create a new user
	 *
	 * @param email string. The email address to create
	 *
	 * @return user_id int. The newly create user ID
	 *
	 */
	static function Create($email = '') {
		CybPHP_Validate::ValidateEmail($email);

		$email = mysql_escape_string($email);

		CybPHP_MySQL::Query('INSERT INTO webfinance_users SET '.
							"email = '$email', " .
							"login = '$email'");

		return mysql_insert_id();
	}

	/**
	 * Get companies owned by the user
	 *
	 * @return companies array. An array containing the companies, example:
	 *
	 * \code
	 *
	 * $companies = array(
	 *    0 => array(
	 *       'id'   => 4,
	 *       'name' => 'GooSoft',
	 *    ),
	 *    1 => array(
	 *       'id'   => 32,
	 *       'name' => 'MicroGle',
	 *    ),
	 * );
	 *
	 * \endcode
	 *
	 */
	function GetCompanies() {
		CybPHP_Validate::ValidateEmail($this->_email);
		
		$result = CybPHP_MySQL::Query(
			'SELECT c.id_client AS id, nom AS name '.
			'FROM webfinance_clients c '.
			'JOIN webfinance_clients2users c2u ON c2u.id_client = c.id_client '.
			'JOIN webfinance_users u ON u.id_user = c2u.id_user '.
			"WHERE u.email = '$this->_email'");

		$companies = array();
		while ($row = mysql_fetch_assoc($result))
			$companies[] = $row;

		return $companies;
	}

	/**
	 * Get user ID from email address
	 *
	 * @param email string. The email address
	 *
	 * @return user_id int. The user ID.
	 *
	 */
	static function GetIdFromEmail($email = '') {
		CybPHP_Validate::ValidateEmail($email);
		$email = mysql_escape_string($email);
		
		$result = CybPHP_MySQL::Query('SELECT id_user '.
									  'FROM webfinance_users '.
									  "WHERE email = '$email'");

		$user = mysql_fetch_assoc($result);
		return $user['id_user'];
	}

}

?>
