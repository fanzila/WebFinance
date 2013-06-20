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

/**
 * This class handles Webfinance documents
 */

require_once('WebfinanceCompany.php');

class WebfinanceDocument
{
  private $_document_dir = null;

  function __construct()
  {
    $this->_document_dir = dirname(__FILE__) . '/../document';
  }

  /**
   * List the documents of a company
   *
   * @param company_id int. The company ID.
   *
   * @return documents array. The array defining the documents. Example:
   *
   * \code
   * array(
   *       'file1.pdf' => array(
   *         ctime => 1348324843,
   *         size  => 4329,
   *       ),
   *       'file2.pdf' => array(
   *         ctime => 1348324843,
   *         size  => 4329,
   *       ),
   *     );
   * \endcode
   *
   */
  function ListByCompany($company_id = null)
  {
    CybPHP_Validate::ValidateInt($company_id);
    WebfinanceCompany::ValidateExists($company_id);

    $directory = $this->GetCompanyDirectory($company_id);

    if(!is_readable($directory))
      die("Unable to open directory $directory");

    // Fetch directory listing
    $file_list = glob("$directory/*");

    // Sort files by date
    usort($file_list, function($a, $b) {
        return filemtime($a) < filemtime($b);
      });

    // Fetch file information
    $files = array();
    foreach($file_list as $file)
      $files[basename($file)] = array
        (
          'size'  => filesize($file),
          'mtime' => filemtime($file),
        );

    return $files;
  }

  function GetCompanyDirectory($company_id = null)
  {
    CybPHP_Validate::ValidateInt($company_id);
    WebfinanceCompany::ValidateExists($company_id);

    $result = CybPHP_MySQL::Query(
      'SELECT nom '.
      'FROM webfinance_clients '.
      "WHERE id_client = $company_id ".
      'LIMIT 1');

    if(mysql_num_rows($result) !== 1)
      die("Unable to fetch document directory path for company $company_id");

    list($directory) = mysql_fetch_row($result);

    if(empty($directory))
      die("Directory seems empty in the database");

    return "$this->_document_dir/$directory";
  }



  /**
   * Delete a document from a company
   *
   * @param company_id int. The company ID.
   *
   * @param filename string. The file name.
   *
   * @return status bool. True if OK, false in any other case.
   *
   */
  /* function Delete($company_id = null, $filename = null) */
  /* { */
  /*   WebfinanceCompany::ValidateExists($company_id); */

    

  static function ValidateFileName($filename)
  {
    if(preg_match('/(\.\.|\/)/', $filename))
      throw new SoapFault(__CLASS__ .'->'. __FUNCTION__.'()',
        'Invalid file name syntax');
    return true;
  }
}

?>
