<?php
if (!defined( 'ABSPATH' )) { exit; }

function atec_wpca_settings_fields()
{ 

	$page_slug 		= 'atec_WPCA';
    $option_group 	= $page_slug.'_settings';
    $section			= $page_slug.'_section';
	$options			= get_option($option_group,[]);

	// ** flush the pcache if pcache settings change ** //
	if (str_contains(add_query_arg(null,null),'settings-updated=true')) 
	{
		if (get_option('atec_wpca_last_cache')!==($options['cache']??false)) 
		{ require_once('atec-cache-apcu-pcache-tools.php'); atec_wpca_delete_page_cache_all(); };
		if (($options['salt']??'')==='') 	{ $options['salt']=hash('crc32', get_bloginfo(), FALSE); update_option($option_group,$options); }

	}
	update_option('atec_wpca_last_cache', $options['cache']??false, false);

  	function optArr($opt) { return array('name'=>$opt, 'opt-name' => 'atec_WPCA_settings' ); };
	
  	register_setting($page_slug,$option_group);
	
  	add_settings_section($section,__('APCu Page Cache','atec-cache-apcu'),'',$page_slug);
	
  	$middot='&middot;&middot;&middot;&#187;&#187; ';
  	add_settings_field('cache', __('Enable page cache','atec-cache-apcu'), 'atec_checkbox', $page_slug, $section, optArr('cache'));
	  
	$section.='_options';
	add_settings_section($section,__('Page Cache Options','atec-cache-apcu'),'',$page_slug);

  	add_settings_field('debug', $middot.__('Show debug','atec-cache-apcu').'<br>
  	<span style="font-size:80%; color:#999;">'.__('Cache indicator and browser console log','atec-cache-apcu').'.</span>', 'atec_checkbox', $page_slug, $section, optArr('debug'));
	
  	add_settings_field('clear', $middot.__('Auto clear','atec-cache-apcu').'<br>
  	<span style="font-size:80%; color:#999;">'.__('Clear cache, after plugin & theme changes','atec-cache-apcu').'.</span>', 'atec_checkbox', $page_slug, $section, optArr('clear'));
}
add_action( 'admin_init',  'atec_wpca_settings_fields' );
?>