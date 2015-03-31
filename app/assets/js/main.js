jQuery('document').ready(function () {

	// Edit-buttons
	jQuery('body').on('click', 'a.toggle-edit', function () {
		jQuery('#edit_' + jQuery(this).attr('data-target')).toggleClass('show');
		jQuery('#display_' + jQuery(this).attr('data-target')).toggleClass('show');
		return false;
	});

	// Delete-buttons
	jQuery('body').on('click', 'a.delete-row', function () {
		jQuery('#edit_' + jQuery(this).attr('data-target')).remove();
		jQuery('#display_' + jQuery(this).attr('data-target')).remove();
		update_ids();
		return false;
	});

	jQuery('a.add-row').on('click', function () {
		jQuery.ajax({
			type: 'POST',
			url: rss_pi.ajaxurl,
			data: ({
				action: 'rss_pi_add_row'
			}),
			success: function (data) {
				jQuery('.rss-rows').append(data);
				jQuery('.empty_table').remove();
				update_ids();
			}
		});
		return false;
	});

	jQuery('#save_and_import').on('click', function () {
		jQuery('#save_to_db').val('true');
	});

	jQuery('a.load-log').on('click', function () {
		jQuery('#main_ui').hide();
		jQuery('.ajax_content').html('<img src="/wp-admin/images/wpspin_light.gif" alt="" class="loader" />');
		jQuery.ajax({
			type: 'POST',
			url: rss_pi.ajaxurl,
			data: ({
				action: 'rss_pi_load_log'
			}),
			success: function (data) {
				jQuery('.ajax_content').html(data);
			}
		});
		return false;
	});

	jQuery('body').delegate('a.show-main-ui', 'click', function () {
		jQuery('#main_ui').show();
		jQuery('.ajax_content').html('');
		return false;
	});

	jQuery('body').delegate('a.clear-log', 'click', function () {
		jQuery.ajax({
			type: 'POST',
			url: rss_pi.ajaxurl,
			data: ({
				action: 'rss_pi_clear_log'
			}),
			success: function (data) {
				jQuery('.log').html(data);
			}
		});
		return false;
	});

	jQuery("#from_date").datepicker();
	jQuery("#till_date").datepicker();

	if ( jQuery("#rss_pi-stats-placeholder").length ) {
		rss_filter_stats = function(form) {
			var data = {
					action: "rss_pi_stats",
					rss_from_date: jQuery("#from_date").val() || "",
					rss_till_date: jQuery("#till_date").val() || ""
				},
				$loading = false;
			if (form && jQuery("#submit-rss_filter_stats").length) {
				data.rss_filter_stats = jQuery("#submit-rss_filter_stats").val();
			} else {
				$loading = jQuery('<div class="rss_pi_overlay"><img class="rss_pi_loading" src="'+rss_pi.pluginurl+'app/assets/img/loading.gif" /><p>Stats are loading. Please wait...</p></div>').appendTo("#rss_pi-stats-placeholder");
			}
			jQuery.ajax({
				type: "POST",
				url: rss_pi.ajaxurl,
				data: data,
				success: function (data) {
					if ($loading) { $loading.remove(); $loading = false; }
					jQuery("#rss_pi-stats-placeholder").empty().append(data);
					drawChart();
					jQuery("#from_date").datepicker();
					jQuery("#till_date").datepicker();
					jQuery("#submit-rss_filter_stats").on("click",function(e){
						e.preventDefault();
						$loading = jQuery('<div class="rss_pi_overlay"><img class="rss_pi_loading" src="'+rss_pi.pluginurl+'app/assets/img/loading.gif" /><p>Stats are loading. Please wait...</p></div>').appendTo("#rss_pi-stats-placeholder");
						rss_filter_stats(true);
					});
				}
			});
		};
		rss_filter_stats();
	}

	if ( jQuery("#rss_pi_progressbar").length && feeds !== undefined && feeds.count ) {
		var import_feed = function(id) {
			jQuery.ajax({
				type: 'POST',
				url: rss_pi.ajaxurl,
				data: {
					action: 'rss_pi_import',
					feed: id
				},
				success: function (data) {
					var data = data.data || {};
					jQuery("#rss_pi_progressbar").progressbar({
						value: feeds.processed()
					});
					jQuery("#rss_pi_progressbar_label .processed").text(feeds.processed());
					if ( data.count !== undefined ) feeds.imported(data.count);
					if (feeds.left()) {
						jQuery("#rss_pi_progressbar_label .count").text(feeds.imported());
						import_feed(feeds.get());
					} else {
						jQuery("#rss_pi_progressbar_label").html("Import completed. Imported posts: " + feeds.imported());
					}
				}
			});
		}
		jQuery("#rss_pi_progressbar").progressbar({
			value: 0,
			max: feeds.total()
		});
		jQuery("#rss_pi_progressbar_label").html("Import in progres. Processed feeds: <span class='processed'>0</span> of <span class='max'>"+feeds.total()+"</span>. Imported posts so far: <span class='count'>0</span>");
		import_feed(feeds.get());
	}

});

function update_ids() {

	ids = jQuery('input[name="id"]').map(function () {
		return jQuery(this).val();
	}).get().join();

	jQuery('#ids').val(ids);

}

var feeds = {
	ids: [],
//	ids_cache: <?php echo json_encode($ids); ?>,
	count: 0,
	imported_posts: 0,
	set: function(ids){
		this.ids = ids;
		this.count = ids.length;
	},
	get: function(){
		return this.ids.splice(0,1)[0];
	},
//	has: function(){
//		return !!this.ids.length;
//	},
	left: function(){
		return this.ids.length;
	},
	processed: function(){
		return this.count - this.ids.length;
	},
	total: function(){
		return this.count;
	},
	imported: function(num){
		if ( num !== undefined && !isNaN(parseInt(num)) ) this.imported_posts += parseInt(num);
		return this.imported_posts;
	}
};
