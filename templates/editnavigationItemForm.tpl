{**
 * templates/editnavigationItemForm.tpl
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form for editing a navigation item
 *}
<script src="{$pluginJavaScriptURL}/NavigationFormHandler.js"></script>
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#navigationForm').pkpHandler(
			'$.pkp.controllers.form.navigation.NavigationFormHandler',
			{ldelim}
			{rdelim}
		);
	{rdelim});
</script>

{url|assign:actionUrl router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.navigation.controllers.grid.NavigationGridHandler" op="updateNavigationItem" escape=false}
<form class="pkp_form" id="navigationForm" method="post" action="{$actionUrl}">
	{csrf}
	{if $navigationItemId}
		<input type="hidden" name="navigationItemId" value="{$navigationItemId|escape}" />
	{/if}
	{fbvFormArea id="navigationFormArea" class="border"}

		{fbvFormSection}
			{fbvElement type="select" label="common.type" id="type" from=$listTypes selected=$type translate=false size=$fbvStyles.size.SMALL}
			{fbvElement type="text" label="plugins.generic.navigation.url" id="url" value=$url maxlength="255" inline=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection}
			{fbvElement type="text" label="plugins.generic.navigation.parent" id="parent" value=$parent maxlength="10" inline=true size=$fbvStyles.size.SMALL}
			{fbvElement type="text" label="plugins.generic.navigation.sort" id="sort" value=$sort maxlength="10" inline=true size=$fbvStyles.size.SMALL}
		{/fbvFormSection}
		
		{fbvFormSection}
			{fbvElement type="text" label="common.title" id="title" value=$title maxlength="50" inline=true multilingual=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" label="plugins.generic.navigation.translateTitle" id="translateTitle" value=$translateTitle maxlength="50" inline=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		
		
	{/fbvFormArea}
	{fbvFormSection class="formButtons"}
		{assign var=buttonId value="submitFormButton"|concat:"-"|uniqid}
		{fbvElement type="submit" class="submitFormButton" id=$buttonId label="common.save"}
	{/fbvFormSection}
</form>
