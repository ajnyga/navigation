# navigation
Navigation plugin for OJS 3.x

The plugin replaces the frontend/components/primaryNavMenu.tpl template with a template that loads all navigation item from the OJS database. The plugin creates two tables: navigation and navigation_settings.

Upload and enable in plugin settings. New navigation items can be added from Settings->Website->Navigation.

The type of a navigation item is *url* or *smarty*, if you use *smarty* the URL should be a json encoded array containing router elements ie. '{"page":"issue","op":"current"}'.

Parent ID is 0 if the item is placed on the top level. Title is either a localised string or an existing translation key. 

Some translation keys have functionality:
- *announcement.announcements*, only visible if $enableAnnouncements
- *navigation.current* and *navigation.archives*, only visible if $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE
- *about.editorialTeam*, only visible if $currentJournal->getLocalizedSetting('masthead')
- *about.contact*, only visible if $currentJournal->getSetting('mailingAddress') || $currentJournal->getSetting('contactName')

## Todo / problems

- *Better UI* for arranging items, something like Nestable?
- *Custom themes* may have own css classes used in frontend/components/primaryNavMenu.tpl (for example Bootstrap) and these are now hard coded in createMenu function. One option would be to use plugin settings to save css class names.
- Upon enabling the plugin in a journal the tables should be prefilled with 
- When turning on warnings in config.inc.php editing items leads to a blank page with {status: true etc. Nothing in the error_log and no javascript errors present.
- a lot of tidying up. OJS3 plugins are hard to write because there are so few of them and no documentation exists
