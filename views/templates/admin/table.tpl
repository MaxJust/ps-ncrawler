{*<h2>nCrawler.com price suggests</h2>*}

<div style="clear: both"></div>
<div style="border-bottom: 1px solid #dedede; margin-bottom: 20px;">
	<div style="width: 300px;height: 150px;overflow: hidden;float: left;border-right: 1px solid #dedede;margin-right: 30px;">
		<h3>nCrawler Access Settings (nCAS):</h3>
		<div><span style="font-weight: bold;">Login:</span> {$login}</div>
		<div><span style="font-weight: bold;">Token:</span> {$token}</div>
		<div><span style="font-weight: bold;">Url:</span> {$url}</div>
	</div>

	<div style="height: 150px;width: 200px;overflow: hidden;float: left;border-right: 1px solid #dedede;margin-right: 30px;">
		<h3>Синхронизация:</h3>
		<div id="rebindProgress" class="progress-container" style="float: left;"></div>
		<button id="reSendProducts" class="resend-products-button" style="float: left">Сихронизировать</button>
	</div>

	<div style="width: 150px;height: 150px;overflow: hidden;float: left;border-right: 1px solid #dedede;margin-right: 30px;">
		<h3>Установить цены:</h3>
		<select id="setPricesType" title="">
			<option value="all">Все</option>
			<option value="selected">Только выбранные</option>
			<option value="bad">Только дорогие</option>
			<option value="lowprofit">С низкой маржой</option>
			<option value="optimal">Только оптимальные</option>
		</select>
		<button id="setPrice" style="margin: 10px;">Установить</button>
	</div>

	<div style="width: 150px;height: 150px;overflow: hidden;float: left;border-right: 1px solid #dedede;margin-right: 30px;">
		<h3>Сервер nCrawler:</h3>
		<button id="updateRemote">Обновить сравнения</button>
	</div>

	{*<div style="width: 150px;height: 150px;overflow: hidden;float: left;border-right: 1px solid #dedede;margin-right: 30px;">*}
		{*<h3>Фильтры:</h3>*}
		{*<div class="filterWrap"></div>*}
	{*</div>*}

	<div style="clear: both;"></div>
</div>

<div id="username"></div>

<table id="mainProductList" class="hover main-product-list">
	<thead>
	<tr>
		<th></th>
		<th>Название</th>
		<th>Ваша текущая цена</th>
		<th>Предложение nCrawler</th>
		<th>Конечная цена</th>
		<th class=".select-filter">Состояние</th>
		<th>Действия</th>
	</tr>
	</thead>
</table>