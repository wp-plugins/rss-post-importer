<?php

/**
 * The class that handles the admin screen
 *
 * @author saurabhshukla
 */
class rssPIAdmin {

        /**
         * Whether the API key is valid
         * 
         * @var boolean
         */
        var $is_key_valid;

        /**
         * The options
         * 
         * @var array 
         */
        var $options;

        /**
         * Aprompt for invalid/absent API keys
         * @var string
         */
        var $key_prompt;

        /**
         *  Start
         * 
         * @global object $rss_post_importer
         */
        public function __construct() {

                $this->load_options();

                // add a key prompt
                $this->key_prompt = __('You need a <a href="http://www.feedsapi.com/?utm_source=rsspi-full-rss-key-here" target="_blank">Full Text RSS Key</a> to activate this section, please <a href="http://www.feedsapi.com/?utm_source=rsspi-full-rss-key-here" target="_blank">get one and try it free</a> for the next 14 days to see how it goes.', 'rss_pi');

                // initialise logging
                $this->log = new rssPILog();
                $this->log->init();

                // load the form processor
                $this->processor = new rssPIAdminProcessor();
        }
        
        private function load_options(){
                global $rss_post_importer;

                // add options
                $this->options = $rss_post_importer->options;
                
                // check if key is valid
                $this->is_key_valid = $rss_post_importer->is_valid_key($this->options['settings']['feeds_api_key']);
        }

        /**
         * Initialise and hook all actions
         */
        public function init() {

                // add to admin menu
                add_action('admin_menu', array($this, 'admin_menu'));

                // load scripts and styles we need
                add_action('admin_enqueue_scripts', array($this, 'enqueue'));

                // the ajax for adding new feeds (table rows)
                add_action('wp_ajax_rss_pi_add_row', array($this, 'add_row'));

                // disable the feed author dropdown for invalid/absent API keys
                add_filter('wp_dropdown_users', array($this, 'disable_user_dropdown'));
        }

        /**
         * Add to admin menu
         */
        function admin_menu() {

                add_options_page('Rss Post Importer', 'Rss Post Importer', 'manage_options', 'rss_pi', array($this, 'screen'));
        }

        /**
         * Enqueue our admin css and js
         * 
         * @param string $hook The current screens hook
         * @return null
         */
        public function enqueue($hook) {

                // don't load if it isn't our screen
                if ($hook != 'settings_page_rss_pi') {
                        return;
                }

                // register scripts & styles
                wp_enqueue_script('rss-pi', RSS_PI_URL . 'app/assets/js/main.js', array('jquery'), RSS_PI_VERSION);
                wp_enqueue_style('rss-pi', RSS_PI_URL . 'app/assets/css/style.css', array(), RSS_PI_VERSION);

                // localise ajaxuel for use
                $localise_args = array(
                    'ajaxurl' => admin_url('admin-ajax.php')
                );
                wp_localize_script('rss-pi', 'rss_pi', $localise_args);
        }

        /**
         * Display the screen/ui
         */
        function screen() {

                // load the form processor first
                $this->processor->process();
                // it'll process any submitted form data
                
                // reload the options just in case
                $this->load_options();
                
                // include the template for the ui
                include( RSS_PI_PATH . 'app/templates/admin-ui.php');
        }

        /**
         * Display errors
         * 
         * @param string $error The error message
         * @param boolean $inline Whether the error is inline or shown like regular wp errors
         */
        function key_error($error, $inline = false) {

                $class = ($inline) ? 'rss-pi-error' : 'error';

                echo '<div class="' . $class . '"><p>' . $error . '</p></div>';
        }

        /**
         * Add a new row for a new feed
         */
        function add_row() {

                include( RSS_PI_PATH . 'app/templates/feed-table-row.php');
                die();
        }
        
        /**
         * Disable the user dropdwon for each feed
         * 
         * @param string $output The html of the select dropdown
         * @return string
         */
        function disable_user_dropdown($output) {
                
                // if we have a valid key we don't need to disable anything
                if ($this->is_key_valid) {
                        return $output;
                }
                
                // check if this is the feed dropdown (and not any other)
                preg_match('/rss-pi-specific-feed-author/i', $output, $matched);
                
                // this is not our dropdown, no need to disable
                if (empty($matched)) {
                        return $output;
                }
                
                // otherwise just disable the dropdown
                return str_replace('<select ', '<select disabled="disabled" ', $output);
        }

}
