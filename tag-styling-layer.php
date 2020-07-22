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

require_once QA_INCLUDE_DIR . 'app/blobs.php';
require_once QA_INCLUDE_DIR . 'app/format.php';

class qa_html_theme_layer extends qa_html_theme_base 
{
    public $tag;
    public $tagstyle;
    public $question;

    function __construct($template, $content, $rooturl, $request)
    {
        global $qa_layers;
        qa_html_theme_base::qa_html_theme_base($template, $content, $rooturl, $request);
    }
  
    function doctype() 
    {	
        $request = qa_request_part(2);
        
        if ($this->template == 'tag')
        {
            $this->tag = qa_db_select_with_pending( qa_db_tag_word_selectspec(qa_request_part(1)));
            $this->tagstyle = qa_db_select_with_pending( ts_db_tag_style_selectspec($this->tag['wordid']));
            
            if (qa_get_logged_in_level() >= QA_USER_LEVEL_ADMIN || !qa_user_maximum_permit_error('permit_moderate') ||
                !qa_user_maximum_permit_error('permit_hide_show') || !qa_user_maximum_permit_error('permit_delete_hidden')
            ) {
                $this->content['navigation']['sub'] = ts_sub_navigation('', qa_request_part(1));

                if (qa_request_part(0) == 'tagstyle') $this->template = 'ask';
            }
        }
        else if ($this->template == 'question')
        {
            $questionid = qa_request_part(0);
            $userid = qa_get_logged_in_userid();
            $this->question = qa_db_select_with_pending( qa_db_full_post_selectspec($userid, $questionid) );
            $tags = explode(',', $this->question['tags']);
            $this->tag = qa_db_select_with_pending( qa_db_tag_word_selectspec( $tags[0] ));
            $this->tagstyle = qa_db_select_with_pending( ts_db_tag_style_selectspec($this->tag['wordid']));
        } 
        else qa_html_theme_base::doctype();
    }
    
    function head_css() {
        qa_html_theme_base::head_css();
        if ($this->template == 'tag' || $this->template == 'question')
        {           
            if ($this->tagstyle != null && strlen($this->tagstyle['customcss']))
            {
                $customcss = '';
                $csslines = preg_split('/\r\n|\r|\n/', $this->tagstyle['customcss']);
                foreach ($csslines as $cssline) {
                    $cssarr = explode('|', $cssline);
                    if (array_key_exists(1, $cssarr))
                        $customcss .= trim($cssarr[0]) . " {\n\t" . trim($cssarr[1]) . "\n}\n";
                }

                $this->output_array(array(
                    "<style>",                
                        $customcss,
                    "</style>",
                ));
            }
        }      	       		
    }
    
	public function logo()
	{ 
        if ($this->template == 'tag' || $this->template == 'question')
        {
            if ($this->tagstyle != null && strlen($this->tagstyle['iconblobid']) > 0)
            {
                $this->output(
                    '<div class="qa-logo">',
                    '<a href="' . qa_path_html('') . '" class="qa-logo-link" title="' . qa_html(qa_opt('site_title')) . '">' .
                    qa_get_avatar_blob_html($this->tagstyle['iconblobid'], $this->tagstyle['iconwidth'], $this->tagstyle['iconheight'], 49),
                    '</a>',
                    '</div>'
                );
            }
            else qa_html_theme_base::logo();
        }
        else qa_html_theme_base::logo();
	}

	public function q_list_and_form($q_list)
	{
        if ($this->template == 'tag')  
        {
            if (empty($q_list))
			return;

            $this->part_title($q_list);

            $iconhtml = '';
            if (strlen($this->tagstyle['iconblobid'])) 
                $iconhtml = '<span style="float: left; margin-right: 10px;">' . 
                    qa_get_avatar_blob_html($this->tagstyle['iconblobid'], $this->tagstyle['iconwidth'], $this->tagstyle['iconheight'], 100). '</span>';
            if ($this->tagstyle != null)
            {
                if (strlen($this->tagstyle['content'])) {
                    $this->output('<div class="qa-part-custom"',
                    strlen($this->tagstyle['iconblobid']) ? ' style="min-height: 150px;">' : '>',
                    $iconhtml,
                    $this->tagstyle['content'],
                    '</div>');
                }
            }

            if (!empty($q_list['form']))
                $this->output('<form ' . $q_list['form']['tags'] . '>');

            $this->q_list($q_list);

            if (!empty($q_list['form'])) {
                unset($q_list['form']['tags']);
                $this->q_list_form($q_list);
                $this->output('</form>');
            }

            $this->part_footer($q_list);
        }
        else qa_html_theme_base::q_list_and_form($q_list);
	}

}