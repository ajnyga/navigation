<?php

/**
 * @file controllers/grid/NavigationGridHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NavigationGridHandler
 * @ingroup controllers_grid_navigation
 *
 * @brief Handle Navigation grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('plugins.generic.navigation.controllers.grid.NavigationGridRow');
import('plugins.generic.navigation.controllers.grid.NavigationGridCellProvider');

class NavigationGridHandler extends GridHandler {
	/** @var NavigationPlugin The Navigation plugin */
	static $plugin;

	/**
	 * Set the Navigation plugin.
	 * @param $plugin NavigationPlugin
	 */
	static function setPlugin($plugin) {
		self::$plugin = $plugin;
	}

	/**
	 * Constructor
	 */
	function NavigationGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER),
			array('index', 'fetchGrid', 'fetchRow', 'addNavigationItem', 'editNavigationItem', 'updateNavigationItem', 'delete')
		);
	}


	//
	// Overridden template methods
	//
	/**
	 * @copydoc Gridhandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request);
		$context = $request->getContext();

		// Set the grid details.
		$this->setTitle('plugins.generic.navigation.navigation');
		$this->setEmptyRowText('plugins.generic.navigation.noneCreated');

		// Get the navigation items and add the data to the grid
		$navigationDao = DAORegistry::getDAO('NavigationDAO');
		$this->setGridDataElements($navigationDao->getOrderedByContextId($context->getId()));
		
		// Add grid-level actions
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'addNavigationItem',
				new AjaxModal(
					$router->url($request, null, null, 'addNavigationItem'),
					__('plugins.generic.navigation.addNavigationItem'),
					'modal_add_item'
				),
				__('plugins.generic.navigation.addNavigationItem'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new NavigationGridCellProvider();
		$this->addColumn(new GridColumn(
			'id',
			'common.id',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'title',
			'common.title',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'type',
			'common.type',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'parent',
			'plugins.generic.navigation.parent',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));
		$this->addColumn(new GridColumn(
			'sort',
			'plugins.generic.navigation.sort',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			$cellProvider
		));		
		
		
	}

	//
	// Overridden methods from GridHandler
	//
	/**
	 * @copydoc Gridhandler::getRowInstance()
	 */
	function getRowInstance() {
		return new NavigationGridRow();
	}

	//
	// Public Grid Actions
	//
	/**
	 * Display the grid's containing page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, $request) {
		$context = $request->getContext();
		import('lib.pkp.classes.form.Form');
		$form = new Form(self::$plugin->getTemplatePath() . 'navigation.tpl');
		$json = new JSONMessage(true, $form->fetch($request));
		return $json->getString();
	}

	/**
	 * An action to add a new custom navigationItem
	 * @param $args array Arguments to the request
	 * @param $request PKPRequest Request object
	 */
	function addNavigationItem($args, $request) {
		// Calling editNavigationitem with an empty ID will add
		// a new Navigation item.
		return $this->editNavigationItem($args, $request);
	}

	/**
	 * An action to edit a navigationItem
	 * @param $args array Arguments to the request
	 * @param $request PKPRequest Request object
	 * @return string Serialized JSON object
	 */
	function editNavigationItem($args, $request) {
		$navigationItemId = $request->getUserVar('navigationItemId');
		$context = $request->getContext();
		$this->setupTemplate($request);

		// Create and present the edit form
		import('plugins.generic.navigation.controllers.grid.form.NavigationForm');
		$navigationPlugin = self::$plugin;
		$navigationForm = new NavigationForm(self::$plugin, $context->getId(), $navigationItemId);
		$navigationForm->initData();
		$json = new JSONMessage(true, $navigationForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a navigation item
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateNavigationItem($args, $request) {
		$navigationItemId = $request->getUserVar('navigationItemId');
		$context = $request->getContext();
		$this->setupTemplate($request);

		// Create and populate the form
		import('plugins.generic.navigation.controllers.grid.form.NavigationForm');
		$navigationPlugin = self::$plugin;
		$navigationForm = new navigationForm(self::$plugin, $context->getId(), $navigationItemId);
		$navigationForm->readInputData();

		// Check the results
		if ($navigationForm->validate()) {
			// Save the results
			$navigationForm->execute();
 			return DAO::getDataChangedEvent();
		} else {
			// Present any errors
			$json = new JSONMessage(true, $navigationForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a navigationItem
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function delete($args, $request) {
		$navigationItemId = $request->getUserVar('navigationItemId');
		$context = $request->getContext();

		// Delete the navigation item
		$navigationDao = DAORegistry::getDAO('NavigationDAO');
		$navigationItem = $navigationDao->getById($navigationItemId, $context->getId());
		$navigationDao->deleteObject($navigationItem);

		return DAO::getDataChangedEvent();
	}
}

?>
