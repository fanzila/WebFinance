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

require_once('../htdocs/inc/dbconnect.php');

session_start();

$_SESSION = array();
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000, $params['path'],
			  $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

$return_url = (($_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://') .
	$_SERVER['HTTP_HOST'] . '/';

header('Location: '. WF_CYBSSO_URL . "?action=logout&return_url=$return_url");
exit;

?>
