<?php

class AdminNetCrawlerController extends ModuleAdminController
{

	private $nc_access_login 	= '';
	private $nc_access_token 	= '';
	private $nc_access_url 		= '';
	private $nc_source_sld		= '';

	public function __construct()
	{
		$this->module		= 'ncrawler';
		$this->bootstrap	= true;
//		$this->table 		= 'ncrawler';
//		$this->className 	= 'NetCrawler';
		$this->lang 		= false;

		$this->display 		= 'view';

		parent::__construct();

		$config = Configuration::getMultiple(array(nCrawler::NC_ACCESS_LOGIN, nCrawler::NC_ACCESS_TOKEN, nCrawler::NC_ACCESS_URL, nCrawler::NC_SOURCE_SLD));
		$this->nc_access_login 	= $config[nCrawler::NC_ACCESS_LOGIN];
		$this->nc_access_token 	= $config[nCrawler::NC_ACCESS_TOKEN];
		$this->nc_access_url	= $config[nCrawler::NC_ACCESS_URL];
		$this->nc_source_sld	= $config[nCrawler::NC_SOURCE_SLD];
	}

	/**
	 * Update remote data
	 */
	public function ajaxProcessUpdateRemote() {
		$full_url = self::generateNetCrawlerAccessUrl('matchers', 'recalculate-all-matchers');
		$response = self::curlRequest($full_url);
		echo Tools::jsonEncode(array(
			'action' 	=> 'UpdateRemote',
			'type'		=> !empty($response['type']) ? $response['type'] : 'error',
			'message'	=> !empty($response['message']) ? $response['message'] : 'Неизвестное сообщение :(',
		));
		exit;
	}

	/**
	 * Set All Prices Callback
	 */
	public function ajaxProcessSetPrices() {

		$saveData = Tools::getValue('saveData');

		if(empty($saveData)) {
			echo Tools::jsonEncode(array(
				'action' 	=> 'SetPrices',
				'type'		=> 'error',
				'message'	=> 'Нет данных для сохранения',
			));
			exit;
		}

		$ids 		= array();
		$values 	= array();
		$columns 	= array('id_product', 'price', 'pc_price');
		foreach($saveData as $pid => $price) {
			$ids[] = (int) $pid;
			$value = array((int) $pid, (int) $price, (int) $price,);
			$values[] = '(' . implode(',', $value) . ')';
		}

		if(empty($values)) {
			echo Tools::jsonEncode(array(
				'type'		=> 'error',
				'message' 	=> 'Нечего сохранять',
			));
			exit;
		}

		//INSERT INTO table (id,Col1,Col2) VALUES (1,1,1),(2,2,3),(3,9,3),(4,10,12) ON DUPLICATE KEY UPDATE Col1=VALUES(Col1),Col2=VALUES(Col2);
		$query = 'INSERT INTO  ' . _DB_PREFIX_ . 'product (' . implode(',', $columns) . ') 
					VALUES ' . implode(',', $values) . ' 
		 			ON DUPLICATE KEY UPDATE price = VALUES(price), pc_price = VALUES(pc_price);';

		$results = Db::getInstance()->execute($query);
		if(!$results) {
			echo Tools::jsonEncode(array(
				'type'		=> 'error',
				'message' 	=> 'DB PRODUCT ERROR!',
			));
			exit;
		}

		$query = 'UPDATE ' . _DB_PREFIX_ . 'product_shop as ps 
			LEFT JOIN ' . _DB_PREFIX_ . 'product as p ON ps.id_product = p.id_product 
			SET ps.price = p.price 
			WHERE ps.id_shop = 1 AND ps.id_product IN (' . implode(',', $ids) . ');';

		$results = Db::getInstance()->execute($query);
		if(!$results) {
			echo Tools::jsonEncode(array(
				'type'		=> 'error',
				'message' 	=> 'DB SHOP ERROR!',
			));
			exit;
		}

		echo Tools::jsonEncode(array(
			'action' 		=> 'SetPrices',
			'type'			=> 'success',
			'$saveData'		=> $saveData,
			'message'		=> 'test message',
		));

		exit;
	}

	/**
	 * Rebind products on nCrawler.com server
	 */
	public function ajaxProcessResendProductsData() {
		$url = $this->generateNetCrawlerAccessUrl('matchers', 'rebind-products');

		$page_size = 100;

		$page = intval(Tools::getValue('page'));
		if(empty($page)) {
			echo Tools::jsonEncode(array(
				'action' 	=> 'ResendProductsData',
				'type'		=> 'error',
				'message'	=> 'wrong page number',
			));
			exit;
		}

		$bind_data = array();
		$productObj = new Product();
		$products = $productObj->getProducts($this->context->language->id, $page_size * ($page - 1), $page_size, 'id_product', 'DESC', false, false);

		foreach($products as $product) {
			$link = new Link();
			$p_url = $link->getProductLink($product['id_product']);

			// Rewrite host if needed
			if(!empty($this->nc_source_sld)) {
				$parts 			= parse_url($p_url);
				$parts['host'] 	= $this->nc_source_sld;
				$p_url 			= self::build_url($parts);
			}

			$bind_data[$product['id_product']] = $p_url;
		}

		// Bind request
		$nc_response = self::curlRequest($url, $bind_data);

		echo Tools::jsonEncode(array(
			'action' 		=> 'ResendProductsData',
			'type'			=> (!empty($nc_response['type']) ? $nc_response['type'] : 'error'),
			'url'			=> $url,
			'nc_response' 	=> $nc_response,
			'page'	 		=> $page,
		));
		exit;
	}

	/**
	 * Get Matchers List Ajax Callback
	 */
	public function ajaxProcessGetMatchers() {
		$url	= self::generateNetCrawlerAccessUrl('matchers', 'get-list');
		$data 	= self::curlRequest($url);
		echo Tools::jsonEncode(array(
			'action' 	=> 'get-matchers',
			'data'	 	=> $data,
		));
		exit;
	}

	/**
	 * Get Active product quantity
	 */
	public function ajaxProcessGetProductsQuantity() {
		$sql = 'SELECT count(*) as prod_quant FROM ' . _DB_PREFIX_ . 'product WHERE active = 1';
		if ($results = Db::getInstance()->ExecuteS($sql)) {
			echo Tools::jsonEncode(array(
				'type'			=> 'success',
				'prod_quant' 	=> $results[0]['prod_quant'],
			));
		} else {
			echo Tools::jsonEncode(array(
				'type'			=> 'error',
				'prod_quant' 	=> 0,
			));
		}
		exit;
	}

	/**
	 * Get All Products CallBack
	 */
	public function ajaxProcessGetAllProducts() {

		$full_url = self::generateNetCrawlerAccessUrl('matchers', 'get-all-products');
		$response = self::curlRequest($full_url, array('site' => $this->nc_source_sld));

		if($response['type'] != 'success' || empty($response['pids']) || empty($response['products'])) {
			echo Tools::jsonEncode($response);
			exit;
		}

		$pids 		= $response['pids'];
		$products	= $response['products'];

		// Get data from local DB
		$sql = 'SELECT id_product, price FROM ' . _DB_PREFIX_ . 'product WHERE id_product IN ('.implode(',', $pids).') ';
		$db_results = Db::getInstance()->ExecuteS($sql);
		if(empty($db_results)) {
			echo Tools::jsonEncode(array('type' => 'empty', 'total' => 0,));
			exit;
		}

		// Generate products table
		$prod_json = array();
		foreach($db_results as $product) {

			$current_price = $product['price'];
			$suggest_price =  $products[$product['id_product']]['actual_price'];

			if(empty($suggest_price)) continue;

			if($current_price < $suggest_price) {
				$status = 'lowprofit';
			} elseif($current_price > $suggest_price) {
				$status = 'bad';
			} else {
				$status = 'optimal';
			}

			$prod_json[] = array(
				'RowID'		 	=> $product['id_product'],
				'RowStatus'		=> $status,
				'select' 		=> '',
				'name' 			=> $products[$product['id_product']]['title'],
				'url' 			=> $products[$product['id_product']]['url'],
				'current_price'	=> number_format($current_price, 2, '.', ' '),
				'suggest_price'	=> number_format($suggest_price, 2, '.', ' '),
				'end_price'		=> '<span class="endPrice editable">' . $suggest_price . '</span>',
				'status'		=> $status,
				'actions'		=> '',
			);
		}

		echo Tools::jsonEncode(array(
			'type'		=> 'success',
			'pids'		=> $response['pids'],
			'DT'		=> $prod_json,
			'products' 	=> $response['products'],
			'total' 	=> count($response['products']),
		));

		exit;
	}

	/**
	 * Render view
	 * @return mixed
	 */
	public function renderView()
	{
		$this->context->controller->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/sweetalert2.min.css');
		$this->context->controller->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/datatables.min.css');
		$this->context->controller->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/jquery-editable.css');
		$this->context->controller->addCSS(_MODULE_DIR_ . $this->module->name . '/views/css/ncrawler.css');

		$this->context->controller->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/sweetalert2.min.js');
		$this->context->controller->addJs(_MODULE_DIR_ . $this->module->name . '/views/js/datatables.min.js');
		$this->context->controller->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/poshytip.min.js');
		$this->context->controller->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/jquery-editable-poshytip.min.js');
		$this->context->controller->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/progressbar.min.js');
		$this->context->controller->addJS(_MODULE_DIR_ . $this->module->name . '/views/js/ncrawler.js');

		$tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . '/ncrawler/views/templates/admin/table.tpl');
		$tpl->assign('login', $this->nc_access_login);
		$tpl->assign('token', $this->nc_access_token);
		$tpl->assign('url', $this->nc_access_url);
		return $tpl->fetch();
	}

	/**
	 * Helper - makre curl request
	 * @param $full_url
	 * @param array $data
	 * @param int $max_sec
	 * @return mixed
	 */
	static private function curlRequest($full_url, $data = array(), $max_sec = 60) {

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($curl, CURLOPT_TIMEOUT, $max_sec);
		curl_setopt($curl, CURLOPT_URL, $full_url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$curl_result= curl_exec($curl);
		$curl_code 	= curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($curl_code >= 200 && $curl_code <= 203) {
			$result = json_decode($curl_result, true);
		} else {
			if(empty($result['type'])) $result['type'] = 'error';
			$result['message'] 	= 'Error wrong CURL Code ' . $curl_code . ' in query: ' . $full_url;
		}

		$result['curl']['code'] 	= $curl_code;
		$result['curl']['error'] 	= curl_errno($curl);
//		$result['curl']['raw']		= $curl_result;
		return $result;
	}

	/**
	 *
	 * Helper to create access url to nCrawler.com
	 * @param $section
	 * @param $sub_action
	 * @return string
	 */
	private function generateNetCrawlerAccessUrl($section, $sub_action) {

		$parts = array(
			trim($this->nc_access_url, '/'),
			trim($section, '/'),
			trim($this->nc_access_token, '/'),
			trim($this->nc_access_login, '/'),
			trim($sub_action, '/'),
		);

		$url = join('/', $parts);

		return $url;
	}

	/**
	 * Helper to build access url
	 * @param array $elements
	 * @return string
	 */
	static private function build_url(array $elements) {
		$e = $elements;
		return
			(isset($e['host']) ? (
				(isset($e['scheme']) ? "$e[scheme]://" : '//') .
				(isset($e['user']) ? $e['user'] . (isset($e['pass']) ? ":$e[pass]" : '') . '@' : '') .
				$e['host'] .
				(isset($e['port']) ? ":$e[port]" : '')
			) : '') .
			(isset($e['path']) ? $e['path'] : '/') .
			(isset($e['query']) ? '?' . (is_array($e['query']) ? http_build_query($e['query'], '', '&') : $e['query']) : '') .
			(isset($e['fragment']) ? "#$e[fragment]" : '')
			;
	}

}


//	public function init() {
//		ppp('DIE INIT');
//	}

//	public function initContent()
//	{
//		ppp('DIEjjjjjjj INIT');
//		$this->renderView();
//		return parent::initContent();
//	}
//		$this->base_tpl_view = 'table.tpl';
//		return parent::renderView();
//		$this->base_tpl_view	= 'table.tpl';

//		$this->addRowAction('edit');
//		$this->addRowAction('delete');

//		$this->fieldImageSettings = array('name' => 'logo', 'dir' => 'pay');
//
//		$this->fields_list = array(
//			'id_universalpay_system' => array(
//				'title' => $this->l('ID'),
//				'align' => 'center', 'width' => 30
//			),
//			'logo' => array('title' => $this->l('Logo445'),
//				'align' => 'center',
//				'image' => 'pay',
//				'orderby' => false,
//				'search' => false
//			),
//			'name' => array(
//				'title' => $this->l('Name5454'),
//				'width' => 150
//			),
//			'description_short' => array(
//				'title' => $this->l('Short descriptio5555n'),
//				'width' => 450,
//				'maxlength' => 90,
//				'orderby' => false
//			),
//			'active' => array(
//				'title' => $this->l('Displayed'),
//				'active' => 'status',
//				'align' => 'center',
//				'type' => 'bool',
//				'orderby' => false)
//		);

//		$productObj = new Product();
//		$products = $productObj->getProducts($this->context->language->id, 0, 100, 'id_product', 'DESC', false, false);
//
//		$prod_json = [];
//		foreach($products as $product) {
////			$link = new Link();
////			$url = $link->getProductLink($product['id_product']);
//
//			$status = 'Неизвестно';
//
////			if()
//
//			$prod_json[] = [
//				'RowID'		 	=> $product['id_product'],
//				'name' 			=> $product['name'],
//				'current_price'	=> number_format($product['price'], 2, '.', ' '),
//				'suggest_price'	=> 0,
//				'end_price'		=> '',
//				'status'		=> $status,
//				'actions'		=> '',
//			];
//		}