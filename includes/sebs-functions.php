<?php
/**
 * Queue updates for the Seb's Studio Updater
 */
if ( ! function_exists( 'sebs_studio_queue_update' ) ) {
	function sebs_studio_queue_update( $file, $file_id, $product_id ) {
		global $sebs_studio_queued_updates;

		if ( ! isset( $sebs_studio_queued_updates ) )
			$sebs_studio_queued_updates = array();

		$plugin             = new stdClass();
		$plugin->file       = $file;
		$plugin->file_id    = $file_id;
		$plugin->product_id = $product_id;

		$sebs_studio_queued_updates[] = $plugin;
	}
}

/**
 * Load installer for the Seb's Studio Updater.
 * @return $api Object
 */
if ( ! class_exists( 'Sebs_Studio_Updater' ) && ! function_exists( 'sebs_studio_updater_install' ) ) {
	function sebs_studio_updater_install( $api, $action, $args ) {
		$download_url = 'http://plugins.sebs-studio.com/sebs-studio-updater/sebs-studio-updater.zip';

		if ( 'plugin_information' != $action ||
			false !== $api ||
			! isset( $args->slug ) ||
			'sebs-studio-updater' != $args->slug
		) return $api;

		$api = new stdClass();
		$api->name = 'Sebs Studio Updater';
		$api->version = '1.0.0';
		$api->download_link = esc_url( $download_url );
		return $api;
	}

	add_filter( 'plugins_api', 'sebs_studio_updater_install', 10, 3 );
}

/**
 * Seb's Studio Updater Installation Prompts
 */
if ( ! class_exists( 'Sebs_Studio_Updater' ) && ! function_exists( 'sebs_studio_updater_notice' ) ) {

	/**
	 * Display a notice if the "Seb's Studio Updater" plugin hasn't been installed.
	 * @return void
	 */
	function sebs_studio_updater_notice() {
		$active_plugins = apply_filters( 'active_plugins', get_option('active_plugins' ) );
		if ( in_array( 'sebs-studio-updater/sebs-studio-updater.php', $active_plugins ) ) return;

		$slug = 'sebs-studio-updater';
		$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $slug ), 'install-plugin_' . $slug );
		$activate_url = 'plugins.php?action=activate&plugin=' . urlencode( 'sebs-studio-updater/sebs-studio-updater.php' ) . '&plugin_status=all&paged=1&s&_wpnonce=' . urlencode( wp_create_nonce( 'activate-plugin_sebs-studio-updater/sebs-studio-updater.php' ) );

		$message = '<a href="' . esc_url( $install_url ) . '">Install the Seb\'s Studio Updater plugin</a> to get updates for your Seb\'s Studio plugins.';
		$is_downloaded = false;
		$plugins = array_keys( get_plugins() );
		foreach ( $plugins as $plugin ) {
			if ( strpos( $plugin, 'sebs-studio-updater.php' ) !== false ) {
				$is_downloaded = true;
				$message = '<a href="' . esc_url( admin_url( $activate_url ) ) . '">Activate the Seb\'s Studio Updater plugin</a> to get updates for your Seb\'s Studio plugins.';
			}
		}
		echo '<div class="updated fade"><p>' . $message . '</p></div>' . "\n";
	}

	add_action( 'admin_notices', 'sebs_studio_updater_notice' );
}
?>