<?php

class AdminNetCrawlerController extends ModuleAdminController
{

	private $nc_access_login 	= '';
	private $nc_access_token 	= '';
	private $nc_access_url 		= '';

	public function __construct()
	{
		$this->module		= 'ncrawler';
		$this->bootstrap	= true;
		$this->table 		= 'ncrawler';
//		$this->className 	= 'NetCrawler';
		$this->lang 		= false;

		$this->display 		= 'view';

		parent::__construct();

		$config = Configuration::getMultiple([nCrawler::NC_ACCESS_LOGIN, nCrawler::NC_ACCESS_TOKEN, nCrawler::NC_ACCESS_URL]);
		$this->nc_access_login 	= $config[nCrawler::NC_ACCESS_LOGIN];
		$this->nc_access_token 	= $config[nCrawler::NC_ACCESS_TOKEN];
		$this->nc_access_url	= $config[nCrawler::NC_ACCESS_URL];
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

	public function ajaxProcessTest() {

		$productObj = new Product();
		$products = $productObj->getProducts($this->context->language->id, 0, 10, 'id_product', 'DESC', false, false);

		$prod_json = [];
		foreach($products as $product) {

			$link = new Link();
			$url = $link->getProductLink($product['id_product']);

			$prod_json[] = [
				'RowID'		 	=> $product['id_product'],
				'name' 			=> $product['name'],
				'current_price'	=> number_format($product['price'], 2, '.', ' '),
				'suggest_price'	=> 0,
				'watcher_name'	=> '',
				'actions'		=> '',
				'full_url'		=> $url,
			];
//			$q = $product;
//			break;
		}

		echo Tools::jsonEncode([
			'use_parent_structure' 	=> false,
			'products' 				=> $prod_json,
			'p_nums' 				=> count($products),
//			'q' 					=> $q,
		]);

		exit;
	}

	public function renderView()
	{
		$tpl = $this->context->smarty->createTemplate(_PS_MODULE_DIR_ . '/ncrawler/views/templates/admin/table.tpl');
		$tpl->assign('login', $this->nc_access_login);
		$tpl->assign('token', $this->nc_access_token);
		$tpl->assign('url', $this->nc_access_url);
		return $tpl->fetch();
	}

//	public function ajaxProcessMyFunction() {
//
//		// Get param
//		$mydata = (int)Tools::getValue('mydata');
//
//		$answer = 'just a test';
//
//		if( $mydata > 0 ) {}
//
//		// Response
//		die(Tools::jsonEncode(array(
//			'answer' => htmlspecialchars($answer)
//		)));
//
//	}

}




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