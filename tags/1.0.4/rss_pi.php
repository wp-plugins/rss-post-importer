<?php
/*
Plugin Name: Rss Post Importer
Plugin URI: https://wordpress.org/plugins/rss-post-importer/
Description: This plugin lets you set up an import posts from one or several rss-feeds and save them as posts on your site, simple and flexible.
Author: feedsapi
Version: 1.0.7
Author URI: https://www.feedsapi.org/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

add_action('wp_ajax_rss_pi_add_row', 'rss_pi_add_row');
add_action('wp_ajax_rss_pi_load_log', 'rss_pi_load_log');
add_action('wp_ajax_rss_pi_clear_log', 'rss_pi_clear_log');

function rss_pi_add_row()
{
	include( plugin_dir_path( __FILE__ ) . 'parts/table_row.php');
	exit;
}

function rss_pi_load_log()
{
	$log = file_get_contents(plugin_dir_path( __FILE__ ) . 'log.txt');
	include( plugin_dir_path( __FILE__ ) . 'parts/rss_pi-log.php');

	exit;
}

function rss_pi_clear_log()
{
	$log_file = plugin_dir_path( __FILE__ ) . 'log.txt';
	
	file_put_contents($log_file, '');
	?>
	<div id="message" class="updated">
	    <p><strong><?php _e('Log has been cleared.', "rss_pi"); ?></strong></p>
	</div>
	<?php
	
	exit;
}


class rss_pi {

	function __construct() {
		add_action('admin_menu', array(&$this, 'admin_menu'));
		
		$this->settings = array(
			'version'	=>	'1.0.7',
			'dir'		=>	plugin_dir_path( __FILE__ )
		);

		load_textdomain('rss_pi', $this->settings['dir'] . 'lang/rss_pi-' . get_locale() . '.mo');

		add_action( 'wp', array(&$this, 'rss_pi_setup_schedule') );
		add_action( 'rss_pi_cron',  array(&$this, 'rss_pi_do_this_hourly') );

	}
	
	
	// On an early action hook, check if the hook is scheduled - if not, schedule it.
	function rss_pi_setup_schedule()
	{
		if ( ! wp_next_scheduled( 'rss_pi_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'rss_pi_cron');
		}
	}
	
	// On the scheduled action hook, run a function.
	function rss_pi_do_this_hourly()
	{
		$this->import_all_feeds();
	}
	
	// Add to settings-menu
	function admin_menu () {
		add_options_page('Rss Post Importer','Rss Post Importer','manage_options','rss_pi', array($this, 'settings_page'));
	}
	
	function settings_page () {
		// If logpage is requested
		if(isset($_GET['show']))
		{
			// Display the logpage
			$do = $_GET['show'];
			if($do == 'log')
			{
				die($this->log_page());
			}
		}
		// Changes submitted, check for correct nonce
		if( isset($_POST['info_update']) && wp_verify_nonce($_POST['rss_pi_nonce'],'settings_page')) : 
			
			// Get ids of feed-rows
			$ids =  explode(",", $_POST['ids']);
			
			$feeds = array();
			
			// Get selected settings for all imported posts
			$settings = array(
				'frequency' => $_POST['frequency'],
				'feeds_api_key'=> $_POST['feeds_api_key'],
				'post_template' => stripslashes_deep($_POST['post_template']),
				'post_status' => $_POST['post_status'],
				'author_id' => $_POST['author_id'],
				'allow_comments' => $_POST['allow_comments'],
				'enable_logging' => $_POST['enable_logging']
			);
			
			$this->is_correct_api( $_POST['feeds_api_key'] );

			// If cron settings have changed
			if( wp_get_schedule( 'rss_pi_cron' ) != $settings['frequency'] )
			{
				// Reset cron
				wp_clear_scheduled_hook( 'rss_pi_cron' );
				wp_schedule_event( time(), $settings['frequency'], 'rss_pi_cron');
			}
			
			// Loop through feed-rows
			foreach($ids as $id)
			{
				if($id)
				{
					array_push($feeds, array(
						'id' => $id,
						'url' => strtolower($_POST[$id . '-url']),
						'name' => $_POST[$id . '-name'],
						'max_posts' =>  $_POST[$id . '-max_posts'],
						'category_id' =>  $_POST[$id . '-category_id'],
						'strip_html' => $_POST[$id . '-strip_html']
					));
				}
			}

			$options = $this->rss_pi_get_option();

			update_option('rss_pi_feeds', array('feeds' => $feeds, 'settings' => $settings, 'latest_import' => $options['latest_import'], 'imports' => $options['imports']));
			
			?>
			<div id="message" class="updated">
			    <p><strong><?php _e('Settings saved.') ?></strong></p>
			</div>
			<?php
			
			if($_POST['save_to_db'] == 'true') :
				$imported = $this->import_all_feeds();
				?>
				<div id="message" class="updated">
				    <p><strong><?php echo($imported); ?> <?php _e('new posts imported.') ?></strong></p>
				</div>
				<?php
			endif;
		endif;
		
		$options = $this->rss_pi_get_option();
				
		$ids = array();
		
		// Load js and css
		$this->input_admin_enqueue_scripts();
		
		include( $this->settings['dir'] . 'rss_pi-ui.php');
	}
	
	function import_feed($url, $feed_title, $max_posts, $category_id, $strip_html, $save_to_db)
	{
		include_once( ABSPATH . WPINC . '/feed.php' );
		
		$options = $this->rss_pi_get_option();

		$rss = "";

		//if api key has been saved by user and is not empty
		if(isset($options['settings']["feeds_api_key"]) && $options['settings']["feeds_api_key"]) {
			
			$feeds_api_key = $options['settings']["feeds_api_key"];
			
			$feedsapi_url = "http://www.feedsapi.org/fetch.php?key=".$feeds_api_key."&url=".$url;

			/* DEBUGGING

				touch("newfile.txt");
				$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
				$txt = print_r($options, true)."\n".$feeds_api_key."\n".$feedsapi_url;
				fwrite($myfile, $txt);
				fclose($myfile);
			
			*/
			// Get a SimplePie feed object from the specified feed source.
		
			$rss = fetch_feed( $feedsapi_url );
		}
		else {
			
			$rss = fetch_feed( $url );
			
		}
		
		// Remove the surrounding <div> from XHTML content in Atom feeds.
		
		if ( ! is_wp_error( $rss ) ) : // Checks that the object is created correctly
		
		    // Figure out how many total items there are, but limit it to 5. 
		    $maxitems = $rss->get_item_quantity( $max_posts ); 
		
		    // Build an array of all the items, starting with element 0 (first element).
		    $rss_items = $rss->get_items( 0, $max_posts );
			
			if($save_to_db)
			{
				$saved_posts = array();
				
				$log = '';
				
				foreach ( $rss_items as $item )
				{
					if (!$this->post_exists($item->get_permalink()))
					{
						$new_post = array(
							'post_title'    => $item->get_title(),
							'post_content'  => $this->parse_content($item, $feed_title, $strip_html),
							'post_status'   => $options['settings']['post_status'],
							'post_author'   => $options['settings']['author_id'],
							'post_category' => array($category_id),
							'comment_status'=> $options['settings']['allow_comments'],
							'post_date'		=> $item->get_date('Y-m-d H:i:s')
						);
						
						$post_id = wp_insert_post( $new_post );
						
						add_post_meta( $post_id, 'rss_pi_source_url', esc_url($item->get_permalink()) );
						
						array_push($saved_posts, $new_post);
						
					}
				}
				
				return $saved_posts;
				exit;
			}
			return $rss_items;
		endif;
	}
	
	function return_frequency($seconds)
	{
		$options = $this->rss_pi_get_option();
		return $options['settings']['frequency'];
	}
	
	function import_all_feeds()
	{
				
		$post_count = 0;
		
		$options = $this->rss_pi_get_option();

		add_filter( 'wp_feed_cache_transient_lifetime', array(&$this, 'return_frequency' ) );

		foreach($options['feeds'] as $f)
		{
			$rss_items = $this->import_feed($f['url'], $f['name'], $f['max_posts'], $f['category_id'], $f['strip_html'], true);
			$post_count += count($rss_items);
		}
		$imports = intval($options['imports']) + $post_count;
		
		update_option('rss_pi_feeds', array(
			'feeds' => $options['feeds'],
			'settings' => $options['settings'],
			'latest_import' => date("Y-m-d H:i:s"),
			'imports' => $imports
		));
		
		remove_filter( 'wp_feed_cache_transient_lifetime', array(&$this, 'return_frequency' ) );
		
		if($options['settings']['enable_logging'] == 'true')
		{
			$log = date("Y-m-d H:i:s") . "\t Imported " . $post_count . " new posts. \n";
			$log_file = $this->settings['dir'] . 'log.txt';
			file_put_contents($log_file, $log, FILE_APPEND);
		}
		
		return $post_count;
	}
	
	function parse_content($item, $feed_title, $strip_html)
	{
		$options = $this->rss_pi_get_option();
		$post_template = $options['settings']['post_template'];
		$c = $item->get_content() != "" ? $item->get_content() : $item->get_description();
		
		$parsed_content = str_replace('{$content}', $c, $post_template);
		$parsed_content = str_replace('{$permalink}', esc_url( $item->get_permalink() ), $parsed_content);
		$parsed_content = str_replace('{$feed_title}', $feed_title, $parsed_content);
		$parsed_content = str_replace('{$title}', $item->get_title(), $parsed_content);	
		
		if($strip_html == 'true')
		{
			$parsed_content = strip_tags($parsed_content);
		}
		return $parsed_content;
	}
	
	function post_exists($permalink)
	{
		
		$args = array(
			'post_status' => 'any',
			'meta_key' => 'rss_pi_source_url',
			'meta_value' => esc_url($permalink)
		);
		
		$posts = get_posts( $args );
		
		// Not already imported
		return(count($posts) > 0);
	}
	
	function input_admin_enqueue_scripts()
	{
		// register scripts & styles
		wp_register_script( 'rss_pi', plugins_url( 'js/rss_pi.js', __FILE__ ), array('jquery'), $this->settings['version'] );
		wp_register_style( 'rss_pi', plugins_url( 'css/rss_pi.css', __FILE__ ) , array(), $this->settings['version'] ); 
		wp_localize_script('rss_pi', 'rss_pi_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		
		// scripts
		wp_enqueue_script(array(
			'rss_pi'
		));

		// styles
		wp_enqueue_style(array(
			'rss_pi'
		));
		
	}
	
	// Returns the settings for the plugin
	function rss_pi_get_option()
	{
		$options = get_option('rss_pi_feeds');
		
		// Insert new settings
		if( is_array($options) )
		{
			if(!array_key_exists('enable_logging', $options['settings']))
			{
				$options['settings']['enable_logging'] = 'false';
			}
			if(!array_key_exists('imports', $options))
			{
				$options['imports'] = 0;
			}
		}
		
		return $options;
	}

	function is_correct_api($new_key) {
		$options = $this->rss_pi_get_option();
		$old_key = $options["settings"]["feeds_api_key"];
		
		if($new_key == $old_key)  return true;
		
		$url = "http://www.feedsapi.org/fetch.php?key=$new_key&url=http://dummyurl.com";
		$content = file_get_contents($url);
		if(trim($content) == "A valid key must be supplied") {
			echo '<div class="error">
			        <p>Invalid API key!</p>
				</div>';
			return false;
		}
		else {
			return true;
		}
	}


}
new rss_pi;
?>