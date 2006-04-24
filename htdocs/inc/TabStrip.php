<?php

// $Id$
// IDEA : this object builds a DHTML tabstrip. Find a way (callback, with
// buffered php output probably) to have the including of this .php setup a
// call for onglets.js.
// FIXME : onglets.js should be renamed tabStrip.js

class TabStrip {
  var $nb_tab = 0;
  var $title = array();
  var $content = array();
  var $width = 740; // default pixel width of the tabstrip

  function TabStrip($nb_tabs=0, $title="") {
  }

  function includeTab($title, $file, $id=0) {
    // FIXME : file include 
  }

  function addTab($title, $content, $id="") {
    if ($id == 0) {
      $id=count($this->title);
    }
    $this->title[$id] = $title;
    $this->content[$id] = $content;

    $this->nb_tab++;
  }

  function setTitle($id, $title) {
  }

  function realise() {
    $html = <<<EOF
<input type="hidden" name="focused_onglet" value="$this->focusedTab" />
<table width="$this->width" border="0" cellspacing="0" cellpadding="0" class="tabstrip">
<tr class="onglets">
EOF;
    foreach ($this->title as $id=>$t) {
      $html .= sprintf('<td id="handle_%s" onclick="focusOnglet(\'%s\');">%s</td>',
                        $id, $id, _($t) );
      if (strlen($this->content[$id]) == 0) {
        die("TabStrip::Tab $id has no content");
      }
    }
    $html .= <<<EOF
  <td style="background: none;" width="100%"></td>
</tr>
<tr style="vertical-align: top;">
<td colspan="6" class="onglet_holder">
EOF;
    foreach ($this->title as $id=>$t) {
      $html .= sprintf('<div style="display: none;" id="tab_%s">', $id);
      $html .= $this->content[$id];
      $html .= sprintf('</div>');
    }

    $html .= "<script type=\"text/javascript\">focusOnglet('".$this->focused_onglet."');</script>";

    print $html;
  }
}

?>
