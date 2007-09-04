var selectedItem = null;

function selectItem(e,id) {
  item = document.getElementById('mti_'+id);
  if (selectedItem) {
    selectedItem.className='node';
  }
  item.className='nodeSelected';
  selectedItem = item;
}

function openCloseTree(e,id) {
  if (!e) { e = window.event; }

  div = document.getElementById(id);
  img = document.getElementById('mthi_'+id);
  if (div.className == 'treeNode_closed') {
    newstate = 'open';
    img.src = '/imgs/icons/moins.gif';
  } else {
    newstate = 'closed';
    img.src = '/imgs/icons/plus.gif';
  }
  div.className = 'treeNode_'+newstate;

  return true;
}

// Unconditional open
function openTree(id) {
  div = document.getElementById(id);
  div.className = 'treeNode_open';

  img = document.getElementById('mthi_'+id);
  img.src = '/imgs/icons/moins.gif';
}

// Unconditional close
function closeTree(id) {
  div = document.getElementById(id);
  div.className = 'treeNode_closed';

  img = document.getElementById('mthi_'+id);
  img.src = '/imgs/icons/plus.gif';
}
