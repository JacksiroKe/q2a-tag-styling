<?php

/*
	Tag Styling
	https://github.com/JacksiroKe
	Set a icon/changing color for any part of specific tag with any Q2A theme while changing h1, header colors of the tag
	
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

function qa_tag_htmlx($tag, $microformats=false, $favorited=false)
{
	global $at_lang_list;
	
	$taghtml = qa_tag_html_base($tag, $microformats, $favorited);
	
	require_once QA_INCLUDE_DIR.'util/string.php';
	
	$taglc = qa_strtolower($tag);
	$at_lang_list[$taglc]=true;
	
	$anglepos = strpos($taghtml, '>');

	if ($anglepos!==false)
		$taghtml = substr_replace($taghtml, ' title=", TAG_DESC,'.$taglc.',"', $anglepos, 0);
	
	return $taghtml;
}

/**
 * Convert textual tag to HTML representation, linked to its tag page.
 *
 * @param string $tag  The tag.
 * @param bool $microdata  Whether to include microdata.
 * @param bool $favorited  Show the tag as favorited.
 * @return string  The tag HTML.
 */
function qa_tag_html($tag, $microdata = false, $favorited = false)
{
	$url = qa_path_html('tag/' . $tag);
	$attrs = $microdata ? ' rel="tag"' : '';
	$class = $favorited ? ' qa-tag-favorited' : '';
	$icon = '';
	$title = '';
	
	$tagword = qa_db_select_with_pending( qa_db_tag_word_selectspec($tag));
	$tagstyle = qa_db_select_with_pending( ts_db_tag_style_selectspec($tagword['wordid']));
	
	if ($tagstyle != null && isset($tagstyle['iconblobid'])) {
		$icon = qa_get_avatar_blob_html($tagstyle['iconblobid'], $tagstyle['iconwidth'], $tagstyle['iconheight'], 20) . ' ';
		$title = ' title="' . $tagstyle['content'] . '"';
	}

	return '<a href="' . $url . '"' . $attrs . ' class="qa-tag-link' . $class . '"'.$title.'>' . $icon . qa_html($tag) . '</a>';
}
