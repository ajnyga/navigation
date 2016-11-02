<?php

/**
 * @file classes/NavigationDAO.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.navigation
 * @class NavigationDAO
 * Operations for retrieving and modifying navigation objects.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.navigation.classes.Navigation');

class NavigationDAO extends DAO {
	/**
	 * Constructor
	 */
	function NavigationDAO() {
		parent::DAO();
	}

	
	/**
	 * Get a navigation item by ID
	 * @param $navigationItemId int Static page ID
	 * @param $contextId int Optional context ID
	 */
	
	function getById($navigationItemId, $contextId = null) {
		$params = array((int) $navigationItemId);
		if ($contextId) $params[] = $contextId;

		$result = $this->retrieve(
			'SELECT * FROM navigation WHERE navigation_id = ?'
			. ($contextId?' AND context_id = ?':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}
	

	/**
	 * Get navigation menu by context ID
	 * @param $contextId int
	 * @param $rangeInfo Object optional
	 * @return DAOResultFactory
	 */
	function getByContextId($contextId, $rangeInfo = null) {
		$result = $this->retrieveRange(
			'SELECT * FROM navigation WHERE context_id = ? ORDER BY parent, sort ASC',
			(int) $contextId,
			$rangeInfo
		);
		
		return new DAOResultFactory($result, $this, '_fromRow');
	}
	
	/**
	 * Get ordered navigation menu by context ID
	 * @param $contextId int
	 * @param $rangeInfo Object optional
	 * @return DAOResultFactory
	 */
	function getOrderedByContextId($contextId, $rangeInfo = null) {
		$result = $this->retrieveRange(
		'SELECT 
      t1.* 
   FROM 
      navigation t1 
         LEFT JOIN navigation t2 
            ON t1.parent = t2.navigation_id
   WHERE t1.context_id = ?
   ORDER BY
      COALESCE( t2.sort, t1.sort ),
      case when t2.navigation_id = 0 then 1 else 2 end,
      t1.parent, t1.sort ASC',
			(int) $contextId,
			$rangeInfo
		);
		
		return new DAOResultFactory($result, $this, '_fromRow');
	}	

	/**
	 * Insert a navigation.
	 * @param $navigation navigation
	 * @return int Inserted navigation ID
	 */
	function insertObject($navigation) {
		$this->update(
			'INSERT INTO navigation (context_id, type, sort, parent) VALUES (?, ?, ? , ?)',
			array(
				(int) $navigation->getContextId(),
				$navigation->getType(),
				$navigation->getSort(),
				$navigation->getParent()
			)
		);

		$navigation->setId($this->getInsertId());
		
		$this->updateLocaleFields($navigation);

		return $navigation->getId();
	}

	/**
	 * Update the database with a navigation object
	 * @param $navigation Navigation
	 */
	function updateObject($navigation) {
		$this->update(
			'UPDATE	navigation
			SET	context_id = ?,
				type = ?,
				sort = ?,
				parent = ?
			WHERE	navigation_id = ?',
			array(
				(int) $navigation->getContextId(),
				$navigation->getType(),
				$navigation->getSort(),
				$navigation->getParent(),
				(int) $navigation->getId()
			)
		);
		$this->updateLocaleFields($navigation);
		
	}

	/**
	 * Delete a navigation item by ID.
	 * @param $navigationId int
	 */
	function deleteById($navigationId) {
		$this->update(
			'DELETE FROM navigation WHERE navigation_id = ?',
			(int) $navigationId
		);
		$this->update(
			'DELETE FROM navigation_settings WHERE navigation_id = ?',
			(int) $navigationId
		);
	}

	/**
	 * Delete a navigation object.
	 * @param $navigation Navigation
	 */
	function deleteObject($navigation) {
		$this->deleteById($navigation->getId());
	}

	/**
	 * Generate a new navigation object.
	 * @return StaticPage
	 */
	function newDataObject() {
		return new Navigation();
	}

	/**
	 * Return a new navigation object from a given row.
	 * @return StaticPage
	 */
	function _fromRow($row) {
		$navigation = $this->newDataObject();
		
		$navigation->setId($row['navigation_id']);
		$navigation->setParent($row['parent']);
		$navigation->setSort($row['sort']);
		$navigation->setType($row['type']);		
		$navigation->setContextId($row['context_id']);
		
		
		$this->getDataObjectSettings('navigation_settings', 'navigation_id', $row['navigation_id'], $navigation);
		
		return $navigation;
		
	}

	/**
	 * Get the insert ID for the last inserted navigation item.
	 * @return int
	 */
	function getInsertId() {
		return $this->_getInsertId('navigation', 'navigation_id');
	}

	/**
	 * Get field names for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title');
	}
	
	 /**
	 * Get the list of non-localized additional fields to store.
	 * @return array
	 */
	function getAdditionalFieldNames() {
		return array('url', 'translateTitle');
	}	

	/**
	 * Update data for this object
	 */
	function updateLocaleFields($navigation) {
		$this->updateDataObjectSettings('navigation_settings', $navigation, array(
			'navigation_id' => $navigation->getId()
		));
	}	
	
}

?>
