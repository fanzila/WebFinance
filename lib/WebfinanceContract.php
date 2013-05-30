<?php
  /*
   * Copyright (C) 2013 Cyril Bouthors <cyril@boutho.rs>
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

/**
 * This class handles Webfinance contracts
 */

class WebfinanceContract
{
  private $_contract_dir = null;

  function __construct()
  {
    $this->_contract_dir = dirname(__FILE__) . '/../contract';
  }

  /**
   * List the contracts templates
   *
   * @return contract_templates array. The array defining the contract
   * templates. Example:
   *
   * \code
   * array(
   *       'template1',
   *       'template2',
   *       'template3'
   *     );
   * \endcode
   *
   */
  function ListTemplates()
  {
    if (!$dh = opendir($this->_contract_dir))
      die("Unable to open directory $this->_contract_dir");

    $templates = array();

    while (($template = readdir($dh)) !== false)
      {
        if(!preg_match('/\.md$/', $template))
          continue;

        $templates[] = preg_replace('/\.md$/', '', $template);
      }

    closedir($dh);

    sort($templates);
    return $templates;
  }
}

?>
