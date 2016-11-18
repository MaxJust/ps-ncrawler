nCrawler = {
	IsInit : false,
	selectors : {
		mainTable 		: null,
		reSendProducts 	: null,
		rebindProgress	: null,
		setPrices		: null,
		updateRemote	: null
	},
	pointers : {
		mainTable 		: null,
		rebindProgress	: null
	},
	controller 		: null,
	products_total 	: 0,
	data : {
		ajax 		: true,
		action 		: null,
		token 		: null,
		page		: 0,
		saveData 	: {}
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

		// Save products
		me.selectors.setPrices.on('click', function(e) {
			e.preventDefault();
			swal({
				title: 'Сохранить данные?',
				text: "Это действие сохранит данные из поля 'конечная цена' (endprice) в базу, все продукты в таблице будут обновлены",
				type: 'warning',
				showCancelButton	: true,
				confirmButtonColor	: '#3085d6',
				cancelButtonColor	: '#d33',
				cancelButtonText	: 'Нет, я передумал',
				confirmButtonText	: 'Да, я понимаю!'
			}).then(function () {
				me.saveProducts();
			}, function (dismiss) {
				// dismiss can be 'cancel', 'overlay', 'close', and 'timer'
				if (dismiss === 'cancel') {console.log('save canceled');}
			});
		});

		// Update Remote matchers
		me.selectors.updateRemote.on('click', function(e) {
			e.preventDefault();
			me.data.action = 'UpdateRemote';
			me.requestData(function(response) {
				swal('Ответ сервера', response['message'], response['type']);
				if(response.type == 'success') {
					// Reget table
					me.data.action = 'GetAllProducts';
					me.requestData(function (response) {
						if(response.type != 'success') {swal('Ошибка', 'Ошибка рендера таблицы', 'error');return;}
						me.renderMainTable(response['DT']);
					});
				}
			});
		});

		console.log('nCrawler inited successfully');
		me.IsInit = true;
	},

	saveProducts : function() {
		var me = this;

		var error_flag = false;

		var selector;
		switch(jQuery('#setPricesType').val()) {
			case 'lowprofit':	selector = '.lowprofit';break;
			case 'bad':			selector = '.bad';break;
			case 'optimal':		selector = '.optimal';break;
			case 'selected':	selector = '.selected';break;
			case 'all':			selector = false;break;
			default:
				error_flag = true;
				alert('Error happen');
				return;
		}

		me.data.saveData 	= {};

		nCrawler.pointers.mainTable.rows(selector).eq(0).each(function (index) {
			var jRow = nCrawler.pointers.mainTable.row(index).nodes().to$();

			var product_id 			= jRow.attr('data-product-id');
			var product_endprice 	= parseInt(jRow.find('.endPrice').html());

			// Check if price exist
			if(!product_endprice) {error_flag = true;return true;}

			me.data.saveData[product_id] = product_endprice;

		});

		if(error_flag) {
			swal(
				'Fatal Error',
				'Во время формирования списка произошла ошибка,<br> ' +
				'пожалуйста свяжитесь с nCrawler.com<br>' +
				'(+7[495]419-00-13)',
				'error'
			);
			return;
		}

		me.data.action = 'SetPrices';
		me.requestData(function(response) {

			if(response.type != 'success') {
				swal('Ошибка', 'Во время сохранения произошла ошибка: <br>' + response.message || "неизвестная ошибка", 'error');
				return;
			}

			swal('Все сделано!', 'Данные сохранены: <br>' + response.message, 'success');

			// Reget table
			me.data.action = 'GetAllProducts';
			me.requestData(function (response) {
				if(response.type != 'success') {swal('Ошибка', 'Ошибка рендера таблицы', 'error');return;}
				me.renderMainTable(response['DT']);
			});
		});

		// console.log(me.data.saveData);
	},

	rebindProductsData : function () {
		var me = this;
		me.data.action = 'ResendProductsData';
		// console.log('current request page', me.data.page);

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
				// console.log(prg);
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

		// Reget table
		me.data.action = 'GetAllProducts';
		me.requestData(function (response) {
			if(response.type != 'success') {swal('Ошибка', 'Ошибка рендера таблицы', 'error');return;}
			me.renderMainTable(response['DT']);
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
			select: {
				style:    'os',
				selector: 'td:first-child'
			},
			columns : [
				{data : 'select', orderable: false, className: 'select-checkbox', targets: 0},
				{data : 'name'},
				{data : 'current_price', bSearchable: false},
				{data : 'suggest_price', bSearchable: false},
				{data : 'end_price', bSearchable: false},
				{data : 'status'},
				{data : 'actions', bSearchable: false}
			],
			pageLength : 25,
			lengthMenu : [25, 50, 100, 500],
			data : data,
			dom: '<"main-list-top"<"pull-left"f><"pull-right"l><"pull-right"p><"clearfix">>rt',
			initComplete : function() {
				//// jQuery('#content').find('.saveWrap').html('<a href="#" class="saveProducts">Сохранить данные</a>')
				// this.api().columns('.main-product-list .select-filter').every(function () {
				// 	var column = this;
				// 	var select = jQuery('<select class="pull-right"><option value="">Фильтр (Все)</option></select>')
				// 		.appendTo(jQuery('.filterWrap').empty())
				// 		.on('change', function () {
				// 			var val = jQuery.fn.dataTable.util.escapeRegex(jQuery(this).val());
				// 			column.search(val ? '^' + val + '$' : '', true, false).draw();
				// 		});
				// 	column.data().unique().sort().each(function (d, j) {
				// 		select.append('<option value="' + d + '">' + d + '</option>')
				// 	});
				// });
			},
			fnCreatedRow : function(nRow, aData, iDataIndex) {
				jQuery(nRow).attr('data-product-id', aData['RowID'])
					.attr('data-status', aData['RowStatus'])
					.addClass(aData['RowStatus']);
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
				// console.log('success', response);
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
		me.selectors.setPrices		= jQuery('#setPrice');
		me.selectors.updateRemote	= jQuery('#updateRemote');
	}

};

jQuery(document).ready(function () {nCrawler.Init();});



// me.pointers.mainTable.rows().every(function (rowIdx, tableLoop, rowLoop) {
// 	var row = me.pointers.mainTable.row(rowIdx);
//
// 	console.log('row', row.nod);
//
// 	var api = this.api();
// 	var rows = api.rows({page:'current'}).nodes();
// 	var last = null;
// 	api.column(4, {page:'current'}).data().each(function(group, i) {
// 		if(last !== group) {
// 			jQuery(rows).eq(i).before(
// 				'<tr class="group ' + ( i % 2 ? ' even' : ' odd') + '" role="row" data-key="' + jQuery(rows).eq(i).data('key') + '">' +
// 				'<td colspan="5">' + group + '</td>' +
// 				'</tr>');
// 			last = group;
// 		}
// 	});
//
// });