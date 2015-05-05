<?php
include_once('Dilmot_ShortCodeLoader.php');
 
class Dilmot_StreamShortCode extends Dilmot_ShortCodeLoader {
    static $addedAlready = false;

    /**
     * @param  $atts shortcode inputs
     * @return string shortcode content
     */
    public function handleShortcode($atts) {
        $templateData = array();

        $is_single_post = is_single();

        // we allow only one shortcode per post.
        if ($is_single_post && self::$addedAlready) {
            $templateData['errorMsg'] = (__('Dilmot plugin shortcode should be included only once in each post', 'dilmot'));
            return $this->render_template('error.php', $templateData);

        } else {
        	self::$addedAlready = true;
        }

        $shortcode_params = shortcode_atts( array(
            'id' => null,
            'account' => null
        ), $atts );

        $aPlugin = new Dilmot_Plugin();
        $stream_id = $aPlugin->get_single_custom_value('stream_id');
        
        if ($stream_id === null) {
            $templateData['errorMsg'] = (__('Error in stream-id configuration (101)', 'dilmot'));
            return $this->render_template('error.php', $templateData);
        } elseif ($stream_id != $shortcode_params['id']) {
            $templateData['errorMsg'] = (__('Error in stream-id configuration (102)', 'dilmot'));
            return $this->render_template('error.php', $templateData);
        }

        $account = $aPlugin->getOption('DilmotAccount');
        if ($account == '') {
            $templateData['errorMsg'] = (__('You should set dilmot account in plugin settings page', 'dilmot'));
            return $this->render_template('error.php', $templateData);
        } elseif ($account != $shortcode_params['account']) {
            $templateData['errorMsg'] = (__('Error in account configuration (202)', 'dilmot'));
            return $this->render_template('error.php', $templateData);
        }

        $stream_status = $aPlugin->get_single_custom_value('stream_status');
        $templateData['plugin'] = $aPlugin;
        $templateData['stream_id'] = $stream_id;
        $templateData['pusher_key'] = $aPlugin->get_single_custom_value('pusher_key');
        $templateData['channel_name'] = $aPlugin->get_single_custom_value('channel_name');
        $templateData['stream_slug'] = $aPlugin->get_single_custom_value('stream_slug');
        $templateData['accout_url'] = $aPlugin->getAccountUrl($account);
        $templateData['stream_info_box_html'] = $aPlugin->get_single_custom_value('stream_info_box_html');

        $templateData['stream_image'] = $aPlugin->get_single_custom_value('image');
        $templateData['stream_image_thumb'] = $aPlugin->get_single_custom_value('image_thumb');

        $isPrivateStream = $aPlugin->get_single_custom_value('private_stream');
        if ($isPrivateStream) {
            $templateData['private_stream_pass'] = $aPlugin->get_single_custom_value('private_stream_pass');
        } else {
            $templateData['private_stream_pass'] = '';
        }

        // render stream header
        $custom_stream_header = $aPlugin->get_single_custom_value('custom_stream_header');
        $stream_header_file = "stream_header/$custom_stream_header.php";
        if (!file_exists(dirname(__FILE__)."/templates/$stream_header_file")) {
            error_log("Stream header file '$stream_header_file' does not exist");
            $stream_header_file = "stream_header/vertical_image.php";
        }
        $templateData['stream_header'] = $this->render_template($stream_header_file, $templateData);

        $this->register_stylesheets_and_scripts($aPlugin, $is_single_post);

        // if not single post, we render stream info + 'read more' link
        if (!$is_single_post) {
            return $this->render_template('stream_info.php', $templateData);
        }
        
        if ($stream_status === null) {
            $templateData['errorMsg'] = (__('Could not determine stream status', 'dilmot'));
            return $this->render_template('error.php', $templateData);
        } elseif ($stream_status == 'closed') {
            $templateData['stream_html'] = $aPlugin->get_single_custom_value('stream_html');
            return $this->render_template('closed_stream.php', $templateData);    
        } else {
            return $this->render_template('active_stream.php', $templateData);
        }
    }

    private function render_template($templateName, $templateData) {
        ob_start();
        include sprintf("%s/templates/$templateName", dirname(__FILE__));
        $html = ob_get_clean();
        return $html;
    }

    private function register_stylesheets_and_scripts($aPlugin, $is_single_post) {
        wp_enqueue_style( 'bootstrap.min', $aPlugin->plugin_url('/bootstrap/css/bootstrap.min.css'));
        wp_enqueue_script( 'bootstrap.min', $aPlugin->plugin_url('/bootstrap/js/bootstrap.min.js'), array( 'jquery' ));

        wp_enqueue_style( 'dilmot', $aPlugin->plugin_url('/css/dilmot.css'));

        if ($is_single_post) {
            wp_enqueue_style( 'bubbles', $aPlugin->plugin_url('/css/bubbles.css'));
            
            wp_enqueue_style( 'font-awesome' , $aPlugin->plugin_url('/css/font-awesome.css'));
            wp_enqueue_style( 'social-share-button' , $aPlugin->plugin_url('/css/social-share-button.css'));

            wp_enqueue_script( 'dilmot', $aPlugin->plugin_url('/js/dilmot.js'), array( 'jquery' ));
            wp_enqueue_script( 'publication_service', $aPlugin->plugin_url('/js/publication_service.js'));
            wp_enqueue_script( 'pusher', $aPlugin->plugin_url('/js/pusher.min.js'));
        }
    }
}