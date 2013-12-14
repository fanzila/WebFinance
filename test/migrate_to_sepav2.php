<?php
/*
This file is part of Webfinance.

Copyright (c) Pierre Doleans <pierre@doleans.net>

Webfinance is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Webfinance is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Webfinance; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* INFOS SEPA 
http://www.banquepopulaire.fr/Institutionnel/a-savoir/single-euro-payments-area/Pages/single-euro-payments-area.aspx
http://www.sepafrance.fr/
http://www.ingsepa.com/media/16526/sepa_formats_-_une_introduction___l_xml__fr_.pdf
http://fr.iban-bic.com/iban_und_bic.0.html?&L=5

*/

error_reporting(E_ALL);

require(dirname(__FILE__) . "/../htdocs/inc/main.php");

//PATCH SQL 
mysql_query("DROP PROCEDURE IF EXISTS patch_migrate") or die(mysql_error());
mysql_query("create procedure patch_migrate() begin 
ALTER TABLE `direct_debit` ADD COLUMN type ENUM ('SEPA', 'CFONB') NOT NULL;
UPDATE direct_debit SET type='CFONB';
end;") or die(mysql_error());

mysql_query("CALL patch_migrate()") or die(mysql_error());
mysql_query("DROP PROCEDURE IF EXISTS patch_migrate") or die(mysql_error());

echo "\n All done, no problem if no error above :)";
echo "\n\n";
?>
