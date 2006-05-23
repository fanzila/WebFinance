<?php
//
// This file is part of « Webfinance »
//
// Copyright (c) 2004-2006 NBI SARL
// Author : Nicolas Bouthors <nbouthors@nbi.fr>
//
// You can use and redistribute this file under the term of the GNU GPL v2.0
//
// Tree builder class.
//
// $Id$

class Tree {
  var $data = array();
  var $openedNodes = array();
  var $rootNodeId = 0;
  var $openAllNodes = FALSE;
  var $itemSelectable = FALSE;
  var $handle_image_open = '/imgs/icons/moins.gif';
  var $handle_image_closed = '/imgs/icons/plus.gif';
  var $nodeclick_js_callback = null;
  
  function Tree($id_tree=null) {
  }

  function setData($data) {
  }

  function setOpenAll($val) {
    $this->openAllNodes = $val;
  }

  function setItemSelectable($val) {
    $this->itemSelectable = $val;
  }

  function setRootNode($id) {
    $this->rootNodeId = $id;
  }

  function addNode($id, $node, $parent=0) {
    $createNode = (!is_array($this->data[$id]));
    if ($createNode) {
      $this->data[$id] = array();
      $this->data[$id]['text'] = $node;
      $this->data[$id]['parent'] = $parent;
      $this->data[$id]['children'] = array();
    }
    if (is_array($this->data[$parent]) && !$this->data[$parent]['children'][$id]) {
      $this->data[$parent]['children'][$id] = $id;
    }
    return $createNode;
  }

  function setOpenNodes($opennodes) {
    if (is_array($opennodes)) {
      $this->openedNodes = $opennodes;
    } else {
      die("Tree:setOpenNodes argument should be an array");
    }
  }

  function setClickCallBack($cb) {
    $this->nodeclick_js_callback = $cb;
  }

  function _recurseNodeRealise($id) {
    $this_node = $this->data[$id];
    $label = $this_node['text'];

    if (array_key_exists($id, $this->openedNodes) || // User asked for it to be opened
        count($this_node['children'])==0 || // Has no child so is open defacto
        $this->openAllNodes // Caller has asked to build a fully developped tree 
       ) { 
      $tree_class="treeNode_open";
      $handle_img=$this->handle_image_open;
    } else {
      $tree_class="treeNode_closed";
      $handle_img=$this->handle_image_closed;
    }

    if ($this->itemSelectable) {
      $js = "onclick=\"";
      if ($this->nodeclick_js_callback != null) {
        $js .= $this->nodeclick_js_callback."(event, $id);";
      }
      $js .= "selectItem(event,$id);\"";
    }

    if (count($this_node['children'])) {
      $handle_js = "onclick=\"openCloseTree(event, '$id');\"";
    }

    $html = <<<EOF
<div id="mtd_$id" class="treeContent"><img src="$handle_img" class="handle" id="mthi_$id" $handle_js><span id="mti_$id" $js>$label</span></div>
<div class="$tree_class" id="$id">
EOF;
    foreach ($this_node['children'] as $child) {
      $html .= $this->_recurseNodeRealise($child);
    }
    $html .= "</div>\n";
    return $html;
  }

  function realise() {
    $html = "";
    foreach ($this->data as $id=>$node_data) {
      if ($node_data['parent'] == $this->rootNodeId) {
        $html .= $this->_recurseNodeRealise($id);
      }
    }
    print $html;
  }
}
