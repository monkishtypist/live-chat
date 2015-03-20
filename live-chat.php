<?php
/*
Plugin Name: Live Chat
Plugin URI: http://ninthlink.com
Description: Activates Live Chat button. Edit plugin file to update JavaScript code.
Author: Tim Spinks
Version: 1.0
Author URI: https://profiles.wordpress.org/nlktim
*/


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Whoopsiedaisy!';
	exit;
}

define('LIVE_CHAT_VERS', '1.0');
define('LIVE_CHAT_PLUGIN_URL', plugin_dir_url( __FILE__ ));

//------------------------------------------------------------------------//
//---Config---------------------------------------------------------------//
//------------------------------------------------------------------------//




//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//
if ( ! live_chat_blacklist() ) {
	add_action( 'wp_enqueue_scripts', 'live_chat_scripts' );
	add_action( 'wp_footer', 'live_chat_code' );
}
add_action( 'admin_menu', 'live_chat_plugin_menu' );
add_action( 'admin_init', 'live_chat_register_mysettings' );
add_action( 'admin_notices','live_chat_warn_nosettings' );
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( 'livechat_chatscriptyui' !== $handle )
		return $tag;
	return str_replace( ' src', ' defer="defer" src', $tag );
}, 10, 2 );


//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//
// options page link
function live_chat_plugin_menu() {
	add_options_page('Live Chat', 'Live Chat', 'create_users', 'live_chat_options', 'live_chat_plugin_options');
}


function live_chat_register_mysettings(){
	register_setting('live_chat_options','live_chat_js_sources');
	register_setting('live_chat_options','live_chat_blacklist');
}

// blacklist settings
function live_chat_blacklist() {
	$live_chat_blacklist = get_option('live_chat_blacklist');
	if ( ! empty( $live_chat_blacklist ) ) {
		$blacklist_array = explode( "\r\n", $live_chat_blacklist );
		if ( is_page( $blacklist_array ) )
			return true;
	}
	return false;
}

//------------------------------------------------------------------------//
//---Output Functions-----------------------------------------------------//
//------------------------------------------------------------------------//
function live_chat_code() {
	
	$live_chat_sources = get_option('live_chat_js_sources');
	$output = '<!-- Live Chat Button Code -->';
	$output .= '<div id="live_chat_status"></div>';
	$output .= '<!-- Live Chat Button Code -->';
	$output .= '<!--Start of Chat Window Code-->';
	$output .= '<div id="floatDiv"></div>';
	$output .= '<!--End of Chat Window Code-->';
	if ( ! empty( $live_chat_sources ) ) {
		echo $output;
		remove_action("wp_footer", "live_chat_code");
	} else {
		return false;
	}
}
function live_chat_scripts() {
	$live_chat_sources = get_option('live_chat_js_sources');
	wp_enqueue_script( 'livechat_library', "http://greeterware.com/Dashboard/cwgen/scripts/library.js", array(), '1.0', true );
	wp_enqueue_script( 'livechat_chatscriptyui', "http://greeterware.com/Dashboard/cwgen/scripts/chatscriptyui.js", array(), '1.0', true );
	if ( ! empty( $live_chat_sources ) ) {
		$sources_array = explode( "\r\n", $live_chat_sources );
		foreach ($sources_array as $key => $value) {
			wp_enqueue_script( 'livechat_source_'.$key, $value, array(), '1.0', true );
		}
	}
}



//------------------------------------------------------------------------//
//---Page Output Functions------------------------------------------------//
//------------------------------------------------------------------------//
// options page
function live_chat_plugin_options() { ?>
	<div class="wrap">
		<h2>Jacuzzi Live Chat</h2>
		<p>You need to have a <a href="http://greeterware.com/">Live Chat</a> account in order to use this plugin. This plugin inserts the neccessary code into your Wordpress site automatically without you having to touch anything.</p>
		<form method="post" action="options.php">
		<?php settings_fields( 'live_chat_options' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row">Live Chat script sources</th>
			</tr>
			<tr>
				<td>Enter the JavaScript sources (URLs) for your custom Live Chat code. There are 4 scripts that must load for Live Chat. Two are included by default. Please enter only the two sources that include <code>yoursite.com</code> in the URL. Enter each source on a new line.</td>
			</tr>
			<tr>
				<td><textarea type="text" name="live_chat_js_sources" cols="80" rows="5" placeholder="//greeterware.com/Dashboard/cwgen/Company/LiveAdmins/mysite.com/gvars.js"><?php echo get_option('live_chat_js_sources'); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row">Prevent Live Chat from loading on the following pages</th>
			</tr>
			<tr>
				<td><p>Use this field below to blacklist pages from loading the Live Chat button. Uses Wordpress <code>is_page()</code> method. <a href="http://codex.wordpress.org/Function_Reference/is_page">View codex</a>. Enter each page on a new line.</p></td>
			</tr>
			<tr>
				<td><textarea type="text" name="live_chat_blacklist" cols="40" rows="5"><?php echo get_option('live_chat_blacklist'); ?></textarea></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
	</div><?php
}


function live_chat_warn_nosettings(){
	if (!is_admin())
		return;

	$live_chat_option = get_option("live_chat_js_sources");
	if ( !$live_chat_option || empty($live_chat_option) ) {
		echo "<div id='vwo-warning' class='updated fade'><p><strong>Live Chat is almost ready.</strong> You must <a href=\"options-general.php?page=live_chat_options\">add your Live Chat javascript file sources</a> for it to work.</p></div>";
	}
}

?>