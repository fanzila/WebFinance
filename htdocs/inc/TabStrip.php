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

  function addTab($title, $content, $id=null) {
    if ($id == null) {
      $id=count($this->title);
    }
    if (!$this->focusedTab) { $this->focusedTab = $id; } // First added tab is focused by default
    $this->title[$id] = $title;
    $this->content[$id] = $content;

    $this->nb_tab++;
  }

  function setFocusedTab($id) {
    $this->focusedTab = $id;
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
    $colspan = count($this->title)+1;
    $html .= <<<EOF
  <td style="border: none; background: none;" width="100%"></td>
</tr>
<tr style="vertical-align: top;">
<td colspan="$colspan" class="onglet_holder">
EOF;
    foreach ($this->title as $id=>$t) {
      $html .= sprintf('<div style="display: none;" id="tab_%s">'."\n", $id);
      $html .= $this->content[$id];
      $html .= sprintf('</div>'."\n");
    }

    $html .= <<<EOF
</td>
</tr>
</table>
EOF;
    $html .= "<script type=\"text/javascript\">focusOnglet('".$this->focusedTab."');</script>";

    print $html;
  }
}

?>
