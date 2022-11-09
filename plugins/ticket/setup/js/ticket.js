var Ticket = new Object();
//---------------------------------
// WRAP
//---------------------------------
Ticket.wrap = function(id, openTag, closeTag) {
	element	  = document.getElementById( id );
	sel		  = SelectionRange.get(element);
	len		  = element.value.length;
	selectedText = element.value.substring(sel.start, sel.end);
	replacement  = openTag + selectedText + closeTag;
	if(sel.start != sel.end) {
		SelectionRange.insert(element, replacement);
		//PublisherEditor.print();
	}
	element.focus();
}
//---------------------------------
// PRINT
//---------------------------------
Ticket.print = function(e) {
	keycode = null;
	if (window.event) keycode = window.event.keyCode;
	else if (e) keycode = e.which;
	if(
		keycode != 16 &&
		keycode != 17 &&
		keycode != 37 &&
		keycode != 38 &&
		keycode != 39 &&
		keycode != 40
	) {
		//PublisherEditor.history.set();
	}
	return false;
}
//---------------------------------
// Html
//---------------------------------
Ticket.html = function(id, box, move_x, move_y) {
	this.box     = document.getElementById(box);
	this.element = document.getElementById(id);
	this.url     = document.getElementById('a_url');
	this.target  = document.getElementById('a_target');
	this.ok      = document.getElementById('a_ok');
	this.label   = document.getElementById('a_label');
	sel          = SelectionRange.get(this.element);
	len          = this.element.value.length;
	selectedText = this.element.value.substring(sel.start, sel.end);
	this.label.value = selectedText;

	phppublisher.modal.label = 'a';
	this.modalid = phppublisher.modal.init(this.box);
	this.box.style.display = 'block';

	(function(ok, label, url, element, modalid) { ok.onclick = function () {
		if(url.value !== '' && label.value !== '') {
			string  = '[[a '+url.value+']]';
			string += label.value;
			string += '[['+'/a]]';
			SelectionRange.insert(element, string);

			url.value = '';
			label.value = '';

			$('#'+modalid).modal('hide');
			element.focus();
		}
	}})(this.ok, this.label, this.url, this.element, this.modalid)

	$('#'+this.modalid).modal('show');

	if(this.label.value === '') {
		this.label.focus();
	} else {
		this.url.focus();
	}
}
