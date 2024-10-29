<?php
if (!defined( 'ABSPATH' )) { exit; }

function atec_opt_arr($opt,$slug): array { return array('name'=>$opt, 'opt-name' => 'atec_'.$slug.'_settings' ); }

function atec_checkbox_button($id,$str,$disabled,$option,$url,$param,$nonce): void
{
	$option=$option??'false';
	if ($option==1) $option='true';
	echo '
	<div class="atec-ckbx atec-dilb">
		<input name="check_', esc_attr($id), '"', ($disabled?'disabled="true"':''), ' type="checkbox" value="', esc_attr($option), '"', checked($option,'true',true), '>';
	if ($disabled) echo '<label for="check_', esc_attr($id), '" class="check_disabled"></label>';
	else echo '<label for="check_', esc_attr($id), '" onclick="location.href=\'', esc_url($url), esc_attr($param), '&_wpnonce=',esc_attr($nonce),'\'"></label>';
	echo '
	</div>';
}

function atec_checkbox_button_div($id,$str,$disabled,$option,$url,$param,$nonce,$pro=null): void
{
	echo '<div class="alignleft" style="padding: 2px 4px; ', $pro===false?'background: #f0f0f0; border: solid 1px #d0d0d0; border-radius: 3px; marin-right: 10px;':'' ,'">';
	if ($pro===false) 
	{
		$disabled=true;
		$link=get_admin_url().'admin.php?page=atec_group&license=true&_wpnonce='.esc_attr(wp_create_nonce('atec_license_nonce'));
		echo '
		<a class="atec-nodeco atec-blue" href="', esc_url($link), '">
			<span class="atec-dilb atec-fs-9"><span class="', esc_attr(atec_dash_class('awards','atec-blue atec-fs-16')), '"></span>PRO license required.</span>
		</a><br>';
	}
	echo '
		<div class="atec_checkbox_button_div atec-dilb">', esc_attr($str);
			atec_checkbox_button($id,$str,$disabled,$option,$url,$param,$nonce);
	echo '
		</div>
	</div>';
}

function atec_checkbox($args): void
{
	$option = get_option($args['opt-name'],[]); $field=$args['name']; $value=$option[$field]??'false';
	echo '
	<div class="atec-ckbx">
		<input id="check_', esc_attr($field), '" type="checkbox" name="', esc_attr($args['opt-name']), '[', esc_attr($field), ']" value="', esc_attr($value), '" onclick="atec_check_validate(\'', esc_attr($field), '\');" ', checked($value,'true',true), '>
		<label for="check_', esc_attr($field), '">
	</div>';
}

function atec_select($args): void
{
	global $atec_wpmc_types;
	$option = get_option($args['opt-name'],[]); $field=$args['name']; $value=$option[$field]??'false';
	echo '
	<select name="', esc_attr($args['opt-name']), '[', esc_attr($field), ']" id="', esc_attr($field), '">';
	foreach ($atec_wpmc_types as $type) echo '<option ', selected($type,$value,true), ' value="', esc_attr($type), '">', esc_attr($type), '</option>';
	 echo '
	</select>';
}
?>
