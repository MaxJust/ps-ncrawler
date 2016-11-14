nCrawler = {
	IsInit : false,
	selectors : {},
	pointers : {},
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

		me.requestData();

		console.log('nCrawler js inited');
		me.IsInit = true;
	},

	requestData : function() {
		var me = this;

		$.ajax({
			url : 'index.php?controller=' + me.controller,
			data : {
				ajax 	: true,
				action 	: 'Test',
				token 	: me.data.token
			},
			success : function(response) {
				console.log('success', response);
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
	}

};

jQuery(document).ready(function () {nCrawler.Init();});

