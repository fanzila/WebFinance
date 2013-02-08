<?php
  /*
   * Copyright (C) 2013 Cyril Bouthors <cyril@bouthors.org>
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
 * This class handles Webfinance preferences
 */

class WebfinancePreferences
{
  public $prefs;

  function __construct()
  {
    $result = mysql_query('SELECT type_pref, value ' .
              'FROM webfinance.webfinance_pref '.
              "WHERE owner = -1")
      or die(mysql_error());

    while($row = mysql_fetch_assoc($result))
    {

      $base64_encoded = array(
        'rib',
        'paybox',
        'logo',
        'societe',
        'mail_quote_fr_FR',
        'mail_invoice_fr_FR',
        'mail_user_fr_FR',
        'mail_paypal_en_US',
        'mail_invoice_en_US',
        'mail_quote_en_US',
        'mail_user_en_US',
        'mail_paypal_fr_FR',
      );

      if(in_array($row['type_pref'], $base64_encoded))
        $row['value'] = base64_decode($row['value']);

      # Detect if we need to unserialize
      if(strpos($row['value'], ':"stdClass":') !== false)
        $row['value'] = unserialize($row['value']);

      $this->prefs[$row['type_pref']] = $row['value'];
    }
    
    if (!isset($this->prefs['logo']))
      die(_("You didn't setup the logo for your company. ".
          "<a href='../admin/societe'>Go to 'Admin' and ".
          "'My company'</a>"));
  }
}

?>
