<?php

/*
	Tag Styling
	https://github.com/JacksiroKe
	Set a logo/changing color for any part of specific tag with any Q2A theme while changing h1, header colors of the tag
	
*/

if ( !defined('QA_VERSION') )
{
	header('Location: ../../');
	exit;
	
}

class tag_styling
{
    private $directory;
    private $urltoroot;
    
    public function load_module($directory, $urltoroot)
    {
        $qa_directory = $directory;
        $qa_urltoroot = $urltoroot;
    }

    public function match_request( $request )
    {
        return strpos($request, 'tagstyle') !== false;
    }
    
    function option_default($option)
    {
        switch ($option) {
            case 'tag_styling_editor': return '';
        }
    }
    
    function init_queries( $tableslc )
    {
        $tbl1 = qa_db_add_table_prefix('tagstyles');
        if ( in_array($tbl1, $tableslc)) return null;

        return array(
            'CREATE TABLE IF NOT EXISTS ^tagstyles (
                `tagstyleid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `wordid` INT(10) UNSIGNED DEFAULT 0,
                `userid` INT(10) UNSIGNED DEFAULT 0,
                `lastuser` INT(10) UNSIGNED DEFAULT 0,
                `format` VARCHAR(20) CHARACTER SET ascii NOT NULL DEFAULT \'\',
                `color` VARCHAR(800) DEFAULT NULL,
                `content` VARCHAR(5000) DEFAULT NULL,
                `customcss` VARCHAR(5000) DEFAULT NULL,
                `embedads` VARCHAR(1000) DEFAULT NULL,
                `iconlink` VARCHAR(800) DEFAULT NULL,
                `iconblobid` BIGINT UNSIGNED,
                `iconwidth` SMALLINT UNSIGNED,
                `iconheight` SMALLINT UNSIGNED,
                `extra` VARCHAR(500) DEFAULT NULL,
                `cookieid` VARCHAR(10) DEFAULT NULL,
                `createip` VARCHAR(100) DEFAULT NULL,
                `created` DATETIME NOT NULL,
                `updated` DATETIME NOT NULL,
                PRIMARY KEY (`tagstyleid`),
                CONSTRAINT `tagstyle_ibfk_1` FOREIGN KEY (`wordid`) REFERENCES ^words (`wordid`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8',
        );
    }
    
    public function process_request($request)
    {
        require_once QA_INCLUDE_DIR . 'db/selects.php';
        require_once QA_INCLUDE_DIR . 'app/format.php';
        require_once QA_INCLUDE_DIR . 'app/updates.php';
        require_once QA_INCLUDE_DIR . 'util/string.php';
        
        $tag = qa_request_part(1); // picked up from qa-page.php
        $start = qa_get_start();
        $userid = qa_get_logged_in_userid();
        
        if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN || !qa_user_maximum_permit_error('permit_moderate') ||
            !qa_user_maximum_permit_error('permit_hide_show') || !qa_user_maximum_permit_error('permit_delete_hidden')
        ) {
            $in = array();
            
            // Find the questions with this tag
            
            if (!strlen($tag)) qa_redirect('tags');
            
            $tagword = qa_db_select_with_pending( qa_db_tag_word_selectspec($tag));
            $tagstyle = qa_db_select_with_pending( ts_db_tag_style_selectspec($tagword['wordid']));
            
            $tagstyleid = $tagstyle['tagstyleid'];
            // Prepare content for theme
            
            $qa_content = qa_content_prepare(true);
            
            $qa_content['title'] = qa_lang_html_sub('ts_lang/ts_edit_tag_x', qa_html($tag));
            $qa_content['navigation']['sub'] = ts_sub_navigation('tagstyle', $tag);
            
            if (isset($userid) && isset($tagword)) {
                $favoritemap = qa_get_favorite_non_qs_map();
                $favorite = @$favoritemap['tag'][qa_strtolower($tagword['word'])];
            
                $qa_content['favorite'] = qa_favorite_form(QA_ENTITY_TAG, $tagword['wordid'], $favorite,
                    qa_lang_sub($favorite ? 'main/remove_x_favorites' : 'main/add_tag_x_favorites', $tagword['word']));
            }
            

            if (qa_clicked('doedit')) {            
                qa_get_post_content('editor', 'content', $in['editor'], $in['content'], $in['format'], $in['text']);
                $cookieid = isset($userid) ? qa_cookie_get() : qa_cookie_get_create();
                
                if (!qa_check_form_security_code('edit-tag', qa_post_text('code')))
                    $pageerror = qa_lang_html('misc/form_security_again');

                else { 
                    $in['customcss'] = strip_tags(qa_post_text('customcss'));
                    $in['iconblobid'] = $tagstyle['iconblobid'];
                    $in['iconwidth'] = $tagstyle['iconwidth'];
                    $in['iconheight'] = $tagstyle['iconheight'];

                    if (is_array(@$_FILES['file'])) {
                        $iconfileerror = $_FILES['file']['error'];

                        if ($iconfileerror === 1) {
                            $errors['avatar_default_show'] = qa_lang('main/file_upload_limit_exceeded');
                        } elseif ($iconfileerror === 0 && $_FILES['file']['size'] > 0) {
                            require_once QA_INCLUDE_DIR . 'util/image.php';
        
                            $toobig = qa_image_file_too_big($_FILES['file']['tmp_name'], qa_opt('avatar_store_size'));
        
                            if ($toobig) {
                                $errors['avatar_default_show'] = qa_lang_sub('main/image_too_big_x_pc', (int)($toobig * 100));
                            } else {
                                $imagedata = qa_image_constrain_data(file_get_contents($_FILES['file']['tmp_name']), $width, $height, qa_opt('avatar_store_size'));
        
                                if (isset($imagedata)) {
                                    require_once QA_INCLUDE_DIR . 'app/blobs.php';
        
                                    $newblobid = qa_create_blob($imagedata, 'jpeg');

                                    $in['iconblobid'] = $newblobid;
                                    $in['iconwidth'] = $width;
                                    $in['iconheight'] = $height;
        
                                    if (strlen($tagstyle['iconblobid']))
                                        qa_delete_blob($tagstyle['iconblobid']);
                                } else {
                                    $errors['avatar_default_show'] = qa_lang_sub('main/image_not_read', implode(', ', qa_gd_image_formats()));
                                }
                            }
                        }
                    }

                    if ($tagstyle == null) ts_create_tag_style($tagword['wordid'], $userid, $in['content'], $in['format'], $in['customcss'], '', $in['iconblobid'], $in['iconwidth'], $in['iconheight']);
                    else ts_update_tag_style($tagstyleid, $userid, $in['content'], $in['format'], $in['customcss'], '', $in['iconblobid'], $in['iconwidth'], $in['iconheight']);
                    qa_redirect('tag/' . $tag);
                }
            }
            
            if (qa_clicked('doclear')) {
                if ($tagstyle != null) ts_delete_tag_style($tagstyleid);
                qa_redirect('tag/' . $tag);
            }
            
            $editorname = isset($in['editor']) ? $in['editor'] : qa_opt('editor_for_qs');
            $editor = qa_load_editor(@$in['content'], @$in['format'], $editorname);
            
            $field = qa_editor_load_field($editor, $qa_content, @$tagstyle['content'], @$tagstyle['format'], 'content', 12, false);
            $field['label'] = qa_lang('ts_lang/ts_tag_description');
            $field['value'] = qa_html(@$tagstyle['content']);
            $field['style'] = 'tall';
            $field['error'] = qa_html(@$errors['content']);
            
            $iconoptions = array();
            $iconoptions[''] = qa_lang_html('users/avatar_none');
            $iconvalue = $iconoptions[''];

            if (strlen($tagstyle['iconblobid']))
            {
                $iconoptions['uploaded'] = qa_get_avatar_blob_html($tagstyle['iconblobid'], $tagstyle['iconwidth'], $tagstyle['iconheight'], 40) . 
                ' <input name="file" type="file">';
                $iconvalue = $iconoptions['uploaded'];
            }
            else $iconoptions['uploaded'] = '<input name="file" type="file">';

            $qa_content['form'] = array(
                'tags' => 'enctype="multipart/form-data" method="post" action="' . qa_self_html() . '"',
                'style' => 'wide',

                'fields' => array(

                    'icon' => array(
                        'type' => 'select-radio',
                        'label' => qa_lang_html('ts_lang/ts_tag_icon'),
                        'tags' => 'name="icon"',
                        'options' => $iconoptions,
                        'value' => $iconvalue,
                        'error' => qa_html(@$errors['icon']),
                    ),
                        
                    'customcss' => array(
                        'type' => 'textarea',
                        'style' => 'tall',
                        'rows' => 10,
                        'label' => qa_lang_html('ts_lang/ts_tag_customcss'),
                        'note' => qa_lang_html('ts_lang/ts_tag_customcss_note'),
                        'tags' => 'name="customcss"',
                        'value' => qa_html($tagstyle['customcss']),
                        'error' => qa_html(@$errors['customcss']),
                    ),
                    
                    'content' => $field,

                ),

                'buttons' => array(
                    'save' => array(
                        'tags' => 'name="doedit" onclick="qa_show_waiting_after(this, false); '.
                            (method_exists($editor, 'update_script') ? $editor->update_script('content') : '').'"',
                        'label' => qa_lang_html('ts_lang/ts_save_changes'),
                    ),

                    'cancel' => array(
                        'tags' => 'name="doclear" onclick="return confirm(' . qa_js(qa_lang_html('ts_lang/ts_confirm_clear')) . ');"',
                        'label' => qa_lang_html('ts_lang/ts_clear_style'),
                    ),
                ),

                'hidden' => array(
                    'editor' => qa_html($editorname),
                    'code' => qa_get_form_security_code('edit-tag'),
                ),
            );

            $qa_content['focusid'] = 'color';        
            return $qa_content;
        }
        else qa_redirect('tag/' . $tag);
    }

}