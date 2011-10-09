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

require_once('dbconnect.php');

session_start();

try{
	# Connect to the SSO API
	$cybsso = new SoapClient(null, array(
								 'location' => WF_CYBSSO_URL . 'api/',
								 'login'    => WF_CYBSSO_LOGIN,
								 'password' => WF_CYBSSO_PASSWORD,
								 'uri'      => '',
								 ));

	$return_url = (($_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://') .
		$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	# Check if ticket is defined and is still valid
	if(!isset($_SESSION['cybsso_ticket'],
			  $_SESSION['cybsso_ticket_expiration_date'],
			  $_SESSION['cybsso_user']['email']) or
	   $_SESSION['cybsso_ticket_expiration_date'] <= time()) {

		# Redirect to the auth page if ticket is invalid and no information is
		# given
		if(!isset($_GET['cybsso_ticket'], $_GET['cybsso_email']))
			throw new SoapFault('inc/sso.php', 'Invalid SSO ticket');

		# If the user has just logged in, then we set the session and redirect
		# to ourself
		$expiration = $cybsso->TicketCheck($_GET['cybsso_ticket'],
										   $_GET['cybsso_email']);

		$cybsso_user = $cybsso->UserGetInfo($_GET['cybsso_email']);

		$_SESSION = array(
			'cybsso_ticket'                 => $_GET['cybsso_ticket'],
			'cybsso_ticket_expiration_date' => $expiration,
			'cybsso_user'                   => $cybsso_user,
		);

		header("Location: $return_url");
		exit;
	}

	# Check if the ticket is valid
	$_SESSION['cybsso_ticket_expiration_date'] =
		$cybsso->TicketCheck($_SESSION['cybsso_ticket'],
							 $_SESSION['cybsso_user']['email']);
	unset($return_url);
}
catch(SoapFault $fault) {
	# If the ticket is invalid for some reason, then we destroy the session and
	# redirect to the SSO
	$_SESSION = array();
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'],
				  $params['domain'], $params['secure'], $params['httponly']);
	}
	session_destroy();
	header('Location: ' . WF_CYBSSO_URL . "?return_url=$return_url");
	exit;
}
?>
