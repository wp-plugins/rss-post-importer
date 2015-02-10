<?php

/**

 * One class to rule them all

 * 

 * @author Saurabh Shukla <saurabh@yapapaya.com>

 */

class rssPostImporter {



        /**

         * A var to store the options in

         * @var array

         */

        public $options = array();



        /**

         * To initialise the admin and cron classes

         * 

         * @var object

         */

        private $admin, $cron;



        /**

         * Start

         */

        function __construct() {



                // populate the options first

                $this->load_options();



                // hook translations

                add_action('plugins_loaded', array($this, 'localize'));

                

                add_filter( 'plugin_action_links_' . RSS_PI_BASENAME, array($this, 'settings_link') );

        }



        /**

         * Load options from the db

         */

        public function load_options() {



                $default_settings = array(

                    'enable_logging' => false,

                    'feeds_api_key' => false,

                    'frequency' => 0,

                    'post_template' => "{\$content}\nSource: {\$feed_title}",

                    'post_status' => 'publish',

                    'author_id' => 1,

                    'allow_comments' => 'open',

					'block_indexing' => false,

					'nofollow_outbound' => true,

                    'keywords' => array(),

                    'import_images_locally' => false

                );



                $options = get_option('rss_pi_feeds', array());



                if (!isset($options['settings'])) {

                        $options['settings'] = array();

                }



                $options['settings'] = wp_parse_args($options['settings'], $default_settings);



                if (!array_key_exists('imports', $options)) {

                        $options['imports'] = 0;

                }



                $this->options = $options;

        }



        /**

         * Load translations

         */

        public function localize() {



                load_plugin_textdomain('rss_pi', false, RSS_PI_PATH . 'app/lang/');

        }



        /**

         * Initialise

         */

        public function init() {



                // initialise admin and cron

                $this->cron = new rssPICron();

                $this->cron->init();

                

                $this->admin = new rssPIAdmin();

                $this->admin->init();

				

				$this->front = new rssPIFront();

                $this->front->init();

        }



        /**

         * Check if a given API key is valid

         * 

         * @param string $key

         * @return boolean

         */

        public function is_valid_key($key) {



                if (empty($key)) {

                        return false;

                }



                $url = "http://www.feedsapi.org/fetch.php?key=$key&url=http://dummyurl.com";

                $content = file_get_contents($url);



                if (trim($content) == "A valid key must be supplied") {

                        return false;

                }



                return true;

        }

        

        /**

         * Adds a settings link

         * 

         * @param array $links EXisting links

         * @return type

         */

        public function settings_link($links) {

                $settings_link = array(

                    '<a href="' . admin_url('options-general.php?page=rss_pi') . '">Settings</a>',

                );

                return array_merge($settings_link, $links);

        }




}

