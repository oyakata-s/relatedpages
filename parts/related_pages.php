<?php
/*
 * 関連ページ出力テンプレート
 */
?>
<div id="related_pages_container" class="<?php echo get_option('relatedp_container_class'); ?>">
	<<?php echo get_relatedp_option('relatedp_heading_tag','h3'); ?> class="related_pages_title <?php echo get_option('relatedp_heading_class'); ?>">
		<?php echo get_relatedp_option('relatedp_heading_text', __('Related Pages', 'relatedpages')); ?>
	</<?php echo get_relatedp_option('relatedp_heading_tag','h3'); ?>>
	<div class="related_pages <?php echo get_option('relatedp_grouping_class'); ?>">
<?php
	global $post;
	$related_posts = get_related_posts($post->ID);
	$max_count = get_relatedp_option('relatedp_number_post', 4);
	add_filter('post_image_url', 'custom_noimage_url');
	$count = 0;
	if (0 < count($related_posts)) :
		foreach ($related_posts as $post) : setup_postdata($post);
?>
		<a class="related_page <?php echo get_option('relatedp_element_class'); ?>" href="<?php the_permalink(); ?>">
			<div class="thumbnail" style="background-image:url(<?php echo get_post_image_url('thumbnail', true); ?>);"></div>
			<p class="related_page_title"><?php the_title(); ?></p>
		</a>
<?php
		if (++$count >= $max_count) { break; }
		endforeach;
	else :
?>
		<p class="no_related_pages"><?php _e('There is no related pages.', 'relatedpages'); ?></p>
<?php
	endif;
	wp_reset_postdata();
	remove_filter('post_image_url', 'custom_noimage_url');
?>
	</div>
	<!-- /.posts -->
</div>
<!-- /#related_pages -->
