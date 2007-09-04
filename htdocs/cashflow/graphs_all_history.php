<?php
/*
   This file is part of Webfinance.

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
?>
<?php
// $Id: graphs_all_history.php 531 2007-06-13 12:32:31Z thierry $

require("../inc/main.php");

$title = _("Financial graphics");
$roles = 'manager,accounting';
require("../top.php");
require("nav.php");

?>

<img src="../graphs/cashflow.php?account=&end_date=&width=800&height=400&movingaverage=0" />

<img src="../graphs/income_outgo_all.php?account=&width=800&height=400"/>

<img src="../graphs/categ_all.php?type=category&sign=positive&plot=piecharts"/>

<img src="../graphs/categ_all.php?type=category&sign=negative&plot=piecharts"/>

<img src="../graphs/categ_all.php?type=category&sign=positive&plot=bars"/>

<img src="../graphs/categ_all.php?type=category&sign=negative&plot=bars"/>

<?
$Revision = '$Revision: 531 $';
require("../bottom.php");
?>
