<?php
if (!defined( 'ABSPATH' )) { exit; }

class ATEC_apcu_groups { 

	function __construct($url, $nonce, $wpc_tools, $prefix) {

	atec_little_block('Persistent WP-APCu-'.__('Object-Cache','atec-cache-apcu').' entries');

	echo '
	<div class="atec-g"><div>';

		if (!defined('WP_APCU_KEY_SALT')) define('WP_APCU_KEY_SALT','WHATEVER');
		$apcu_cache=apcu_cache_info(true);
		if ($apcu_cache)
		{
			$c=0; $total=0;
			$apcu_it=new APCUIterator('/'.WP_APCU_KEY_SALT.'/');
			if (iterator_count($apcu_it)!==0)
			{
				atec_table_header_tiny(['#',__('Key','atec-cache-apcu'),__('Hits','atec-cache-apcu'),__('Size','atec-cache-apcu'),__('Value','atec-cache-apcu')]);
				foreach ($apcu_it as $entry) 
				{
					$c++;
					echo '<tr>
							<td class="atec-nowrap">', esc_attr($c), '</td>
							<td class="atec-anywrap">';
							echo esc_attr(str_replace(WP_APCU_KEY_SALT,'*',$entry['key']));
						echo '</td>
							<td class="atec-nowrap">', esc_html($entry['num_hits']), '</td>
							<td class="atec-nowrap">', esc_html(size_format($entry['mem_size'])), '</td>
							<td class="atec-anywrap">', esc_html(htmlentities(substr(serialize($entry['value']),0,128))), '</td>
						</tr>';
                    $total+=$entry['mem_size'];
				}
				atec_TR_empty();
				echo '<tr class="atec-table-tr-bold"><td>', esc_attr($c), '</td><td></td><td></td><td class="atec-nowrap">', esc_html(size_format($total)), '</td><td></td></tr>
				</tbody></table>';				
			}
			else { atec_error_msg(__('WP-APCu-Cache is empty','atec-cache-apcu')); echo '<br><br>'; }

			$c=0; $total=0;
			$apcu_it=new APCUIterator('/^(?!'.WP_APCU_KEY_SALT.').*$/');
			if (iterator_count($apcu_it)!==0)
			{
				atec_little_block('Other persistent APCu-'.__('Object-Cache','atec-cache-apcu').' entries');
				atec_table_header_tiny(['#',__('Key','atec-cache-apcu'),__('Hits','atec-cache-apcu'),__('Size','atec-cache-apcu'),__('Value','atec-cache-apcu')]);
				$salt=get_option($prefix.'_settings',[])['salt']??'';
				$reg_apcu = '/'.$prefix.'_'.$salt.'.*/';
				$atec_wpca_page_cache_found=false;
				foreach ($apcu_it as $entry) 
				{
					$c++;
					preg_match($reg_apcu, $entry['key'], $match);
					if (isset($match[0])) $atec_wpca_page_cache_found=true;
					echo '<tr>
							<td class="atec-nowrap">', esc_attr($c), '</td>
							<td class="atec-anywrap ', (isset($match[0])?'atec-blue':'') ,'">';
							echo esc_attr($entry['key']);
						echo '</td>
							<td class="atec-nowrap">', esc_html($entry['num_hits']), '</td>
							<td class="atec-nowrap">', esc_html(size_format($entry['mem_size'])), '</td>
							<td class="atec-anywrap">', esc_html(htmlentities(substr(serialize($entry['value']),0,128))), '</td>
						</tr>';
                    $total+=$entry['mem_size'];
				}
				atec_TR_empty();
				echo '<tr class="atec-table-tr-bold"><td>', esc_attr($c), '</td><td></td><td></td><td class="atec-nowrap">', esc_html(size_format($total)), '</td><td></td></tr>
				</tbody></table>';
				
				if ($atec_wpca_page_cache_found) { atec_nav_button($url,$nonce,'delete_file','APCu',__('Delete PCache items','atec-cache-apcu').' (APCu)',false,true); }
			}
		}
		else $wpc_tools->error('APCu',__('cache data could NOT be retrieved','atec-cache-apcu'));
		
	echo '
	</div></div>';

}}

?>