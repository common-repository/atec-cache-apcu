<?php
if (!defined( 'ABSPATH' )) { exit; }
if (!class_exists('ATEC_wpc_tools')) require_once('atec-wpc-tools.php');

class ATEC_wpcu_settings { function __construct() {

$tools=new ATEC_wpc_tools();
require_once('atec-check.php');

global $atec_wpca_apcu_enabled;
$options=get_option('atec_WPCA_settings',[]);
$atec_wpca_pcache = $options['cache']??null==true;

atec_little_block('APCu '.__('Object Cache','atec-cache-apcu').' – '.__('Extension','atec-cache-apcu'));

echo '	
<div class="atec-g atec-g-50">
	<div>
    	<div class="atec-border-white">
    		<h4>APCu '.esc_attr__('Object Cache','atec-cache-apcu').' '; $tools->enabled($atec_wpca_apcu_enabled); echo '</h4><hr>';
			if ($atec_wpca_apcu_enabled)
			{    
				$apcu_cache=apcu_cache_info(true);
				if ($apcu_cache)
				{
					$total	= $apcu_cache['num_entries'];
					$size	= $apcu_cache['mem_size'];
					echo '<p style="padding: 4px;" class="atec-dilb atec-bg-white">', 
						esc_attr__('Current size is','atec-cache-apcu'), ' <strong>', esc_attr(size_format($size)),
						'</strong> (', esc_attr(number_format($total)), ' ', $total>1?esc_attr__('items','atec-cache-apcu'):esc_attr__('item','atec-cache-apcu'), 	').</p><br>';
					atec_success_msg(__('You now have a persistent WP object cache','atec-cache-apcu'));
					$tools->p(__('This is the main feature of the plugin and will speed up your site','atec-cache-apcu'));
				}
				else { $tools->error('',__('No object cache data available','atec-cache-apcu')); }
			}
			else { $tools->error('APCu',__('extension is NOT installed/enabled','atec-cache-apcu')); }
		echo '
		</div>
		<div class="atec-border-white">
		<h4>'.esc_attr__('APCu Page Cache','atec-cache-apcu').' '; $tools->enabled($atec_wpca_pcache); echo '</h4><hr>';
							
		if ($atec_wpca_apcu_enabled)
		{    
			$apcu_cache=apcu_cache_info(true);
			if ($apcu_cache)
			{
				$apcu_it=new APCUIterator('/atec_WPCA_p_/');
				if (!empty($apcu_it))
				{
					$c=0; $size=0;
					foreach ($apcu_it as $entry) 
					{ if (!str_contains($entry['key'],'_h')) { $c++; $size+=$entry['mem_size']; }
					}
					echo '<p style="padding: 4px;" class="atec-dilb atec-bg-white">', 
					esc_attr__('Current size is','atec-cache-apcu'), ' <strong>', esc_attr(size_format($size)),
					'</strong> (', esc_attr(number_format($c)), ' ', $c>1?esc_attr__('items','atec-cache-apcu'):esc_attr__('item','atec-cache-apcu'), 	').</p><br>';
				}
				else { $tools->error('',__('No page cache data available','atec-cache-apcu')); }
				echo '<p>'.esc_attr__('The page cache is an additional feature of this plugin','atec-cache-apcu').'.<br>'.esc_attr__('It will give your page an additonal boost, by delivering pages from APCu cache','atec-cache-apcu').'.</p>';
			}
		}
		else { $tools->error('',__('APCu not enabled – Page cache disabled','atec-cache-apcu').'.'); }
		echo '<p>', esc_attr__('The page cache saves pages, posts and categories – no product/shop pages (WooCommerce).','atec-cache-apcu'), '</p>';
		if (defined('LITESPEED_ALLOWED') && LITESPEED_ALLOWED) 
		{ 
			atec_badge('',__('LiteSpeed-server and -cache plugin detected','atec-cache-apcu'),false); 
			atec_badge(__('Please do not use LiteSpeed page-cache together with APCu page-cache – choose either one','atec-cache-apcu'),'',true); 
		}
		echo '
		</div>
	</div>';

	echo '
	<div>';				
		if (!$atec_wpca_pcache) { atec_reg_inline_style('apcu_settings_form', '.form-table TR, #enableAll { display:none; } .form-table TR:first-child { display:table-row; }'); }
		if ($atec_wpca_apcu_enabled)
		{
			echo '
			<div id="atec_WPCA_settings" class="atec-border-white">
				  <form method="post" action="options.php">
				  <input type="hidden" name="atec_WPCA_settings[salt]" value="', esc_attr($options['salt']??''), '">';
				$slug = 'atec_WPCA';
				  settings_fields($slug);
				  do_settings_sections($slug);
				  submit_button(__('Save','atec-cache-apcu'));
				echo '
				</form>
				<p class="atec-red">', esc_attr__('Do not use multiple page cache plugins simultaneously.','atec-cache-apcu'), '</p>';
				atec_help('show_debug',__('„Show debug“','atec-cache-apcu'));
				echo '
				<div id="show_debug_help" class="atec-help atec-dn">',
				esc_attr__('The „Show debug“ feature is for temporary use. It will show a small green circle in the upper left corner, when the page is served from cache. In addition you will find further details in your browser console. Please flush the page cache, once you are done with testing.','atec-cache-apcu');
				echo '
				</div>
			</div>';
		}
	echo '
	</div>
</div>';
}}

new ATEC_wpcu_settings();
?>