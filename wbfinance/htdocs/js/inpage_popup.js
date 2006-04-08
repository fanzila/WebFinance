// $Id$

// Copyright (c) 2006 NBI SARL
// Tous droits réservés
//

// FIXME : this style should go in a separate file in /css 
// CSS
document.write(
'<style type="text/css">          '+
'iframe.inpage_popup {            '+
'  border: solid 1px #124468;         '+
'  border-top: none;         '+
'  position: absolute;            '+
'  padding-top: 16px;            '+
'  background: url(\'/imgs/inpage_popup/menubar.gif\') top right no-repeat;'+
'}                                '+
'</style>                         '
);

// PAGE HOOK(s)
document.write('<iframe class="inpage_popup" name="inpage_popup" id="inpage_popup" width="200" height="300" frameborder="0" style="display: none;" src="about:blank"> />');

// OBJECT HOOKS

function mouseX(e) {
  var x;
  if (document.all) { // IE
    e = window.event;
    x = event.clientX + document.body.scrollLeft;
  } else { // The rest
    x = e.pageX;
  }

  return x;
}

function mouseY(e) {
  var y;
  if (document.all) { // IE
    e = window.event;
    y = event.clientY + document.body.scrollTop;
  } else { // The rest
    y = e.pageY;
  }

  return y;
}

// CALLBACKS

function cancel() {
  iframe = document.getElementById('inpage_popup');
  iframe.document
  if (document.all) {
    this.iframe_doc = iframe.document;
  } else {
    this.iframe_doc = iframe.contentDocument;
  }
  this.iframe.style.display='none';
}
function validate() {
  iframe = document.getElementById('inpage_popup');
  iframe.document
  if (document.all) {
    this.iframe_doc = iframe.document;
  } else {
    this.iframe_doc = iframe.contentDocument;
  }
  f = iframe_doc.getElementById('main_form');
  if (f) { f.submit(); }
}
function moveHandler(e) {
  x = mouseX(e);
  y = mouseY(e);
  this.iframe.style.left = (x-10)+'px';
  this.iframe.style.top = (y-5)+'px';
}
function settleMe() {
  alert('settle');
  this.onmousemouve = null;
  this.onclick = inpagePopupClicked;
}
function moveMe() {
  if (this.moving) {
    this.onmousemove = null;
    this.moving = 0;
  } else {
    this.onmousemove = moveHandler;
    this.moving = 1;
  }
}
function inpagePopupClicked(e) {
  y = parseInt(this.style.top);
  x = parseInt(this.style.left);

  //alert(width + ' - ' + (mouseX(e) - x) );
  pos = parseInt((width - (mouseX(e) - x))/16);

  switch (pos) {
    case 1: cancel(); break;
    case 0: validate(); break;
    default:
            moveMe();
  }

}

function inpagePopup(event, trigger, width, height, url) {

  this.iframe = document.getElementById('inpage_popup');
  iframe = document.getElementById('inpage_popup');
  if (document.all) {
    this.iframe_doc = iframe.document;
  } else {
    this.iframe_doc = iframe.contentDocument;
  }

  /* Show and place popup */
  this.iframe.style.display = "block";
  this.iframe_doc.location = url;
  this.iframe.style.width = width+'px';
  this.iframe.style.height = height+'px';

  this.width = width;
  this.height = height;
  this.x  = mouseX(event) - (width/2);
  this.y = mouseY(event) - (height/2);

  x_position = (mouseX(event) - (width/2));
  y_position = (mouseY(event)-height/2);

  if (x_position < 0) { x_position = 0; }
  if (y_position < 0) { y_position = 0; }
  this.iframe.style.left = x_position+'px';
  this.iframe.style.top =  y_position+'px';

  /* Hook callbacks for move close and validate */
  this.iframe.onclick = inpagePopupClicked;

/*
  y = findPosY(trigger);
  iframe.style.left = x+'px';
  iframe.style.top = y+'px'; */
}

