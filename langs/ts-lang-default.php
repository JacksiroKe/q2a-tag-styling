<?php

/*
	Tag Styling
	https://github.com/JacksiroKe
	Set a logo/changing color for any part of specific tag with any Q2A theme while changing h1, header colors of the tag
	
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

return array(
	'ts_settings_saved' 	=> 'Tag Styling plugin settings saved',
	'ts_tag_page_styles' 	=> 'Custom css styles for tag page',
	'ts_save_changes'		=> 'Save Changes',
	'ts_clear_style'		=> 'Clear Style',
	'ts_view_tag'			=> 'View Tag',
	'ts_edit_tag'			=> 'Edit Tag',
	'ts_edit_icon'			=> 'Manage Icon',
	'ts_edit_tag_x'			=> 'Edit Tag: ^',
	'ts_tag_description'  	=> 'Tag Description (optional):',
	'ts_tag_customcss'  	=> 'Tag Customization (each css class/id on its on line):',
	'ts_tag_customcss_note' => 'Format: .qa-main-heading | background: #000; color: #fff;',
	'ts_tag_color'  		=> 'Tag Color (optional):',
	'ts_tag_color_note'  	=> 'Color format: either: #FF0000 or even: red',
	'ts_tag_icon'  			=> 'Tag Icon (optional):',
	'ts_confirm_clear'		=> 'Are you sure you want to clear this style? It will be lost and you have to create it again.'
);
