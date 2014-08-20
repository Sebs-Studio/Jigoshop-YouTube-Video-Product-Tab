<?php
/*
 * Plugin Name: Jigoshop YouTube Video Product Tab
 * Plugin URI: http://www.sebs-studio.com/wp-plugins/jigoshop-youtube-video-product-tab/
 * Description: Extends Jigoshop to allow you to add a YouTube Video to the Product page. Customise the player the way you want. An additional tab is added on the single products page to allow your customers to view the video you added. 
 * Version: 1.0
 * Author: Sebs Studio
 * Author URI: http://www.sebs-studio.com
 *
 * Text Domain: jigo_youtube_video_product_tab
 * Domain Path: /lang/
 * Language File Name: jigo_youtube_video_product_tab-'.$locale.'.mo
 *
 * Copyright 2013  Seb's Studio  (email : sebastien@sebs-studio.com)
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

// Required minimum version of WordPress.
if(!function_exists('jigo_youtube_video_tab_min_required')){
	function jigo_youtube_video_tab_min_required(){
		global $wp_version;
		$plugin = plugin_basename(__FILE__);
		$plugin_data = get_plugin_data(__FILE__, false);

		if(version_compare($wp_version, "3.3", "<")){
			if(is_plugin_active($plugin)){
				deactivate_plugins($plugin);
				wp_die("'".$plugin_data['Name']."' requires WordPress 3.3 or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress Admin</a>.");
			}
		}
	}
	add_action('admin_init', 'jigo_youtube_video_tab_min_required');
}

// Checks if the Jigoshop plugin is installed and active.
if(in_array('jigoshop/jigoshop.php', apply_filters('active_plugins', get_option('active_plugins')))){

	/* Localisation */
	$locale = apply_filters('plugin_locale', get_locale(), 'jigoshop-youtube-video-product-tab');
	load_textdomain('jigo_youtube_video_product_tab', WP_PLUGIN_DIR."/".plugin_basename(dirname(__FILE__)).'/lang/jigo_youtube_video_product_tab-'.$locale.'.mo');
	load_plugin_textdomain('jigo_youtube_video_product_tab', false, dirname(plugin_basename(__FILE__)).'/lang/');

	if(!class_exists('Jigoshop_YouTube_Video_Product_Tab')){
		class Jigoshop_YouTube_Video_Product_Tab{

			public static $plugin_prefix;
			public static $plugin_url;
			public static $plugin_path;
			public static $plugin_basefile;

			private $tab_data = false;

			/**
			 * Gets things started by adding an action to 
			 * initialize this plugin once Jigoshop is 
			 * known to be active and initialized.
			 */
			public function __construct(){
				Jigoshop_YouTube_Video_Product_Tab::$plugin_prefix = 'jigo_youtube_video_tab_';
				Jigoshop_YouTube_Video_Product_Tab::$plugin_basefile = plugin_basename(__FILE__);
				Jigoshop_YouTube_Video_Product_Tab::$plugin_url = plugin_dir_url(Jigoshop_YouTube_Video_Product_Tab::$plugin_basefile);
				Jigoshop_YouTube_Video_Product_Tab::$plugin_path = trailingslashit(dirname(__FILE__));
				add_action('init', array(&$this, 'jigoshop_init'), 0);
				// Settings
				add_action('init', array(&$this, 'install_settings'));
			}

			// Adds a settings page to control the youtube video in the tab.
			public function install_settings(){
				Jigoshop_Base::get_options()->install_external_options_tab(__('YouTube Video Product Tab', 'jigo_youtube_video_product_tab'), $this->youtube_video_tab_settings());
			}

			/** 
			 * Adds a tab section in the settings to control the youtube video tab.
			 */
			public function youtube_video_tab_settings(){
				$setting = array();

				// Checks if the JWPlayer plugin is installed and active.
				if(in_array('jw-player-plugin-for-wordpress/jwplayermodule.php', apply_filters('active_plugins', get_option('active_plugins')))){
					$setting[] = array(
														'name' => __('YouTube Video Product Tab', 'jigo_youtube_video_product_tab'),
														'type' => 'title',
														'desc' => __('If you have a license for JWPlayer, activate the official <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=JW Player for WordPress').'" target="_blank">JWPlayer</a> plugin and enter the <a href="'.admin_url('admin.php?page=jwp6_menu_licensing').'" target="_blank">license key.</a>', 'jigo_youtube_video_product_tab'),
														'id'   => 'youtube_video_product_tab'
												);
					$setting[] = array(
														'name' => __('Enable JWPlayer', 'jigo_youtube_video_product_tab'),
														'desc' => __('Enable the use of JWPlayer as the video player for your videos in the product tab.', 'jigo_youtube_video_product_tab'),
														'id'   => 'jigo_youtube_video_tab_custom_player',
														'type' => 'checkbox',
														'std'  => '',
												);
					$setting[] = array(
														'name' => __('Player Skin', 'jigo_youtube_video_product_tab'),
														'desc' 		=> __('Select the player skin you want to use. <small>Licensed version of JWPlayer Premium/Ads is required for additional premium skins to work!</small>', 'jigo_youtube_video_product_tab'),
														'id' 		=> 'jigo_youtube_video_tab_player_skin',
														'type' 		=> 'select',
														'choices'	=> array(
												    		            '' => 'Six '.__('(default)', 'jigo_youtube_video_product_tab'),
																						'custom-skin' => __('Custom Skin', 'jigo_youtube_video_product_tab'),
												            		    'beelden' => 'Beelden',
											  			              'bekle' => 'Bekle', 
											        			        'five' => 'Five', 
											              			  'glow' => 'Glow',
											                			'modieus' => 'Modieus',
											                			'roundster' => 'Roundster',
											                			'stormtrooper' => 'Stormtrooper',
									    		            			'vapor' => 'Vapor',
																					),
														'std'		=> '',
												);
					$setting[] =  array(
														'name' => __('Custom Skin Location', 'jigo_youtube_video_product_tab'),
														'desc' => __('Enter the url location of the custom skin. Upload your skin using the <a href="'.admin_url('media-new.php').'">media uploader</a>.', 'jigo_youtube_video_product_tab'),
														'desc_tip' => false,
														'id' => 'jigo_youtube_video_tab_player_skin_custom',
														'type' => 'text',
												);
					$setting[] = array(
													'type' => 'sectionend',
													'id'   => 'youtube_video_product_tab'
												);
				}
				else{
					$setting[] = array(
														'name' => __('YouTube Video Product Tab', 'jigo_youtube_video_product_tab'),
														'type' => 'title',
														'desc' => '',
														'id'   => 'youtube_video_product_tab'
												);
					$setting[] = array(
														'name' => __('Enable JWPlayer', 'jigo_youtube_video_product_tab'),
														'desc' => __('Enable the use of JWPlayer as the video player for your videos in the product tab.', 'jigo_youtube_video_product_tab'),
														'id'   => 'jigo_youtube_video_tab_custom_player',
														'type' => 'checkbox',
														'std'  => '',
												);
					$setting[] = array(
														'name' => __('Player Skin', 'jigo_youtube_video_product_tab'),
														'desc' 		=> __('Select the player skin you want to use. <small>If you have a licensed version of JWPlayer Premium/Ads edition, it will unlock more skins to choose!</small>', 'jigo_youtube_video_product_tab'),
														'id' 		=> 'jigo_youtube_video_tab_player_skin',
														'type' 		=> 'select',
														'choices'	=> array(
										    				            '' => 'Six '.__('(default)', 'jigo_youtube_video_product_tab'),
																						'custom-skin' => __('Custom Skin', 'jigo_youtube_video_product_tab'),
																				),
														'std'		=> '',
												);
					$setting[] = array(
													'name' => __('Custom Skin Location', 'jigo_youtube_video_product_tab'),
													'desc' => __('Enter the url location of the custom skin. Upload your skin using the <a href="'.admin_url('media-new.php').'">media uploader</a>.', 'jigo_youtube_video_product_tab'),
													'desc_tip' => false,
													'id' => 'jigo_youtube_video_tab_player_skin_custom',
													'type' => 'text',
												);
					$setting[] = array(
													'type' => 'sectionend',
													'id'   => 'youtube_video_product_tab'
												);
				}
				return $setting;
			}

			/**
			 * Init Jigoshop YouTube Video Product Tab extension once we know Jigoshop is active.
			 */
			public function jigoshop_init(){
				// backend stuff
				add_filter('plugin_row_meta', array(&$this, 'add_support_link'), 10, 2);
				add_action('admin_print_scripts', array(&$this, 'admin_script'));
				add_action('admin_enqueue_scripts', array(&$this, 'register_media_uploader'), 10, 1);
				// frontend stuff
				add_action('jigoshop_product_tabs', array(&$this, 'youtube_video_product_tabs'), 999);
				add_action('jigoshop_product_tab_panels', array(&$this, 'youtube_video_product_tabs_panel'), 999);
				// If the official JWPlayer plugin is installed and active then don't load the script in the header.
				if(!in_array('jw-player-plugin-for-wordpress/jwplayermodule.php', apply_filters('active_plugins', get_option('active_plugins')))){
					add_action('wp_enqueue_scripts', array(&$this, 'jigo_youtube_video_product_tab_scripts'));
				}
				// Write panel
				add_action('jigoshop_product_write_panel_tabs', array(&$this, 'write_youtube_video_tab'));
				add_action('jigoshop_product_write_panels', array(&$this, 'write_youtube_video_tab_panel'));
				add_action('jigoshop_process_product_meta', array(&$this, 'write_youtube_video_tab_panel_save'));
			}

			/**
			 * Add links to plugin page.
			 */
			public function add_support_link($links, $file){
				if(!current_user_can('install_plugins')){
					return $links;
				}
				if($file == Jigoshop_YouTube_Video_Product_Tab::$plugin_basefile){
					$links[] = '<a href="http://www.sebs-studio.com/forum/jigoshop-youtube-video-product-tab/" target="_blank">'.__('Support', 'jigo_youtube_video_product_tab').'</a>';
					$links[] = '<a href="http://www.sebs-studio.com/wp-plugins/jigoshop-extensions/" target="_blank">'.__('More Jigoshop Extensions', 'jigo_youtube_video_product_tab').'</a>';
				}
				return $links;
			}


			/* 
			 * Add javascript to the admin control panel.
			 */
			function admin_script(){
				global $post;
				$screen = get_current_screen();

				/* If we are adding or editing a product. */
				if($screen->id == 'product'){
					$post_id = $post->ID;
				}
				else{
					$post_id = '';
				}

				/* Localize the javascript. */
				$jigo_youtube_video_product_tab_translations = array(
																'vwidth' => __('Video Width', 'jigo_youtube_video_product_tab'),
																'vheight' => __('Video Height', 'jigo_youtube_video_product_tab'),
																'vskinloc' => __('Custom Skin Location', 'jigo_youtube_video_product_tab'),
																'vupload' => __('Upload', 'jigo_youtube_video_product_tab'),
																'insertURL' => __('Insert file URL', 'jigo_youtube_video_product_tab'),
																'skin_url_input_placeholder' => __('Enter the url location of the custom skin.', 'jigo_youtube_video_product_tab'),
																'post_id' => esc_attr($post_id),
				);
				wp_enqueue_script('jigoshop_youtube_video_product_tab', plugins_url('/assets/js/admin-product-edit.js', __FILE__), array('jquery'), '1.0');
				wp_localize_script('jigoshop_youtube_video_product_tab', 'youtube_video_product_tab', apply_filters('jigo_youtube_video_product_tab_translations', $jigo_youtube_video_product_tab_translations));
			}

			/* Register WordPress Media Manager/Uploader */
			function register_media_uploader($hook){
				if($hook == 'admin.php?page=jigoshop_settings&tab=catalog'){
					if(function_exists('wp_enqueue_media')){
						wp_enqueue_media();
					}
					else{
						wp_enqueue_style('thickbox');
						wp_enqueue_script('media-upload');
						wp_enqueue_script('thickbox');
					}
				}
			}

			/**
			 * Add javascript to the front.
			 */
			function jigo_youtube_video_product_tab_scripts(){
				if(get_post_type() == 'product'){
					wp_enqueue_script('jigoshop-youtube-video-product-tab', plugins_url('/assets/js/jwplayer.js', __FILE__), '', '1.0', false);
				}
			}

			/**
			 * Write the video tab on the product view page.
			 * In Jigoshop these are handled by templates.
			 */
			public function youtube_video_product_tabs($current_tab){
				global $post;

				if($this->product_has_youtube_tab($post)){
					foreach($this->tab_data as $tab){
					?>
					<li<?php if($current_tab == '#tab-youtube-video'){ echo ' class="active"'; } ?>><a href="#tab-youtube-video"><?php echo __('YouTube Video', 'jigo_youtube_video_product_tab'); ?></a></li>
					<?php
					}
				}
			}

			/**
			 * Write the video tab panel on the product view page.
			 * In Jigoshop these are handled by templates.
			 */
			public function youtube_video_product_tabs_panel(){
				global $post;

				$embed = new WP_Embed();

				if($this->product_has_youtube_tab($post)){
					foreach($this->tab_data as $tab){
						echo '<div class="panel" id="tab-youtube-video">';
						echo '<h2>'.$tab['title'].'</h2>';
						if(empty($tab['video_suggest'])){ $suggest = '?rel=0'; }else{ $suggest = ''; }
						if($tab['video_size'] == 'custom'){
							$width = $tab['video_width'];
							$height = $tab['video_height'];
						}
						if($tab['video_size'] == '560315'){
							$width = '560';
							$height = '315';
						}
						if($tab['video_size'] == '640360'){
							$width = '640';
							$height = '360';
						}
						if($tab['video_size'] == '583480'){
							$width = '583';
							$height = '480';
						}
						if($tab['video_size'] == '1280720'){
							$width = '1280';
							$height = '720';
						}
						$video_url = str_replace('watch?v=', 'embed/', $tab['video']);
						$embed_code = '<iframe width="'.$width.'" height="'.$height.'" src="'.$video_url.''.$suggest.'" frameborder="0" allowfullscreen></iframe>';
						if(!empty($tab['video_secure'])){ $embed_code = str_replace('http://', 'https://', $embed_code); }
						if(!empty($tab['video_enhanced'])){ $embed_code = str_replace('youtube.com', 'youtube-nocookie.com', $embed_code); }
						if(!empty($tab['video_rich_snippets'])){ // If rich snippet has been enabled.
							$find_thumb_url = array('http://', 'https://', 'www.', 'youtube.com', 'youtube-nocookie.com', 'watch?v=', '/');
							$video_thumb_default = str_replace($find_thumb_url, '', $tab['video']);
							$video_thumb_default = 'http://i4.ytimg.com/vi/'.$video_thumb_default.'/hqdefault.jpg';
						?>
						<div itemscope="video" itemscope itemtype="http://schema.org/VideoObject">
						<link itemprop="url" href="<?php echo get_permalink($post->ID); ?>">
						<meta itemprop="name" content="<?php if(get_post_meta($post->ID, '_yoast_wpseo_title', true)){ echo get_post_meta($post->ID, '_yoast_wpseo_title', true); }else{ echo get_the_title($post->ID); } ?>">
						<meta itemprop="description" content="<?php if(get_post_meta($post->ID, '_yoast_wpseo_metadesc', true)){ echo get_post_meta($post->ID, '_yoast_wpseo_metadesc', true); }else{ echo get_the_excerpt($post->ID); } ?>">
						<link itemprop="thumbnailUrl" href="<?php echo $video_thumb_default; ?>">
						<span itemprop="thumbnail" itemscope itemtype="http://schema.org/ImageObject">
							<link itemprop="url" href="<?php echo $video_thumb_default; ?>">
							<meta itemprop="width" content="480">
							<meta itemprop="height" content="360">
						</span>
						<?php $embed_url = str_replace('watch?v=', 'v/', $tab['video']); ?>
						<link itemprop="embedURL" href="<?php echo $embed_url; ?>?autohide=1&amp;version=3">
						<meta itemprop="playerType" content="Flash">
						<meta itemprop="width" content="<?php echo $width; ?>">
						<meta itemprop="height" content="<?php echo $height; ?>">
						<meta itemprop="isFamilyFriendly" content="<?php if(empty($tab['video_friendly']) || $tab['video_friendly'] == 'yes'){ echo 'True'; }else{ echo 'False'; } ?>">
						<?php
						} // end of video seo rich snippet.
						if(get_option('jigo_youtube_video_tab_custom_player') != 'no'){
							if(!empty($tab['video_secure'])){ $video_url = str_replace('http://', 'https://', $tab['video']); }
							$youtube_video_url = $video_url;
						?>
						<div id="jigo_youtube_video"><?php _e('Loading Video ...', 'jigo_youtube_video_product_tab'); ?></div>
						<script type="text/javascript">
						jwplayer('jigo_youtube_video').setup({
							flashplayer: "<?php echo plugins_url('/assets/swf/jwplayer.flash.swf', __FILE__); ?>",
							<?php
							$load_skin = '0'; // No player skin is loaded.
							if(get_option('jigo_youtube_video_tab_player_skin') != '' || !empty($tab['video_skin'])){
								if(!empty($tab['video_skin'])){ $video_skin = $tab['video_skin']; }
								else{ $video_skin = get_option('jigo_youtube_video_tab_player_skin'); }
								// Checks if custom skin was selected, is so load custom skin.
								if($video_skin == 'custom-skin'){ if(!empty($tab['video_skin_custom'])){ $video_skin = $tab['video_skin_custom']; }
								else{ $video_skin = get_option('jigo_youtube_video_tab_player_skin_custom'); } }
								// Load skin if any selected.
								if($video_skin != ''){ if($tab['video_skin_custom'] != ''){ $load_skin = '1'; } }
								if($load_skin == '1'){ // Player skin is loaded.
							?>
							skin: "<?php if($tab['video_skin'] == 'custom-skin'){ echo $tab['video_skin_custom']; }else{ echo $video_skin; } ?>",
							<?php
								} // end if $load_skin equals one.
							} // end if skin is not empty.
							?>
							file: "<?php echo $youtube_video_url; ?>",
							width: <?php echo $width; ?>,
							height: <?php echo $height; ?>
						});
						</script>
						<?php
						}
						else{
							echo $embed->autoembed(apply_filters('jigoshop_youtube_video_product_tab', $embed_code, $tab['id']));
						}
						if(!empty($tab['video_rich_snippets'])){ echo '</div>'; }
						echo '</div>';
					}
				}
			}

			/**
			 * Lazy-load the product_tabs meta data, and return true if it exists,
			 * false otherwise.
			 * 
			 * @return true if there is video tab data, false otherwise.
			 */
			private function product_has_youtube_tab($post){
				if($this->tab_data === false){
					$this->tab_data = maybe_unserialize(get_post_meta($post->ID, 'jigo_youtube_video_product_tab', true));
				}
				// tab must at least have a embed code inserted.
				return !empty($this->tab_data) && !empty($this->tab_data[0]) && !empty($this->tab_data[0]['video']);
			}

			/**
			 * Adds a new tab to the Product Data postbox in the admin product interface.
			 */
			public function write_youtube_video_tab(){
				$tab_icon = Jigoshop_YouTube_Video_Product_Tab::$plugin_url.'assets/img/play.png';
				?>
				<style type="text/css">
				#jigoshop-product-data ul.product_data_tabs li.youtube_video_tab a { padding:10px 8px 10px 32px; line-height:8px; text-shadow:0 1px 1px #fff; color:#555555; background-image:url('<?php echo $tab_icon; ?>'); background-repeat:no-repeat; background-position:9px 6px; }
				p.form-field._tab_youtube_video_width_field, 
				p.form-field._tab_youtube_video_height_field { float: left; margin-top: 2px; }
				p.form-field._tab_youtube_video_height_field { clear: right; }
				p.form-field._tab_youtube_video_width_field label, 
				p.form-field._tab_youtube_video_height_field label { width: 80px; }
				p.form-field._tab_youtube_video_skin_field, 
				p.form-field._tab_youtube_video_suggestions_field { clear: left; }
				p.form-field._tab_youtube_video_skin_custom_field { display: none; }
				</style>
				<li class="youtube_video_tab"><a href="#youtube_video_tab"><?php echo __('YouTube Video', 'jigo_youtube_video_product_tab'); ?></a></li>
				<?php
			}

			/**
			 * Adds the panel to the Product Data postbox in the product interface
			 */
			public function write_youtube_video_tab_panel(){
				global $post;

				// Pull the video tab data out of the database
				$tab_data = maybe_unserialize(get_post_meta($post->ID, 'jigo_youtube_video_product_tab', true));

				if(empty($tab_data)){
					$tab_data[] = array('title' => '', 'video' => '', 'video_size' => '', 'video_width' => '', 'video_height' => '', 'video_skin' => '', 'video_suggest' => '', 'video_secure' => '', 'video_enhanced' => '', 'video_rich_snippets' => '');
				}

				// Display the video tab panel
				foreach($tab_data as $tab){
					echo '<div id="youtube_video_tab" class="panel jigoshop_options_panel" style="display:none;">';
					echo '<fieldset>';
					$this->jigo_youtube_video_product_tab_text_input(
										array(
											'id' => '_tab_youtube_video_title', 
											'label' => __('Video Title', 'jigo_youtube_video_product_tab'), 
											'placeholder' => __('Enter your title here.', 'jigo_youtube_video_product_tab'), 
											'value' => $tab['title'], 
											'style' => 'width:70%;',
										)
					);
					$this->jigo_youtube_video_product_tab_text_input(
										array(
											'id' => '_tab_youtube_video_url', 
											'label' => __('Video URL', 'jigo_youtube_video_product_tab'), 
											'placeholder' => 'http://www.youtube.com/watch?v=yhz4A5BCMAA', 
											'value' => $tab['video'], 
											'style' => 'width:70%;',
										)
					);
					$this->jigo_youtube_video_product_tab_select(
										array(
											'id' => '_tab_youtube_video_size', 
											'label' => __('Video Size', 'jigo_youtube_video_product_tab'), 
											'options' => array(
																			'560315' => __('560 x 315', 'jigo_youtube_video_product_tab'),
																			'640360' => __('640 x 360', 'jigo_youtube_video_product_tab'),
																			'853480' => __('853 x 480', 'jigo_youtube_video_product_tab'),
																			'1280720' => __('1280 x 720', 'jigo_youtube_video_product_tab'),
																			'custom' => __('Custom Size', 'jigo_youtube_video_product_tab')
											),
											'value' => $tab['video_size'],
											'class' => 'select'
										)
					);

					if($tab_data[0]['video_size'] == 'custom'){
						$this->jigo_youtube_video_product_tab_text_input(
																	array(
																		'id' => '_tab_youtube_video_width', 
																		'label' => __('Video Width', 'jigo_youtube_video_product_tab'),  
																		'value' => $tab['video_width'], 
																		'style' => 'width:60px;'
																	)
						);
						$this->jigo_youtube_video_product_tab_text_input(
																	array(
																		'id' => '_tab_youtube_video_height', 
																		'label' => __('Video Height', 'jigo_youtube_video_product_tab'),  
																		'value' => $tab['video_height'], 
																		'style' => 'width:60px;'
																	)
						);
					}

					// Checks if the JWPlayer plugin is installed and active.
					if(in_array('jw-player-plugin-for-wordpress/jwplayermodule.php', apply_filters('active_plugins', get_option('active_plugins')))){

						$this->jigo_youtube_video_product_tab_select(
											array(
												'id' => '_tab_youtube_video_skin', 
												'label' => __('Player Skin', 'jigo_youtube_video_product_tab'), 
												'options' => array(
										    		            '' => 'Six '.__('(default)', 'jigo_youtube_video_product_tab'),
										    		            'custom-skin' => __('Custom Skin', 'jigo_youtube_video_product_tab'),
										            		    'beelden' => 'Beelden',
												                'bekle' => 'Bekle', 
												                'five' => 'Five', 
												                'glow' => 'Glow',
												                'modieus' => 'Modieus',
											    	            'roundster' => 'Roundster',
											        	        'stormtrooper' => 'Stormtrooper',
								    			        	    'vapor' => 'Vapor',
												),
												'description' => __('This overides the player skin selected in the <a href="'.admin_url('admin.php?page=jigoshop_settings&tab=catalog').'" target="_blank">settings</a>.', 'jigo_youtube_video_product_tab'),
												'desc_tip' => __('Player skin only applies if you have enabled the JWPlayer in the <a href="'.admin_url('admin.php?page=jigoshop_settings&tab=youtube-video-product-tab').'" target="_blank">settings</a>.', 'jigo_youtube_video_product_tab'),
												'value' => $tab['video_skin'],
												'class' => 'select'
											)
						);

					}
					else{

						$this->jigo_youtube_video_product_tab_select(
											array(
												'id' => '_tab_youtube_video_skin', 
												'label' => __('Player Skin', 'jigo_youtube_video_product_tab'), 
												'options' => array(
																				'' => 'Six '.__('(default)', 'jigo_youtube_video_product_tab'),
																				'custom-skin' => __('Custom Skin', 'jigo_youtube_video_product_tab'),
												),
												'description' => __('Player skin only applies if you have enabled the JWPlayer in the <a href="'.admin_url('admin.php?page=jigoshop_settings&tab=youtube-video-product-tab').'" target="_blank">settings</a>.', 'jigo_youtube_video_product_tab'),
												'desc_tip' => __('This overides the player skin selected in the settings.', 'jigo_youtube_video_product_tab'),
												'value' => $tab['video_skin'],
												'class' => 'select'
											)
						);

					}

					$this->jigo_youtube_video_product_tab_input_upload(
										array(
											'id' => '_tab_youtube_video_skin_custom', 
											'label' => __('Custom Skin Location', 'jigo_youtube_video_product_tab'), 
											'placeholder' => __('Enter the url location of the custom skin.', 'jigo_youtube_video_product_tab'), 
											'value' => $tab['video_skin_custom'], 
											'style' => 'width:50%;',
											'upload' => __('Upload', 'jigo_youtube_video_product_tab'),
										)
					);

					if(!empty($tab['video_suggest'])){ $suggest_check = true; }else{ $suggest_check = false; }
					if(!empty($tab['video_secure'])){ $secure_check = true; }else{ $secure_check = false; }
					if(!empty($tab['video_enhanced'])){ $enhanced_check = true; }else{ $enhanced_check = false; }
					if(!empty($tab['video_rich_snippets'])){ $snippets_check = true; }else{ $snippets_check = false; }

					echo jigoshop_form::checkbox('_tab_youtube_video_suggestions', __('Suggested videos', 'jigo_youtube_video_product_tab'), $suggest_check, __('Show suggested videos when the video is finished playing. - YouTube Player Only', 'jigo_youtube_video_product_tab'));

					echo jigoshop_form::checkbox('_tab_youtube_video_https', __('Secure connection', 'jigo_youtube_video_product_tab'), $secure_check, __('Use HTTPS', 'jigo_youtube_video_product_tab'));

					echo jigoshop_form::checkbox('_tab_youtube_video_privacy_enhanced', __('No tracking', 'jigo_youtube_video_product_tab'), $enhanced_check, __('Enable privacy-enhanced mode. - YouTube Player Only', 'jigo_youtube_video_product_tab'));

					echo jigoshop_form::checkbox('_tab_youtube_video_rich_snippets', __('Rich Snippets', 'jigo_youtube_video_product_tab'), $snippets_check, __('Enable video rich snippets for better product search results in search engines.', 'jigo_youtube_video_product_tab'));

					if(!empty($tab_data[0]['video_rich_snippets'])){

						$this->jigo_youtube_video_product_tab_select(
											array(
												'id' => '_tab_youtube_video_friendly', 
												'label' => __('Family Friendly', 'jigo_youtube_video_product_tab'), 
												'description' => __('If the video is not family friendly, select "No"', 'jigo_youtube_video_product_tab'), 
												'options' => array(
																				'yes' => __('Yes', 'jigo_youtube_video_product_tab'), 
																				'no' => __('No', 'jigo_youtube_video_product_tab'), 
												),
												'value' => $tab['video_friendly'],
												'class' => 'select'
											)
						);

					}
					echo '</fieldset>';
					echo '</div>';
				}
			}

			/**
			 * Output a text input box with a upload button to load the media manager.
			 */
			public function jigo_youtube_video_product_tab_input_upload($field){
				global $thepostid, $post, $jigoshop;

				$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
				$field['upload']        = isset( $field['upload'] ) ? $field['upload'] : '';
				$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
				$field['class']         = isset( $field['class'] ) ? $field['class'] : 'file_path';
				$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : ' ';
				$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
				$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
				$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';

				echo '<p class="form-field '.esc_attr($field['id']).'_field'.esc_attr($field['wrapper_class']).'"><label for="'.esc_attr($field['id']).'">'.wp_kses_post($field['label']).'</label><input type="'.esc_attr($field['type']).'" class="'.esc_attr($field['class']).'" name="'.esc_attr($field['name']).'" id="'.esc_attr($field['id']).'" value="'.esc_attr($field['value']).'" placeholder="'.esc_attr($field['placeholder']).'"'.(isset($field['style']) ? ' style="'.$field['style'].'"' : '').' /> <input type="button" class="upload_skin button" data-postid="'.esc_attr($post->ID).'" data-choose="'.esc_attr($field['upload']).'" data-update="'.__('Insert file URL', 'jigo_youtube_video_product_tab').'" value="'.esc_attr($field['upload']).'" />';

				if(!empty($field['desc_tip'])){
					if(!empty($field['description'])){ echo '<span class="description">'.wp_kses_post($field['description']).'</span>'; }
					echo '<img class="help_tip" data-tip="'.esc_attr($field['desc_tip']).'" src="'.Jigoshop_YouTube_Video_Product_Tab::$plugin_url.'assets/img/help.png" height="16" width="16" />';
				}
				else{
					if(!empty($field['description'])){ echo '<span class="description">'.wp_kses_post($field['description']).'</span>'; }
				}
				echo '</p>';
			}

			/**
			 * Output a text input box.
			 */
			public function jigo_youtube_video_product_tab_text_input($field){
				global $thepostid, $post, $jigoshop;

				$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
				$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
				$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
				$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
				$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
				$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
				$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';

				echo '<p class="form-field '.esc_attr($field['id']).'_field '.esc_attr($field['wrapper_class']).'"><label for="'.esc_attr($field['id']).'">'.wp_kses_post($field['label']).'</label><input type="'.esc_attr($field['type']).'" class="'.esc_attr($field['class']).'" name="'.esc_attr($field['name']).'" id="'.esc_attr($field['id']).'" value="'.esc_attr($field['value']).'" placeholder="'.esc_attr($field['placeholder']).'"'.(isset($field['style']) ? ' style="'.$field['style'].'"' : '').' /> ';

				if(!empty($field['desc_tip'])){
					if(!empty($field['description'])){ echo '<span class="description">'.wp_kses_post($field['description']).'</span>'; }
					echo '<img class="help_tip" data-tip="'.esc_attr($field['desc_tip']).'" src="'.Jigoshop_YouTube_Video_Product_Tab::$plugin_url.'assets/img/help.png" height="16" width="16" />';
				}
				else{
					if(!empty($field['description'])){ echo '<span class="description">'.wp_kses_post($field['description']).'</span>'; }
				}
				echo '</p>';
			}

			/**
			 * Output a select input box.
			 */
			public function jigo_youtube_video_product_tab_select($field){
				global $thepostid, $post, $jigoshop;

				$thepostid 				      = empty( $thepostid ) ? $post->ID : $thepostid;
				$field['class'] 		    = isset( $field['class'] ) ? $field['class'] : 'select short';
				$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
				$field['value'] 		    = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );

				echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['id'] ) . '" class="' . esc_attr( $field['class'] ) . '">';

				foreach($field['options'] as $key => $value){
					echo '<option value="'.esc_attr($key).'" '.selected(esc_attr($field['value']), esc_attr($key), false).'>'.esc_html($value).'</option>';
				}
				echo '</select> ';

				if(!empty($field['desc_tip'])){
					if(!empty($field['description'])){ echo '<span class="description">'.wp_kses_post($field['description']).'</span>'; }
					echo '<img class="help_tip" data-tip="'.esc_attr($field['desc_tip']).'" src="'.Jigoshop_YouTube_Video_Product_Tab::$plugin_url.'assets/img/help.png" height="16" width="16" />';
				}
				else{
					if(!empty($field['description'])){ echo '<span class="description">'.wp_kses_post($field['description']).'</span>'; }
				}
				echo '</p>';
			}

			/**
			 * Saves the data inputed into the product boxes, as post meta data
			 * identified by the name 'jigo_youtube_video_product_tab'
			 * 
			 * @param int $post_id the post (product) identifier
			 * @param stdClass $post the post (product)
			 */
			public function write_youtube_video_tab_panel_save($post_id){
				$tab_title = stripslashes($_POST['_tab_youtube_video_title']);
				if($tab_title == ''){
					$tab_title = __('Video', 'jigo_youtube_video_product_tab');
				}
				$tab_video = stripslashes($_POST['_tab_youtube_video_url']);
				$tab_video_size = $_POST['_tab_youtube_video_size'];
				$tab_video_width = $_POST['_tab_youtube_video_width'];
				$tab_video_height = $_POST['_tab_youtube_video_height'];
				$tab_video_skin = $_POST['_tab_youtube_video_skin'];
				$tab_video_skin_custom = $_POST['_tab_youtube_video_skin_custom'];
				$tab_video_suggest = isset($_POST['_tab_youtube_video_suggestions']);
				$tab_video_secure = isset($_POST['_tab_youtube_video_https']);
				$tab_video_enhanced = isset($_POST['_tab_youtube_video_privacy_enhanced']);
				$tab_video_rich_snippets = isset($_POST['_tab_youtube_video_rich_snippets']);
				$tab_video_friendly = $_POST['_tab_youtube_video_friendly'];

				if(empty($tab_video) && get_post_meta($post_id, 'jigo_youtube_video_product_tab', true)){
					// clean up if the video tabs are removed
					delete_post_meta($post_id, 'jigo_youtube_video_product_tab');
				}
				elseif(!empty($tab_video)){
					$tab_data = array();

					$tab_id = '';
					// convert the tab title into an id string
					$tab_id = strtolower($tab_title);
					$tab_id = preg_replace("/[^\w\s]/", '', $tab_id); // remove non-alphas, numbers, underscores or whitespace 
					$tab_id = preg_replace("/_+/", ' ', $tab_id); // replace all underscores with single spaces
					$tab_id = preg_replace("/\s+/", '-', $tab_id); // replace all multiple spaces with single dashes
					$tab_id = 'tab-'.$tab_id; // prepend with 'tab-' string

					// save the data to the database
					$tab_data[] = array(
									'title' => $tab_title, 
									'id' => $tab_id, 
									'video' => $tab_video,
									'video_size' => $tab_video_size,
									'video_width' => $tab_video_width,
									'video_height' => $tab_video_height,
									'video_skin' => $tab_video_skin,
									'video_skin_custom' => $tab_video_skin_custom,
									'video_suggest' => $tab_video_suggest,
									'video_secure' => $tab_video_secure,
									'video_enhanced' => $tab_video_enhanced,
									'video_rich_snippets' => $tab_video_rich_snippets,
									'video_friendly' => $tab_video_friendly,
					);
					update_post_meta($post_id, 'jigo_youtube_video_product_tab', $tab_data);
				}
			}
		}
	}

	/* 
	 * Instantiate plugin class and add it to the set of globals.
	 */
	$jigoshop_youtube_video_tab = new Jigoshop_YouTube_Video_Product_Tab();
}
else{
	add_action('admin_notices', 'jigo_youtube_video_tab_error_notice');
	function jigo_youtube_video_tab_error_notice(){
		global $current_screen;
		if($current_screen->parent_base == 'plugins'){
			echo '<div class="error"><p>Jigoshop YouTube Video Product Tab '.__('requires <a href="http://www.jigoshop.com" target="_blank">Jigoshop</a> to be activated in order to work. Please install and activate <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=Jigoshop').'" target="_blank">Jigoshop</a> first.', 'jigo_youtube_video_product_tab').'</p></div>';
		}
	}
}
?>
