<?php
	$show = '';
	
	if(!isset($f))
	{
		$f = array(
			'id' => uniqid(),
			'name' => 'New feed',
			'url' => '',
			'max_posts' => 5,
			'category_id' => 1,
			'strip_html' => 'false'
		);
		
		$show = 'show';
	}
	
	$category = get_the_category_by_ID( intval($f['category_id']) );

?>

<tr id="display_<?php echo ($f['id']); ?>" class="data-row <?php echo $show; ?>">
	<td>
		<strong><a href="#" class="toggle-edit" data-target="<?php echo ($f['id']); ?>"><?php echo $f['name']; ?></a></strong>
		<div class="row-options">
			<a href="#" class="toggle-edit" data-target="<?php echo ($f['id']); ?>"><?php _e('Edit'); ?></a> | 
			<a href="#" class="delete-row" data-target="<?php echo ($f['id']); ?>"><?php _e('Delete'); ?></a>
		</div>
	</td>
	<td><?php echo $f['url']; ?></td>
	<td><?php echo $f['max_posts']; ?></td>
	<td><?php echo $category; ?></td>
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
				<td><label for=""><?php _e("Category"); ?></label></td>
				<td><?php wp_dropdown_categories( array('hide_empty' => 0, 'name' => 'select_name', 'hierarchical' => true, 'id' => $f['id'] . '-category_id', 'name' => $f['id'] . '-category_id', 'selected' => $f['category_id'] )); ?></td>
			</tr>
			<tr>
				<td><label for=""><?php _e("Strip html tags", 'rss_pi'); ?></label></td>
				<td>
					<ul class="radiolist">
						<li>
							<label><input type="radio" id="<?php echo($f['id']); ?>-strip_html" name="<?php echo($f['id']); ?>-strip_html" value="true" <?php echo($f['strip_html'] == 'true' ? 'checked="checked"' : ''); ?> /> <?php _e('Yes'); ?></label>
						</li>
						<li>
							<label><input type="radio" id="<?php echo($f['id']); ?>-strip_html" name="<?php echo($f['id']); ?>-strip_html" value="false" <?php echo($f['strip_html'] == 'false' ? 'checked="checked"' : ''); ?> /> <? _e('No'); ?></label>
						</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td><input type="hidden" name="id" value="<?php echo($f['id']); ?>" /></td>
				<td><a id="close-edit-table" class="button button-large toggle-edit" data-target="<?php echo ($f['id']); ?>"><?php _e('Close'); ?></a></td>
			</tr>
		</table>
		
	</td>
</tr>
