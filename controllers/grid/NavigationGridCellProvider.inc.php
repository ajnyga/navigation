<?php

/**
 * @file controllers/grid/NavigationGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StaticPageGridCellProvider
 * @ingroup controllers_grid_navigation
 *
 * @brief Class for a cell provider to display information about navigation items
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');
import('lib.pkp.classes.linkAction.request.RedirectAction');

class NavigationGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function NavigationGridCellProvider() {
		parent::GridCellProvider();
	}


	//
	// Template methods from GridCellProvider
	//


	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$navigationItem = $row->getData();

		switch ($column->getId()) {
			case 'id':
				return array('label' => $navigationItem->getId());
			case 'type':
				return array('label' => $navigationItem->getType());
			case 'title':
				$title = NavigationPlugin::createTitle($navigationItem, 'Smarty');
				if ($navigationItem->getParent() != '0')
					$title = "-- ".$title;	
				return array('label' => $title);
			case 'parent':
				return array('label' => $navigationItem->getParent());	
			case 'sort':
				return array('label' => $navigationItem->getSort());	
		}
	}
}

?>
