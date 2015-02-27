<?php

include_once('Dilmot_LifeCycle.php');
include_once('Dilmot_Api.php');

class Dilmot_Plugin extends Dilmot_LifeCycle {
    private $dilmot_server_data = array();

    function __construct() {
        $this->init_config();
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return array of option meta data.
     */
    public function getOptionMetaData() {
        //  http://plugin.michael-simpson.com/?page_id=31
        $optionsMetaDataInfo = $this->getOptionMetaDataInfo();

        $optionMetaData = array();
        foreach ($optionsMetaDataInfo as $aOptionKey => $aOptionInfo) {
            if ($aOptionInfo['values']) {
                $aOptionMeta = array_merge(array($aOptionInfo['name']), $aOptionInfo['values']);
            } else {
                $aOptionMeta = array($aOptionInfo['name']);
            }
            
            $optionMetaData[$aOptionKey] = $aOptionMeta;
        }
        return $optionMetaData;
    }

    public function getOptionMetaDataInfo() {
        $aCategoriesObjs = get_categories('hide_empty=0');
        $aCategories = Array();
        foreach($aCategoriesObjs as $category) {
            $aCategories[$category->term_id] = $category->cat_name;
        }

        $aPagesObjs = get_pages();
        $aPages = Array();
        foreach($aPagesObjs as $page) {
            $aPages[$page->ID] = $page->post_title;
        }

        $createNewPageLink = '<a href="/wp-admin/post-new.php?post_type=page">' . __("create","dilmot") . '</a> ' . __("a new page","dilmot");
        $createNewCategoryLink = '(<a href="/wp-admin/edit-tags.php?taxonomy=category">' . __("create","dilmot") . '</a> ' . __("a new category","dilmot") . ')';

        return array(
            /*
            '_version' => array(
                'name' => 'Installed Version'
                ), // Leave this one commented-out. Uncomment to test upgrades.
            */
            'DilmotAccount' => array(
                'name' => __('Dilmot account', 'dilmot'),
                'required' => true,
                'prefixAddon' => $this->getDilmotProtocol() . "://",
                'postfixAddon' => "." . $this->getDilmotUrl(),
                ),
            'StreamsCategory' => array(
                'name' => __('Streams category', 'dilmot'),
                'values' => $aCategories,
                'hint' => $createNewCategoryLink 
                ),
/*
            'FutureStreams' => array(
                'name' => __('Future streams page', 'my-awesome-plugin'),
                'values' => $aPages,
                'hint' => __("In this page future and active streams will be published", 'my-awesome-plugin') . " (" . $createNewPageLink . ")"
                ),
            'ClosedStreams' => array(
                'name' => __('Closed streams page', 'my-awesome-plugin'),
                'values' => $aPages,
                'hint' => __("In this page closed streams will be published", 'my-awesome-plugin') . " (" . $createNewPageLink . ")"
                ),
*/
        );
    }


//    protected function getOptionValueI18nString($optionValue) {
//        $i18nValue = parent::getOptionValueI18nString($optionValue);
//        return $i18nValue;
//    }

    protected function initOptions() {
        $options = $this->getOptionMetaData();
        if (!empty($options)) {
            foreach ($options as $key => $arr) {
                if (is_array($arr) && count($arr > 1)) {
                    $this->addOption($key, $arr[1]);
                }
            }
        }

        // generate new api key and reset access token
        $api_key = wp_generate_password(15, false);
        $this->addOption('ApiKey', $api_key);
        $this->addOption('LastAccessToken', 0);
    }

    public function getPluginDisplayName() {
        return 'Dilmot';
    }

    protected function getMainPluginFileName() {
        return 'dilmot.php';
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Called by install() to create any database tables if needed.
     * Best Practice:
     * (1) Prefix all table names with $wpdb->prefix
     * (2) make table names lower case only
     * @return void
     */
    protected function installDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("CREATE TABLE IF NOT EXISTS `$tableName` (
        //            `id` INTEGER NOT NULL");
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
        //        global $wpdb;
        //        $tableName = $this->prefixTableName('mytable');
        //        $wpdb->query("DROP TABLE IF EXISTS `$tableName`");
    }


    /**
     * Perform actions when upgrading from version X to version Y
     * See: http://plugin.michael-simpson.com/?page_id=35
     * @return void
     */
    public function upgrade() {
    }


    public function api_plugin_parse_request($wp) {
        // only process requests with "my-plugin=ajax-handler"
        if (array_key_exists('dilmot-plugin-api', $wp->query_vars)) {

            $api = new Dilmot_api($this);
            $api->authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

            $entityBody = file_get_contents('php://input');
            $bodyArr = json_decode($entityBody, true);

            $errMsg = null;

            // check that we could decode the json
            if ($bodyArr === null) {
                $errMsg = "Failed to decode json";
                $jsonErr = json_last_error_msg();
                if ($jsonErr !== null) {
                    $errMsg .= " ($jsonErr)";
                }

                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                echo 'Message: ' . $errMsg;
                exit;
            }

            // check required params
            $required_params = array('action', 'stream_id', 'data');
            $missingParams = array_diff($required_params, array_keys($bodyArr));
            if (!empty($missingParams)) {
                $errMsg = 'Missing required parameters ' . implode(',', $missingParams);
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                echo 'Message: ' . $errMsg;
                exit;
            }

            $api->execute($bodyArr['action'], $bodyArr['stream_id'], $bodyArr['data']);
        }
    }
    function api_plugin_query_vars($vars) {
        $vars[] = 'dilmot-plugin-api';
        return $vars;
    }

    public function addActionsAndFilters() {
        // Add options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        add_action('admin_menu', array(&$this, 'addSettingsSubMenuPage'));

        // Add parse request hooks for api
        add_action( 'parse_request', array(&$this, 'api_plugin_parse_request') );
        add_action( 'wp', array(&$this, 'api_plugin_parse_request') );
        add_filter('query_vars', array(&$this, 'api_plugin_query_vars') );


        // Example adding a script & style just for the options administration page
        // http://plugin.michael-simpson.com/?page_id=47
        //        if (strpos($_SERVER['REQUEST_URI'], $this->getSettingsSlug()) !== false) {
        //            wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));
        //            wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        }


        // Add Actions & Filters
        // http://plugin.michael-simpson.com/?page_id=37


        // Adding scripts & styles to all pages
        // Examples:
        //        wp_enqueue_script('jquery');
        //        wp_enqueue_style('my-style', plugins_url('/css/my-style.css', __FILE__));
        //        wp_enqueue_script('my-script', plugins_url('/js/my-script.js', __FILE__));


        // Register short codes
        // http://plugin.michael-simpson.com/?page_id=39
        include_once('Dilmot_StreamShortCodeLoader.php');
        $sc = new Dilmot_StreamShortCode();
        $sc->register('dilmot-stream');

        // Register AJAX hooks
        // http://plugin.michael-simpson.com/?page_id=41
        add_action('wp_ajax_reset_dilmot_api_key', array(&$this, 'ajax_reset_dilmot_api_key'));
    }

    public function plugin_url($file = '') {
        return plugins_url($file, __FILE__);
    }

    public function getDilmotUrl() {
        return $this->dilmot_server_data['dilmot_url'];
    }
    public function getDilmotProtocol() {
        return $this->dilmot_server_data['dilmot_protocol'];
    }
    public function getAccountUrl($account) {
        return $this->getDilmotProtocol() . "://" .  $account . "." . $this->getDilmotUrl();
    }

    private function init_config() {
        // init configuration
        $ini_default_array = parse_ini_file(__DIR__ . "/config.default.ini");

        if (file_exists(__DIR__ . "/config.ini")) {
            $ini_array = parse_ini_file(__DIR__ . "/config.ini");
        }

        foreach ($ini_default_array as $key => $value) {
            $this->dilmot_server_data[$key] = $value;

            // if we overrided this value in config.ini, set this value
            if (isset($ini_array) && array_key_exists($key,$ini_array)) {
                $new_val = trim($ini_array[$key]);
                if (!empty($new_val)) {
                    $this->dilmot_server_data[$key] = $new_val;                    
                }
            }
        }
    }

    public function get_single_custom_value($key) {
        $val_arr = get_post_custom_values($key);
        if (!is_array($val_arr) || count($val_arr) != 1) {
            return null;
        } else {
            return $val_arr[0];
        }
    }

    public function ajax_reset_dilmot_api_key() {
        // generate new api key and reset access token
        $api_key = wp_generate_password(15, false);
        $this->updateOption('ApiKey', $api_key);
        $this->updateOption('LastAccessToken', 0);

        // Don't let IE cache this request
        header("Pragma: no-cache");
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
     
        header("Content-type: text/plain");
     
        echo $api_key;
        die();
    }    
}
   