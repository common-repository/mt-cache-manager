<?php

abstract class Purger {
abstract public function purge_url( $url, $feed = true );
abstract public function custom_purge_urls();
abstract public function purge_all();
public function purge_post_on_comment( $comment_id, $comment ) {

		$oldstatus = '';
		$approved  = $comment->comment_approved;

		if ( null === $approved ) {
			$newstatus = false;
		} elseif ( '1' === $approved ) {
			$newstatus = 'approved';
		} elseif ( '0' === $approved ) {
			$newstatus = 'unapproved';
		} elseif ( 'spam' === $approved ) {
			$newstatus = 'spam';
		} elseif ( 'trash' === $approved ) {
			$newstatus = 'trash';
		} else {
			$newstatus = false;
		}

		$this->purge_post_on_comment_change( $newstatus, $oldstatus, $comment );

	}
public function purge_post_on_comment_change( $newstatus, $oldstatus, $comment ) {

		global $mt_cache_manager_admin, $blog_id;

		if ( ! $mt_cache_manager_admin->options['enable_purge'] ) {
			return;
		}

		$_post_id    = $comment->comment_post_ID;
		$_comment_id = $comment->comment_ID;

		switch ( $newstatus ) {

			case 'approved':
				if ( 1 === (int) $mt_cache_manager_admin->options['purge_page_on_new_comment'] ) {

					$this->purge_post( $_post_id );

				}
				break;

			case 'spam':
			case 'unapproved':
			case 'trash':
				if ( 'approved' === $oldstatus && 1 === (int) $mt_cache_manager_admin->options['purge_page_on_deleted_comment'] ) {

					$this->purge_post( $_post_id );

				}
				break;

		}

	}
public function purge_post( $post_id ) {

		global $mt_cache_manager_admin, $blog_id;

		if ( ! $mt_cache_manager_admin->options['enable_purge'] ) {
			return;
		}

        $this->_purge_homepage();

		$this->custom_purge_urls();
	}
protected function do_remote_get( $url, $hostname ) {
	$url = apply_filters( 'mt_cache_manager_remote_purge_url', $url );
	do_action( 'mt_cache_manager_before_remote_purge_url', $url );

        $purge_host = "127.0.0.1";
        $purge_port = '8889';
        $purge_url_full = sprintf("http://%s:%s%s",
            $purge_host,
            $purge_port,
            $url
            );

        $response = wp_remote_get( $purge_url_full,
            array(
                'timeout'     => 5,
                'headers' => array(
                    'Host' => $hostname
                )
            )
        );
    }
public function purge_image_on_edit( $attachment_id ) {

		global $mt_cache_manager_admin;

		// Do not purge if not enabled.
		if ( ! $mt_cache_manager_admin->options['enable_purge'] ) {
			return;
		}

		if ( wp_attachment_is_image( $attachment_id ) ) {

			$this->purge_url( wp_get_attachment_url( $attachment_id ), false );
			$attachment = wp_get_attachment_metadata( $attachment_id );

			if ( ! empty( $attachment['sizes'] ) && is_array( $attachment['sizes'] ) ) {

				foreach ( array_keys( $attachment['sizes'] ) as $size_name ) {

					$resize_image = wp_get_attachment_image_src( $attachment_id, $size_name );

					if ( $resize_image ) {
						$this->purge_url( $resize_image[0], false );
					}
				}
			}

			$this->purge_url( get_attachment_link( $attachment_id ) );

		}

	}
public function purge_on_post_moved_to_trash( $new_status, $old_status, $post ) {

		global $mt_cache_manager_admin, $blog_id;

		if ( ! $mt_cache_manager_admin->options['enable_purge'] ) {
			return;
		}

		if ( 'trash' === $new_status ) {
            $this->_purge_homepage();
		}

		return true;

	}
private function _purge_homepage() {

		// WPML installetd?.
		if ( function_exists( 'icl_get_home_url' ) ) {

			$homepage_url = trailingslashit( icl_get_home_url() );

		} else {

			$homepage_url = trailingslashit( home_url() );

		}

		$this->purge_url( $homepage_url );

		return true;

	}
private function _purge_personal_urls() {

		global $mt_cache_manager_admin;

		if ( isset( $mt_cache_manager_admin->options['purgeable_url']['urls'] ) ) {

			foreach ( $mt_cache_manager_admin->options['purgeable_url']['urls'] as $url ) {
				$this->purge_url( $url, false );
			}
		}

		return true;

	}
public function purge_them_all() {
		$this->_purge_homepage();
		return true;

	}
public function purge_on_term_taxonomy_edited( $term_id, $tt_id, $taxon ) {

		global $mt_cache_manager_admin;

		if ( ! $mt_cache_manager_admin->options['enable_purge'] ) {
			return;
		}

		$term           = get_term( $term_id, $taxon );
		$current_filter = current_filter();

		$this->_purge_homepage();

		return true;

	}
public function purge_on_check_ajax_referer( $action ) {

		global $mt_cache_manager_admin;

		if ( ! $mt_cache_manager_admin->options['enable_purge'] ) {
			return;
		}

		switch ( $action ) {

			case 'save-sidebar-widgets':
				$this->_purge_homepage();
				break;

			default:
				break;

		}

		return true;

	}

}
