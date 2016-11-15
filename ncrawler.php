<?php

if (!defined('_PS_VERSION_'))
	exit;

class nCrawler extends Module
{
	const ADMIN_TAB_NAME 	= 'AdminNetCrawler';

	const NC_ACCESS_LOGIN	= 'NC_ACCESS_LOGIN';
	const NC_ACCESS_TOKEN	= 'NC_ACCESS_TOKEN';
	const NC_ACCESS_URL		= 'NC_ACCESS_URL';

	private $_html 	= '';

	private $nc_access_login 	= '';
	private $nc_access_token 	= '';
	private $nc_access_url 		= '';

	private $_postErrors 		= [];

	public function __construct()
	{
		$this->name		= 'ncrawler';
		$this->tab 		= 'others';
		$this->version 	= '0.0.1';
		$this->author 	= 'ncrawler.com';
		$this->need_instance = 1; // open module setting page after install

		//$this->module_key = 'a4e3c26ec6e4316dccd6d7da5ca30411';
		//$this->controllers = array('payment', 'validation');

		$this->ps_versions_compliancy['min'] = '1.5.0';

		//$this->author_uri = 'http://addons.prestashop.com/ru/payments-gateways/5507-universal-payment-module.html';
		$this->bootstrap = true; // use bootstrap for creation module struct

		$config = Configuration::getMultiple([self::NC_ACCESS_LOGIN,self::NC_ACCESS_TOKEN,self::NC_ACCESS_URL,]);
		if (isset($config[self::NC_ACCESS_LOGIN])) $this->nc_access_login = $config[self::NC_ACCESS_LOGIN];
		if (isset($config[self::NC_ACCESS_TOKEN])) $this->nc_access_token = $config[self::NC_ACCESS_TOKEN];
		if (isset($config[self::NC_ACCESS_URL])) $this->nc_access_url = $config[self::NC_ACCESS_URL];

		parent::__construct();

		$this->displayName = $this->l('nCrawler.com integration');
		$this->description = $this->l('Интеграция сервиса nCrawler.com');
		$this->confirmUninstall = $this->l('Вы действительно хотите удалить модуль?'); //сообщение, при удалении модуля

		if (!Configuration::get('NCRAWLER'))
			$this->warning = $this->l('Упс, произошла какая-то ошибка!'); //проверка на ошибки во время установки

		//Shop::addTableAssociation('universalpay_system', array('type' => 'shop'));
	}


	/**
	 * Install module
	 * @return bool
	 */
	public function install()
	{
//		if (Shop::isFeatureActive()) //если несколько магазинов, то включаем модуль для всех
//			Shop::setContext(Shop::CONTEXT_ALL);

		return
			parent::install() 				&&
//			$this->registerHook('top') 		&&
//			$this->registerHook('header') 	&&
//			$this->registerHook('nav') 		&&
			$this->registerHook('actionAdminControllerSetMedia') &&
			Configuration::updateValue('NCRAWLER', 'my value') &&
			self::installModuleTab(self::ADMIN_TAB_NAME,
				[
					'ru' 		=> 'nCrawler.com',
					'default' 	=> 'nCrawler.com',
				],'AdminParentModules');
	}

	public function hookActionAdminControllerSetMedia($params) {
		$this->context->controller->addCSS($this->_path . 'views/css/sweetalert2.min.css');
		$this->context->controller->addCSS($this->_path . 'views/css/datatables.min.css');
		$this->context->controller->addCSS($this->_path . 'views/css/ncrawler.css');

		$this->context->controller->addJS($this->_path . 'views/js/sweetalert2.min.js');
		$this->context->controller->addJS($this->_path . 'views/js/datatables.min.js');
		$this->context->controller->addJS($this->_path . 'views/js/ncrawler.js');
	}

	/**
	 * uninstall module
	 * @return bool
	 */
	public function uninstall()
	{
		self::uninstallModuleTab(self::ADMIN_TAB_NAME);

		return
			parent::uninstall() &&
			Configuration::deleteByName('NCRAWLER') &&
			Configuration::deleteByName(self::NC_ACCESS_LOGIN) &&
			Configuration::deleteByName(self::NC_ACCESS_TOKEN) &&
			Configuration::deleteByName(self::NC_ACCESS_URL)
			;

//			self::rrmdir(_PS_IMG_DIR_ . 'pay') &&
//		if (!parent::uninstall() || !Configuration::deleteByName('NCRAWLER')) return false;
//		return true;
	}

	/**
	 * Content function
	 * @return string
	 */
	public function getContent() {

		if (!empty($_POST)) {
			$this->_postValidation();
			if (!sizeof($this->_postErrors)) $this->_postProcess();
			else foreach ($this->_postErrors AS $err) $this->_html .= '<div class="alert error">'. $err .'</div>';
		}
		else $this->_html .= '<br />';

		$this->_displaySettingsForm();
		return $this->_html;
	}

	/**
	 * Settings form
	 */
	private function _displaySettingsForm() {
		$this->_html .=
			'<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
            <fieldset>
            <legend><img src="../img/admin/contact.gif" />' . $this->l('nCrawler integration settings') . '</legend>
                <table border="0" width="500" cellpadding="0" cellspacing="0" id="form">
                    <tr><td colspan="2">'.$this->l('Please specify required data').'.<br /><br /></td></tr>
                    <tr><td width="140" style="height: 35px;">'.$this->l('Your nCrawler login').'</td><td><input type="text" name="nc_access_login" value="'.htmlentities(Tools::getValue('nc_access_login', $this->nc_access_login), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
                    <tr><td width="140" style="height: 35px;">'.$this->l('Your nCrawler token').'</td><td><input type="text" name="nc_access_token" value="'.htmlentities(Tools::getValue('nc_access_token', $this->nc_access_token), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
                    <tr><td width="140" style="height: 35px;">'.$this->l('nCrawler access url').'</td><td><input type="text" name="nc_access_url" value="'.htmlentities(Tools::getValue('nc_access_url', $this->nc_access_url), ENT_COMPAT, 'UTF-8').'" style="width: 300px;" /></td></tr>
                    <tr><td colspan="2" align="center"><br /><input class="button" name="btnSubmit" value="'.$this->l('Save settings').'" type="submit" /></td></tr>
                </table>
            </fieldset>
        </form>';
	}

	/**
	 * Check setttings form
	 */
	private function _postValidation() {
		if (isset($_POST['btnSubmit'])) {
			if (empty($_POST['nc_access_login'])) $this->_postErrors[] = $this->l('Login is required');
			if (empty($_POST['nc_access_token'])) $this->_postErrors[] = $this->l('Token is required');
			if (empty($_POST['nc_access_url'])) $this->_postErrors[] = $this->l('Url is requeired');
		}
	}

	/**
	 * Process settings form
	 */
	private function _postProcess()
	{
		if (isset($_POST['btnSubmit'])) {
			Configuration::updateValue(self::NC_ACCESS_LOGIN, $_POST['nc_access_login']);
			Configuration::updateValue(self::NC_ACCESS_TOKEN, $_POST['nc_access_token']);
			Configuration::updateValue(self::NC_ACCESS_URL, $_POST['nc_access_url']);

			$this->_html .= '
			<div class="conf confirm">
				<img src="../img/admin/ok.gif" alt="' . $this->l('OK') . '" /> 
				' . $this->l('Settings updated') . '
			</div>';
		}
	}

//	public function hookHeader()
//	{
//		$this->context->controller->addCSS(($this->_path).'views/css/ncrawler.css', 'all');
//		$this->context->controller->addJS(($this->_path).'view/js/ncrawler.js');
//	}


//	/**
//	 * after install content
//	 * @return string
//	 */
//	public function getContent()
//	{
//		$output = 'фывфывжфдылвдл';
////		$output .= $this->postProcess();
////		$output .= $this->renderSettingsForm();
//		return $output;
//	}


	/**
	 * @param $tab_class
	 * @param $tab_name
	 * @param $tab_parent
	 * @return bool
	 */
	private function installModuleTab($tab_class, $tab_name, $tab_parent)
	{
		if (!($id_tab_parent = Tab::getIdFromClassName($tab_parent))) {return false;}

		$tab = new Tab();
		$languages = Language::getLanguages(true);
		foreach ($languages as $language) {
			if (!isset($tab_name[$language['iso_code']])) {
				$tab->name[$language['id_lang']] = $tab_name['default'];
			} else {
				$tab->name[(int)$language['id_lang']] = $tab_name[$language['iso_code']];
			}
		}
		$tab->class_name	= $tab_class;
		$tab->module 		= $this->name;
		$tab->id_parent 	= $id_tab_parent;
		$tab->active 		= 1;

		if (!$tab->save()) return false;

		return true;
	}

	/**
	 * @param $tab_class
	 * @return bool
	 */
	private function uninstallModuleTab($tab_class)
	{
		$id_tab = Tab::getIdFromClassName($tab_class);

		if ($id_tab != 0) {
			$tab = new Tab($id_tab);
			$tab->delete();
			return true;
		}

		return false;
	}

}