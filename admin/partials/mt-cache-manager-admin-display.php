<?php
global $pagenow;
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap mt-cache-manager-wrapper">
	<h2 class="mt_cache_manager_option_title">
		<?php esc_html_e( 'MT Cache Manager Settings', 'mt-cache-manager' ); ?>
	</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<?php

						include plugin_dir_path( __FILE__ ) . 'mt-cache-manager-general-options.php';

				?>
			</div> <!-- End of #post-body-content -->
			<div id="postbox-container-1" class="postbox-container">
				<?php
					require plugin_dir_path( __FILE__ ) . 'mt-cache-manager-sidebar-display.php';
				?>
			</div> <!-- End of #postbox-container-1 -->
		</div> <!-- End of #post-body -->
	</div> <!-- End of #poststuff -->
</div> <!-- End of .wrap .mt-cache-manager-wrapper -->
