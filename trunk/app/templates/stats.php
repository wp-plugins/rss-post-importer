<?php 

	$stats = new Rss_pi_stats();

?>
<table class="widefat rss_pi-table" id="rss_pi-table">
     <thead>
            <tr>
                    <th colspan="5"><?php _e('Stats', 'rss_pi'); ?></th>
            </tr>
    </thead>
    <tbody class="setting-rows">
            <tr class="edit-row show">
            	<td>
            		<?php
            			$stats->show_charts();
            		?>
            	</td>
            	<td></td>
            </tr>
	</tbody>
</table>