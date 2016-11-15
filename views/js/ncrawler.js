nCrawler = {
	IsInit : false,
	selectors : {
		mainTable 		: null,
		reSendProducts 	: null,
		rebindProgress	: null
	},
	pointers : {
		mainTable 		: null,
		rebindProgress	: null
	},
	controller 		: null,
	products_total 	: 0,
	data : {
		ajax 	: true,
		action 	: null,
		token 	: null,
		page	: 0
	},

	Init : function() {
		var me = this;
		if(me.IsInit) return;

		me.parseUrl();
		me.initPointers();

		me.data.action = 'GetAllProducts';
		me.requestData(function (response) {
			me.renderMainTable(response['DT']);
		});

		me.selectors.reSendProducts.on('click', function() {
			swal({
				title: 'Пересвязать данные?',
				text: "Идентификационные данные ваших продуктов будут обновлены на центральном сервере nCrawler.com",
				type: 'warning',
				showCancelButton	: true,
				confirmButtonColor	: '#3085d6',
				cancelButtonColor	: '#d33',
				cancelButtonText	: 'Нет, я передумал',
				confirmButtonText	: 'Да, обновить!'
			}).then(function () {
				me.data.page = 0;
				me.selectors.rebindProgress.fadeIn();
				me.selectors.reSendProducts.hide();
				me.getProductQuantity(function (response) {me.rebindProductsData();});

			}, function (dismiss) {
				// dismiss can be 'cancel', 'overlay', 'close', and 'timer'
				if (dismiss === 'cancel') {console.log('canceled');}
			});
		});

		me.pointers.rebindProgress = new ProgressBar.Circle(rebindProgress, {
			color: '#aaa',
			// This has to be the same size as the maximum width to
			// prevent clipping
			strokeWidth: 4,
			trailWidth: 1,
			easing: 'easeInOut',
			duration: 1400,
			text: {
				autoStyleContainer: false
			},
			from: { color: '#aaa', width: 3 },
			to: { color: '#333', width: 7 },
			// Set default step function for all animate calls
			step: function(state, circle) {
				circle.path.setAttribute('stroke', state.color);
				circle.path.setAttribute('stroke-width', state.width);

				var value = Math.round(circle.value() * 100);
				if (value === 0) {
					circle.setText('');
				} else {
					circle.setText(value);
				}
			}
		});

		// me.getMatchersList();
		me.pointers.rebindProgress.text.style.fontFamily = '"Raleway", Helvetica, sans-serif';
		me.pointers.rebindProgress.text.style.fontSize = '12px';

		// jQuery('#username').editable({
		// 	mode: 'inline',
		// 	showbuttons : false,
		// 	type: 'text',
		// 	title: 'Новое значение'
		// });

		console.log('nCrawler inited successfully');
		me.IsInit = true;
	},

	rebindProductsData : function () {
		var me = this;
		me.data.action = 'ResendProductsData';
		console.log('current requerst page', me.data.page);

		me.data.page++;
		me.requestData(function (response) {

			// Error happen
			if(response.type != 'success') {
				me.rebindComplete();
				alert('ERROR');
				return;
			}

			if(me.data.page <= me.products_total / 100) {
				var t_p = me.products_total / 100;
				var prg = parseFloat(me.data.page / t_p).toFixed(2);
				console.log(prg);
				me.pointers.rebindProgress.animate(prg);
				me.rebindProductsData();
			} else {
				me.rebindComplete();
			}
		});
	},

	rebindComplete : function() {
		var me = this;
		me.pointers.rebindProgress.animate(1.0);
		me.selectors.reSendProducts.fadeIn();
		me.selectors.rebindProgress.fadeOut(500, function() {
			me.pointers.rebindProgress.animate(0.0);
		});
	},

	getProductQuantity : function (callback) {
		var me = this;
		me.products_total = 0;
		me.data.action = 'GetProductsQuantity';

		me.requestData(function(response) {
			if(response.type != 'success') {
				alert('Error');
				return;
			}
			me.products_total = parseInt(response['prod_quant']);
			if(typeof callback == 'function') callback(response);
		});
	},

	getMatchersList : function() {
		var me = this;
		me.data.action = 'GetMatchers';
		me.requestData(function (response) {

			console.log('list-response', response);
		})
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
				{data : 'end_price'},
				{data : 'status'},
				{data : 'actions'}
			],
			pageLength : 25,
			lengthMenu : [25, 50, 100],
			data : data,
			dom: '<"main-list-top"<f><p><l>>rt',
			fnCreatedRow : function(nRow, aData, iDataIndex) {
				jQuery(nRow).attr('data-product-id', aData['RowID']);
			},
			fnDrawCallback: function(settings) {

				jQuery('#mainProductList').find('.editable').editable({
					mode: 'inline',
					type: 'text',
					showbuttons : false,
					title: 'Новое значение'
					// url: '/matcher-price/ajax/set-price'
					// params: function(params) {
					// 	params.matcher_id = nCrawler.Matcher.data.matcher_id;
					// 	return params;
					// },
					// success: function(response, new_value) {
					// 	jQuery(this).parents('td').find('i.fa').removeClass('fa-unlock').addClass('fa-lock');
					// 	console.log('response', response);
					// 	console.log('new_al', new_value);
					// }
				});

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
			data : me.data,
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

		me.selectors.mainTable 		= jQuery('#mainProductList');
		me.selectors.reSendProducts	= jQuery('#reSendProducts');
		me.selectors.rebindProgress	= jQuery('#rebindProgress');
	}

};

jQuery(document).ready(function () {nCrawler.Init();});

