<?php
$show = '';

if (!isset($f)) {
        $f = array(
            'id' => uniqid(),
            'name' => 'New feed',
            'url' => '',
            'max_posts' => 5,
            'author_id' => 1,
            'category_id' => 1,
			'tags_id' => array(),
            'strip_html' => 'false'
        );

        $show = 'show';
}

if(is_array($f['tags_id'])){
	if(!empty($f['tags_id'])){
		foreach ( $f['tags_id'] as $tag ) {  
			$tagname = get_tag($tag);
			$tagarray[] = $tagname->name;
		}
		$tag = join(',', $tagarray);
	}
	else{
		$tag = array();
	}
	
}else{
	if(empty($f['tags_id'])){
		$f['tags_id'] = array();
		$tag = '';
	}
	else{
		$f['tags_id'] = array($f['tags_id']);
		$tagname = get_tag(intval($f['tags_id']));
		$tag = $tagname->name;
	}
		
}

/*echo "<pre>";
print_r($f);
exit;*/
if(is_array($f['category_id'])){
	foreach ( $f['category_id'] as $cat ) {  
		$catarray[] = get_cat_name($cat);
	}
	$category = join(',', $catarray);
}else{
	if(empty($f['category_id'])){
		$f['category_id'] = array(1);
		$category = get_the_category_by_ID(1);
	}
	else{
		$f['category_id'] = array($f['category_id']);
		$category = get_the_category_by_ID(intval($f['category_id']));
	}
		
}

?>

<tr id="display_<?php echo ($f['id']); ?>" class="data-row <?php echo $show; ?>">
        <td>
                <strong><a href="#" class="toggle-edit" data-target="<?php echo ($f['id']); ?>"><?php echo $f['name']; ?></a></strong>
                <div class="row-options">
                        <a href="#" class="toggle-edit" data-target="<?php echo ($f['id']); ?>"><?php _e('Edit', 'rss_pi'); ?></a> | 
                        <a href="#" class="delete-row" data-target="<?php echo ($f['id']); ?>"><?php _e('Delete', 'rss_pi'); ?></a>
                </div>
        </td>
        <td><?php echo $f['url']; ?></td>
        <td><?php echo $f['max_posts']; ?></td>
       <!-- <td width="20%"><?php //echo $category; ?></td>-->
</tr>
<tr id="edit_<?php echo ($f['id']); ?>" class="edit-row <?php echo $show; ?>">
        <td colspan="4">
                <table class="widefat edit-table">
                        <tr>
                                <td><label for="<?php echo ($f['id']); ?>-name"><?php _e("Feed name", 'rss_pi'); ?></label></td>
                                <td>
                                        <input type="text" name="<?php echo ($f['id']); ?>-name" id="<?php echo ($f['id']); ?>-name" value="<?php echo ($f['name']); ?>" />
                                </td>
                        </tr>
                        <tr>
                                <td>
                                        <label for="<?php echo ($f['id']); ?>"><?php _e("Feed url", 'rss_pi'); ?></label>
                                        <p class="description">ie "http://news.google.com/?output=rss"</p>
                                </td>
                                <td><input type="text" name="<?php echo ($f['id']); ?>-url" id="<?php echo ($f['id']); ?>-url" value="<?php echo ($f['url']); ?>" /></td>
                        </tr>
                        <tr>
                                <td><label for=""><?php _e("Max posts / import", 'rss_pi'); ?></label></td>
                                <td><input type="number" name="<?php echo ($f['id']); ?>-max_posts" id="<?php echo ($f['id']); ?>-max_posts" value="<?php echo ($f['max_posts']); ?>" min="1" max="100" /></td>
                        </tr>
                        <tr>
                                <td><label for=""><?php _e("Feed Author", 'rss_pi'); ?></label></td>
                                <td>
                                        <?php
                                        if(!$this->is_key_valid){
                                             $this->key_error($this->key_prompt, true);   
                                        }
                                        $args = array(
                                            'id' => $f['id'] . '-author_id',
                                            'name' => $f['id'] . '-author_id',
                                            'selected' => $f['author_id'],
                                            'class'     => 'rss-pi-specific-feed-author'
                                        );
                                        wp_dropdown_users($args);
                                        ?>
                                </td>
                        </tr>
                        <tr>
                                <td><label for=""><?php _e("Category", 'rss_pi'); ?></label></td>
                                <td>
                                <?php
									$rss_post_pi_admin = new rssPIAdmin();
									$disabled = '';
									if (!$this->is_key_valid) {
										   $this->key_error($this->key_prompt_multiple_category, true);
										  wp_dropdown_categories(array('hide_empty' => 0,  'hierarchical' => true, 'id' => $f['id'] . '-category_id', 'name' => $f['id'] . '-category_id', 'selected' => $f['category_id']));
									}
									else{
										?>
										<div class="category_container">
										<?php 
										
										$allcats = $rss_post_pi_admin->wp_category_checklist_rss_pi(0, false,$f['category_id']);
										$allcats = str_replace( 'name="post_category[]"', 'name="'.$f['id'].'-category_id[]"', $allcats );
										echo $allcats;
										  ?></div>
										<?php
									}
									?>
								</td>
                        </tr>
                        <tr>
                                <td><label for=""><?php _e("Tags", 'rss_pi'); ?></label></td>
                                <td>
                                <?php
									$disabled = '';
									if (!$this->is_key_valid) {
										   $this->key_error($this->key_prompt_multiple_tags, true);
										   echo $rss_post_pi_admin->rss_pi_tags_dropdown($f['id'],$f['tags_id']);
									}
									else{
										?>
										<div class="tags_container">
										<?php 
												echo $rss_post_pi_admin->rss_pi_tags_checkboxes($f['id'],$f['tags_id']);
										  ?></div>
										<?php
									}
									?>
								</td>
                        </tr>
                        <tr>
                                <td><label for=""><?php _e("Strip html tags", 'rss_pi'); ?></label></td>
                                <td>
                                        <ul class="radiolist">
                                                <li>
                                                        <label><input type="radio" id="<?php echo($f['id']); ?>-strip_html" name="<?php echo($f['id']); ?>-strip_html" value="true" <?php echo($f['strip_html'] == 'true' ? 'checked="checked"' : ''); ?> /> <?php _e('Yes', 'rss_pi'); ?></label>
                                                </li>
                                                <li>
                                                        <label><input type="radio" id="<?php echo($f['id']); ?>-strip_html" name="<?php echo($f['id']); ?>-strip_html" value="false" <?php echo($f['strip_html'] == 'false' ? 'checked="checked"' : ''); ?> /> <?php _e('No', 'rss_pi'); ?></label>
                                                </li>
                                        </ul>
                                </td>
                        </tr>
                        <tr>
                                <td><input type="hidden" name="id" value="<?php echo($f['id']); ?>" /></td>
                                <td><a id="close-edit-table" class="button button-large toggle-edit" data-target="<?php echo ($f['id']); ?>"><?php _e('Close', 'rss_pi'); ?></a></td>
                        </tr>
                </table>

        </td>
</tr>
