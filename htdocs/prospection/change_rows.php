<?php
include("../inc/main.php");

$id_facture       = isset($_GET['id_facture']) ?$_GET['id_facture'] : 0;
$id_facture_ligne = isset($_GET['id_facture_ligne']) ?$_GET['id_facture_ligne'] : 0;
$action           = isset($_GET['raise_lower']) ?$_GET['raise_lower'] : 0;
$authorized       = array ("raise", "lower", "delete");

if (empty($id_facture_ligne) || empty($id_facture)) {
    die("invoice does not exist");
}

if (! in_array($action, $authorized)) {
    die("action does not exist");
}
   
function renum ($id_facture, $id_facture_ligne, $order = 'DESC') 
{
    $sens = ($order == 'DESC' ? '<=' : '>=');

    $query = sprintf(
                     "SELECT id_facture_ligne, ordre " .
                     "FROM webfinance_invoice_rows " .
                     "WHERE id_facture = %d "  .
                     "AND ordre %s (".
                     "   SELECT ordre " .
                     "   FROM webfinance_invoice_rows " .
                     "   WHERE id_facture_ligne = %d " .
                     ") " .
                     "ORDER BY ordre %s LIMIT 2", $id_facture, $sens, $id_facture_ligne, $order);
    
    $result = mysql_query($query);
    if (mysql_num_rows($result) != 2) {
        return false;
    }

    $query = "UPDATE webfinance_invoice_rows ".
             "SET ordre = %d "  .
             "WHERE id_facture_ligne = %d ";

    mysql_query(sprintf($query,  mysql_result($result, 0, "ordre"),mysql_result($result, 1, "id_facture_ligne"))) or wf_mysqldie();
    mysql_query(sprintf($query,  mysql_result($result, 1, "ordre"),mysql_result($result, 0, "id_facture_ligne"))) or wf_mysqldie();
}

switch ($action) {

case "raise" :
    renum($id_facture, $id_facture_ligne);
    break;
    
case "lower" :
    renum($id_facture, $id_facture_ligne, 'ASC');
    break;
    
case "delete" : 
    $query = "DELETE FROM webfinance_invoice_rows WHERE id_facture_ligne=". $id_facture_ligne;
    mysql_query($query) or wf_mysqldie();
    break;
}

header('Location: /prospection/edit_facture.php?id_facture=' 
       . $id_facture . '&id_facture_ligne=' 
       . $id_facture_ligne);
exit();
