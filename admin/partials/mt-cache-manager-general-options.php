<?php

global $mt_cache_manager_admin;

$args = array(
	'enable_purge'                     => FILTER_SANITIZE_STRING,
	'is_submit'                        => FILTER_SANITIZE_STRING,
	'purge_homepage_on_edit'           => FILTER_SANITIZE_STRING,
	'purge_homepage_on_del'            => FILTER_SANITIZE_STRING,
	'smart_http_expire_save'           => FILTER_SANITIZE_STRING,
	'purge_archive_on_edit'            => FILTER_SANITIZE_STRING,
	'purge_archive_on_del'             => FILTER_SANITIZE_STRING,
	'purge_archive_on_new_comment'     => FILTER_SANITIZE_STRING,
	'purge_archive_on_deleted_comment' => FILTER_SANITIZE_STRING,
	'purge_page_on_mod'                => FILTER_SANITIZE_STRING,
	'purge_page_on_new_comment'        => FILTER_SANITIZE_STRING,
	'purge_page_on_deleted_comment'    => FILTER_SANITIZE_STRING,
	'smart_http_expire_form_nonce'     => FILTER_SANITIZE_STRING,
);

$all_inputs = filter_input_array( INPUT_POST, $args );

if ( isset( $all_inputs['smart_http_expire_save'] ) && wp_verify_nonce( $all_inputs['smart_http_expire_form_nonce'], 'smart-http-expire-form-nonce' ) ) {
	unset( $all_inputs['smart_http_expire_save'] );
	unset( $all_inputs['is_submit'] );

	$nginx_settings = wp_parse_args(
		$all_inputs,
		$mt_cache_manager_admin->mt_cache_manager_default_settings()
	);
	update_site_option( 'mt_cache_manager_options', $nginx_settings );

	echo '<div class="updated"><p>' . esc_html__( 'Settings saved.', 'mt-cache-manager' ) . '</p></div>';

}

$mt_cache_manager_settings = $mt_cache_manager_admin->mt_cache_manager_settings();
?>

<form id="post_form" method="post" action="#" name="smart_http_expire_form" class="clearfix">
	<div>
		<div class="inside">
			<table class="form-table">
				<th scope="row">
					<?php esc_html_e( 'Purging Options', 'mt-cache-manager' ); ?>
				</th>
				<td>
				    <fieldset>
				        <legend class="screen-reader-text"><span><?php esc_html_e( 'Purging Options', 'mt-cache-manager' ); ?></span></legend>
				        <label for="enable_purge">
					        <input type="checkbox" value="1" id="enable_purge" name="enable_purge" <?php checked( $mt_cache_manager_settings['enable_purge'], 1 ); ?> />
					    <?php esc_html_e( 'Enable Purge', 'mt-cache-manager' ); ?></label>
					</fieldset>
				</td>
			</table>
		</div> <!-- End of .inside -->
	</div>

	<?php if ( ! ( ! is_network_admin() && is_multisite() ) ) { ?>
		<div class="enable_purge"<?php echo ( empty( $mt_cache_manager_settings['enable_purge'] ) ) ? ' style="display: none;"' : ''; ?>>

			<div class="inside">
				<table class="form-table mtcachemanager-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Purge Homepage:', 'mt-cache-manager' ); ?></th>
						<td>
								<fieldset>
								    <legend class="screen-reader-text">
								        <span><?php esc_html_e( 'Purge Homepage:', 'mt-cache-manager' ); ?></span>
								    </legend>
								        <label for="purge_homepage_on_edit"> 
									        <input type="checkbox" value="1" id="purge_homepage_on_edit" name="purge_homepage_on_edit" <?php checked( $mt_cache_manager_settings['purge_homepage_on_edit'], 1 ); ?> />
									        
									        <?php echo wp_kses( __( 'when a <strong>post</strong> (or page/custom post) is <strong>modified</strong> or <strong>added</strong>.', 'mt-cache-manager' ), array( 'strong' => array() )    ); ?>
									    </label>

								        <br />
								        <label for="purge_homepage_on_del">
								            <input type="checkbox" value="1" id="purge_homepage_on_del" name="purge_homepage_on_del" <?php checked( $mt_cache_manager_settings['purge_homepage_on_del'], 1 ); ?> />
								            
								            <?php
										        echo wp_kses(
											        __( 'when a <strong>published post</strong> (or page/custom post) is <strong>trashed</strong>', 'mt-cache-manager' ),
											            array( 'strong' => array() )
										               );
									        ?>
									   </label>
							</fieldset>
							
						</td>
					</tr>
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Purge Post/Page/Custom Post Type:', 'mt-cache-manager' ); ?>
						</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<span><?php esc_html_e( 'Purge Post/Page/Custom Post Type:', 'mt-cache-manager' ); ?></span>
								</legend>
								<label for="purge_page_on_mod">
									<input type="checkbox" value="1" id="purge_page_on_mod" name="purge_page_on_mod" <?php checked( $mt_cache_manager_settings['purge_page_on_mod'], 1 ); ?>>
									
									<?php
										echo wp_kses(
											__( 'when a <strong>post</strong> is <strong>published</strong>.', 'mt-cache-manager' ),
											array( 'strong' => array() )
										);
									?>
								</label>
								<br />
								<label for="purge_page_on_new_comment">
									<input type="checkbox" value="1" id="purge_page_on_new_comment" name="purge_page_on_new_comment" <?php checked( $mt_cache_manager_settings['purge_page_on_new_comment'], 1 ); ?>>
									
									<?php
										echo wp_kses(
											__( 'when a <strong>comment</strong> is <strong>approved/published</strong>.', 'mt-cache-manager' ),
											array( 'strong' => array() )
										);
									?>
								</label>
								<br />
								<label for="purge_page_on_deleted_comment">
									<input type="checkbox" value="1" id="purge_page_on_deleted_comment" name="purge_page_on_deleted_comment" <?php checked( $mt_cache_manager_settings['purge_page_on_deleted_comment'], 1 ); ?>>

									<?php
										echo wp_kses(
											__( 'when a <strong>comment</strong> is <strong>unapproved/deleted</strong>.', 'mt-cache-manager' ),
											array( 'strong' => array() )
										);
									?>
								</label>
								<br />
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row">
								<?php esc_html_e( 'Purge Archives:', 'mt-cache-manager' ); ?><br/>
							    <small><?php esc_html_e( '(date, category, tag, author, custom taxonomies)', 'mt-cache-manager' ); ?></small>
						</th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<span><?php esc_html_e( 'Purge Archives:', 'mt-cache-manager' ); ?></span>
								</legend>
								<label for="purge_archive_on_edit">
									<input type="checkbox" value="1" id="purge_archive_on_edit" name="purge_archive_on_edit" <?php checked( $mt_cache_manager_settings['purge_archive_on_edit'], 1 ); ?> />

									<?php
										echo wp_kses(
											__( 'when a <strong>post</strong> (or page/custom post) is <strong>modified</strong> or <strong>added</strong>.', 'mt-cache-manager' ),
											array( 'strong' => array() )
										);
									?>
								</label>
								<br />
								<label for="purge_archive_on_del">
									<input type="checkbox" value="1" id="purge_archive_on_del" name="purge_archive_on_del"<?php checked( $mt_cache_manager_settings['purge_archive_on_del'], 1 ); ?> />
									
									<?php
										echo wp_kses(
											__( 'when a <strong>published post</strong> (or page/custom post) is <strong>trashed</strong>.', 'mt-cache-manager' ),
											array( 'strong' => array() )
										);
									?>
								</label>
								<br />
							</fieldset>
						</td>
					</tr>
					<tr>
					    <th scope="row">
					        <?php esc_html_e( 'Purge Comments:', 'mt-cache-manager' ); ?>
					    </th>
					    <td>
							<fieldset>
								<legend class="screen-reader-text">
									<span><?php esc_html_e( 'Purge Comments:', 'mt-cache-manager' ); ?></span>
								</legend>
								<label for="purge_archive_on_new_comment">
									<input type="checkbox" value="1" id="purge_archive_on_new_comment" name="purge_archive_on_new_comment" <?php checked( $mt_cache_manager_settings['purge_archive_on_new_comment'], 1 ); ?> />

									<?php
										echo wp_kses(
											__( 'when a <strong>comment</strong> is <strong>approved/published</strong>.', 'mt-cache-manager' ),
											array( 'strong' => array() )
										);
									?>
								</label>
								<br />
								<label for="purge_archive_on_deleted_comment">
									<input type="checkbox" value="1" id="purge_archive_on_deleted_comment" name="purge_archive_on_deleted_comment" <?php checked( $mt_cache_manager_settings['purge_archive_on_deleted_comment'], 1 ); ?> />

									<?php
										echo wp_kses(
											__( 'when a <strong>comment</strong> is <strong>unapproved/deleted</strong>.', 'mt-cache-manager' ),
											array( 'strong' => array() )
										);
									?>
								</label>
								
							</fieldset>
						</td>
					</tr>
				</table>
			</div> <!-- End of .inside -->
		</div>
		<?php
	} // End of if.

	?>
    <input type="hidden" name="smart_http_expire_form_nonce" value="<?php echo wp_create_nonce('smart-http-expire-form-nonce'); ?>"/>
	<?php
		submit_button( __( 'Save All Changes', 'mt-cache-manager' ), 'primary', 'smart_http_expire_save', true );
	?>
</form><!-- End of #post_form -->
