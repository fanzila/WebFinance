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

require('smarty/Smarty.class.php');
$smarty = new Smarty();

$smarty_home = dirname(__FILE__) . '/../../template/';

$smarty->template_dir = $smarty_home .'/template';
$smarty->compile_dir = $smarty->config_dir = $smarty->cache_dir
  = $smarty_home . '/cache';

$smarty->register_function('money_format', 'smarty_money_format');

function smarty_money_format($params, &$smarty) {
  if(empty($params['price']))
    $params['price'] = 0;

  return money_format('%.2n', $params['price']);
}

?>
