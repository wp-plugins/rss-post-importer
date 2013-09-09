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
	
})(jQuery);

function update_ids()
{
	ids = jQuery('input[name="id"]').map(function() {
		return jQuery(this).val();
    }).get().join();
    
	jQuery('#ids').val(ids);
}