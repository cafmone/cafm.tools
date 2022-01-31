/* TREEBUILDER */
var treebuilder = {

	__crumps : {},
	__search : '',
	__result : '',
	__tree : [],

	//
	// INIT
	//
	init : function() {
		if(typeof timestamp == 'undefined') { console.log('Timestamp missing'); return; }
		if(typeof identifiers == 'undefined') { console.log('Identifiers missing'); return; }
		if(typeof tree == 'undefined') { console.log('Tree missing'); return; }
		if(typeof lang == 'undefined') { console.log('Lang missing'); return;  }
		if(typeof languages == 'undefined') { console.log('Languages missing'); return;  }

		this.__search = document.getElementById("SearchInput");
		this.__result = document.getElementById("SearchResult");
		this.__status = document.getElementById("PageStatus");

		// start init search in background
		test = setTimeout(function() {
			treebuilder.initSearch();
		}, 0);
		//this.__status.innerHTML = 'Loading ...';
		this.__status.style.display = 'block';

		// reset crumps
		this.__crumps = {};

		// handle id
		if(typeof id != 'undefined' && id != '') {
			this.print(id);
		} else {
			this.print();
		}
	},

	//
	// Init search data
	//
	initSearch : function() {
		for(idx in tree) {
			crump = this.setSearch(idx);
			link = document.createElement("div");
			//link.style.whiteSpace = 'nowrap';
			link.style.display = 'none';
			link.style.cursor = 'pointer';
			link.innerHTML = idx+' : '+crump;
			link.addEventListener('click', function() { location.href = '?id='+idx+'&lang='+lang; });
			this.__result.append(link);
		}
		// reset crumps
		this.__crumps = {};
		this.__status.style.display = 'none';
	},

	//
	// PRINT
	//
	print : function(id) {

		$('#breadcrumps').css('display','block');

		if(typeof id != 'undefined') {
			this.setCrumps(id);
		}

		// close modal
		$("#myModal").modal('hide');

		// close search
		this.__result.style.display = 'none';


		// build environment
		var i = 1;
		var keys = Object.keys(identifiers);
		if(Object.keys(this.__crumps).length == 0) {
			maximum = 3;
		} else {
			maximum = Object.keys(this.__crumps).length+1;
		}

		previousid = '';
		nextid = '';

		menubox = $(document.createElement("div"));
		menubox.addClass('list-group flex-fill');
		menubox.css('width','100%');

		for (view in identifiers) {

			if (i > maximum) {

				select  = '<div style="margin-top:5px;">';
				select += '<label>'+identifiers[view]+'</label>';
				select += '<div class="input-group">';
				select += '<input class="form-control" value="" disabled="disabeled">';
				select += '<span class="input-group-addon disabled"><span class="caret"></span></span>';
				select += '</div>';
				select += '</div>';
				menubox.append(select);

			} else {

				parent = '';
				if (typeof this.__crumps[view] != 'undefined') {
					parent = this.__crumps[view].parent;
				}
				else if (Object.keys(this.__crumps).length != 0 && i == maximum) {
					parent = id;
				}

				select  = '<div style="margin-top:5px;">';
				select += '<label>'+identifiers[view]+'</label>';
				select += '<div class="input-group">';
				select += '<div class="form-control" style="outline:0;">';
				if (typeof this.__crumps[view] != 'undefined') {
					select += ''+this.__crumps[view].label+'';
				} else {
					select += '';
				}
				select += '</div>';
				select += '<a class="input-group-addon btn" onclick="treebuilder.modal(\''+view+'\');">';
				select += '<span class="caret"></span>';
				select += '</a>';
				select += '</div>';
				select += '</div>';

				group = $(document.createElement("div"));
				group.attr('id',view);
				group.addClass('list-group');
				group.attr('tabindex','0');
				group.css('display','none');
				group.css('outline','0');
				group.bind("mouseover", function(event) {
					this.focus();
				})

				// sort voodoo part 1
				container = [];
				for (tid in tree) {
					if(view == tree[tid]['v']) {
						if(parent != '' && parent != tree[tid]['p']) { continue; }
						container.push(tree[tid]['l']+'[[*]]'+tid);
					}
				}

				// sort voodoo part 2
				next = '';
				previous = '';
				__previous = '';
				container.sort();
				for(x in container) {

						tmp   = container[x].split('[[*]]');
						tid   = tmp[1];
						label = tmp[0];

						css = 'list-group-item list-group-item-action';
						if (typeof this.__crumps[view] != 'undefined' && this.__crumps[view]['id'] == __previous) {
							if(i == Object.keys(this.__crumps).length) {
								next = tid;
							}
						}
						else if(typeof this.__crumps[view] != 'undefined' && this.__crumps[view]['id'] == tid ) {
							css += ' active';
							if(i == Object.keys(this.__crumps).length) {
								previous = __previous;
							}
						}

						str  = '';
						str += '<a class="'+css+'" href="javascript:treebuilder.print(\''+tid+'\');">';
						str += label;
						str += '</a>';
						str += '';

						__previous = tid;
						group.append(str);
				}

				// build menu
				menubox.append(select);
				//menubox.append(head);
				//menubox.append(menu);
				menubox.append(group);

				if(previous != '') {
					previousid = previous;
				}
				if(next != '') {
					nextid = next;
				}
			}
			i = i+1;
		}

		target = $('#navbar-left');
		target.html('');
		target.append(menubox);

/*
		$('#previousid').unbind('click');
		$('#previousid').css('display', 'none');
		$('#nextid').unbind('click');
		$('#nextid').css('display', 'none');
		if(previousid != '') {
			$('#previousid').bind('click', function() { treebuilder.print(previousid); });
			//$('#previousid').html('< '+tree[previousid]['l']);
			$('#previousid').attr('title', tree[previousid]['l']);
			$('#previousid').css('display', 'block');
		}

		if(nextid != '') {
			$('#nextid').bind('click', function() { treebuilder.print(nextid); });
			//$('#nextid').html(tree[nextid]['l']+' >');
			$('#nextid').attr('title', tree[nextid]['l']);
			$('#nextid').css('display', 'block');
		}
*/

		langselect = $('#langselect');
		$('.langlabel', langselect).html(languages['language']);
		$('.dropdown-menu', langselect).html('');
		if(typeof id != 'undefined') {
			idlink = '&id='+id;
		} else {
			idlink = '';
		}
		for( l in languages) {
			if(l != 'language') {
				$('.dropdown-menu', langselect).append('<li><a class="dropdown-item" href="?lang='+l+idlink+'">'+languages[l]+'</a></li>');
			}
		}

	},

	//
	// Search
	//
	search : function() {

		$('#breadcrumps').css('display','none');

		filter = this.__search.value;
		if(filter.length > 3) {
			this.__result.style.display = 'block';
			links = $('#'+this.__result.id+' div');
			for(var i=0; i < links.length; i++) {
				regex = new RegExp(filter, "gi");
				text = links[i].innerHTML;
				result = text.search(regex);
				if(result != -1) {
					//regex = new RegExp('('+filter+')', "gi");
					//text = links[i].innerHTML;
					//text = text.replace(regex, '<b>$1</b>');
					//links[i].innerHTML = text;
					links[i].style.display = 'block';
				} else {
					links[i].style.display = 'none';
				}
			}
		} else {
			this.__result.style.display = 'none';
		}

	},

	//
	// Modal
	//
	modal: function(id) {

		group = $('#'+id).clone();
		group.attr('tabindex','0');
		group.css('display','block');
		group.bind("mouseover", function(event) {
			this.focus();
		})

		modal = $('#myModal');

		searchbox = $('.form-control',modal);
		searchbox.attr('placeholder', this.__search.placeholder);
		searchbox.val('');
		searchbox.on('keyup', function() {
			var value = $(this).val().toLowerCase();
			$('#'+id+' a').filter(function() {
				$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
			});
		});

		head = $('.modal-title', modal);
		head.html(identifiers[id]);

		body = $('.modal-body', modal);
		body.html('');
		body.append(group);

		$('#myModal').modal({
			backdrop: 'static',
			keyboard: true
		})

	},

	setSearch: function(id) {
		this.__crumps = {};
		this.__setCrumps(id, 'search');
		i = 0;
		crumps = '';
		for( view in identifiers ) {
			if(typeof this.__crumps[view] != 'undefined') {
				if(i != 0) {
					crumps += ' / ';
				}
				crumps += this.__crumps[view].label;
			}
			i = i+1;
		}
		return crumps;
	},

	setCrumps: function(id) {
		this.__crumps = {};
		this.__setCrumps(id);
		i = 0;
		crumps = '';
		for( view in identifiers ) {
			if(typeof this.__crumps[view] != 'undefined') {
				if(i != 0) {
					crumps += ' / ';
				}
				crumps += '<a href="?id='+this.__crumps[view].id+'&lang='+lang+'">'+this.__crumps[view].label+'</a>';
			}
			i = i+1;
		}
		$('#breadcrumps').html(crumps);
	},

	__setCrumps : function(id) {

		result = tree[id];
		if(typeof result != 'undefined') {
			this.__crumps[result.v] = { 'label':result.l, 'id':id, 'parent':result.p };
			if(typeof result.p != 'undefined') {
				this.__setCrumps(result.p);
			}
		}

	},


}




