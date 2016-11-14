<?php

class AdminNetCrawlerController extends ModuleAdminController
{
	public function __construct()
	{
		$this->bootstrap	= true;
		$this->table 		= 'universalpay_system';
		$this->className 	= 'UniPaySystem';
		$this->lang 		= true;

		$this->addRowAction('edit');
		$this->addRowAction('delete');

		$this->fieldImageSettings = array('name' => 'logo', 'dir' => 'pay');

		$this->fields_list = array(
			'id_universalpay_system' => array(
				'title' => $this->l('ID'),
				'align' => 'center', 'width' => 30
			),
			'logo' => array('title' => $this->l('Logo'),
				'align' => 'center',
				'image' => 'pay',
				'orderby' => false,
				'search' => false
			),
			'name' => array(
				'title' => $this->l('Name'),
				'width' => 150
			),
			'description_short' => array(
				'title' => $this->l('Short description'),
				'width' => 450,
				'maxlength' => 90,
				'orderby' => false
			),
			'active' => array(
				'title' => $this->l('Displayed'),
				'active' => 'status',
				'align' => 'center',
				'type' => 'bool',
				'orderby' => false)
		);

		parent::__construct();
	}
}