<?php

/**
 * The class that handles the front screen
 *
 * 
 */
class rssPIFront {

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
         * Initialise and hook all actions
         */
        public function init() {
                // add noidex to front
				add_action('wp_head', array($this, 'rss_pi_noindex_meta_tag'));
        }
		
		
		function rss_pi_noindex_meta_tag()
		{
			global $post,$rss_post_importer;
			// Check if single post
			if(is_single())
			{
				
				// Get current post id
				$current_post_id = $post->ID;
				
				// add options
				$this->options = $rss_post_importer->options;
				
				// get value of block indexing
				$block_indexing = $this->options['settings']['block_indexing'];
			
				// Check for block indexing
				if($this->options['settings']['block_indexing'] == 'true'){
					$meta_values = get_post_meta( $current_post_id, 'rss_pi_source_url', false );
					// if meta value array is empty it means post is not imported by this plugin.
					if(!empty($meta_values)){
						echo '<meta name="robots" content="noindex">';
					}
				}
			}
		}
		
}
