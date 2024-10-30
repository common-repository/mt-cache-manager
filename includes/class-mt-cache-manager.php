<?php
class MT_Cache_Manager {
protected $loader;
protected $plugin_name;
protected $version;
protected $minimum_wp;
public function __construct() {

		$this->plugin_name = 'mt-cache-manager';
		$this->version     = '1.1.0';
		$this->minimum_wp  = '5.7';

		if ( ! $this->required_wp_version() ) {
			return;
		}

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}
private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mt-cache-manager-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mt-cache-manager-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-purger.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mt-cache-manager-admin.php';
		$this->loader = new MT_Cache_Manager_Loader();

	}
private function set_locale() {

		$plugin_i18n = new MT_Cache_Manager_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}
private function define_admin_hooks() {

		global $mt_cache_manager_admin, $nginx_purger;

		$mt_cache_manager_admin = new MT_Cache_Manager_Admin( $this->get_plugin_name(), $this->get_version() );

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fastcgi-purger.php';
        $nginx_purger = new FastCGI_Purger();
		$this->loader->add_action( 'admin_enqueue_scripts', $mt_cache_manager_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $mt_cache_manager_admin, 'enqueue_scripts' );

		if ( is_multisite() ) {
			$this->loader->add_action( 'network_admin_menu', $mt_cache_manager_admin, 'mt_cache_manager_admin_menu' );
			$this->loader->add_filter( 'network_admin_plugin_action_links_' . MT_CACHE_MANAGER_BASENAME, $mt_cache_manager_admin, 'mt_cache_manager_settings_link' );
		} else {
			$this->loader->add_action( 'admin_menu', $mt_cache_manager_admin, 'mt_cache_manager_admin_menu' );
			$this->loader->add_filter( 'plugin_action_links_' . MT_CACHE_MANAGER_BASENAME, $mt_cache_manager_admin, 'mt_cache_manager_settings_link' );
		}

		if ( ! empty( $mt_cache_manager_admin->options['enable_purge'] ) ) {
			$this->loader->add_action( 'admin_bar_menu', $mt_cache_manager_admin, 'mt_cache_manager_toolbar_purge_link', 100 );
		}

		$this->loader->add_action( 'add_init', $mt_cache_manager_admin, 'update_map' );

		// Add actions to purge.
		$this->loader->add_action( 'wp_insert_comment', $nginx_purger, 'purge_post_on_comment', 200, 2 );
		$this->loader->add_action( 'transition_comment_status', $nginx_purger, 'purge_post_on_comment_change', 200, 3 );
		$this->loader->add_action( 'transition_post_status', $mt_cache_manager_admin, 'set_future_post_option_on_future_status', 20, 3 );
		$this->loader->add_action( 'delete_post', $mt_cache_manager_admin, 'unset_future_post_option_on_delete', 20, 1 );
		$this->loader->add_action( 'edit_attachment', $nginx_purger, 'purge_image_on_edit', 100, 1 );
		$this->loader->add_action( 'transition_post_status', $nginx_purger, 'purge_on_post_moved_to_trash', 20, 3 );
		$this->loader->add_action( 'edit_term', $nginx_purger, 'purge_on_term_taxonomy_edited', 20, 3 );
		$this->loader->add_action( 'delete_term', $nginx_purger, 'purge_on_term_taxonomy_edited', 20, 3 );
		$this->loader->add_action( 'check_ajax_referer', $nginx_purger, 'purge_on_check_ajax_referer', 20 );
		$this->loader->add_action( 'admin_bar_init', $mt_cache_manager_admin, 'purge_all' );

		// expose action to allow other plugins to purge the cache.
		$this->loader->add_action( 'mt_cache_manager_purge_all', $nginx_purger, 'purge_all' );
	}
public function run() {
		$this->loader->run();
	}
public function get_plugin_name() {
		return $this->plugin_name;
	}
public function get_loader() {
		return $this->loader;
	}
public function get_version() {
		return $this->version;
	}
public function required_wp_version() {

		global $wp_version;

		$wp_ok = version_compare( $wp_version, $this->minimum_wp, '>=' );

		if ( false === $wp_ok ) {

			add_action( 'admin_notices', array( &$this, 'display_notices' ) );
			add_action( 'network_admin_notices', array( &$this, 'display_notices' ) );
			return false;

		}

		return true;

	}
public function display_notices() {
		?>
	<div id="message" class="error">
		<p>
			<strong>
				<?php
				printf(
					esc_html__( 'Sorry, MT Cache Manager requires WordPress %s or higher', 'mt-cache-manager' ),
					esc_html( $this->minimum_wp )
				);
				?>
			</strong>
		</p>
	</div>
		<?php
	}
}
