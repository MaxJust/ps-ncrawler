<?php

if (!defined('_PS_VERSION_'))
	exit;

class nCrawler extends Module
{
	const ADMIN_TAB_NAME = 'AdminNetCrawler';

	public function __construct()
	{
		$this->name		= 'ncrawler';
		$this->tab 		= 'others';
		$this->version 	= '0.0.1';
		$this->author 	= 'ncrawler.com';
		$this->need_instance = 0; // open module setting page after install

		//$this->module_key = 'a4e3c26ec6e4316dccd6d7da5ca30411';
		//$this->controllers = array('payment', 'validation');

		$this->ps_versions_compliancy['min'] = '1.5.0';

		//$this->author_uri = 'http://addons.prestashop.com/ru/payments-gateways/5507-universal-payment-module.html';
		$this->bootstrap = true; // use bootstrap for creation module struct

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
			$this->registerHook('top') 		&&
			$this->registerHook('header') 	&&
			$this->registerHook('nav') 		&&
			Configuration::updateValue('NCRAWLER', 'my value') &&
			self::installModuleTab(self::ADMIN_TAB_NAME,
				[
					'ru' => 'Платежные системы',
					'default' => 'Pay Systems',
					'it' => 'Metodi di pagamento'
				],'AdminParentModules');

//		//установка модуля и привязка его к необходимым хукам, в которых он будет использован, создание конфигурации для модуля в базе данных
//		if (!parent::install() || //установлен ли родительский класс
//			!$this->registerHook('top') || //модуль прикрепился к хуку 'top'
//			!$this->registerHook('header') || //модуль прикрепился к хуку 'header'
//			!$this->registerHook('nav') || //модуль прикрепился к хуку 'nav'
//			!Configuration::updateValue('NCRAWLER', 'my value') //создаём конфигурацию 'NCRAWLER' со значением 'my value'
//		) return false;
//
//		return true;
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
			Configuration::deleteByName('NCRAWLER');

//			self::rrmdir(_PS_IMG_DIR_ . 'pay') &&
//		if (!parent::uninstall() || !Configuration::deleteByName('NCRAWLER')) return false;
//		return true;
	}


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