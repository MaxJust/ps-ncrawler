<h2>nCrawler.com price suggests</h2>

<div style="clear: both"></div>

<div style="width: 300px;overflow: hidden;float: left;">
	<h3>nCrawler Access Settings (nCAS):</h3>
	<div>Login: {$login}</div>
	<div>Token: {$token}</div>
	<div>Url: {$url}</div>
</div>

<div style="height: 150px;width: 300px;overflow: hidden;float: left;">
	<h3>Products Synchronization:</h3>
	<div id="rebindProgress" class="progress-container" style="float: left;"></div>
	<a id="reSendProducts" class="resend-products-button" href="#" style="float: left">ReSync</a>
</div>

<div style="width: 300px;overflow: hidden;float: left;">
	<h3>Matcher Lists:</h3>
	<select id="matchersList" title=""></select>
</div>

<div style="clear: both;"></div>

<table id="mainProductList" class="hover">
	<thead>
	<tr>
		<th>Название</th>
		<th>Текущая цена</th>
		<th>Предложение</th>
		<th>Имя вотчера</th>
		<th>Действия</th>
	</tr>
	</thead>
</table>