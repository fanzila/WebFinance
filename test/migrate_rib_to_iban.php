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

require("../htdocs/inc/main.php");

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
	'30003' => 'SOGEFRPP', //IBAN: FR7630003000200002007658895
	'17906' => 'AGRIFRPP879', //IBAN: FR7617906001120007934371707
	'30004' => 'BNPAFRPP', //IBAN: FR7630004026010001005085224
	'50140' => '', //IBAN: FR7650140750012013558000413
	'19106' => 'AGRIFRPP891', //IBAN: FR7619106000084361368334988
	'43799' => 'SIBLFRPP', //IBAN: FR7643799000010146990001590
	'10207' => 'CCBPFRPPMTG', //IBAN: FR7610207000840408400543149
	'10228' => 'LAYDFR2W', //IBAN: FR7610228028182705000020043
	'10278' => 'CMCIFR2A', //IBAN: FR7610278065170002009690124
	'30076' => 'NORDFRPP', //IBAN: FR7630076021421462930020073
	'20041' => '', //IBAN: FR0920041010020676103V02392
	'15629' => 'CMCIFR2A', //IBAN: FR7615629027010004347550118
	'10107' => 'BREDFRPP', //IBAN: FR7610107002830012501299162
	'18706' => 'AGRIFRPP887', //IBAN: FR7618706000000002389062059
	'20041' => '', //IBAN: FR5420041010124900983W03362
	'30066' => 'CMCIFRPP', //IBAN: FR7630066109130002002470143
	'20041' => '', //IBAN: FR4120041000011216530W02075
	'11306' => 'AGRIFRPP813', //IBAN: FR7611306000391912318300091
	'10278' => 'CMCIFR2A', //IBAN: FR7610278060020003006324155
	'42559' => 'CCOPFRPP', //IBAN: FR7642559000012102580950953
	'13506' => 'AGRIFRPP835', //IBAN: FR7613506100009460890300011
	'14406' => 'AGRIFRPP844', //IBAN: FR7614406470008333897343628
	'10096' => 'CMCIFRPP', //IBAN: FR7610096182180005197160133
	'19106' => 'AGRIFRPP891', //IBAN: FR7619106006514350307480042
	'30002' => 'CRLYFRPP', //IBAN: FR4430002006770000006445Q15
	'17806' => 'AGRIFRPP878', //IBAN: FR7617806002136220964078964
	'15829' => '', //IBAN: FR7615829394090002064560155
	'30056' => 'CCFRFRPP', //IBAN: FR7630056009490949000417823
	'42559' => 'CCOPFRPP', //IBAN: FR7642559000734100000111513
	'14445' => 'CEPAFRPP444', //IBAN: FR7614445004000810050808946
	'16806' => 'AGRIFRPP868', //IBAN: FR7616806054000468628400141
	'15607' => 'CCBPFRPPNCE', //IBAN: FR7615607000656032160891263
	'10107' => 'BREDFRPP', //IBAN: FR7610107001220041022085768
	'40618' => 'BOUSFRPP', //IBAN: FR7640618802610004034652478
	'20041' => '', //IBAN: FR2720041010040946888P02556
	'10268' => 'COURFR2T', //IBAN: FR7610268025793310830020059
	'13807' => 'CCBPFRPPNAN', //IBAN: FR7613807000553042151240552

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
$query = mysql_query("SELECT id_client, rib_code_banque, rib_code_guichet, rib_code_compte, rib_code_cle
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
	}
	
	$bic = $bank_to_bic[$row['rib_code_banque']];
	$iban = Rib2Iban($row['rib_code_banque'],$row['rib_code_guichet'],$row['rib_code_compte'],$row['rib_code_cle']);
	mysql_query("UPDATE webfinance_clients SET iban = '".$iban."' WHERE id_client = ".$row['id_client'])  or die('insert error: ' . mysql_error());
	mysql_query("UPDATE webfinance_clients SET bic = '".$bic."' WHERE id_client = ".$row['id_client'])  or die('insert error: ' . mysql_error());
	echo "$iban => $bic \n";

}
echo "\n All done, RIB error: $error on $num entries";
echo "\n\n";

mysql_query("DROP PROCEDURE IF EXISTS patch_migrate_rib");
mysql_query("create procedure patch_migrate_rib() begin 
ALTER TABLE `webfinance_clients` DROP `rib_code_cle`;
ALTER TABLE `webfinance_clients` DROP `rib_code_guichet`;
ALTER TABLE `webfinance_clients` DROP `rib_code_compte`;
ALTER TABLE `webfinance_clients` DROP `rib_code_banque`;
end;");
mysql_query("CALL patch_migrate_rib()");
mysql_query("DROP PROCEDURE IF EXISTS patch_migrate_rib");
?>
