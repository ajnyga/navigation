<?php

/**
 * @file plugins/generic/navigation/GoogleAnalyticsPlugin.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NavigationPlugin
 * @ingroup plugins_generic_navigation
 *
 * @brief Navigation plugin class
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class NavigationPlugin extends GenericPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	
	function register($category, $path) {
		
		if (parent::register($category, $path)) {
			
			if ($this->getEnabled()) {
				
				// Register the navigation DAO.
				import('plugins.generic.navigation.classes.NavigationDAO');
				$navigationDao = new NavigationDAO();
				DAORegistry::registerDAO('NavigationDAO', $navigationDao);
				
				HookRegistry::register ('TemplateManager::include', array(&$this, 'addNavigation'));
				
				HookRegistry::register('Templates::Management::Settings::website', array($this, 'showSettings'));
				
				HookRegistry::register('LoadComponentHandler', array($this, 'setupGridHandler'));
				
				
			}
			return true;
		}
		
		return false;
		
	}
	
	/*

TODO: prefill when plugin is enabled, if tables are empty for context

INSERT INTO `navigation` (`navigation_id`, `parent`, `sort`, `type`, `context_id`) VALUES
(1,	0,	1,	'smarty',	?),
(2,	0,	2,	'smarty',	?),
(3,	0,	3,	'smarty',	?),
(4,	0,	4,	'smarty',	?),
(5,	4,	1,	'smarty',	?),
(6,	4,	2,	'smarty',	?),
(7,	4,	3,	'smarty',	?),
(8,	4,	4,	'smarty',	?);

INSERT INTO `navigation_settings` (`navigation_id`, `locale`, `setting_name`, `setting_value`, `setting_type`) VALUES
(1,	'',	'translateTitle',	'announcement.announcements',	'string'),
(1,	'',	'url',	'{\"page\":\"announcement\"}',	'string'),
(2,	'',	'translateTitle',	'navigation.current',	'string'),
(2,	'',	'url',	'{\"page\":\"issue\",\"op\":\"current\"}',	'string'),
(3,	'',	'translateTitle',	'navigation.archives',	'string'),
(3,	'',	'url',	'{\"page\":\"issue\",\"op\":\"archive\"}',	'string'),
(4,	'',	'translateTitle',	'navigation.about',	'string'),
(4,	'',	'url',	'{\"page\":\"about\"}',	'string'),
(5,	'',	'translateTitle',	'about.aboutContext',	'string'),
(5,	'',	'url',	'{\"page\":\"about\"}',	'string'),
(6,	'',	'translateTitle',	'about.editorialTeam',	'string'),
(6,	'',	'url',	'{\"page\":\"about\",\"op\":\"editorialTeam\"}',	'string'),
(7,	'',	'translateTitle',	'about.submissions',	'string'),
(7,	'',	'url',	'{\"page\":\"about\",\"op\":\"submissions\"}',	'string'),
(8,	'',	'translateTitle',	'about.contact',	'string'),
(8,	'',	'url',	'{\"page\":\"about\",\"op\":\"contact\"}',	'string');
*/
	
	/**
	 * Get the plugin display name.
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.navigation.displayName');
	}

	/**
	 * Get the plugin description.
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.navigation.description');
	}		
	
	function getInstallSchemaFile() {
		return $this->getPluginPath() . '/schema.xml';
	}

	function getTemplatePath($inCore = false) {
		return parent::getTemplatePath($inCore) . 'templates/';
	}

	function getJavaScriptURL($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath() . '/js';
	}	
	
	
	function showSettings($hookName, $args) {
		$output =& $args[2];
		$request =& Registry::get('request');
		$dispatcher = $request->getDispatcher();
		$output .= '<li><a name="navigation" href="' . $dispatcher->url($request, ROUTE_COMPONENT, null, 'plugins.generic.navigation.controllers.grid.NavigationGridHandler', 'index') . '">' . __('plugins.generic.navigation.navigation') . '</a></li>';
		return false;
	}	
	
	function setupGridHandler($hookName, $params) {
		$component =& $params[0];
		if ($component == 'plugins.generic.navigation.controllers.grid.NavigationGridHandler') {
			import($component);
			NavigationGridHandler::setPlugin($this);
			return true;
		}
		return false;
	}

		
	/**
	 * Create translated title
	 * @return string
	 */
	function createTitle(&$naviItem, $smarty){

		$templateMgr =& TemplateManager::getManager();
				
		if ($naviItem->getTranslateTitle()){
			$title = $templateMgr->smartyTranslate(array('key'=>$naviItem->getTranslateTitle()), $smarty);				
		}
		else{
			$title = $naviItem->getLocalizedTitle();
		}

		return $title;
		
	}		


	/**
	 * Add custom navigation, replace primaryNavMenu.tpl
	 * @param $hookName string
	 * @param $params array
	 */
	function addNavigation($hookName, $args) {
		
		$params =& $args[1];

		if (!isset($params['smarty_include_tpl_file'])) return false;

		switch ($params['smarty_include_tpl_file']) {
			case 'frontend/components/primaryNavMenu.tpl':
			
			$templateMgr =& $args[0];
			
			$navigationDao = DAORegistry::getDAO('NavigationDAO');

			$currentJournal = $templateMgr->get_template_vars('currentJournal');
			$currentJournalId = $currentJournal->getId();
			
			$naviItems = $navigationDao->getByContextId($currentJournalId);
			 
			$itemList = array();
			while ($naviItem = $naviItems->next()){
							
				# if smarty link, check translateTitle against list of exceptions
				if ($naviItem->getType() == "url" OR $this->checkExceptions($naviItem->getTranslateTitle(), $currentJournal) ){
				
					if ($naviItem->getType() == "smarty")
						$link = $templateMgr->smartyUrl(json_decode($naviItem->getUrl(), true), $smarty);
					else
						$link = $naviItem->getUrl();
					
					$title = $this->createTitle($naviItem, $smarty);
				
					$itemList[$naviItem->getParent()][] = array('id' => $naviItem->getId(), 'parent' => $naviItem->getParent(), 'value' => "<a href=\"". $link . "\">" . $title . "</a>");
				
				}
				
	
			}
			
			$navigation = $this->createMenu($this->createTree($itemList, $itemList[0]));
						
			$templateMgr->assign('navigation', $navigation);
			$templateMgr->display($this->getTemplatePath() . 'primaryNavMenu.tpl', 'text/html', 'TemplateManager::include');
			return true;
				
		}
		return false;
	}
	
	function checkExceptions($translateTitle, &$currentJournal){
				
		if ($translateTitle == 'announcement.announcements')
			if (!$currentJournal->getSetting('enableAnnouncements')) return false;
		if ($translateTitle == 'navigation.current' OR $translateTitle == 'navigation.archives')
			if ($currentJournal->getSetting('publishingMode') == 2) return false;
		if ($translateTitle == 'about.editorialTeam')
			if (!$currentJournal->getLocalizedSetting('masthead')) return false;
		if ($translateTitle == 'about.contact')
			if (!$currentJournal->getSetting('mailingAddress') || !$currentJournal->getSetting('contactName')) return false;

		return true;
	}	
	
	/**
	 * Create nested array from flat array
	 * @return array
	 */
	function createTree(&$list, $parent){
    $tree = array();
    foreach ($parent as $k=>$l){
        if(isset($list[$l['id']])){
            $l['children'] = $this->createTree($list, $list[$l['id']]);
        }
        $tree[] = $l;
    } 
    return $tree;
	}

	/**
	 * Create menu from nested array
	 * @return string
	 */
	function createMenu($menuArray) {
	$naviItems = "";
    foreach($menuArray as $menu) {
        $naviItems .= "<li ";
		if(array_key_exists('children', $menu))
			$naviItems .= 'aria-haspopup="true" aria-expanded="false"';		
		$naviItems .= ">" . $menu['value'];
        if(array_key_exists('children', $menu)) {
            $naviItems .= "\n<ul>\n";
            $naviItems .= $this->createMenu($menu['children']);
            $naviItems .= "</ul>\n";
        }
	$naviItems .= "</li>\n";
    }
	return $naviItems;
	}
	
	
	
}

?>
