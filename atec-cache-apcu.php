<?php
if (!defined('ABSPATH')) { exit; }

/**
* Plugin Name:  atec Cache APCu
* Plugin URI: https://atecplugins.com/
* Description: APCu object-cache and the only APCu based page-cache plugin available.
* Version: 2.0.10
* Requires at least: 5.2
* Tested up to: 6.6.3
* Requires PHP: 7.4
* Author: Chris Ahrweiler
* Author URI: https://atec-systems.com
* License: GPL2
* License URI:  https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:  atec-cache-apcu
*/

wp_cache_set('atec_wpca_version','2.0.10');

$atec_wpca_apcu_enabled=extension_loaded('apcu') && apcu_enabled();
$atec_wpca_settings=get_option('atec_WPCA_settings',[]);

function atec_wpca_settings($opt): bool { global $atec_wpca_settings; return $atec_wpca_settings[$opt]??null==true; }

if (is_admin()) 
{
	register_activation_hook( __FILE__, function() { require_once('includes/atec-wpca-activation.php'); });
	register_deactivation_hook( __FILE__, function() { require_once('includes/atec-wpca-deactivation.php'); });

	function atec_wpca_add_action_info ( $actions ) 
	{
		$links = array('<span style="color:red !important;">APCu extension required!</span>');
		return array_merge( $actions, $links );
	}
	
	if ($atec_wpca_apcu_enabled)
	{ 			
		function atec_wpca_admin_footer_function($content): string
		{
			$yes='dashicons dashicons-yes-alt'; 
			$style='padding-top: 5px; font-size: 16px; color:green;';
			$icon=plugin_dir_url( __FILE__ ) . 'assets/img/atec-group/atec_wpca_icon.svg';
			$content.=' | <sub><img alt="Plugin icon" src="'.esc_url($icon).'" style="height: 20px; vertical-align: bottom;"> APCu-OCache <span style="'.esc_html($style).'" class="'.esc_attr($yes).'"></span>';
			if (atec_wpca_settings('cache')) $content.=' APCu-PCache <span style="'.esc_html($style).'" class="'.esc_attr($yes).'"></span>';
			$content.='</sub>';
			return $content; 
		}
		add_action('admin_footer_text', 'atec_wpca_admin_footer_function');
	}
	else
	{
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'atec_wpca_add_action_info' );
	}

	if (!defined('ATEC_ADMIN_INC')) require_once('includes/atec-admin.php');
	add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'atec_plugin_settings' ); 
  
  	require_once(__DIR__.'/includes/atec-wpca-install.php');
}
else // not is_admin
{
	add_action('init', function() { if (defined('WP_APCU_KEY_SALT') && atec_wpca_settings('cache')) { require_once('includes/atec-cache-apcu-pcache.php'); } });
}

if (defined('WP_APCU_KEY_SALT'))
{
	if (atec_wpca_settings('cache')) { require_once('includes/atec-cache-apcu-pcache-cleanup.php'); }
};
?>