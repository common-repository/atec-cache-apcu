<?php
if (!defined( 'ABSPATH' )) { exit; }

if (!defined('ATEC_INIT_INC')) require_once('atec-init.php');
add_action('admin_menu', function() 
{ 
	global $atec_wpca_apcu_enabled;
	atec_wp_menu(__DIR__,'atec_wpca','<span title="APCu extension is '.($atec_wpca_apcu_enabled?'enabled':'disabled').'">Cache APCu</span>'.($atec_wpca_apcu_enabled?'':'❗')); 
});

add_action('init', function() 
{ 
	if (!class_exists('ATEC_wp_memory')) require_once('atec-wp-memory.php');
	add_action('admin_bar_menu', 'atec_wp_memory_admin_bar', PHP_INT_MAX);

	atec_admin_debug('Cache APCu','wpca');

	$atec_query_args=add_query_arg(null,null);
	if (str_contains($atec_query_args, 'atec_wpca') || str_contains($atec_query_args, 'options.php')) require_once('atec-cache-apcu-register_settings.php');
  
  	if (in_array($slug=atec_get_slug(), ['atec_group','atec_wpca']))
	{
		if (!defined('ATEC_TOOLS_INC')) require_once('atec-tools.php');	
		add_action( 'admin_enqueue_scripts', function() { atec_reg_style('atec',__DIR__,'atec-style.min.css','1.0.002'); });

		if (!function_exists('atec_load_pll')) { require_once('atec-translation.php'); }
		atec_load_pll(__DIR__,'cache-apcu');		
		
		if ($slug!=='atec_group')
		{
			function atec_wpca(): void { require_once('atec-cache-apcu-dashboard.php'); }

			add_action( 'admin_enqueue_scripts', function() 
			{
				atec_reg_style('atec_check',__DIR__,'atec-check.min.css','1.0.001');
				atec_reg_script('atec_check',__DIR__,'atec-check.min.js','1.0.001');
				if (str_contains(add_query_arg(null,null), 'nav=Cache')) atec_reg_style('atec_cache_info',__DIR__,'atec-cache-info-style.min.css','1.0.001');
			});

			if (!defined('WP_APCU_KEY_SALT'))
			{
			  	global $atec_wpca_apcu_enabled;
			  	define('ATEC_CACHE_APCU_WARNING',!$atec_wpca_apcu_enabled?
			  	__('APCu extension is not enabled but it is required for this plugin to work. Once you have activated APCu, please reactivate this plugin to install the object cache.','atec-cache-apcu'):
			  	__('Can not find object-cache.php in your wp-content folder. Please deactivate/activate this plugin. Installing the object-cache.php is part of the activation process.','atec-cache-apcu'));
			  	atec_new_admin_notice('error','atec-cache-APCu: '.ATEC_CACHE_APCU_WARNING); 
			}
		}
	}
	if (defined('WP_APCU_KEY_SALT')) 
	{ 
		if (!defined('ATEC_APCU_OC_VERSION') || ATEC_APCU_OC_VERSION!=='1.0.8')
		{ atec_new_admin_notice('error','atec-cache-APCu: The object-cache.php is outdated, please deactivate & reactivate this plugin to update the file.'); }
		
		global $atec_wpca_apcu_enabled;
		if (!$atec_wpca_apcu_enabled) 
		{ atec_new_admin_notice('error','atec-cache-APCu: APCu was disabled, but object-cache.php is installed – please deactivate this plugin until APCu is re-enabled.'); }
		
		if (atec_wpca_settings('cache'))
		{
			function atec_wpca_admin_bar($wp_admin_bar): void
			{
				$link = get_admin_url().'admin.php?page=atec_wpca&flush=APCu_PCache&nav=Cache&_wpnonce='.esc_attr(wp_create_nonce('atec_wpca_nonce'));
				$style = 'vertical-align: bottom; margin:9px 4px 9px 0;';
					$args = array('id' => 'atec_wpca_admin_bar', 'title' => '
					<span title="'.__('Flush PCache','atec-cache-apcu').'" style="font-size:12px;">
						<img src="'. plugins_url( '/assets/img/atec_wpca_icon_admin.svg', __DIR__ ) .'" style="height:14px; '.esc_attr($style).'">Flush
					</span>', 'href' => $link );
				$wp_admin_bar->add_node($args);
			}
			add_action('admin_bar_menu', 'atec_wpca_admin_bar', PHP_INT_MAX);
		
			if (atec_wpca_settings('clear'))
			{
				require_once(__DIR__.'/atec-cache-apcu-pcache-tools.php');
				add_action( 'after_switch_theme', 'atec_wpca_delete_page_cache_all');
				add_action( 'activated_plugin', 'atec_wpca_delete_page_cache_all');
				add_action( 'deactivated_plugin', 'atec_wpca_delete_page_cache_all');
				add_action( 'wp_ajax_edit_theme_plugin_file', 'atec_wpca_delete_page_cache_all');				
	
				add_action('create_category', 'atec_wpca_delete_category_cache');
				add_action('delete_category', 'atec_wpca_delete_category_cache');
							
				add_action( 'created_term', 'atec_wpca_delete_tag_cache', 10, 3);
				add_action( 'delete_term', 'atec_wpca_delete_tag_cache', 10, 3);
			}
		}
		
	}

});
?>