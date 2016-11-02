<?php

/**
 * @file classes/Navigation.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.navigation
 * @class Navigation
 * Data object representing a static page.
 */

class Navigation extends DataObject {
	/**
	 * Constructor
	 */
	function Navigation() {
		parent::DataObject();
	}

	//
	// Get/set methods
	//
	
	function getContextId(){
		return $this->getData('contextId');
	}

	function setContextId($contextId) {
		return $this->setData('contextId', $contextId);
	}	

	function getParent() {
		return $this->getData('parent');
	}

	function setParent($parent) {
		return $this->setData('parent', $parent);
	}	
	
	function getSort() {
		return $this->getData('sort');
	}

	function setSort($sort) {
		return $this->setData('sort', $sort);
	}	
	
	function getType() {
		return $this->getData('type');
	}

	function setType($type) {
		return $this->setData('type', $type);
	}	

	function getTranslateTitle() {
		return $this->getData('translateTitle');
	}	
	
	function setTranslateTitle($translateTitle) {
		return $this->setData('translateTitle', $translateTitle);
	}	
	
	function setTitle($title, $locale) {
		return $this->setData('title', $title, $locale);
	}

	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}
	
	function getUrl(){
		return $this->getData('url');
	}

	function setUrl($url) {
		return $this->setData('url', $url);
	}	

	
	
}

?>
