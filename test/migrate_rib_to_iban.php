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

require(dirname(__FILE__) . "/../htdocs/inc/main.php");

function CheckIBAN($iban){
 
        $charConversion = array("A" => "10","B" => "11","C" => "12","D" => "13","E" => "14","F" => "15","G" => "16","H" => "17",
"I" => "18","J" => "19","K" => "20","L" => "21","M" => "22","N" => "23","O" => "24","P" => "25","Q" => "26","R" => "27",
"S" => "28","T" => "29","U" => "30","V" => "31","W" => "32","X" => "33","Y" => "34","Z" => "35");
 
        // Déplacement des 4 premiers caractères vers la droite et conversion des caractères
        $tmpiban = strtr(substr($iban,4,strlen($iban)-4).substr($iban,0,4),$charConversion);
 
        // Calcul du Modulo 97 par la fonction bcmod et comparaison du reste à 1
        return (intval(bcmod($tmpiban,"97")) == 1);
}

function checkrib($rib_code_banque, $rib_code_guichet, $rib_code_compte, $rib_code_cle)
{
	$account = (int) strtr(strtoupper($rib_code_compte), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', '12345678912345678923456789');
	$key = 97 - ((89 * $rib_code_banque + 15 * $rib_code_guichet + 3 * $account) % 97);
	return $key === $rib_code_cle;
}

function Rib2Iban($codebanque,$codeguichet,$numerocompte,$cle) {
	$charConversion = array("A" => "10","B" => "11","C" => "12","D" => "13","E" => "14","F" => "15","G" => "16","H" => "17",
	"I" => "18","J" => "19","K" => "20","L" => "21","M" => "22","N" => "23","O" => "24","P" => "25","Q" => "26",
	"R" => "27","S" => "28","T" => "29","U" => "30","V" => "31","W" => "32","X" => "33","Y" => "34","Z" => "35");

	$tmpiban = strtr(strtoupper($codebanque.$codeguichet.$numerocompte.$cle)."FR00",$charConversion);

	// Soustraction du modulo 97 de l'IBAN temporaire à 98
	$cleiban = strval(98 - intval(bcmod($tmpiban,"97")));

	if (strlen($cleiban) == 1)
		$cleiban = "0".$cleiban;

	return "FR".$cleiban.$codebanque.$codeguichet.$numerocompte.$cle;
}

$bank_to_bic = array(
	'30003' => 'SOGEFRPP', 
	'17906' => 'AGRIFRPP879', 
	'30004' => 'BNPAFRPP', 
	'50140' => '', 
	'19106' => 'AGRIFRPP891', 
	'43799' => 'SIBLFRPP', 
	'10207' => 'CCBPFRPPMTG', 
	'10228' => 'LAYDFR2W', 
	'10278' => 'CMCIFR2A', 
	'30076' => 'NORDFRPP', 
	'20041' => '', 
	'15629' => 'CMCIFR2A', 
	'10107' => 'BREDFRPP', 
	'18706' => 'AGRIFRPP887',
	'20041' => '', 
	'30066' => 'CMCIFRPP', 
	'20041' => '', 
	'11306' => 'AGRIFRPP813', 
	'10278' => 'CMCIFR2A', 
	'42559' => 'CCOPFRPP', 
	'13506' => 'AGRIFRPP835', 
	'14406' => 'AGRIFRPP844', 
	'10096' => 'CMCIFRPP', 
	'19106' => 'AGRIFRPP891', 
	'30002' => 'CRLYFRPP', 
	'17806' => 'AGRIFRPP878', 
	'15829' => '', 
	'30056' => 'CCFRFRPP', 
	'42559' => 'CCOPFRPP', 
	'14445' => 'CEPAFRPP444', 
	'16806' => 'AGRIFRPP868', 
	'15607' => 'CCBPFRPPNCE', 
	'10107' => 'BREDFRPP', 
	'40618' => 'BOUSFRPP', 
	'20041' => '', 
	'10268' => 'COURFR2T', 
	'13807' => 'CCBPFRPPNAN',
	'18707' => 'CCBPFRPPVER',

);

//PATCH SQL 
mysql_query("DROP PROCEDURE IF EXISTS patch_migrate_rib");
mysql_query("create procedure patch_migrate_rib() begin 
ALTER TABLE `webfinance_clients` ADD `iban` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
ALTER TABLE `webfinance_clients` ADD `bic` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL; 
end;");

mysql_query("CALL patch_migrate_rib()");
mysql_query("DROP PROCEDURE IF EXISTS patch_migrate_rib");

$error = 0;
$query = mysql_query("SELECT nom, id_client, rib_code_banque, rib_code_guichet, rib_code_compte, rib_code_cle
  FROM webfinance_clients
WHERE rib_code_banque != ''");
$num = mysql_num_rows($query);

echo "To migrate : $num\n\n";

for( $i = 0; $i < $num; ++$i )
{
	$row = mysql_fetch_array( $query );

	$ckcrib = checkrib($row['rib_code_banque'],$row['rib_code_guichet'],$row['rib_code_compte'],$row['rib_code_cle']);
	if($ckcrib) {
		echo "RIB error ID: ".$row['id_client']. "\n"; 
		$error++;
                continue;
	}

        $bic = $bank_to_bic[$row['rib_code_banque']];

	if($row['id_client'] == 73) $bic = 'CRMPFRP1';
	if($row['id_client'] == 117) $bic = 'PSSTFRPPCHA';
	if($row['id_client'] == 134) $bic = 'PSSTFRPPSCE';
	if($row['id_client'] == 183) $bic = 'PSSTFRPTTOU';
	if($row['id_client'] == 243) $bic = 'CMCIFR2A';
	if($row['id_client'] == 394) $bic = 'PSSTFRPPDIJ';
	if($row['id_client'] == 147) $bic = 'PSSTFRPPPAR';
	if($row['id_client'] == 619) $bic = 'CCBPFRPPVER';
	
    if(empty($bic))
 	{
    	echo "Unable to find BIC for client $row[nom] (ID $row[id_client])\n";
        $error++;
        continue;
    }

	$iban = Rib2Iban($row['rib_code_banque'],$row['rib_code_guichet'],$row['rib_code_compte'],$row['rib_code_cle']);
	mysql_query("UPDATE webfinance_clients SET iban = '".$iban."' WHERE id_client = ".$row['id_client'])  or die('insert error: ' . mysql_error());
	mysql_query("UPDATE webfinance_clients SET bic = '".$bic."' WHERE id_client = ".$row['id_client'])  or die('insert error: ' . mysql_error());
	echo "$iban => $bic \n";

}
echo "\n All done, RIB error: $error on $num entries";
echo "\n\n";

mysql_query("DROP PROCEDURE IF EXISTS patch_migrate_rib");
#mysql_query("create procedure patch_migrate_rib() begin 
#ALTER TABLE `webfinance_clients` DROP `rib_code_cle`;
#ALTER TABLE `webfinance_clients` DROP `rib_code_guichet`;
#ALTER TABLE `webfinance_clients` DROP `rib_code_compte`;
#ALTER TABLE `webfinance_clients` DROP `rib_code_banque`;
#end;");
#mysql_query("CALL patch_migrate_rib()");
mysql_query("DROP PROCEDURE IF EXISTS patch_migrate_rib");
?>
