{**
 * templates/navigation.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Navigation plugin -- displays the NavigationGrid.
 *}
{url|assign:navigationGridUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.navigation.controllers.grid.NavigationGridHandler" op="fetchGrid" escape=false}
{load_url_in_div id="navigationGridContainer" url=$navigationGridUrl}
