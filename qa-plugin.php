<?php

/*
	Plugin Name: Tag Styling
	Plugin URI: https://github.com/JacksiroKe
	Plugin Description: customize a tag as you wish
	Plugin Version: 0.1
	Plugin Date: 2020-05-20
	Plugin Author: JacksiroKe
	Plugin Author URI: https://github.com/JacksiroKe
	Plugin License: GPLv3
	Plugin Minimum Question2Answer Version: 1.6
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

	qa_register_plugin_overrides('tag-styling-overrides.php');
	qa_register_plugin_phrases('langs/ts-lang-*.php', 'ts_lang');
	qa_register_plugin_layer('tag-styling-layer.php', 'Tag Styling Layer');
	qa_register_plugin_module('page', 'tag-styling.php', 'tag_styling', 'Tag Styling');

	
	function ts_sub_navigation($request, $tag)
	{		
        $navigation = array();
        if (qa_is_logged_in()) {
            $navigation['view'] = array(	
                'label' => qa_lang('ts_lang/ts_view_tag'),
                'url' => qa_path_html('tag/' . $tag),
                'selected' => ($request == '' ) ? 'selected' : '',
            );
            $navigation['edit'] = array(	
                'label' => qa_lang('ts_lang/ts_edit_tag'),
                'url' => qa_path_html('tagstyle/' . $tag ),
                'selected' => ($request == 'tagstyle' ) ? 'selected' : '',
            );
		}
		
		return $navigation;
	}

	function ts_db_tag_style_selectspec($wordid)
	{
		return array(
			'columns' => array('tagstyleid', 'content', 'format', 'customcss', 'embedads', 'iconblobid', 'iconwidth', 'iconheight'),
			'source' => '^tagstyles WHERE wordid=#',
			'arguments' => array($wordid),
			'single' => true,
		);
	}

	function ts_create_tag_style($wordid, $userid, $content, $format, $customcss, $embedads, $iconblobid, $iconwidth, $iconheight )
	{
		$cookieid = isset($userid) ? qa_cookie_get() : qa_cookie_get_create();
		$ip = qa_remote_ip_address();

		qa_db_query_sub(
			'INSERT INTO ^tagstyles (wordid, userid, cookieid, createip, content, format, customcss, embedads, iconblobid, iconwidth, iconheight, created) ' .
			'VALUES (#, #, UNHEX($), $, $, $, $, $, $, $, $, NOW())',
			$wordid, $userid, $cookieid, bin2hex(@inet_pton($ip)), $content, $format, $customcss, $embedads, $iconblobid, $iconwidth, $iconheight
		);

	}

	function ts_update_tag_style($tagstyleid, $userid, $content, $format, $customcss, $embedads, $iconblobid, $iconwidth, $iconheight )
	{
		$cookieid = isset($userid) ? qa_cookie_get() : qa_cookie_get_create();
		$ip = qa_remote_ip_address();
		
		qa_db_query_sub(
			'UPDATE ^tagstyles SET lastuser=#, cookieid=#, createip=#, content=$, format=$, customcss=$, embedads=$, iconblobid=$, iconwidth=$, iconheight=$, updated=NOW() WHERE tagstyleid=#',
			$userid, $cookieid, bin2hex(@inet_pton($ip)), $content, $format, $customcss, $embedads, $iconblobid, $iconwidth, $iconheight, $tagstyleid
		);

	}

	function ts_delete_tag_style($tagstyleid)
	{		
		qa_db_query_sub(
			'DELETE FROM ^tagstyles WHERE tagstyleid=#',
			$tagstyleid
		);
	}

	function ts_db_user_set($tagstyleid, $fields, $value = null)
	{
		if (!is_array($fields)) {
			$fields = array(
				$fields => $value,
			);
		}

		$sql = 'UPDATE ^tagstyles SET ';
		foreach ($fields as $field => $fieldValue) {
			$sql .= qa_db_escape_string($field) . ' = $, ';
		}
		$sql = substr($sql, 0, -2) . ' WHERE tagstyleid = $';

		$params = array_values($fields);
		$params[] = $tagstyleid;

		qa_db_query_sub_params($sql, $params);
	}
