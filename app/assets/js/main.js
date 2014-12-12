jQuery('document').ready(function(){

	// Edit-buttons
	jQuery('body').on('click', 'a.toggle-edit', function() {
		jQuery('#edit_' + jQuery(this).attr('data-target')).toggleClass('show');
		jQuery('#display_' + jQuery(this).attr('data-target')).toggleClass('show');
		return false;
	});

	// Delete-buttons
	jQuery('body').on( 'click', 'a.delete-row', function() {
		jQuery('#edit_' + jQuery(this).attr('data-target')).remove();
		jQuery('#display_' + jQuery(this).attr('data-target')).remove();
		update_ids();
		return false;
	});

	
	jQuery('a.add-row').on('click', function() {
		jQuery.ajax({
			type: 'POST',
			url: rss_pi.ajaxurl,
			data: ({
				action: 'rss_pi_add_row'
			}),
			success: function(data) {
				jQuery('.rss-rows').append( data );
				jQuery('.empty_table').remove();
				update_ids();
			}
		});
		return false;
	});
	
	jQuery('#save_and_import').on('click', function() {
		jQuery('#save_to_db').val('true');
	});
	
	jQuery('a.load-log').on('click', function() {
		jQuery('#main_ui').hide();
		jQuery('.ajax_content').html( '<img src="/wp-admin/images/wpspin_light.gif" alt="" class="loader" />' );
		jQuery.ajax({
			type: 'POST',
			url: rss_pi.ajaxurl,
			data: ({
				action: 'rss_pi_load_log'
			}),
			success: function(data) {
				jQuery('.ajax_content').html( data );
			}
		});
		return false;
	});
	
	jQuery('body').delegate('a.show-main-ui', 'click', function() {
		jQuery('#main_ui').show();
		jQuery('.ajax_content').html('');
		return false;
	});
	
	jQuery('body').delegate('a.clear-log', 'click', function() {
		jQuery.ajax({
			type: 'POST',
			url: rss_pi.ajaxurl,
			data: ({
				action: 'rss_pi_clear_log'
			}),
			success: function(data) {
				jQuery('.log').html( data );
			}
		});
		return false;
	});
	
});

function update_ids()
{
	ids = jQuery('input[name="id"]').map(function() {
		return jQuery(this).val();
    }).get().join();
    
	jQuery('#ids').val(ids);
}