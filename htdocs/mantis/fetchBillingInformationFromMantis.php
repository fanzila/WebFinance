<?php

  /*
   * Copyright (C) 2012 Cyril Bouthors <cyril@bouthors.org>
   * 
   * This program is free software: you can redistribute it and/or modify it
   * under the terms of the GNU General Public License as published by the
   * Free Software Foundation, either version 3 of the License, or (at your
   * option) any later version.
   * 
   * This program is distributed in the hope that it will be useful, but
   * WITHOUT ANY WARRANTY; without even the implied warranty of
   * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
   * Public License for more details.
   * 
   * You should have received a copy of the GNU General Public License along
   * with this program. If not, see <http://www.gnu.org/licenses/>.
   * 
   */

require_once('../inc/main.php');
$title = _('Mantis');
$roles='manager,accounting,employee';
require_once('../top.php');

// Ugly link from Webfinance-Mantis
$mantis2webfinance = array(
  281 => 80,
  282 => 71,
  283 => 68,
  284 => 73,
  285 => 89,
  287 => 92,
  288 => 111,
  294 => 124,
  292 => 113,
  293 => 131,
  295 => 96,
  296 => 127,
  297 => 139,
  299 => 138,
  300 => 147,
  302 => 130,
  303 => 112,
  306 => 151,
  311 => 166,
  314 => 177,
  315 => 64,
  318 => 189,
  319 => 188,
  322 => 211,
  325 => 192,
  326 => 207,
  329 => 233,
  331 => 238,
  332 => 237,
  334 => 270,
  335 => 274,
  336 => 258,
  337 => 272,
  338 => 0, # internal project 'ISVTEC'
  340 => 283,
  341 => 290,
  346 => 295,
  347 => 163,
  348 => 298,
  349 => 301,
  350 => 302,
  351 => 304,
  352 => 305,
  353 => 309,
  354 => 306,
  355 => 281,
  356 => 316,
  357 => 319,
  358 => 321,
  361 => 330,
  362 => 331,
  363 => 325,
  364 => 329,
  366 => 96,
  367 => 340,
  368 => 337,
  369 => 327,
  370 => 352,
  371 => 349,
  372 => 355,
  373 => 344,
  374 => 356,
  375 => 342,
  376 => 364,
);

mysql_connect('localhost', 'root')
    or die(mysql_error());

mysql_query('SET character_set_results = utf8')
  or die(mysql_error());

mysql_select_db('mantis')
  or die(mysql_error());

function fetchBillingInformationFromMantis($start_date, $end_date) {
  global $mantis2webfinance;

  $start_date = mysql_real_escape_string($start_date) . ' 00:00:00';
  $end_date   = mysql_real_escape_string($end_date)   . ' 00:00:00';

  $res = mysql_query('SELECT bug.id, bug.summary, user.realname AS client, '.
         '  project.name AS project_name, SUM(bugnote.time_tracking) AS time, '.
         '  bug.date_submitted, handler.realname AS handler, '.
         '  project.id AS project_id '.
         'FROM mantis_bug_table bug '.
         'JOIN mantis_bugnote_table bugnote ON bug.id = bugnote.bug_id '.
         'JOIN mantis_project_table project ON bug.project_id = project.id '.
         'JOIN mantis_user_table user ON user.id = bug.reporter_id '.
         'JOIN mantis_user_table handler ON handler.id = bug.handler_id '.
         'WHERE '.
         "  bugnote.last_modified BETWEEN UNIX_TIMESTAMP('$start_date') ".
         "    AND UNIX_TIMESTAMP('$end_date') ".
         'GROUP BY bugnote.bug_id '.
         'ORDER BY project.id ')
    or die(mysql_error());

  $billing = array();

  setlocale(LC_TIME, 'fr_FR');

  // Prepare billing information
  while($row = mysql_fetch_assoc($res)) {
    if(!isset($mantis2webfinance[$row['project_id']]))
      die("Unable to fetch \$mantis2webfinance[$row[project_id]] information ".
        "for project $row[project_name]");

    # Skip internal, non billable projects
    if($mantis2webfinance[$row['project_id']] == 0)
      continue;

    $time = sprintf('%dh%02d', floor($row['time'] / 60), $row['time'] % 60);

    $description = sprintf("%s d'infogérance ponctuelle.\n" .
                   "Traitement du ticket #%d ouvert le %s: %s",
                   $time,
                   $row['id'],
                   strftime('%x', $row['date_submitted']),
                   $row['summary']);

    $webfinance_project_id = $mantis2webfinance[$row['project_id']];
    if(!isset($billing[$webfinance_project_id]))
      $billing[$webfinance_project_id] = array();

    $billing[$webfinance_project_id][$row['id']] =
      array(
        'description'           => $description,
        'quantity'              => $row['time'] / 60,
        'price'                 => 55,
        'mantis_project_name'   => $row['project_name'],
        'time'                  => $row['time'],
        'mantis_ticket_summary' => $row['summary'],
        'mantis_project_id'     => $row['project_id'],
      );

    // Process total time
    if(!isset($total_time[$webfinance_project_id]))
      $total_time[$webfinance_project_id] = 0;

    $total_time[$webfinance_project_id] += $row['time'];
  }

  // Process total time
  foreach($total_time as $webfinance_project_id => $time) {

    $time_to_deduce = 15;
    if($time < 15)
      $time_to_deduce = $time;

    $description =
      "Déduction de l'infogérance ponctuelle comprise dans le contrat";

    $billing[$webfinance_project_id][0] = array(
      'description'           => $description,
      'mantis_ticket_summary' => $description,
      'quantity'              => - $time_to_deduce / 60,
      'time'                  => - $time_to_deduce,
      'price'                 => 55,
      'mantis_project_name'   => '',
      'mantis_project_id'     => $row['project_id'],
    );
  }

  return $billing;
}
?>

<form>
  <select name="month">
  <?
  $year = ( isset($_GET['year']) ? $_GET['year'] : strftime('%Y', time()));
  $month = ( isset($_GET['month']) ? $_GET['month'] : strftime('%m', time()));

  for($i=1; $i<=12; $i++) {
    $month_name = strftime('%B', mktime(0, 0, 0, $i));
    $selected='';
    if($i == $month)
      $selected='selected';
    echo "<option value=\"$i\" $selected> $month_name </option>\n";
  }
  ?>    
  </select>

  <select name="year">
  <?
  for($i=2050; $i>=2012; $i--) {
    $selected='';

    if($i == $year)
      $selected='selected';

    echo "<option value=\"$i\" $selected> $i </option>\n";
  }
  ?>    
  </select>

  <input type="submit">

</form>

<table border="1">

 <tr>
  <th>Client</th>
  <th>Description</th>
  <th>Time</th>
  <th>Price</th>
 </tr>

<?

$date_start = "$year-$month-01";
$date_end = "$year-" . ($month + 1) . "-01";

// Print preview
foreach(fetchBillingInformationFromMantis($date_start, $date_end)
  as $webfinance_id => $billing) {

  $total = 0;

  foreach($billing as $ticket_number => $ticket) {
    $url_ticket =
      "https://www.isvtec.com/infogerance/ticket/view.php?id=$ticket_number";

    $url_webfinance = '/prospection/fiche_prospect.php?onglet=biling&id=' .
      $mantis2webfinance[$ticket['mantis_project_id']];

    $price = round($ticket['price'] * $ticket['quantity'], 2);

    $time_human_readable = sprintf('%dh%02d',
                           floor(abs($ticket['time']) / 60),
                           abs($ticket['time']) % 60);

    echo "<tr>\n  <td> <a href=\"$url_webfinance\">$ticket[mantis_project_name]</a></td>\n";

    if($ticket_number == 0)
      echo "  <td> $ticket[mantis_ticket_summary] </td>\n";
    else
      echo "  <td> <a href=\"$url_ticket\"".
      ">$ticket[mantis_ticket_summary]</a> </td>\n";

    echo "  <td align=\"right\"> $time_human_readable</td>\n";
    echo "  <td align=\"right\"> $price&euro; </td>\n";
    echo "</tr>\n";

    $total += $ticket['time'];
  }

  $total_time_client_human_readable = sprintf('%dh%02d',
                                      floor($total / 60),
                                      $total % 60);

  $total_price = round($total * $ticket['price'] / 60, 2);

  echo "<tr> <td></td> <td align=\"right\"><b>TOTAL INVOICED</b></td> ".
  "<td align=\"right\"><b>$total_time_client_human_readable</b></td> ".
    "<td align=\"right\"><b>$total_price&euro;</b></td></tr>\n";
}

?>

</table>
</body>
</html>

<!--

TODO:

Bouton submit:
→ ajoute les factures dans Webfinance, voir Facture->create()
→ livre les factures (email, postal), voir ../../cronjobs/periodic_invoices.php
→ gère les prelevements, voir ../../cronjobs/periodic_invoices.php

-->
