<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MT_Cache_Manager_WP_CLI_Command' ) ) {
class MT_Cache_Manager_WP_CLI_Command extends WP_CLI_Command {
public function purge_all( $args, $assoc_args ) {

			global $nginx_purger;

			$nginx_purger->purge_all();

			$message = __( 'Purged Everything Done!', 'mt-cache-manager' );
			WP_CLI::success( $message );

		}
	}
}
