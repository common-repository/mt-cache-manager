<?php
class FastCGI_Purger extends Purger {
public function purge_url( $url, $feed = true ) {

		global $mt_cache_manager_admin;
		$url = apply_filters( 'mt_cache_manager_purge_url', $url );

		$parse = wp_parse_url( $url );

		if ( ! isset( $parse['path'] ) ) {
			$parse['path'] = '';
		}

        $this->do_remote_get( '/purge'.$parse['path'], $parse['host'] );
	}
public function custom_purge_urls() {

		global $mt_cache_manager_admin;

		$parse = wp_parse_url( home_url() );

		$purge_urls = isset( $mt_cache_manager_admin->options['purge_url'] ) && ! empty( $mt_cache_manager_admin->options['purge_url'] ) ?
		explode( "\r\n", $mt_cache_manager_admin->options['purge_url'] ) : array();
		$purge_urls = apply_filters( 'mt_cache_manager_purge_urls', $purge_urls, false );
		$_url_purge_base = $this->purge_base_url();

        if ( is_array( $purge_urls ) && ! empty( $purge_urls ) ) {

            foreach ( $purge_urls as $purge_url ) {

                $purge_url = trim( $purge_url );

                if ( strpos( $purge_url, '*' ) === false ) {

                    $purge_url = $_url_purge_base . $purge_url;
                    $this->do_remote_get( $purge_url );

                }
            }
        }

	}
public function purge_all() {

		$this->purge_them_all();
		do_action( 'mt_cache_manager_after_fastcgi_purge_all' );
	}
private function purge_base_url() {

		$parse = wp_parse_url( home_url() );
		$path = apply_filters( 'mt_cache_manager_fastcgi_purge_suffix', 'purge' );

		// Prevent users from inserting a trailing '/' that could break the url purging.
		$path = trim( $path, '/' );

		$purge_url_base = $parse['scheme'] . '://' . $parse['host'] . '/' . $path;
		$purge_url_base = apply_filters( 'mt_cache_manager_fastcgi_purge_url_base', $purge_url_base );

		// Prevent users from inserting a trailing '/' that could break the url purging.
		return untrailingslashit( $purge_url_base );

	}

}
