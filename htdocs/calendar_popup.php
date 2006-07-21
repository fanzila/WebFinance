<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// $Id$

require("inc/main.php");
require("top_popup.php");

$title = _("Pick a date");

if ((isset($_GET['jour'])) && (preg_match("/([0-9]{4})([0-9]{2})([0-9]{2})/", $_GET['jour'], $matches))) {
  $year = $matches[1];
  $month = $matches[2];
  $day = $matches[3];
} else {
  if ((isset($_GET['mois'])) && (preg_match("/([0-9]{2})([0-9]{4})/", $_GET['mois'], $matches))) {
    $year = $matches[2];
    $month = $matches[1];
  } else {
    $year = strftime("%Y", time());
    $month = strftime("%m", time());
  }
  $_GET['jour']=$year . $month."01";
}

function mycal($year, $month) {
    global $on, $off;
    global $day;

    $td = '<td class="cal_day">';
    $td_ = '</td>';
    $tr = '<tr>';
    $tr_ = '</tr>';
    $table = '<table width="100%" border="0" height="100%" cellspacing="1" cellpadding="0">';
    $table_ = '</table>';

    $start_day = strftime('%u', mktime(0, 0, 0, $month, 1, $year));
    $days_in_month = date('j', mktime(0, 0, 0, $month + 1, 0, $year));

    $prev_month = strftime("%m%Y", mktime(0,0,0, $month-1, 1, $year));
    $next_month = strftime("%m%Y", mktime(0,0,0, $month+1, 1, $year));

    $cal = "$table\n$tr";
    $cal .= '<td valign="middle"><a href="?field='.$_GET['field'].'&mois='.$prev_month.'&autosubmit='.$_GET['autosubmit'].'"><img src="/imgs/icons/left_24.gif" alt="" /></a></td>';
    $cal .= '<td align="center" colspan="5">'.ucfirst(strftime('%B %Y', mktime(0, 0, 0, $month, 1, $year))).'</td>';
    $cal .= '<td valign="middle"><a href="?field='.$_GET['field'].'&mois='.$next_month.'&autosubmit='.$_GET['autosubmit'].'"><img src="/imgs/icons/right_24.gif" alt="" /></a></td>';
    $cal .= "$tr_\n$tr";
    for ($i = 2 - $start_day ; $i <= 8 - $start_day ; $i++)
        $cal .= '<td class="cal_header">'. ucfirst(strftime('%a', mktime(0, 0, 0, $month, $i, $year))) . '</td>';
    $cal .= "$tr_\n$tr";

    $line_day = $start_day;
    $cal .= str_repeat($td . $td_, ($start_day - 1));

    for ($i = 1 ; $i <= $days_in_month ; $i++) {
        if ($line_day == 8) {
            $line_day = 1;
            $cal .= "$tr_\n$tr";
        }
        $cal .= sprintf('<td onclick="clickDay(this);" class="cal_day%s" id="%s">%s</td>',
                        ($i==$day)?"_selected":"", strftime("%Y%m%d", mktime(0,0,0, $month,$i,$year)), $i);
        $line_day++;
    }

    $cal .= "$tr_\n$tr<td height=\"30\" align=\"center\" colspan=\"7\"><input type=\"button\" style=\"width: 160px; border: solid 1px black;\" value=\""._('Validate')."\" onclick=\"validateChoice();\" /></td>$tr_$table_";

    return($cal);
}
?>

<script language="javascript" type="text/javascript">
var selectedDay = '<?= $_GET['jour'] ?>';
var autosubmit = <?= (isset($_GET['autosubmit']))?$_GET['autosubmit']:0; ?>

function clickDay(td) {
  day = document.getElementById(selectedDay);
  if (day) {
    day.className = 'cal_day';
  }

  day = document.getElementById(td.id);
  day.className = 'cal_day_selected';

  selectedDay = td.id;

  field = window.parent.document.getElementById('<?= $_GET['field'] ?>');
  if (field) {
    tmp = td.id;
    tmp = tmp.replace(/(....)(..)(..)/, "$3/$2/$1");
    field.value = tmp;
  }
}

function validateChoice() {
  day = document.getElementById(selectedDay);
  if (!day) {
    alert('Veuillez choisir une date !');
  } else {
    popup = window.parent.document.getElementById('inpage_popup');
    popup.style.display = 'none';
    if (autosubmit) {
      field = window.parent.document.getElementById('<?= $_GET['field'] ?>');
      if (field) {
        field.form.submit();
      }
    }
  }
}

</script>

<div style="height: 185px; width: 185px; margin: auto auto auto auto;">
<?php
echo (mycal($year, $month));
?>
</div>
