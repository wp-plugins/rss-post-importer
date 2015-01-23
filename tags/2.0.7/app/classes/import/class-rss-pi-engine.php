<?php

/**
 * Main import engine
 *
 * @author Saurabh Shukla <saurabh@yapapaya.com>
 */
class rssPIEngine {

        /**
         * The options
         * 
         * @var array
         */
        var $options = array();

        /**
         * Start the engine
         * 
         * @global type $rss_post_importer
         */
        public function __construct() {

                global $rss_post_importer;

                // load options
                $this->options = $rss_post_importer->options;
        }

        /**
         * Import feeds
         * 
         * @return int
         */
        public function import_feed() {

                $post_count = 0;

                // filter cache lifetime
                add_filter('wp_feed_cache_transient_lifetime', array($this, 'frequency'));

                foreach ($this->options['feeds'] as $f) {

                        // prepare and import each feed
                        $items = $this->prepare_import($f);
                        $post_count += count($items);
                }

                // reformulate import count
                $imports = intval($this->options['imports']) + $post_count;

                // update options
                update_option('rss_pi_feeds', array(
                    'feeds' => $this->options['feeds'],
                    'settings' => $this->options['settings'],
                    'latest_import' => date("Y-m-d H:i:s"),
                    'imports' => $imports
                ));

                global $rss_post_importer;
                // reload options
                $rss_post_importer->load_options();

                remove_filter('wp_feed_cache_transient_lifetime', array($this, 'frequency'));

                // log this
                rssPILog::log($post_count);

                return $post_count;
        }

        /**
         * Dummy function for filtering because we can't use anon ones yet
         * @return string
         */
        public function frequency() {
                return $this->options['settings']['frequency'];
        }

        /**
         * Prepares arguments and imports
         * 
         * @param array $f feed array
         * @return array
         */
        private function prepare_import($f) {
                $args = array(
                    'feed_title' => $f['name'],
                    'max_posts' => $f['max_posts'],
                    'author_id' => $f['author_id'],
                    'category_id' => $f['category_id'],
					'tags_id' => $f['tags_id'],
                    'strip_html' => $f['strip_html'],
                    'save_to_db' => true
                );
                return $this->_import($f['url'], $args);
        }

        /**
         * Import feeds from url
         * 
         * @param string $url The remote feed url
         * @param array $args Arguments for the import
         * @return null|array
         */
        private function _import($url = '', $args = array()) {

                if (empty($url)) {
                        return;
                }

                $defaults = array(
                    'feed_title' => '',
                    'max_posts' => 5,
                    'author_id' => 1,
                    'category_id' => 0,
					'tags_id' => array(),
                    'strip_html' => true,
                    'save_to_db' => true
                );

                $args = wp_parse_args($args, $defaults);

                // include the default WP feed processing functions
                include_once( ABSPATH . WPINC . '/feed.php' );

                // get the right url for fetching (premium vs free)
                $url = $this->url($url);

                // fetch the feed
                $feed = fetch_feed($url);
				

                // save as posts
                $posts = $this->save($feed, $args);

                return $posts;
        }

        /**
         * Formulate the right url
         * 
         * @param string $url
         * @return string
         */
        private function url($url) {

                $key = $this->options['settings']["feeds_api_key"];

                //if api key has been saved by user and is not empty
                if (isset($key) && !empty($key)) {

                        $api_url = "http://176.58.108.28/makefulltextfeed.php?key=" . $key . "&url=" . $url;

                        return $api_url;
                }

                return $url;
        }

        /**
         * Save the feed
         * 
         * @param object $feed The feed object
         * @param array $args The arguments
         * @return boolean
         */
        private function save($feed, $args = array()) {
                if (is_wp_error($feed)) {
                        return false;
                }
                // filter the feed and get feed items
                $feed_items = $this->filter($feed, $args);

                // if we are saving
                if ($args['save_to_db']) {
                        // insert and return
                        $saved_posts = $this->insert($feed_items, $args);
                        return $saved_posts;
                }

                // otherwsie return the feed items
                return $feed_items;
        }

        /**
         * Filter the feed based on keywords
         * 
         * @param object $feed The feed object
         * @param array $args Arguments
         * @return array
         */
        private function filter($feed, $args) {

                // the count of keyword matched items
                $got = 0;

                // the current index of the items aray
                $index = 0;

                $filtered = array();

                // till we have as many as the posts needed
                while ($got < $args['max_posts']) {

                        // get only one item at the current index
                        $feed_item = $feed->get_items($index, 1);

                        // if this is empty, get out of the while
                        if (empty($feed_item)) {
                                break;
                        }
                        // else be in a forever loop
                        // get the content
                        $content = $feed_item[0]->get_content();

                        // test it against the keywords
                        $tested = $this->test($content);

                        // if this is good for us
                        if ($tested) {
                                $got++;

                                array_push($filtered, $feed_item[0]);
                        }
                        // shift the index
                        $index++;
                }

                return $filtered;
        }

        /**
         * Test a piece of content against keywords
         * 
         * @param string $content
         * @return boolean
         */
        function test($content) {
                $keywords = $this->options['settings']['keywords'];

                if (empty($keywords)) {
                        return true;
                }

                $match = false;

                // loop through keywords
                foreach ($keywords as $keyword) {

                        // if the keyword is not a regex, make it one
                        if (!$this->is_regex($keyword)) {
                                $keyword = '/' . $keyword . '/i';
                        }

                        // look for keyword in content
                        preg_match($keyword, $content, $tested);

                        // if it's there, we are good
                        if (!empty($tested)) {
                                $match = true;
                                // no need to test anymore
                                break;
                        }
                }


                return $match;
        }

        /**
         * Check if a string is regex
         * 
         * @param string $str The string to check
         * @return boolean
         */
        private function is_regex($str) {

                // check regex with a regex!
                $regex = "/^\/[\s\S]+\/$/";
                preg_match($regex, $str, $matched);
                return !empty($matched);
        }

        /**
         * Insert feed items as posts
         * 
         * @param array $items Fetched feed items
         * @param array $args arguments
         * @return array
         */
        private function insert($items, $args = array()) {
                $saved_posts = array();

                // Initialise the content parser
                $parser = new rssPIParser($this->options);

                // Featured Image setter
                $thumbnail = new rssPIFeaturedImage();

                foreach ($items as $item) {
                        if (!$this->post_exists($item->get_permalink())) {
								/* Code to convert tags id array to tag name array **/
								if(!empty($args['tags_id'])){
									foreach($args['tags_id'] as $tagid){
										$tag_name = get_tag($tagid); // <-- your tag ID
										$tags_name[] = $tag_name->name;
									}
								}else{
									$tags_name = array();
								}
								$parser->_parse($item, $args['feed_title'], $args['strip_html']);
                                $post = array(
                                    'post_title' => $item->get_title(),
                                    // parse the content
                                    'post_content' => $parser->_parse($item, $args['feed_title'], $args['strip_html']),
                                    'post_status' => $this->options['settings']['post_status'],
                                    'post_author' => $args['author_id'],
                                    'post_category' => array($args['category_id']),
									'tags_input' => $tags_name,
                                    'comment_status' => $this->options['settings']['allow_comments'],
                                    'post_date' => $item->get_date('Y-m-d H:i:s')
                                );
								
								$content = $post["post_content"];                                                              

							  // catch base url
							  if (preg_match('/src="\//i', $content)) {
								    preg_match('/href="(.+?)"/i', $content, $matches);
									$baseref = (is_array($matches) && !empty($matches)) ? $matches[1] : '';
										if (!empty($baseref)) {                                                                                             
											 $bc = parse_url($baseref);
											 $scheme = (empty($bc["scheme"])) ? "http" : $bc["scheme"];
											 $port = $bc["port"];
											 $host = $bc["host"];
											 if (!empty($host)) {
												$preurl = $scheme . ":" . $port . "//" . $host;
												$post["post_content"] = preg_replace('/(src="\/)/i', 'src="' . $preurl . "/", $content);
												}                                                                
									}
							  }
								
								
                                // insert as post
                                $post_id = $this->_insert($post, $item->get_permalink());

                                // set thumbnail
                                $thumbnail->_set($item, $post_id);

                                array_push($saved_posts, $post);
                        }
                }

                return $saved_posts;
        }

        /**
         * Check if a feed ite is alreday imported
         * 
         * @param string $permalink
         * @return boolean
         */
        private function post_exists($permalink) {

                // get all posts where the meta is stored
                $args = array(
                    'post_status' => 'any',
                    'meta_key' => 'rss_pi_source_url',
                    'meta_value' => esc_url($permalink)
                );

                $posts = get_posts($args);

                // Not already imported
                return(count($posts) > 0);
        }

        /**
         * Insert feed item as post
         * 
         * @param array $post Post array
         * @param string $url source url meta
         * @return int
         */
        private function _insert($post, $url) {

				if($post['post_category'][0] == ""){
					$post['post_category'] = array(1);
				}else{
					if(is_array($post['post_category'][0]))
						$post['post_category']= array_values($post['post_category'][0]);
					else
						$post['post_category']= array_values($post['post_category']);
				}
	
                $_post = apply_filters('pre_rss_pi_insert_post', $post);


                $post_id = wp_insert_post($_post);

                add_action('save_rss_pi_post', $post_id);

                add_post_meta($post_id, 'rss_pi_source_url', esc_url($url));

                return $post_id;
        }

}
