<?php
if (!defined( 'ABSPATH' )) { exit; }
if (!class_exists('ATEC_wpc_tools')) require_once('atec-wpc-tools.php');
if (!class_exists('ATEC_wp_memory')) require_once('atec-wp-memory.php');

class ATEC_wpcu_results { function __construct() {	

$tools=new ATEC_wpc_tools();
$mem_tools=new ATEC_wp_memory();

echo '
<div class="atec-page">';
	$mem_tools->memory_usage();
	atec_header(__DIR__,'wpca','Cache APCu');

	echo '
	<div class="atec-main">';

	global $wp_object_cache, $atec_wpca_apcu_enabled;
	
	$flush=atec_clean_request('flush');
	if ($flush!='')
	{
		echo '
		<div class="notice is-dismissible">
		<p>', esc_attr__('Flushing','atec-cache-apcu'), ' ', esc_html($flush),' ... ';
		atec_progress();
		$result=false;
		switch ($flush) 
		{
			case 'OPcache': $result=opcache_reset(); break;
	    	case 'APCu_Cache': if (function_exists('apcu_clear_cache')) $result=apcu_clear_cache(); break;
	    	case 'WP_Ocache': $result=$wp_object_cache->flush(); break;
			case 'APCu_PCache': atec_wpca_delete_page_cache_all(); $result=true; break;	    
		}
		echo $result?'<span class="atec-green">'.esc_attr__('successful','atec-cache-apcu').'</span>':'<span class="atec-red">'.esc_attr__('failed','atec-cache-apcu').'</span>';
		echo '.</p>
				</div>';
	}
	
	$url			= atec_get_url();
	$nonce		= wp_create_nonce(atec_nonce());
	$nav 		= atec_clean_request('nav');
	$action 	= atec_clean_request('action');

	if ($nav=='') $nav='Settings';	

	$atec_wpca_pcache 	= atec_wpca_settings('cache');

    $actions=array('#gear Settings','#box Cache','#server Server');       
	if ($atec_wpca_apcu_enabled && defined('WP_APCU_KEY_SALT')) 
	{
		$actions[]='#memory APCu';
		if ($atec_wpca_pcache) $actions=array_merge($actions,['Page cache']);
	}
	atec_nav_tab($url, $nonce, $nav, $actions,999,false);
	
	echo '<div class="atec-g atec-border">';
	atec_progress();

	if ($nav=='Info') { require_once('atec-info.php'); new ATEC_info(__DIR__); }
	elseif ($nav=='Settings') { require_once('atec-cache-apcu-settings.php'); }
	elseif ($nav=='Server') { require_once('atec-server-info.php'); }
	elseif ($nav=='APCu') { require_once('atec-cache-apcu-groups.php'); new ATEC_apcu_groups($url, $nonce, $tools,'atec_WPCA'); }
	elseif ($nav=='Page_cache') { require_once('atec-cache-apcu-pcache-stats.php'); new ATEC_wpcu_pcache($url, $nonce, $action,$tools); }
	elseif ($nav=='Cache')
	{

		atec_little_block('APCu & WP '.__('Object Cache','atec-cache-apcu'));
		$atec_wpca_key='atec_wpca_key';

		global $atec_wpca_apcu_enabled;
		$wp_enabled=is_object($wp_object_cache);
		
		$opcache_enabled=false; $op_status=false; $op_conf=false; $opcache_file_only=false;
		if (function_exists('opcache_get_configuration'))
		{ 
			$op_conf=opcache_get_configuration(); 
			$opcache_enabled=$op_conf['directives']['opcache.enable']; 
			if (function_exists('opcache_get_status')) $op_status=opcache_get_status();
			$opcache_file_only=$op_conf['directives']['opcache.file_cache_only'];
		}

		echo '<div class="atec-g atec-g-25">
		
				<div class="atec-border-white">
    				<h4>OPcache '; $tools->enabled($opcache_enabled);
		    		echo ($opcache_enabled?'<a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class="atec-right button" href="'.esc_url($url).'&flush=OPcache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span> '.esc_attr__('Flush','atec-cache-apcu').'</a>':''),
					'</h4><hr>';
    				if ($opcache_enabled) { require_once('atec-OPC-info.php'); new ATEC_OPcache_info($op_conf,$op_status,$opcache_file_only,$tools); }
					else $tools->p('OPcache '.esc_attr__('extension is NOT installed/enabled','atec-cache-apcu'));
					require_once('atec-OPC-help.php');
	    		echo '
	    		</div>
					
				<div class="atec-border-white">
    	    		<h4>WP '.esc_attr__('Object Cache','atec-cache-apcu').' '; $tools->enabled($wp_enabled);
		        		echo ($wp_enabled?' <a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class="atec-right button" id="WP_Ocache_flush" href="'.esc_url($url).'&flush=WP_Ocache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span> '.esc_attr__('Flush','atec-cache-apcu').'</a>':''),
					'</h4><hr>';
					if ($wp_enabled) { require_once('atec-WPC-info.php'); new ATEC_WPcache_info($op_conf,$op_status,$opcache_file_only,$tools); }
    			echo '
				</div>
				
				<div class="atec-border-white">
					<h4>APCu Cache'; $tools->enabled($atec_wpca_apcu_enabled);
					echo ($atec_wpca_apcu_enabled?'<a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class="atec-right button" id="APCu_flush" href="'.esc_url($url).'&flush=APCu_Cache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span> '.esc_attr__('Flush','atec-cache-apcu').'</a>':''),
					'</h4><hr>';
					 if ($atec_wpca_apcu_enabled) { require_once('atec-APCu-info.php'); new ATEC_APCu_info($tools); }
					else $tools->p('APCu '.esc_attr__('extension is NOT installed/enabled','atec-cache-apcu'));
				echo '
				</div>';
	    
				echo '    
					<div class="atec-border-white">
		    			<h4>APCu '.esc_attr__('Page Cache','atec-cache-apcu').' '; $tools->enabled($atec_wpca_pcache);
		    			echo ($atec_wpca_pcache?' <a title="'.esc_attr__('Empty cache','atec-cache-apcu').'" class="atec-right button" id="APCu_PCache_flush" href="'.esc_url($url).'&flush=APCu_PCache&nav=Cache&_wpnonce='.esc_attr($nonce).'"><span class="'.esc_attr(atec_dash_class('trash')).'"></span> '.esc_attr__('Flush','atec-cache-apcu').'</a>':''),
						'</h4><hr>';
						
						$c=0;
		    			if ($atec_wpca_apcu_enabled)
		    			{    
			    			$apcu_cache=apcu_cache_info(true);
			    			if ($apcu_cache)
			    			{
				    			$apcu_it=new APCUIterator();
				    			if (!empty($apcu_it))
				    			{
					    			$size=0;
									$reg_apcu = '/atec_WPCA_([\w\d]+)_([f|p|c|t]+)_([\d|\|]+)/';
									foreach ($apcu_it as $entry) 
									{							
										preg_match($reg_apcu, $entry['key'], $match);
										if (isset($match[3])) { $c++; $size+=$entry['mem_size']; } 
									}
                                    echo'<table class="atec-table atec-table-tiny atec-table-td-first">
										<tbody>
											<tr><td>'.esc_attr__('Items','atec-cache-apcu').':</td><td>',esc_attr(number_format($c)),'</td></tr>
											<tr><td>'.esc_attr__('Size','atec-cache-apcu').':</td><td>',esc_attr(size_format($size)),'</td></tr>
	    									</tbody>
									</table>';
				    			}
				    			else { $tools->error('',__('No page cache data available','atec-cache-apcu')); }
								if (!$atec_wpca_pcache) { atec_error_msg(__('Page cache is NOT active','atec-cache-apcu')); }
			    			}
		    			}
						else { atec_error_msg(__('Page cache requires APCu object cache','atec-cache-apcu')); }
						if ($c==0) atec_reg_inline_script('APCu_PCache_flush', 'jQuery("#APCu_PCache_flush").hide();', true);
	    			echo '
					</div>
				</div>';
	}
	
	echo '</div>
	</div>
</div>';

if (!class_exists('ATEC_footer')) require_once('atec-footer.php');

}}

new ATEC_wpcu_results();
?>