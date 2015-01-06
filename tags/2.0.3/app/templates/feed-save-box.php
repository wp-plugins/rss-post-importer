<div class="postbox">
        <div class="inside">
                <div class="misc-pub-section">
                        <h3 class="version">V. <?php echo RSS_PI_VERSION; ?></h3>
                        <ul>
                                <li>
                                        <i class="icon-calendar"></i> <?php _e("Latest import:", 'rss_pi'); ?> <strong><?php echo($this->options['latest_import']); ?></strong>
                                </li>
                                <li><i class="icon-eye-open"></i> <a href="#" class="load-log"><?php _e("View the log", 'rss_pi'); ?></a></li>
                        </ul>
                </div>
                <div id="major-publishing-actions">
                        <input class="button button-primary button-large right" type="submit" name="info_update" value="<?php _e('Save', 'rss_pi'); ?>" />
                        <input class="button button-large" type="submit" name="info_update" value="<?php _e('Save and import', "rss_pi"); ?>" id="save_and_import" />
                </div>
        </div>
</div>
<?php if ($this->options['imports'] > 10) : ?>
        <div class="rate-box">
                <h4><?php printf(__('%d posts imported and counting!', "rss_pi"), $this->options['imports']); ?></h4>
                <i class="icon-star"></i>
                <i class="icon-star"></i>
                <i class="icon-star"></i>
                <i class="icon-star"></i>
                <i class="icon-star"></i>
                <p class="description"><a href="http://wordpress.org/plugins/rss-post-importer/" target="_blank">Please support this plugin by rating it!</a></p>
        </div>
<?php endif; ?>

<?php $banner_url = RSS_PI_URL . "app/assets/img/rss-post-importer_280x600.jpg"; ?>
<a target="_blank" href="http://www.feedsapi.com/?utm=rsspostimporter_banner">
        <img class='rss_pi_banner_img' src="<?php echo $banner_url; ?>" />
</a>