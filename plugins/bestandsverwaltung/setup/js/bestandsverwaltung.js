var bezeichnerhelp = {
	init: function(element) {
		if(typeof element.dataset.content == 'undefined') {
			var bezeichner = element.title;
			var params = "&plugin=bestandsverwaltung&bestand_action=help&bezeichner="+bezeichner;
			setTimeout((function(params, element) {
				bezeichnerhelp.ajax(params,element);
			})(params, element), 10);
			$('#'+element.id).attr('data-content','Loading ...');
			$('#'+element.id).popover('show');
		}
	},
	print : function(element,response) {
		var popover = $('#'+element.id).data('bs.popover');
		if(response != '') {
			$('#'+element.id).attr('data-content',response);
			$('#'+element.id).attr('data-html',true);
		} else {
			$('#'+element.id).attr('data-content','No help available');
		}
		//if (popover.tip().is(':visible')) {
			$('#'+element.id).popover('show');
		//}
	},
	ajax : function (params, element) {
		html = $.ajax({
			url: "api.php",
			global: false,
			type: "POST",
			data: params,
			dataType: "html",
			async: true,
			cache: true,
			success: function(response){
				bezeichnerhelp.print(element, response);
			}
		});
	}
};

function btoggle_all(id) {
	if(id) {
		el = document.getElementById(id);
		ols = el.getElementsByTagName('ol');
	} else {
		ols = document.getElementsByTagName('ol');
	}
	state = 'none';

//alert(ols.length);

	for(i=1;i<ols.length;i++) {
		if(i == 1) {
			if(ols[i].style.display == 'block') {
				document.getElementById('toggleall').innerHTML = '&#9658;';
				state = 'none';
			} else {
				document.getElementById('toggleall').innerHTML = '&#9660;';
				state = 'block';
			}
		}
//alert(ols[i].id);

		if(ols[i].id != 'undefined' && ols[i].id != '') {
			btoggle(ols[i].id, state);
		}
	}

}

function btoggle(id, state) {
	box = document.getElementById(id);
	try {
		if(state) {
			box.style.display = state;
			if(state == 'block') {
				if(document.getElementById('l_'+id)) {
					document.getElementById('l_'+id).innerHTML = '&#9660;';
				}
			} else {
				if(document.getElementById('l_'+id)) {
					document.getElementById('l_'+id).innerHTML = '&#9658;';
				}
			}
		} else {
			if(box.style.display == 'block') {
				if(document.getElementById('l_'+id)) {
					document.getElementById('l_'+id).innerHTML = '&#9658;';
				}
				box.style.display = 'none';
			} else {
				if(document.getElementById('l_'+id)) {
					document.getElementById('l_'+id).innerHTML = '&#9660;';
				}
				box.style.display = 'block';
			}
		}
	} catch(e) { alert(e); }
}

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
		$('.table_panel').addClass( 'tiny' );
	}
	if(bool == false){
		$('.filebrowser_functions').css('display', 'none');
		$('.table_panel').removeClass( 'tiny' );
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

			if(width > 250) {
				factor = width / 250;
				width = 250;
				height = Math.round(height / factor);
			}
			if(height > 190) {
				factor = height / 190;
				height = 190;
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

/*
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
