<script type="text/javascript">
function tr_hover(element) {
	x = element.className.match(/tr_hover/g);
	if(x == null) { element.className = element.className + " tr_hover"; }
	else { element.className = element.className.replace(/ tr_hover/g, ""); }
}
function tr_click(element, arg) {
	FileBrowser.preview( document.getElementById(arg).value );
}
</script>

<script type="text/javascript">
function ButtonPanelToggle(arg) {
	if(arg == '-') {
		$('.breadcrumps_panel').css('display', 'none');
		$('.button_panel').css('display', 'block');
	}
	if(arg == '+') {
		$('.button_panel').css('display', 'none');
		$('.breadcrumps_panel').css('display', 'block');	}
}

var FileBrowser = new Object();

FileBrowser.init = function( callback ){
	//document.getElementById('FileBrowser').style.display = "block";
	this.callback = callback;
	this.root     = '/images/';
	//this.form     = document.forms['reloadform'];
	//this.type = '';
	this.pre( false );

}

FileBrowser.insert = function(){
	path = document.getElementById('url').value;
	file = document.getElementById('file').value;
	type = document.getElementById('filetype').value;
	if(file != '') {
		path = path +'/'+ file;
		eval(this.callback)(path, type);
	}
}

FileBrowser.newfile = function( action, mode ){
	this.preload( true );
	element = document.forms['reloadform'];
	Name = prompt("", "");
	if (Name != "" && Name != null) {
		element.elements[action].value = mode;
		element.elements['new_dir'].value = Name;

		html = FileBrowser.ajax(element.id);
		element.elements[action].value = "select";
		element.elements['new_dir'].value = '';
		if(html == '200'){
			html = FileBrowser.ajax(element.id);
			$('.filebrowser').replaceWith(html);
			FileBrowser.handlers();
		} else {
			alert( html );
		}
	}
	this.preload( false );
}

FileBrowser.del = function( action ){
	this.preload( true );
	element = document.forms['reloadform'];
	element.elements[action].value = "delete";
	document.getElementById('identifier').value = document.getElementById('file').value;
	html = FileBrowser.ajax(element.id);
	element.elements[action].value = "select";
	if(html == '200'){
		html = FileBrowser.ajax(element.id);
		$('.filebrowser').replaceWith(html);
		FileBrowser.handlers();
	} else {
		alert( html );
	}
	this.preload( false );
}

FileBrowser.rename = function( file, action ){
	if(	file != '' ) {
		this.preload( true );
		element = document.forms['reloadform'];
		element.elements[action].value = "rename";
		document.getElementById('identifier').value = document.getElementById('file').value;
		element.elements['new[0]'].value = file;
		html = FileBrowser.ajax(element.id);
		element.elements[action].value = "select";
		if(html == '200'){
			html = FileBrowser.ajax(element.id);
			$('.filebrowser').replaceWith(html);
			FileBrowser.handlers();
			FileBrowser.preview(file);
		} else {
			alert( html );
		}
		this.preload( false );
	}
}

FileBrowser.pre = function( bool ){
	if(bool == true){
		$('.filebrowser_functions').css('display', 'block');
		$('.table_panel').addClass( 'small' );
	}
	if(bool == false){
		$('.filebrowser_functions').css('display', 'none');
		$('.table_panel').removeClass( 'small' );
	}
}

FileBrowser.msg = function( data ){
	if(data != '') {
		// Message
	}
}

FileBrowser.preload = function( bool ){
	if(bool == true) {
		$('.filebrowser_preload').css( 'display', 'block' );
		$('.filebrowser_preload').addClass( 'preload_panel' );
	}
	if(bool == false) {
		$('.filebrowser_preload').css( 'display', 'none' );
		$('.filebrowser_preload').removeClass('preload_panel');
	}
}

FileBrowser.preview = function( file ){
	if( file ) {
		this.pre( true );

		$('.delete_panel').css('display', 'none');
		$('.rename_panel').css('display', 'none');
		$('.preview_panel').css('display', 'block');
		$('.picture_box').html('{lang_loading}');

		element = document.forms['reloadform'];
		path    = element.path.value +'/'+ file +'?'+("upload"+(new Date()).getTime());
		element.file.value = file;

		var image = new Image();
		image.src = path;

		//image.src = path;
		$(image).load(function(){
			element.filetype.value = 'image';

			width  = image.width;
			height = image.height;
			image.title  = image.width + ' x ' + image.height;

			if(width > 150) {
				factor = width / 150;
				width = 150;
				height = Math.round(height / factor);
			}
			if(height > 100) {
				factor = height / 100;
				height = 100;
				width = Math.round(width / factor);
			}

			image.width  = width;
			image.height = height;
			image.border = 1;

			$('.picture_box').html('');
			$('.picture_box').append(image);
		}).error(function(){
				$('.picture_box').html('no image');
				element.filetype.value = 'file';
		});
		document.getElementById('info_name').style.visibility = 'visible';
		document.getElementById('info_name').innerHTML = file;
	} else {
		$('.preview_panel').css('display', 'none');
	}
}

// todo url from form?

FileBrowser.ajax = function(id){
	html = $.ajax({
		url: "{thisfile}",
		global: false,
		type: "POST",
		data: $('#'+id).serialize(),
		dataType: "html",
		async: false,
		cache: false
	}).responseText;
	return html;
}

FileBrowser.get = function( url, form ){
	if(url) {
		html = $.ajax({
			url: url,
			global: false,
			type: "GET",
			dataType: "html",
			async: false,
			cache: false
		}).responseText;
		$('.filebrowser').replaceWith(html);
		FileBrowser.handlers();
	}
	if(form) {
		html = $.ajax({
			url: "{thisfile}",
			global: false,
			type: "POST",
			data: form.serialize(),
			dataType: "html",
			async: false,
			cache: false
		}).responseText;
		$('.filebrowser').replaceWith(html);
		FileBrowser.handlers();
	}
}

FileBrowser.handlers = function(){

	$('.filebrowser a').click( function(element){ FileBrowser.get(this.href); return false; });

	$('.filter_button form').submit(function(element){ FileBrowser.get(null, $('.filter_button form') ); return false; });

	$(".Filedata").change( function(data){ $(".upload_button form").submit(); });
	$('.upload_button form').submit( function(data)
	{
		FileBrowser.preload( true );
		var submittingForm = $(this);
		var frameName      = ("upload"+(new Date()).getTime());
		var uploadInput    = $(".Filedata");
		var uploadFrame    = $("<iframe src=\"#\" name=\""+frameName+"\">");
		$('.filebrowser_preload .preload').click( function(){ 
				parent.frames[frameName].stop();
				FileBrowser.preload( false ); 
			});
		uploadFrame.css("display", "none");
		uploadFrame.load(function(data){
			body = parent.frames[frameName].document.getElementsByTagName('body')[0];
			setTimeout(function(){
				uploadFrame.remove();
				FileBrowser.preload( false );
			},100);
			msg = body.innerHTML;
			
			if(msg == '200') {
				element = document.forms['reloadform'];
				FileBrowser.get(null, $('#'+element.id));
				FileBrowser.preview(uploadInput.val());
				uploadInput.val('');
			} else {
				alert(msg);
				uploadInput.val('');
			}
			
		});
		$("body:first").append(uploadFrame);
		submittingForm.attr("target", frameName);
	});

	// toggle breadcrumps_panel when filter is set
	if($("input[name='{prefix}[filter]']").val() != '') {
		ButtonPanelToggle('-');
	}

	// fix ie overflow problem
/*
	if ($.browser.msie) {
		full    = $('.filebrowser').css('width');
		full    = full.replace(/px|em/ig,'');
		folders = $('.filebrowser_folders').css('width');
		folders = folders.replace(/px|em/ig,'');
		$('.table_panel').css({ 'width' : full - folders - 5});
	};
*/

	// disable rename, delete and new_folder when dir is readonly
	if($('.fakefile').attr('disabled') == true) {
		$('.readonly').attr('disabled', true);
	}
	if($('.fakefile').attr('disabled') == false) {
		$('.readonly').attr('disabled', false);
	}

}
</script>

<script type="text/javascript">

FileBrowser.handlers();
</script>
