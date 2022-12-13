/**
 * Add String.trim()
 * 
 */
if (!String.prototype.trim) {
	(function() {
		// Make sure we trim BOM and NBSP
		var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
		String.prototype.trim = function() {
			return this.replace(rtrim, '');
		};
	})();
}

/**
 * InArray
 */
function inArray(needle, haystack) {
	var length = haystack.length;
	for(var i = 0; i < length; i++) {
		if(haystack[i] == needle) return true;
	}
	return false;
}

/**
 * Parse object attributes
 */
function objects(obj) {
	val = new Array();
	i = 0;
	for (var attrib in obj) {
		val[i] = attrib + ' : ' + obj[attrib];
		i = i+1;
	}
	val.sort();
	document.write(val.join("<br> "));
}

/**
 * Get Browser Infos
 * 
 */
var BrowserInfo = {
	swfVersion : function() {
		var isIE	= (navigator.appVersion.indexOf("MSIE") != -1) ? true : false;
		var isWin   = (navigator.appVersion.toLowerCase().indexOf("win") != -1) ? true : false;
		var isOpera = (navigator.userAgent.indexOf("Opera") != -1) ? true : false;
		var version = "false";	
		if (navigator.plugins != null && navigator.plugins.length > 0) {
			if (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]) {
				var swVer2 = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "";
				var flashDescription = navigator.plugins["Shockwave Flash" + swVer2].description;
				var descArray = flashDescription.split(" ");
				var tempArrayMajor = descArray[2].split(".");			
				var vMa = tempArrayMajor[0];
				var vMin = tempArrayMajor[1];
				var vRev = descArray[3];
				if (vRev == "") {
					vRev = descArray[4];
				}
				if (vRev[0] == "d") {
					vRev = vRev.substring(1);
				} else if (vRev[0] == "r") {
					vRev = vRev.substring(1);
					if (vRev.indexOf("d") > 0) {
						vRev = vRev.substring(0, vRev.indexOf("d"));
					}
				}
				version = vMa + "," + vMin + "," + vRev;
			}
		}
		else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.6") != -1) version = 4;
		else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.5") != -1) version = 3;
		else if (navigator.userAgent.toLowerCase().indexOf("webtv") != -1) version = 2;
		else if (isIE && isWin && !isOpera) {
			try {
				axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
				version = axo.GetVariable("$version");
			} catch (e) {}
			if (!version)
			{
				try {
					axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");			
					version = "WIN 6,0,21,0";
					axo.AllowScriptAccess = "always";
					version = axo.GetVariable("$version");
				} catch (e) {}
			}
			if (!version)
			{
				try {
					axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");
					version = axo.GetVariable("$version");
				} catch (e) {}
			}
			if (!version)
			{
				try {
					axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");
					version = "WIN 3,0,18,0";
				} catch (e) {}
			}
			if (!version)
			{
				try {
					axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
					version = "WIN 2,0,0,11";
				} catch (e) { version = "false"; }
			}	
		}
		version = version.replace(/[a-z ]/gi, ''); 
		version = version.replace(/,/g, '.'); 
		return version;
	},
	info : function() {
		info = {
			CODENAME   : navigator.appCodeName,
			APPNAME	: navigator.appName,
			VERSION	: navigator.appVersion,
			COOKIES	: navigator.cookieEnabled,
			LANGUAGE   : navigator.language,
			PLATFORM   : navigator.platform,
			USERAGENT  : navigator.userAgent,
			WxH		: screen.width +" x "+ screen.height,
			COLORDEPTH : screen.colorDepth,
			FLASH	  : this.swfVersion()
		}
		return info;
	}
};

/**
 * Drag functions
 * 
 */
var Drag = {

	obj : null,
	init : function(o, oRoot, minX, maxX, minY, maxY, bSwapHorzRef, bSwapVertRef, fXMapper, fYMapper)
	{
		o.onmousedown	= Drag.start;
		o.hmode			= bSwapHorzRef ? false : true ;
		o.vmode			= bSwapVertRef ? false : true ;
		o.root = oRoot && oRoot != null ? oRoot : o ;

		if (o.hmode  && isNaN(parseInt(o.root.style.left  ))) o.root.style.left   = "0px";
		if (o.vmode  && isNaN(parseInt(o.root.style.top   ))) o.root.style.top	= "0px";
		if (!o.hmode && isNaN(parseInt(o.root.style.right ))) o.root.style.right  = "0px";
		if (!o.vmode && isNaN(parseInt(o.root.style.bottom))) o.root.style.bottom = "0px";

		o.minX	= typeof minX != 'undefined' ? minX : 0;
		o.minY	= typeof minY != 'undefined' ? minY : 0;
		o.maxX	= typeof maxX != 'undefined' ? maxX : null;
		o.maxY	= typeof maxY != 'undefined' ? maxY : null;

		o.xMapper = fXMapper ? fXMapper : null;
		o.yMapper = fYMapper ? fYMapper : null;

		o.root.onDragStart	= new Function();
		o.root.onDragEnd	= new Function();
		o.root.onDrag		= new Function();
	},

	start : function(e)
	{
		var o = Drag.obj = this;
		e = Drag.fixE(e);
		var y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom);
		var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right );
		o.root.onDragStart(x, y);

		o.lastMouseX	= e.clientX;
		o.lastMouseY	= e.clientY;

		if (o.hmode) {
			if (o.minX != null)	o.minMouseX	= e.clientX - x + o.minX;
			if (o.maxX != null)	o.maxMouseX	= o.minMouseX + o.maxX - o.minX;
		} else {
			if (o.minX != null) o.maxMouseX = -o.minX + e.clientX + x;
			if (o.maxX != null) o.minMouseX = -o.maxX + e.clientX + x;
		}

		if (o.vmode) {
			if (o.minY != null)	o.minMouseY	= e.clientY - y + o.minY;
			if (o.maxY != null)	o.maxMouseY	= o.minMouseY + o.maxY - o.minY;
		} else {
			if (o.minY != null) o.maxMouseY = -o.minY + e.clientY + y;
			if (o.maxY != null) o.minMouseY = -o.maxY + e.clientY + y;
		}

		document.onmousemove	= Drag.drag;
		document.onmouseup		= Drag.end;

		return false;
	},

	drag : function(e)
	{
		e = Drag.fixE(e);
		var o = Drag.obj;

		var ey	= e.clientY;
		var ex	= e.clientX;
		var y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom);
		var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right );
		var nx, ny;

		if (o.minX != null) ex = o.hmode ? Math.max(ex, o.minMouseX) : Math.min(ex, o.maxMouseX);
		if (o.maxX != null) ex = o.hmode ? Math.min(ex, o.maxMouseX) : Math.max(ex, o.minMouseX);
		if (o.minY != null) ey = o.vmode ? Math.max(ey, o.minMouseY) : Math.min(ey, o.maxMouseY);
		if (o.maxY != null) ey = o.vmode ? Math.min(ey, o.maxMouseY) : Math.max(ey, o.minMouseY);

		nx = x + ((ex - o.lastMouseX) * (o.hmode ? 1 : -1));
		ny = y + ((ey - o.lastMouseY) * (o.vmode ? 1 : -1));

		if (o.xMapper)		nx = o.xMapper(y)
		else if (o.yMapper)	ny = o.yMapper(x)

		Drag.obj.root.style[o.hmode ? "left" : "right"] = nx + "px";
		Drag.obj.root.style[o.vmode ? "top" : "bottom"] = ny + "px";
		Drag.obj.lastMouseX	= ex;
		Drag.obj.lastMouseY	= ey;

		Drag.obj.root.onDrag(nx, ny);
		return false;
	},

	end : function()
	{
		document.onmousemove = null;
		document.onmouseup   = null;
		Drag.obj.root.onDragEnd(	parseInt(Drag.obj.root.style[Drag.obj.hmode ? "left" : "right"]), 
									parseInt(Drag.obj.root.style[Drag.obj.vmode ? "top" : "bottom"]));
		Drag.obj = null;
	},

	fixE : function(e)
	{
		if (typeof e == 'undefined') e = window.event;
		if (typeof e.layerX == 'undefined') e.layerX = e.offsetX;
		if (typeof e.layerY == 'undefined') e.layerY = e.offsetY;
		return e;
	}
};

/**
 * Get current mouse position
 * 
 */
var MousePosition = {
	init : function ()
	{
		this.x = '';
		this.y = '';
		this.MicrosoftModel = 0;
		if (document.all){
			this.MicrosoftModel  = 1;
			document.onmousemove = this.capture;
		}
		if (!(this.MicrosoftModel)){
			if (typeof(document.addEventListener) == "function"){
				document.addEventListener("mousemove", this.capture, true);
			} else if (document.runner){
		  		window.captureEvents(Event.MOUSEMOVE);
		 		window.onmousemove = this.capture;
			}
		}
	},
	capture : function( event ){
		if (!event){  event = window.event; }
		if (typeof(event)!="object") return;
		if (document.all){
			x = event.clientX;
			y = event.clientY + document.documentElement.scrollTop;
		} else {
			x = event.pageX;
			y = event.pageY;
		}
		MousePosition.x = x;
		MousePosition.y = y;
	},
	get : function (){
		return { x: this.x, y: this.y };
	}
};

/**
 * Get window size
 * 
 */
var WindowSize = function() {
	height = "";
	if(window.innerHeight != "undefined") {
		height = window.innerHeight;
	}
	if(document.body.clientHeight != "undefined") {
		height = document.body.clientHeight;
	}
	if(document.documentElement.clientHeight != "undefined") {
		height = document.documentElement.clientHeight;
	}

	width = "";
	if(window.innerWidth != "undefined") {
		width = window.innerWidth;
	}
	if(document.body.clientWidth != "undefined") {
		width = document.body.clientWidth;
	}
	if(document.documentElement.clientWidth != "undefined") {
		width = document.documentElement.clientWidth;
	}

	return { height: height, width: width };
};

/**
 * Get or Set Selection or Insert at Selection of an element
 */
var SelectionRange = {
	get : function(element){
		if(window.getSelection) {
			start = element.selectionStart;
			end   = element.selectionEnd;
		}
		else if( document.selection ){
			// current selection
			range = document.selection.createRange();
			// use this as a 'dummy'
			stored_range = range.duplicate();
			// select all text
			stored_range.moveToElementText( element );
			// move 'dummy' end point to end point of original range
			stored_range.setEndPoint( 'EndToEnd', range );
			// calculate start and end points
			start = parseInt(stored_range.text.length) - parseInt(range.text.length);
			end   = parseInt(start) + parseInt(range.text.length);
		}
		return {start: start, end: end};
	},
	set : function(element, start, end){
		if (element.setSelectionRange) {
			element.focus();
			element.setSelectionRange(start, end);
		}
		else if (element.createTextRange) {
			range = element.createTextRange();
			start = element.value.substring(0, start).replace(/\r/g,"");
			end   = element.value.substring(0, end).replace(/\r/g,"");
			range.collapse(true);
			range.moveEnd('character', end.length);
			range.moveStart('character', start.length);
			range.select();
		}
	},
	insert : function(element, content){
		st  = element.scrollTop;
		sl  = element.scrollLeft;
		sel = this.get(element);
		element.value = element.value.substring(0, sel.start) + content + element.value.substring(sel.end, element.value.length);
		this.set(element,  parseInt(sel.start) + parseInt(content.length), parseInt(sel.start) + parseInt(content.length));
		element.scrollTop  = st;
		element.scrollLeft = sl;
	}

};
/**
 * Serialize a form
 */
function serialize (form) {
	if (!form || form.nodeName !== "FORM") {
		return;
	}
	var i, j, q = [];
	for (i = form.elements.length - 1; i >= 0; i = i - 1) {
		if (form.elements[i].name === "") {
			continue;
		}
		switch (form.elements[i].nodeName) {
		case 'INPUT':
			switch (form.elements[i].type) {
			case 'text':
			case 'hidden':
			case 'password':
			case 'button':
			case 'reset':
			case 'submit':
				q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
				break;
			case 'checkbox':
			case 'radio':
				if (form.elements[i].checked) {
					q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
				}
				break;
			}
			break;
			case 'file':
			break; 
		case 'TEXTAREA':
			q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
			break;
		case 'SELECT':
			switch (form.elements[i].type) {
			case 'select-one':
				q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
				break;
			case 'select-multiple':
				for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
					if (form.elements[i].options[j].selected) {
						q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].options[j].value));
					}
				}
				break;
			}
			break;
		case 'BUTTON':
			switch (form.elements[i].type) {
			case 'reset':
			case 'submit':
			case 'button':
				q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
				break;
			}
			break;
		}
	}
	return q.join("&");
}

/**
 * PHPPublisher
 */
var waitElementsClick = [
		'input.submit',
		'.htmlobject_tabs li a',
		'.pageturn_head a',
		'.pageturn_bottom a',
		'#Leftbar #plugins a',
		'.actiontable input',
		'#Loginbar a',
	];
var waitElementsChange = [
		'.sort_box select.sort',
		'.sort_box select.order',
		'.sort_box select.limit'
	];

phppublisher = {
	init : function() {
		if (document.querySelectorAll){
			for (var i=0; i<waitElementsClick.length; i++){ 
				var elements=document.querySelectorAll(waitElementsClick[i]);
				for (var j=0; j<elements.length; j++){
					// check a[href]
					if(!elements[j].href || elements[j].href.indexOf("#") == -1 ) {
						if(elements[j].tagName != 'a' || elements[j].href != '#') {
							elements[j].onclick=function(){
								var msg = 'Loading ...';
								if(this.getAttribute('data-message')) {
									var msg = this.getAttribute('data-message');
								}
								phppublisher.wait(msg);
							}
						}
					}
				}
			}
			for (var i=0; i<waitElementsChange.length; i++){ 
				var elements=document.querySelectorAll(waitElementsChange[i]);
				for (var j=0; j<elements.length; j++){
					elements[j].onchange=function(){
						var msg = 'Loading ...';
						if(this.getAttribute('data-message')) {
							var msg = this.getAttribute('data-message');
						}
						phppublisher.wait(msg);
						this.form.submit();
					}
				}
			}
		}
	},
	wait : function(msg) {
		if(!msg || msg == '') {
			msg = 'Loading ...';
		}
		var body = document.getElementsByTagName('body')[0];
		var outer = document.createElement("div");
		outer.className = 'modal-overlay';
		var inner = document.createElement("div");
		inner.className = 'modal-box lead';
		var p = document.createElement("p");
		p.innerHTML = msg;
		inner.appendChild(p);
		body.appendChild(outer);
		body.appendChild(inner);
		size = WindowSize();
		inner.style.top = ((size.height / 2) - (inner.offsetHeight / 2 ))+'px';
		inner.style.left = ((size.width / 2) - (inner.offsetWidth / 2 ) - 20)+'px';
	},
	editor : {
		outer : '',
		inner : '',
		cname : '',
		oldH  : '',
		init : function( outer, inner ){
			this.outer = outer;
			this.inner = inner;
			this.cname = inner;
			var ca = document.cookie.split(';');
			for(var i=0; i<ca.length; i++) {
				var c = ca[i].split('=');
				var trim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
				if(c[0].replace(trim) == this.cname) {
					this.fullscreen(c[1]);
					break;
				}
			}
		},
		fullscreen : function( arg, outer, inner){
			if(inner && outer) {
				this.outer = outer;
				this.inner = inner;
			}
			var html   = document.getElementsByTagName('html')[0];
			var body   = document.getElementsByTagName('body')[0];
			var outer  = document.getElementById(this.outer);
			var inner  = document.getElementById(this.inner);
			var button = $('#'+this.inner+' .btn.resize');
			var close  = $('#'+this.inner+' .btn.shut');
			if(close) {
				close.click( function() {
					phppublisher.editor.fullscreen('-',outer.id,inner.id);
					//outer.style.display = 'none';
				});
			}

			if(!arg){
				arg = button.text();
			}
			if(arg == "+") {
				try {
					outer.removeChild(inner);
				} 
				catch (e) {}

				this.oldH = inner.style.height;

				size = WindowSize();
				inner.style.position = 'absolute';
				inner.style.top = 0;
				inner.style.left = 0;
				inner.style.height = size.height+"px";
				inner.style.width = '100%';
				inner.style.zIndex = '1001';
				html.style.height = '1px';
				html.style.overflow = 'hidden';
				body.style.height = '1px';
				body.style.overflow = 'hidden';
				button.text('-');
				body.appendChild(inner);
				window.scrollTo(0, 0);
				window.onresize = function(){ phppublisher.editor.fullscreen('+'); };
			}
			if(arg == "-") {
				try {
					body.removeChild(inner);
				} 
				catch (e) {}

				inner.style.position = '';
				inner.style.top = '';
				inner.style.left = '';
				inner.style.width = '';
				inner.style.height = this.oldH;
				inner.style.zIndex = '';
				html.style.height = '';
				html.style.overflow = '';
				body.style.height = '';
				body.style.overflow = '';
				button.text('+');
				outer.appendChild(inner);
				window.onresize = function(){};
			}
			if(this.cname != '') {
				document.cookie = this.cname+"="+arg+";";
			}
		}
	},

	modal : {
		width : '50%',
		height : '',
		label : '',
		init : function(data, id) {

			if(typeof(id) == 'undefined') {
				id = 'phppublisherModal';
			}

			var body = document.getElementsByTagName('body')[0];
			old = document.getElementById(id);
			if(old) {
				body.removeChild(old);
			}

			var modal = document.createElement("div");
			modal.id = id;
			modal.className = 'modal';
			modal.tabIndex = '-1';
			modal.style.zIndex = '1041';
			modal.setAttribute('role', 'dialog');

			var helper = document.createElement("div");
			helper.style.display = 'table';
			helper.style.height = '100%';
			helper.style.width = '100%';

			var dialog = document.createElement("div");
			dialog.className = 'modal-dialog';
			dialog.style.display = 'table-cell';
			dialog.style.verticalAlign = 'middle';
			dialog.style.width = this.width;
			dialog.style.overflow = 'initial';
			dialog.setAttribute('role', 'document');

			var content = document.createElement("div");
			content.className = 'modal-content';
			content.style.margin = '0 auto 0 auto';
			content.style.width = 'inherit';
			content.style.height = 'inherit';
			content.style.position = 'relative';

			var header = document.createElement("div");
			header.className = 'modal-header';
			header.innerHTML = this.label;

			var close = document.createElement("button");
			close.className = 'close';
			close.innerHTML = '&times;';
			close.setAttribute('data-dismiss', 'modal');

			if(typeof data == 'object') {
				var middle = data;
			} else {
				var middle = document.createElement("div");
				middle.className = 'modal-body';
				middle.style.minHeight = '150px';
				middle.style.display = 'block';
				middle.style.height = this.height;
				middle.style.verticalAlign = 'middle';
				middle.innerHTML = data;
			}
			this.content = middle;

			header.appendChild(close);
			content.appendChild(header);
			content.appendChild(middle);
			dialog.appendChild(content);
			helper.appendChild(dialog);
			modal.appendChild(helper);
			body.appendChild(modal);

			return id;
		},
		print : function(data) {
			if(typeof data == 'object') {
				this.content.innerHTML = '';
				this.content.appendChild(data);
			} else {
				this.content.innerHTML = data;
			}
		}
	},
	
	select : {
		submit : false,
		width  : '80%',
		height : '358',
		target : '',
		element : '',
		content: '',
		init : function(element, label, targetid) {
			if(typeof(label) == 'undefined') {
				label = '';
			}
			if(typeof targetid !== 'undefined') {
				this.target = document.getElementById(targetid);
			}
			
			this.element = element;

			this.multiple = false;
			if(typeof this.element.multiple !== 'undefined' && this.element.multiple !== false) {
				this.multiple = true;
			}
			
			var content = document.createElement("div");
			content.style.padding = '15px';
			content.style.overflow = 'auto';
			content.style.marginBottom = '15px';
			content.style.height = this.height+'px';
			content.tabIndex = '0';
			content.style.outline = '0';
			content.addEventListener("mouseover", function(event) {
				this.focus();
			});

			var wait = document.createElement("div");
			wait.style.display = 'block';
			wait.style.textAlign = 'center';
			wait.style.zIndex = '1500';
			wait.style.height = (this.height-30)+'px';
			//wait.innerHTML = 'Loading ..';

			var box = document.createElement("div");
			box.className = 'list-group';

			var find = document.createElement("input");
			find.className = 'form-control text';

			find.style.width = '250px';
			find.style.position = 'absolute';
			find.style.top = '8px';
			find.style.right = '50px';
			find.id = 'PublisherSelectSearch';
			find.tabIndex = '0';
			find.setAttribute('placeholder', 'Search ..');

			content.appendChild(wait);
			content.appendChild(box);

			this.content = content;
			this.wait = wait;
			this.box = box;

			phppublisher.modal.width = this.width;
			phppublisher.modal.label = label+find.outerHTML;
			this.modalid = phppublisher.modal.init(content);
			
			//
			if(this.multiple === true) {
				var btn = document.createElement("button");
				btn.className = 'btn btn-default';
				btn.innerHTML = 'Submit';
				btn.addEventListener("click", function(event) {
					phppublisher.select.commit();
				});
			
				var footer = document.createElement("div");
				footer.className = 'modal-footer';
				footer.style.marginTop = '-15px';
				
				footer.appendChild(btn);
				
				target = document.getElementById(this.modalid);
				target = target.getElementsByTagName('div')[0];
				target = target.getElementsByTagName('div')[0];
				target = target.getElementsByTagName('div')[0];
				target.appendChild(footer);
			}

			// add event to search input
			document.getElementById('PublisherSelectSearch').addEventListener("keyup", function(event) {
				if(event.keyCode == 13) {
					phppublisher.select.find(this, event);
				}
			});

			$('#'+this.modalid).modal({ backdrop: true, show: true, keyboard: true});
			test = setTimeout(function() {
				phppublisher.select.print();
			}, 0);

			return null;
		},

		print : function() {

			for(var i=0; i < this.element.options.length; i++) {
				opt = document.createElement("button");
				opt.className = 'list-group-item list-group-item-action';
				opt.id = 'opt'+i;
				if(typeof this.element.options[i].selected !== 'undefined' && this.element.options[i].selected !== false) {
					opt.className = 'list-group-item list-group-item-action active';
					marked = opt;
				}
				opt.style.textAlign = 'left';
				opt.innerHTML = this.element.options[i].text;
				if(this.multiple === false) {
					(function(element,x) { 
						opt.addEventListener('click', function(e) { 
							element.options[x].selected = 'selected';
							phppublisher.select.commit(x); 
						}) 
					})(this.element,i);
				}
				if(this.multiple === true) {
					(function(element,x) { 
						opt.addEventListener('click', function(e) { 
							box = document.getElementById(element.id+'Box');
							if(!box) {
								box = '';
							}
							if(element.options[x].selected !== false) {
								this.className = 'list-group-item list-group-item-action';
								element.options[x].selected = '';
								if(box !== '') {
									text = box.innerHTML;
									result = text.replace(element.options[x].label+'<br>','');
									box.innerHTML = result;
								}
							} else {
								this.className = 'list-group-item list-group-item-action active';
								element.options[x].selected = 'selected';
								if(box !== '') {
									box.innerHTML = box.innerHTML +element.options[x].label+'<br>';
								}
							}
						}) 
					})(this.element,i);
				}
				this.box.appendChild(opt);
			}
			// scroll to marked option
			if(typeof(marked) != 'undefined' && this.multiple === false) {
				marked.scrollIntoView();
			}
			this.wait.style.display = 'none';
		},

		find : function(element) {

			this.box.innerHTML = '';
			value = element.value;
			if(value != '') {
				try {
					regex = new RegExp(value, "gi");
					for(var i=0; i < this.element.options.length; i++) {
						res = this.element.options[i].text.search(regex);
						if(res != -1) {
							opt = document.createElement("button");
							opt.className = 'list-group-item list-group-item-action';
							opt.id = 'opt'+i;
							if(typeof this.element.options[i].selected !== 'undefined' && this.element.options[i].selected !== false) {
								opt.className = 'list-group-item list-group-item-action active';
							}
							opt.style.textAlign = 'left';

							if(this.multiple === false) {
								(function(element,x) { 
									opt.addEventListener('click', function(e) { 
										element.options[x].selected = 'selected';
										phppublisher.select.commit(x); 
									}) 
								})(this.element,i);
							}
							if(this.multiple === true) {
								(function(element,x) { 
									opt.addEventListener('click', function(e) { 
										box = document.getElementById(element.id+'Box');
										if(!box) {
											box = '';
										}
										if(element.options[x].selected !== false) {
											this.className = 'list-group-item list-group-item-action';
											element.options[x].selected = '';
											if(box !== '') {
												text = box.innerHTML;
												result = text.replace(element.options[x].label+'<br>','');
												box.innerHTML = result;
											}
										} else {
											this.className = 'list-group-item list-group-item-action active';
											element.options[x].selected = 'selected';
											if(box !== '') {
												box.innerHTML = box.innerHTML +element.options[x].label+'<br>';
											}
										}
									}) 
								})(this.element,i);
							}
							r = new RegExp('('+value+')', "gi");
							opt.innerHTML = this.element.options[i].text.replace(r, '<b>$1</b>');
							this.box.appendChild(opt);
						}
					}
					// scroll to top
					this.box.scrollIntoView(true);
				}
				catch(e) {
					//console.log(e.message);
				}
			} else {
				this.print();
			}
		},

		commit : function(index) {
			$('#'+this.modalid).modal('hide');
			try {
				this.element.onchange();
			} 
			catch(e) {}
			if(typeof index !== '' && this.target !== '') {
				this.target.value = this.element.options[index].value;
			}
			if(this.submit == true) {
				phppublisher.wait();
				this.element.form.submit();
			}
		}
	},
	//---------------------------------------------
	// Copy to clipboard
	//---------------------------------------------
	clipboard : function (id) {
		url = document.getElementById(id);
		input = document.createElement("input");
		input.value = url.value;
		input.type = 'text';
		input.style.position = 'absolute';
		input.style.top = '-2000px';
		body = document.getElementsByTagName('body')[0];
		body.appendChild(input);
		input.select();
		input.setSelectionRange(0, 99999);
		document.execCommand("copy");
		body.removeChild(input);
	}
}

function sleep(milliseconds) {
	var start = new Date().getTime();
	for (var i = 0; i < 1e7; i++) {
		if ((new Date().getTime() - start) > milliseconds){
			break;
		}
	}
}
