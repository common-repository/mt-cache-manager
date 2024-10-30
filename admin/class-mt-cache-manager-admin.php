<?php

class MT_Cache_Manager_Admin {
private $plugin_name;
private $version;
private $settings_tabs;
public $options;
public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->settings_tabs = apply_filters(
			'mt_cache_manager_settings_tabs',
			array(
				'general' => array(
					'menu_title' => __( 'General', 'mt-cache-manager' ),
					'menu_slug'  => 'general',
				),
			)
		);

		$this->options = $this->mt_cache_manager_settings();

	}
public function enqueue_styles( $hook ) {
if ( 'settings_page_mt-cache-manager' !== $hook ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mt-cache-manager-admin.css', array(), $this->version );

	}
public function enqueue_scripts( $hook ) {
if ( 'settings_page_mt-cache-manager' !== $hook ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mt-cache-manager-admin.js', array( 'jquery' ), $this->version );

		$do_localize = array(
			'purge_confirm_string' => esc_html__( 'Purging entire cache is not recommended. Would you like to continue?', 'mt-cache-manager' ),
		);
		wp_localize_script( $this->plugin_name, 'mt_cache_manager', $do_localize );

	}
public function mt_cache_manager_admin_menu() {

		if ( is_multisite() ) {

			add_submenu_page(
				'settings.php',
				__( 'MT Cache Manager', 'mt-cache-manager' ),
				__( 'MT Cache Manager', 'mt-cache-manager' ),
				'manage_options',
				'mt-cache-manager',
				array( &$this, 'mt_cache_manager_setting_page' )
			);

		} else {

			add_submenu_page(
				'options-general.php',
				__( 'MT Cache Manager', 'mt-cache-manager' ),
				__( 'MT Cache Manager', 'mt-cache-manager' ),
				'manage_options',
				'mt-cache-manager',
				array( &$this, 'mt_cache_manager_setting_page' )
			);

		}

	}
public function mt_cache_manager_toolbar_purge_link( $wp_admin_bar ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( is_admin() ) {
			$mt_cache_manager_urls = 'all';
			$link_title        = __( 'Purge Cache', 'mt-cache-manager' );
		} else {
			$mt_cache_manager_urls = 'current-url';
			$link_title        = __( 'Purge Current Page', 'mt-cache-manager' );
		}

		$purge_url = add_query_arg(
			array(
				'mt_cache_manager_action' => 'purge',
				'mt_cache_manager_urls'   => $mt_cache_manager_urls,
			)
		);

		$nonced_url = wp_nonce_url( $purge_url, 'mt_cache_manager-purge_all' );

		$wp_admin_bar->add_menu(
			array(
				'id'    => 'mt-cache-manager-purge-all',
				'title' => $link_title,
				'href'  => $nonced_url,
				'meta'  => array( 'title' => $link_title ),
			)
		);

	}
public function mt_cache_manager_setting_page() {
		include plugin_dir_path( __FILE__ ) . 'partials/mt-cache-manager-admin-display.php';
	}
public function mt_cache_manager_default_settings() {

		return array(
			'enable_purge'                     => 0,
			'purge_homepage_on_edit'           => 1,
			'purge_homepage_on_del'            => 1,
			'purge_archive_on_edit'            => 1,
			'purge_archive_on_del'             => 1,
			'purge_archive_on_new_comment'     => 0,
			'purge_archive_on_deleted_comment' => 0,
			'purge_page_on_mod'                => 1,
			'purge_page_on_new_comment'        => 1,
			'purge_page_on_deleted_comment'    => 1,
		);

	}
public function mt_cache_manager_settings() {

		$options = get_site_option(
			'mt_cache_manager_options'
		);

		$data = wp_parse_args(
			$options,
			$this->mt_cache_manager_default_settings()
		);

		return $data;

	}
public function mt_cache_manager_settings_link( $links ) {

		if ( is_network_admin() ) {
			$setting_page = 'settings.php';
		} else {
			$setting_page = 'options-general.php';
		}

		$settings_link = '<a href="' . network_admin_url( $setting_page . '?page=mt-cache-manager' ) . '">' . __( 'Settings', 'mt-cache-manager' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;

	}
public function set_future_post_option_on_future_status( $new_status, $old_status, $post ) {

		global $blog_id, $nginx_purger;

		if ( ! $this->options['enable_purge'] ) {
			return;
		}

		$purge_status = array( 'publish', 'future' );

		if ( in_array( $old_status, $purge_status, true ) || in_array( $new_status, $purge_status, true ) ) {

			$nginx_purger->purge_post( $post->ID );

		}

		if (
			'future' === $new_status && $post && 'future' === $post->post_status &&
			(
				( 'post' === $post->post_type || 'page' === $post->post_type ) ||
				(
					isset( $this->options['custom_post_types_recognized'] ) &&
					in_array( $post->post_type, $this->options['custom_post_types_recognized'], true )
				)
			)
		) {

			$this->options['future_posts'][ $blog_id ][ $post->ID ] = strtotime( $post->post_date_gmt ) + 60;
			update_site_option( 'mt_cache_manager_options', $this->options );

		}

	}
public function unset_future_post_option_on_delete( $post_id ) {

		global $blog_id, $nginx_purger;

		if (
			! $this->options['enable_purge'] ||
			empty( $this->options['future_posts'] ) ||
			empty( $this->options['future_posts'][ $blog_id ] ) ||
			isset( $this->options['future_posts'][ $blog_id ][ $post_id ] ) ||
			wp_is_post_revision( $post_id )
		) {
			return;
		}

		unset( $this->options['future_posts'][ $blog_id ][ $post_id ] );

		if ( ! count( $this->options['future_posts'][ $blog_id ] ) ) {
			unset( $this->options['future_posts'][ $blog_id ] );
		}

		update_site_option( 'mt_cache_manager_options', $this->options );
	}
public function purge_all() {

		global $nginx_purger, $wp;

		$method = filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING );

		if ( 'POST' === $method ) {
			$action = filter_input( INPUT_POST, 'mt_cache_manager_action', FILTER_SANITIZE_STRING );
		} else {
			$action = filter_input( INPUT_GET, 'mt_cache_manager_action', FILTER_SANITIZE_STRING );
		}

		if ( empty( $action ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Sorry, you do not have the necessary privileges to edit these options.' );
		}

		if ( 'done' === $action ) {

			add_action( 'admin_notices', array( &$this, 'display_notices' ) );
			add_action( 'network_admin_notices', array( &$this, 'display_notices' ) );
			return;

		}

		check_admin_referer( 'mt_cache_manager-purge_all' );

		$current_url = user_trailingslashit( home_url( $wp->request ) );

		if ( ! is_admin() ) {
			$action       = 'purge_current_page';
			$redirect_url = $current_url;
		} else {
			$redirect_url = add_query_arg( array( 'mt_cache_manager_action' => 'done' ) );
		}

		switch ( $action ) {
			case 'purge':
				$nginx_purger->purge_all();
				break;
			case 'purge_current_page':
				$nginx_purger->purge_url( $current_url );
				break;
		}

		if ( 'purge' === $action ) {
			do_action( 'mt_cache_manager_after_purge_all' );

		}

		wp_redirect( esc_url_raw( $redirect_url ) );
		exit();

	}
public function display_notices() {
		echo '<div class="updated"><p>' . esc_html__( 'Purge initiated', 'mt-cache-manager' ) . '</p></div>';
	}

}
