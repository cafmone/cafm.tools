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
//---------------------------------
// Html
//---------------------------------
Ticket.confirm = function( element, id ) {
	phppublisher.modal.width = '280px';
	var modalid = phppublisher.modal.init('');
	var c = document.createElement("div");
	c.style.textAlign = 'center';
	c.style.margin = '20px';
	var t = document.createElement("div");
	t.innerHTML = 'Remove Notice '+id+'?<br><br>';
	var b = document.createElement("button");
	b.className = 'btn btn-sm btn-default';
	b.innerHTML = 'ok';
	b.type = 'button';
	b.onclick = function() {
		$('#'+modalid).modal('hide');
		phppublisher.wait();
		location.href = element.href;
	}
	c.appendChild(t);
	c.appendChild(b);

	$('#'+modalid).modal('show');
	phppublisher.modal.print(c);
	b.focus();
}

function xmlRequestObject() {
	var arg = null;
	if (typeof XMLHttpRequest != "undefined") {
		return new XMLHttpRequest();
	}
	else {
		try { return new ActiveXObject("Msxml2.XMLHTTP"); }
		catch(e) {
			try { return new ActiveXObject("Microsoft.XMLHTTP"); } 
			catch(e) { return null;  }
		}
	}
}
function get_users(element) {
	value    = element.options[element.selectedIndex].value;
	if(value != '') {
		id       = element.options[element.selectedIndex].text;
		error    = null;
		target   = 'api.php';
		params   = '';
		response = '';
		params   = '&action=plugin&plugin=ticket&command=get_supporters&id='+id;
		request = new xmlRequestObject();	
		if (request) {
			request.open("POST", target, false);
			request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			request.send(params);
			if(request.status == 200) {
				response = request.responseText;
			} else {
				error = 'error: HTTP ' +request.status+' '+request.statusText;
			}
		} else { 
			error = 'error: no xmlRequestObject'; 
		}
		if(!error) {
			if(response != '') {
				s = document.getElementById('supporter').parentNode;
				s.parentNode.style.visibility = "visible";
				s.innerHTML = response;
			} else {
				s = document.getElementById('supporter').parentNode;
				s.parentNode.style.visibility = "hidden";
				s.innerHTML = '<input type="hidden" name="supporter" id="supporter">';
			}
		} else {
			alert(error);
		}
	} else {
		s = document.getElementById('supporter').parentNode;
		s.parentNode.style.visibility = "hidden";
		s.innerHTML = '<input type="hidden" name="supporter" id="supporter">';
	}
}


