<?php
$purge_url  = add_query_arg(
	array(
		'mt_cache_manager_action' => 'purge',
		'mt_cache_manager_urls'   => 'all',
	)
);
$nonced_url = wp_nonce_url( $purge_url, 'mt_cache_manager-purge_all' );
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<form id="purgeall" action="" method="post" class="clearfix">
	<a href="<?php echo esc_url( $nonced_url ); ?>" class="button button-primary">
		<?php esc_html_e( 'Purge Entire Cache', 'mt-cache-manager' ); ?>
	</a>
</form>
