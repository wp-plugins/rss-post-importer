(function($){

	// Edit-buttons
	$('body').delegate('a.toggle-edit', 'click', function() {
		$('#edit_' + $(this).attr('data-target')).toggleClass('show');
		$('#display_' + $(this).attr('data-target')).toggleClass('show');
		return false;
	});

	// Delete-buttons
	$('body').delegate('a.delete-row', 'click', function() {
		$('#edit_' + $(this).attr('data-target')).remove();
		$('#display_' + $(this).attr('data-target')).remove();
		update_ids();
		return false;
	});

	
	$('a.add-row').click(function() {
		$.ajax({
			type: 'POST',
			url: rss_pi_ajax.ajaxurl,
			data: ({
				action: 'rss_pi_add_row'
			}),
			success: function(data) {
				$('.rss-rows').append( data );
				$('.empty_table').remove();
				update_ids();
			}
		});
		return false;
	});
	
	$('#save_and_import').click(function() {
		$('#save_to_db').val('true');
	});
	
	$('a.load-log').click(function() {
		$('#main_ui').hide();
		$('.ajax_content').html( '<img src="/wp-admin/images/wpspin_light.gif" alt="" class="loader" />' );
		$.ajax({
			type: 'POST',
			url: rss_pi_ajax.ajaxurl,
			data: ({
				action: 'rss_pi_load_log'
			}),
			success: function(data) {
				$('.ajax_content').html( data );
			}
		});
		return false;
	});
	
	$('body').delegate('a.show-main-ui', 'click', function() {
		$('#main_ui').show();
		$('.ajax_content').html('');
		return false;
	});
	
	$('body').delegate('a.clear-log', 'click', function() {
		$.ajax({
			type: 'POST',
			url: rss_pi_ajax.ajaxurl,
			data: ({
				action: 'rss_pi_clear_log'
			}),
			success: function(data) {
				$('.log').html( data );
			}
		});
		return false;
	});
	
})(jQuery);

function update_ids()
{
	ids = jQuery('input[name="id"]').map(function() {
		return jQuery(this).val();
    }).get().join();
    
	jQuery('#ids').val(ids);
}