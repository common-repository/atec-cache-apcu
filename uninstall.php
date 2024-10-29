<?php
	if (!defined('ABSPATH')) { exit; }
	$args=array('cache','clear','minify','gzip');
	foreach ($args as $arg) { delete_option('atec_WPCA_p_'.$arg.'_enabled'); }
	delete_option('atec_WPCA_settings');
?>