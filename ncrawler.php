<?php

if (!defined('_PS_VERSION_'))
	exit;

class nCrawler extends Module
{

	public function __construct()
	{
		$this->name		= 'ncrawler';
		$this->tab 		= 'others';
		$this->version 	= '0.0.1'; //версия модуля, например "2.0b", "3.04 beta 5" или "0.67 (для разработчика)"
		$this->author 	= 'ncrawler.com'; //имя автора
		$this->need_instance = 0; //открыть страницу настроек модуля сразу после установки или нет

		//$this->module_key = 'a4e3c26ec6e4316dccd6d7da5ca30411';
		//$this->controllers = array('payment', 'validation');

		$this->ps_versions_compliancy['min'] = '1.5.0';

		//$this->author_uri = 'http://addons.prestashop.com/ru/payments-gateways/5507-universal-payment-module.html';
		$this->bootstrap = true; //использовать инструмент bootstrap для построения элементов модуля, рекомендую установить true

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
		if (Shop::isFeatureActive()) //если несколько магазинов, то включаем модуль для всех
			Shop::setContext(Shop::CONTEXT_ALL);

		//установка модуля и привязка его к необходимым хукам, в которых он будет использован, создание конфигурации для модуля в базе данных
		if (!parent::install() || //установлен ли родительский класс
			!$this->registerHook('top') || //модуль прикрепился к хуку 'top'
			!$this->registerHook('header') || //модуль прикрепился к хуку 'header'
			!$this->registerHook('nav') || //модуль прикрепился к хуку 'nav'
			!Configuration::updateValue('NCRAWLER', 'my value') //создаём конфигурацию 'NCRAWLER' со значением 'my value'
		) return false;

		return true;
	}


	/**
	 * uninstall module
	 * @return bool
	 */
	public function uninstall()
	{
		if (!parent::uninstall() || !Configuration::deleteByName('NCRAWLER')) return false;

		return true;
	}
}