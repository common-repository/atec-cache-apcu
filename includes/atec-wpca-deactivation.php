<?php
if (!defined('ABSPATH')) { exit; }

if (defined('WP_APCU_KEY_SALT'))
{
	if (function_exists('apcu_clear_cache')) { apcu_clear_cache(); }
	global $wp_filesystem; WP_Filesystem();
    $wp_filesystem->delete(WP_CONTENT_DIR.'/object-cache.php'); 
}
?>