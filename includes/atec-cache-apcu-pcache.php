<?php
if (!defined( 'ABSPATH' )) { exit; }

function atec_wpca_page_buffer_start(): void
{ 	 
	@header('X-Cache-Enabled: true');
	// @codingStandardsIgnoreStart
	if (!empty($_POST)) { @header('X-Cache: FAIL-$_POST '.count($_POST)); return; }
	// @codingStandardsIgnoreEnd
	if (is_user_logged_in()) { @header('X-Cache: FAIL-LOGGED_IN'); return; }

	if (($isCat	= is_category()) || ($isTag	= is_tag())) $id=get_queried_object()->term_id;
	else
	{
		$id		= get_the_ID();
		$type 	= get_post_type($id);
		if (!in_array($type,['page','post'])) { @header('X-Cache: FAIL-INVALID_TYPE_'.strtoupper($type)); return; }
	}

	if (wp_doing_ajax()) { @header('X-Cache: FAIL-AJAX'); return; }
	
	if (class_exists('woocommerce' ) && (is_cart() || is_checkout() || is_account_page() || is_woocommerce())) 	
	{ @header('X-Cache: FAIL-WOO'); return; }
	
	$hash = '';
	if ($isCat)
	{
		$cat=get_category($id);
		if (is_wp_error($cat) || is_null($cat)) { @header('X-Cache: FAIL-CAT_EMPTY'); return; }
		$p=get_posts(array('numberposts'=>-1, 'category'=>$id));
		foreach ($p as $value) { $hash.=$value->ID.' '; }
		$id.='|'.get_query_var('paged');
		$hash=rtrim($hash);
		$suffix='c';
	}
	elseif ($isTag)
	{
		$tag=get_tag($id);
		if (is_wp_error($tag) || is_null($tag)) { @header('X-Cache: FAIL-TAG_EMPTY'); return; }
		$p=get_posts(array('numberposts'=>-1, 'tag'=>$id));
		foreach ($p as $value) { $hash.=$value->ID.' '; }
		$id.='|'.get_query_var('paged');
		$hash=rtrim($hash);
		$suffix='t';
	}
	else 
	{
		$hash	= get_post_modified_time('U',false,$id);
		if (!$hash) { @header('X-Cache: FAIL-NO_TIME'); return; }
		$suffix	= 'p';
	}

	if (($isFeed = is_feed())) $suffix.='f';
	global $atec_wpca_settings;
	$key='atec_WPCA_'.($atec_wpca_settings['salt']??'').'_'; 
	$arr=apcu_fetch($key.$suffix.'_'.$id);
	@header('X-Cache-ID: '.$suffix.'_'.$id);
	if (($arr[2]??'')==='') { apcu_delete($key.$suffix.'_'.$id); apcu_delete($key.$suffix.'_h_'.$id); $arr=false; }
	if ($arr!==false)
	{	
		if ($arr[0]===$hash)
		 {
		    @header('X-Cache-Type: atec APCu v'.esc_attr(wp_cache_get('atec_wpca_version')));
			@header('Content-Type: '.($isFeed?'application/rss+xml':'text/html'));
			if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && str_contains(sanitize_text_field(wp_unslash($_SERVER['HTTP_ACCEPT_ENCODING'])), 'gzip') && $arr[1])
			{
				// @codingStandardsIgnoreStart
				$zlib='zlib.output_compression';
				if (ini_get($zlib)) ini_set($zlib,'Off');
				// @codingStandardsIgnoreEnd
				header('Vary: Accept-Encoding');
				header("Content-Encoding: gzip");
				@header('X-Cache: HIT/GZIP');
				// @codingStandardsIgnoreStart
				echo $arr[2];
				// @codingStandardsIgnoreEnd
			}
			else
			{
				@header('X-Cache: HIT');
				if ($arr[1] && function_exists('gzdecode')) $arr[2] = gzdecode($arr[2]);
				// @codingStandardsIgnoreStart
				echo $arr[2];
				// @codingStandardsIgnoreEnd
			}
			apcu_inc($key.$suffix.'_h_'.$id);
			exit(200);
		}
	}
	else ob_start(function($buffer) use ($id, $hash, $suffix) { return atec_wpca_page_buffer_callback($buffer, $suffix, $id, $hash); });
 }

function atec_wpca_page_buffer_callback($buffer, $suffix, $id, $hash)
{
    $gzip=false; $compressed=''; $debug=''; $debugLen=0;
	global $atec_wpca_settings;
	$key='atec_WPCA_'.($atec_wpca_settings['salt']??'').'_';
	if (($atec_wpca_settings['debug']??null)==true && !str_contains($suffix,'f'))
	{
		$debug='	
			<script id="atec_wpca_debug_script">
			console.log(\'APCu Cache: HIT '.get_locale().' | '.strtoupper($suffix).' | '.$id.'\');
			var elemDiv = document.createElement("div");
			elemDiv.innerHTML="🟢";
			elemDiv.id="atec_wpca_debug";
			elemDiv.style.cssText = "position:absolute;top:3px;width:8px;height:8px;font-size:8px;left:3px;z-index:99999;";
			document.body.appendChild(elemDiv);
			setTimeout(()=>{ document.getElementById("atec_wpca_debug").remove(); }, 3000);
			document.getElementById("atec_wpca_debug_script").remove();
		</script>';
		$debugLen=strlen($debug);
	}
	if (function_exists('gzencode')) { $compressed = gzencode($buffer.$debug); $gzip=true; }
	apcu_store($key.$suffix.'_'.$id,array($hash,$gzip,$gzip?$compressed:$buffer.$debug,$gzip?strlen($compressed):strlen($buffer)+$debugLen));
	apcu_store($key.$suffix.'_h_'.$id,0);
	unset($compressed); unset($content);
	return $buffer;
}

add_action('wp', 'atec_wpca_page_buffer_start', -100);
if (@ini_get('zlib.output_compression')??false) 
{ remove_action('shutdown','wp_ob_end_flush_all',1); add_action('shutdown',function() { while (@ob_end_flush()); }); }

add_action( 'wp_footer', function() { echo '<p style=\'font-size:0; margin:0;\'>Powered by <a href=\'https://atecplugins.com\'>atecplugins.com</a></p>'; }); 
?>