function FileBrowser_tr_hover(element) {
	x = element.className.match(/tr_hover/g);
	if(x == null) { element.className = element.className + " tr_hover"; }
	else { element.className = element.className.replace(/ tr_hover/g, ""); }
}
function FileBrowser_tr_click(element, arg) {
	FileBrowser.preview( document.getElementById(arg).value );
}

var FileBrowser = new Object();
FileBrowser.init = function( callback ){
	this.callback = callback;
	this.root     = '/images/';
	this.pre( false );

}
FileBrowser.download = function(){
	path = document.getElementById('url').value;
	file = document.getElementById('file').value;
	if(file != '') {
		path = path +'/'+ file;
		location.href = path;
	}
}
FileBrowser.del = function( action ){
	element = document.forms['reloadform'];
	element.elements[action].value = "delete";
	document.getElementById('identifier').value = document.getElementById('file').value;
	element.submit();
}
FileBrowser.rename = function( action ){
	element = document.forms['reloadform'];
	file    = document.getElementById('new_name').value;
	if(	file != '' ) {
		//this.preload( true );
		element.elements[action].value = "rename";
		document.getElementById('identifier').value = document.getElementById('file').value;
		element.elements['new[0]'].value = file;
		element.submit();
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
FileBrowser.preview = function( file ){
	if( file ) {
		this.pre( true );

		$('.delete_panel').css('display', 'none');
		$('.rename_panel').css('display', 'none');
		$('.preview_panel').css('display', 'block');
		$('.picture_box').html('<center>loading ..</center>');

		element = document.forms['reloadform'];
		path    = element.url.value +'/'+ file +'&'+("upload"+(new Date()).getTime());
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
FileBrowser.handlers = function(){
	$(".Filedata").change( function(data){ $(".upload_button form").submit(); });
	// disable rename, delete and new_folder when dir is readonly
	if($('.fakefile').attr('disabled') == true) {
		$('.readonly').attr('disabled', true);
	}
	if($('.fakefile').attr('disabled') == false) {
		$('.readonly').attr('disabled', false);
	}
}
