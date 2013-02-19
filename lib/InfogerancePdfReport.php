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
require_once('WebfinancePreferences.php');

class InfogerancePdfReport extends FPDF
{
  function Header()
  {
    $prefs = new WebfinancePreferences;

    // Save the logo to a temp file since fpdf cannot read from a var
    $tempfile_logo = tempnam(sys_get_temp_dir(), 'logo') . '.png';
    $logo_tmp = fopen($tempfile_logo, "w");
    fwrite($logo_tmp, $prefs->prefs['logo']);
    fclose($logo_tmp);

    // Logo
    $this->Image($tempfile_logo, 90, 5, 25, 0, 'PNG');

    // UTF8 to ISO
    $prefs->prefs['societe']->invoice_top_line1 = preg_replace(
      "/\xE2\x82\xAC/","EUROSYMBOL",
      $prefs->prefs['societe']->invoice_top_line1);

    // FPDF ne support pas l'UTF-8
    $prefs->prefs['societe']->invoice_top_line1 = utf8_decode(
      $prefs->prefs['societe']->invoice_top_line1);

    $prefs->prefs['societe']->invoice_top_line1 = preg_replace(
      "/EUROSYMBOL/",
      chr(128),
      $prefs->prefs['societe']->invoice_top_line1);

    // Display text headers
    $this->SetFont('Arial','',5);
    $logo_size = getimagesize($tempfile_logo);
    $logo_height=$logo_size[1]*25/$logo_size[0];
    $this->SetXY(10,$logo_height+5);
    $this->Cell(190, 5, $prefs->prefs['societe']->invoice_top_line1, 0, 0, "C");
    $this->SetLineWidth(0.3);
    $this->SetXY(10,$logo_height+8);
    $this->Cell(190, 5,
      utf8_decode($prefs->prefs['societe']->invoice_top_line2), "B", 0, "C");
    $this->Ln(10);
  }

  function Footer()
  {
    $this->SetY(-15);
    $this->SetFont('Arial','I',8);
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'R');
  }
}

?>
