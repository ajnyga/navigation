<?php

/**
 * @file controllers/grid/form/NavigationForm.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NavigationForm
 * @ingroup controllers_grid_navigation
 *
 * Form for editing navigation items
 *
 */

import('lib.pkp.classes.form.Form');

class NavigationForm extends Form {
	/** @var int Context (press / journal) ID */
	var $contextId;

	/** @var string Static page name */
	var $NavigationItemId;

	/** @var StaticPagesPlugin Static pages plugin */
	var $plugin;

	/**
	 * Constructor
	 * @param $navigationPlugin NavigationPlugin The navigation plugin
	 * @param $contextId int Context ID
	 * @param $navigationItemId int Navigation item ID (if any)
	 */
	function NavigationForm($navigationPlugin, $contextId, $navigationItemId = null) {
		parent::Form($navigationPlugin->getTemplatePath() . 'editnavigationItemForm.tpl');

		$this->contextId = $contextId;
		$this->navigationItemId = $navigationItemId;
		$this->plugin = $navigationPlugin;

		// Add form checks
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
		
		
	}

	/**
	 * Initialize form data from current group.
	 */
	function initData() {
		$templateMgr = TemplateManager::getManager();
		$this->setData('listTypes', array('url' => 'url','smarty' => 'smarty'));
		
		if ($this->navigationItemId) {
			$navigationDao = DAORegistry::getDAO('NavigationDAO');
			$navigationItem = $navigationDao->getById($this->navigationItemId, $this->contextId);
			
			$this->setData('parent', $navigationItem->getParent());
			$this->setData('sort', $navigationItem->getSort());
			
			$this->setData('type', $navigationItem->getType());
			
			$this->setData('translateTitle', $navigationItem->getTranslateTitle());
			
			$this->setData('title', $navigationItem->getTitle(null)); // Localized
			
			$this->setData('url', $navigationItem->getUrl()); 
			
			
		}

	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('parent', 'sort', 'type', 'translateTitle', 'title', 'url'));
	}

	/**
	 * @see Form::fetch
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager();
		$templateMgr->assign('navigationItemId', $this->navigationItemId);
		$templateMgr->assign('pluginJavaScriptURL', $this->plugin->getJavaScriptURL($request));
		
		return parent::fetch($request);
	}

	/**
	 * Save form values into the database
	 */
	function execute() {
		$navigationDao = DAORegistry::getDAO('NavigationDAO');
		if ($this->navigationItemId) {
			// Load and update an existing page
			$navigationItem = $navigationDao->getById($this->navigationItemId, $this->contextId);
		} else {
			// Create a new static page
			$navigationItem = $navigationDao->newDataObject();
			$navigationItem->setContextId($this->contextId);
		}

		$navigationItem->setParent($this->getData('parent'));
		$navigationItem->setSort($this->getData('sort'));
		$navigationItem->setType($this->getData('type'));
		$navigationItem->setTranslateTitle($this->getData('translateTitle'));
		$navigationItem->setTitle($this->getData('title'), null);
		$navigationItem->setUrl($this->getData('url'));
		
		if ($this->navigationItemId) {
			$navigationDao->updateObject($navigationItem);
		} else {
			$navigationDao->insertObject($navigationItem);
		}
		
	}
}

?>
