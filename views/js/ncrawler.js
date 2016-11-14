nCrawler = {
	IsInit : false,
	selectors : {
		mainTable 		: null,
		reSendProducts 	: null
	},
	pointers : {
		mainTable : null
	},
	controller : null,
	data : {
		ajax 	: true,
		action 	: 'Test',
		token 	: null
	},

	Init : function() {
		var me = this;
		if(me.IsInit) return;

		me.parseUrl();
		me.initPointers();

		me.requestData(function (response) {
			me.renderMainTable(response['products']);
		});

		me.selectors.reSendProducts.on('click', function() {
			swal({
				title: 'Are you sure?',
				text: "You won't be able to revert this!",
				type: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Yes, delete it!'
			}).then(function () {
				swal(
					'Deleted!',
					'Your file has been deleted.',
					'success'
				)
			})
		});

		console.log('nCrawler js inited');
		me.IsInit = true;
	},

	renderMainTable : function(data) {
		var me = this;

		console.log('render table', data);

		me.pointers.mainTable = me.selectors.mainTable.DataTable({
			destroy : true,
			// order: [[ 3, "desc" ]],
			//responsive: true,
			oLanguage: {
				sSearch: '',
				sSearchPlaceholder: 'Фильтр по таблице',
				sInfo: "с _START_ по _END_ из _TOTAL_",
				oPaginate : {sPrevious: "Предыдущая",sNext: "Следующая"},
				sEmptyTable: "Ничего не найдено",
				sInfoEmpty: "Нечего показывать",
				sLengthMenu: "_MENU_"
			},
			columns : [
				{data : 'name'},
				{data : 'current_price'},
				{data : 'suggest_price'},
				{data : 'watcher_name'},
				{data : 'actions'}
			],
			pageLength : 25,
			lengthMenu : [25, 50, 100],
			data : data,
			dom: '<"top"<"col-lg-5 sub-table-filter"f><"col-lg-6"<"pull-right"p><"pull-right margin-right-10"i>><"col-lg-1"l><"clear">>rt<"top"<"col-lg-5 sub-table-filter"f><"col-lg-6"<"pull-right"p><"pull-right margin-right-10"i>><"col-lg-1"l><"clear">>',
			fnCreatedRow : function(nRow, aData, iDataIndex) {
				// jQuery(nRow).attr('data-key', aData['Data_Key']).attr('data-type', aData['Data_Type'])
			},
			fnDrawCallback: function(settings) {
				// console.log('render from init');
				// var middle_price = {name : 'Средняя цена',type : 'spline',data : []};
				// var price_range = {name : 'Цена от - до',type : 'columnrange',data : []};
				// var quantity = {name : 'Кол-во позиций',type : 'column',yAxis : 1,pointPadding: 0.3,data : []};
				// var categories = [];
			}
		});

	},

	requestData : function(callback) {
		var me = this;

		$.ajax({
			url : 'index.php?controller=' + me.controller,
			dataType : 'json',
			data : {
				ajax 	: true,
				action 	: 'Test',
				token 	: me.data.token
			},
			success : function(response) {
				console.log('success', response);
				if(typeof callback == 'function') callback(response)
			}
		});

	},

	parseUrl : function() {
		var me = this;
		var url = window.location.search;
		var result = {};
		var searchIndex = url.indexOf("?");
		if (searchIndex == -1 ) return result;
		var sPageURL = url.substring(searchIndex +1);
		var sURLVariables = sPageURL.split('&');
		for (var i = 0; i < sURLVariables.length; i++) {
			var sParameterName = sURLVariables[i].split('=');
			result[sParameterName[0]] = sParameterName[1];
		}
		me.controller = result.controller;
		me.data.token = result.token;
		return result;
	},

	initPointers : function() {
		var me = this;

		me.selectors.mainTable = jQuery('#mainProductList');
		me.selectors.reSendProducts = jQuery('#reSendProducts');
	}

};

jQuery(document).ready(function () {nCrawler.Init();});

