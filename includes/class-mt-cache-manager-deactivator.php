<?php
class MT_Cache_Manager_Deactivator {

	public static function deactivate() {

		$role = get_role( 'administrator' );
		$role->remove_cap( 'MT Cache Manager | Config' );
		$role->remove_cap( 'MT Cache Manager | Purge cache' );

	}

}
