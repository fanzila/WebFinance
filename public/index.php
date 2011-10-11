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

require_once('../htdocs/inc/sso.php');
require_once('../template/header.php');

try{
	# Create user if he/she does not exist yet
	try{
		WebfinanceUser::ValidateExists($_SESSION['cybsso_user']['email']);
	}
	catch(Exception $e) {
		WebfinanceUser::Create($_SESSION['cybsso_user']['email']);
	}

	$user = new WebfinanceUser($_SESSION['cybsso_user']['email']);

	# Ask the user to create a company if he/she doesn't own one
	if(count($user->GetCompanies()) == 0) {
		header('Location: /company/new');
		exit;
	}

/*
si return_url est définie, alors on redirige (dans le cas de la creation d'un
compte)
redirect $return_url
*/

/*

Si l'utilisateur a >=1 societe, on affiche les devis, factures, coordonnées,
etc. de la première entreprise
redirect /company/ID */
/*
Prévoir drop down de selection des différentes societes + javascript
*/
}
catch(SoapFault $fault) {
	echo $fault->getMessage();
}

require_once('../template/footer.php');
