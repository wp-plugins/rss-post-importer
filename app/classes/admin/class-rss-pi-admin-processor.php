<?php



/**

 * Processes the admin screen form submissions

 *

 * @author Saurabh Shukla <saurabh@yapapaya.com>

 */

class rssPIAdminProcessor {



        /**

         * If we have a valid api key

         * 

         * @var boolean

         */

        var $is_key_valid;



        /**

         * Process the form result

         * 

         * @return null

         */

        function process() {



                // if there's nothing for processing or invalid data, bail

                if (!isset($_POST['info_update']) || !wp_verify_nonce($_POST['rss_pi_nonce'], 'settings_page')) {

                        return;

                }



                // Get ids of feed-rows

                $ids = explode(",", $_POST['ids']);



                // formulate the settings array

                $settings = $this->process_settings();



                // update cron settings

                $this->update_cron($settings['frequency']);



                // formulate the feeds array

                $feeds = $this->process_feeds($ids);				
				

				// save and reload the options

                $feeds = $this->import_csv($feeds);
				

                // save and reload the options

                $this->save_reload_options($settings, $feeds);



                // display a success message

                ?>

                <div id="message" class="updated">

                        <p><strong><?php _e('Settings saved.', 'rss_pi') ?></strong></p>

                </div>

                <?php

                // check if we need to and import feeds

                $this->import();

        }

		/**

         * Import CSV function to import CSV file data into database
		 
         * @return array

         */

        private function import_csv($feeds) {
			
			if(is_uploaded_file($_FILES['import_csv']['tmp_name'])) {
					$file = $_FILES['import_csv']['tmp_name'];
					$fcount = file($file);
					$linescount = count($fcount)-1;
					$file_handle = fopen($file,"r");
					$t=0;
					$titlearray = array();
					while($csv_line = fgetcsv($file_handle,1024)) {
						if($t <> 0)
						{
						
							for ($i = 0, $j = count($csv_line); $i < $j; $i++) {
								if($i == 0)
									$importdata['feeds'][$t-1]['id'] = uniqid("54d4c".$t);
								
								$importdata['feeds'][$t-1][$titlearray[$i]] = $csv_line[$i];
							
							}
						}
						else{
							for ($i = 0, $j = count($csv_line); $i < $j; $i++) {
								$titlearray[] = $csv_line[$i];
							}
						}
					$t++;
					}
					fclose($file_handle) or die("can't close file");
					
					
					if(!empty($importdata['feeds']))
					{
						for($r=0;$r<count($importdata['feeds']);$r++)
						{
								$importdata['feeds'][$r]['category_id'] = array(1);
								$importdata['feeds'][$r]['tags_id'] = "";
								$importdata['feeds'][$r]['strip_html'] = "false";
								
								array_push($feeds,$importdata['feeds'][$r]);
								
						}
					}
				}
				
				return $feeds;
		}

        /**

         * Process submitted data to formulate settings array

         * 

         * @global object $rss_post_importer

         * @return array

         */

        private function process_settings() {



                // Get selected settings for all imported posts

                $settings = array(

                    'frequency' => $_POST['frequency'],

                    'feeds_api_key' => $_POST['feeds_api_key'],

                    'post_template' => stripslashes_deep($_POST['post_template']),

                    'post_status' => $_POST['post_status'],

                    'author_id' => $_POST['author_id'],

                    'allow_comments' => $_POST['allow_comments'],

					'block_indexing' => $_POST['block_indexing'],

					'nofollow_outbound' => $_POST['nofollow_outbound'],

                    'enable_logging' => $_POST['enable_logging'],
                    
                    'import_images_locally' => $_POST['import_images_locally'],

                    'keywords' => array()

                );



                global $rss_post_importer;



                // check if submitted api key is valid

                $this->is_key_valid = $rss_post_importer->is_valid_key($settings['feeds_api_key']);



                // filter the settings and then send them back for saving

                return $this->filter($settings);

        }



        /**

         * Update the frequency of the import cron job

         * 

         * @param string $frequency

         */

        private function update_cron($frequency) {



                // If cron settings have changed

                if (wp_get_schedule('rss_pi_cron') != $frequency) {



                        // Reset cron

                        wp_clear_scheduled_hook('rss_pi_cron');

                        wp_schedule_event(time(), $frequency, 'rss_pi_cron');

                }

        }



        /**

         * Forms the feeds array from submitted data

         * 

         * @param array $ids feeds ids

         * @return array

         */

        private function process_feeds($ids) {



                $feeds = array();



                foreach ($ids as $id) {

                        if ($id) {

                                array_push($feeds, array(

                                    'id' => $id,

                                    'url' => strtolower($_POST[$id . '-url']),

                                    'name' => $_POST[$id . '-name'],

                                    'max_posts' => $_POST[$id . '-max_posts'],

                                    // different author ids depending on valid API keys

                                    'author_id' => $this->is_key_valid ? $_POST[$id . '-author_id'] : $_POST['author_id'],

                                    'category_id' => $_POST[$id . '-category_id'],

									'tags_id' => $_POST[$id . '-tags_id'],

                                    'strip_html' => $_POST[$id . '-strip_html']

                                ));

                        }

                }



                return $feeds;

        }



        /**

         * Update options and reload global options

         * 

         * @global type $rss_post_importer

         * @param array $settings

         * @param array $feeds

         */

        private function save_reload_options($settings, $feeds) {



                global $rss_post_importer;



                // existing options

                $options = $rss_post_importer->options;



                // new data

                $new_options = array(

                    'feeds' => $feeds,

                    'settings' => $settings,

                    'latest_import' => $options['latest_import'],

                    'imports' => $options['imports']

                );



                // update in db

                update_option('rss_pi_feeds', $new_options);



                // reload so that the new options are used henceforth

                $rss_post_importer->load_options();

        }



        /**

         * Import feeds

         * 

         * @return null

         */

        private function import() {



                // if we don't need to import anything, bail

                if ($_POST['save_to_db'] != 'true') {

                        return;

                }



                // initialise the engine and import

                $engine = new rssPIEngine();

                $imported = $engine->import_feed();

                ?>

                <div id="message" class="updated">

                        <p><strong><?php echo($imported); ?> <?php _e('new posts imported.', 'rss_pi') ?></strong></p>

                </div>

                <?php

        }



        /**

         * Filter settings for API key vs non-API key installs

         * 

         * @param array $settings

         * @return array

         */

        private function filter($settings) {

                

                // if the key is not fine

                if (!empty($settings['feeds_api_key']) && !$this->is_key_valid) {



                        // unset from settings

                        unset($settings['feeds_api_key']);

                        echo '<div class="error">

			        <p>' . __('Invalid API key!', 'rss_api') . '</p>

				</div>';

                }



                // if the key is valid

                if ($this->is_key_valid) {



                        // set up keywords (otherwise don't)

                        $keyword_str = $_POST['keyword_filter'];



                        $keywords = array();



                        if (!empty($keyword_str)) {

                                $keywords = explode(',', $keyword_str);

                        }

                        $settings['keywords'] = $keywords;

                }



                return $settings;

        }



}

