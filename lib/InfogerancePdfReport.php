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

require_once('fpdf/fpdf.php');

class InfogerancePdfReport extends FPDF
{
  function Header()
  {
    global $pdf_title;

    $result = mysql_query('SELECT value ' .
              'FROM webfinance_pref '.
              "WHERE type_pref='logo' AND owner=-1");

    if (mysql_num_rows($result) != 1)
      die(_("You didn't setup the logo for your company. ".
          "<a href='../admin/societe'>Go to 'Admin' and ".
          "'My company'</a>"));

    list($logo_data) = mysql_fetch_array($result);
    $logo_data = base64_decode($logo_data);

    // Save the logo to a temp file since fpdf cannot read from a var
    $tempfile_logo = tempnam(sys_get_temp_dir(), 'logo') . '.png';
    $logo_tmp = fopen($tempfile_logo, "w");
    fwrite($logo_tmp, $logo_data);
    fclose($logo_tmp);

    $this->Image($tempfile_logo,10,6,30);
    $this->SetFont('Arial','B',15);
    $this->Ln(10);
    $this->Cell(80);
    $this->Cell(30,10,utf8_decode($pdf_title),0,0,'C');
    $this->Ln(20);
  }

  function Footer()
  {
    $this->SetY(-15);
    $this->SetFont('Arial','I',8);
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'R');
  }
}

?>
