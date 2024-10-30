<?php
class MT_Cache_Manager_Activator {
public static function activate() {

		global $mt_cache_manager_admin;

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$role = get_role( 'administrator' );

		if ( empty( $role ) ) {

			update_site_option(
				'wp_mt_cache_manager_init_check',
				__( 'Sorry, you need to be an administrator to use MT Cache Manager', 'mt-cache-manager' )
			);

			return;

		}

		$role->add_cap( 'MT Cache Manager | Config' );
		$role->add_cap( 'MT Cache Manager | Purge cache' );

	}

}
