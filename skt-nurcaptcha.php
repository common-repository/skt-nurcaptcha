<?php
/*
	Plugin Name: Skt NURCaptcha
	Plugin URI: https://skt-nurcaptcha.sanskritforum.org/
	Description: If your Blog allows new subscribers to register via the registration option at the Login page, this plugin may be useful to you. It includes a reCaptcha block to the register form, so you get rid of spambots. To use it you have to sign up for (free) public and private keys at <a href="https://www.google.com/recaptcha/admin#createsite" target="_blank">reCAPTCHA API Signup Page</a>. Version 3 added extra security by querying antispam databases for known ip and email of spammers, so you get rid of them even if they break the reCaptcha challenge by solving it as real persons.
	Version: 3.5.0
	Author: Carlos E. G. Barbosa
	Author URI: http://www.yogaforum.org
	Text Domain: skt-nurcaptcha
	Domain Path: /languages
	License: GPL2
*/

// *******************************************************************

/*  Skt NURCaptcha - Copyright (c) 2011  Carlos E. G. Barbosa  
	(email : carlos.eduardo@mais.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
    
    Privacy Disclaimer

    This plugin uses Google reCAPTCHA which uses cookies and collects usage data. Its privacy policy is under Google's Privacy Policy (https://policies.google.com/privacy). 
    NURCaptcha collects and records in a log table: IP, username and email address of each blocked register or login attemptive.

*/


global $sktnurclog_db_version; $sktnurclog_db_version = "3.1";
function sktnurc_load_plugin_textdomain() {
    load_plugin_textdomain( 'skt-nurcaptcha', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'sktnurc_load_plugin_textdomain' );
add_action( 'admin_menu', 'skt_nurc_admin_page' );
add_action( 'login_enqueue_scripts', 'skt_nurc_login_init' );
add_action( 'login_form_register', 'skt_nurCaptcha' );
add_action( 'bp_include', 'skt_nurc_bp_include_hook' ); // Please, do call me, but only if BuddyPress is active...
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'skt_nurc_settings_link', 30, 1 );
if(get_site_option('sktnurc_login_recaptcha')=="true"){
	add_filter( 'wp_authenticate_user', 'skt_nurc_login_checkout',10,2 );
	add_action( 'login_form','skt_nurc_login_add_recaptcha' );
}
$temp = get_site_option('sktnurc_custom_page_list');
if (!empty($temp))
	add_action('wp_head', 'skt_nurc_enable_page_captcha');
if ( is_multisite() && (! is_admin())) {
	add_action( 'signup_header', 'skt_nurc_MU_signup_enqueue' );
	add_action( 'preprocess_signup_form', 'nurCaptchaMU_preprocess' );
	add_action( 'signup_extra_fields', 'nurCaptchaMU_extra',30,1 );
		// located @ wp-signup.php line 179 :: WP v 3.6
	add_filter( 'wpmu_validate_user_signup', 'skt_nurc_validate_captcha', 999, 1 );
		// located @ wp-includes/ms-functions.php line 509 :: WP v 3.6
}
/*
* Missing keys advert
*/
if (( get_site_option('sktnurc_publkey')== '') or ( get_site_option('sktnurc_privtkey')== '' )) {
	skt_nurc_keys_alert();
}

/*
* Login routines
*/
function skt_nurc_login_init() {
	if ( !is_multisite() ) {
		wp_register_script( 'NURCloginscript', plugins_url('/js/skt-nurc-login.js', __FILE__), array('jquery','scriptaculous') );
		wp_enqueue_script('NURCloginscript');
	}
    if(get_site_option('sktnurc_recaptcha_language')=="") update_site_option('sktnurc_recaptcha_language','xx');
    $lang = '';
    if(get_site_option('sktnurc_recaptcha_language')!="xx") $lang = "?hl=".get_site_option('sktnurc_recaptcha_language');
    wp_enqueue_script('NURCloginDisplay', "https://www.google.com/recaptcha/api.js".$lang);
    wp_enqueue_style( 'NURCcustom-login', plugins_url('/skt-nurc-login-style.css', __FILE__) );
}
function skt_nurc_login_add_recaptcha(){
	// check for Google's keys to enable reCAPTCHA on login form
	if(( get_site_option('sktnurc_publkey')!= '') and ( get_site_option('sktnurc_privtkey')!= '' )) {
		nurc_recaptcha_challenge(false);
	}
}
/*
* Login page and custom login page functions
*/
function skt_nurc_login_checkout($user, $password) {
	if ( is_wp_error($user) )
           return $user;
	$privtkey = get_site_option('sktnurc_privtkey');
	if (($privtkey == '')or(get_site_option('sktnurc_publkey')== '')) return $user; // disable checking if keys are not registered
	$user_ip_address =  $_SERVER['REMOTE_ADDR'];
	$response_string = isset($_POST['g-recaptcha-response'])? $_POST['g-recaptcha-response'] : "";
	$query_url = "https://www.google.com/recaptcha/api/siteverify?secret=$privtkey&response=$response_string&remoteip=$user_ip_address";
	$json_data = skt_nurc_get_page($query_url);
	$obj = json_decode($json_data);
	if (trim($obj->{"success"})==true)
		return $user;
	$result = implode(", ", $obj->{"error-codes"}); // this value is an array, so implode it!
	// $result is reserved for future use
	$errors = new WP_Error();
	$log_res = nurc_log_attempt('login-reCAPTCHA', $user); // log attemptive
	$errors->add('reCAPTCHA error', __('There was an error in your captcha response.', 'skt-nurcaptcha'));
	return $errors;
}
/* ******************
* This function renders code to the <head> section every page in the site, as to enable reCAPTCHA sitewide
* to activate it place this code anywhere in the theme's functions.php file:
add_action('wp_head', 'skt_nurc_sitewide_enable_captcha');
**************** */
function skt_nurc_sitewide_enable_captcha(){ // enables captcha sitewide, avoiding duplicity on listed custom pages
	$form_pages = get_site_option('sktnurc_custom_page_list');
	if (is_page($form_pages)) return;
	skt_nurc_core_enable_captcha();
}
// ****************
function skt_nurc_enable_page_captcha() { // checks if currently displayed page is listed as a custom page 
	$form_pages = get_site_option('sktnurc_custom_page_list');
	if (is_page($form_pages)) skt_nurc_core_enable_captcha();
}
function skt_nurc_core_enable_captcha(){
	global $wp_version;
	if(get_site_option('sktnurc_recaptcha_language')=="") update_site_option('sktnurc_recaptcha_language','xx');
	$lang = "?ver=$wp_version";
	if(get_site_option('sktnurc_recaptcha_language')!="xx") $lang .= "&hl=".get_site_option('sktnurc_recaptcha_language');
	echo "<script  type=\"text/javascript\" src=\"https://www.google.com/recaptcha/api.js".$lang."\" async defer></script>";
}
/*
* Admin page functions
*/
function skt_nurc_settings_link($links) {
	$settings_link = "<a href='options-general.php?page=skt_nurcaptcha'>".__('Settings', 'skt-nurcaptcha')."</a>";
	array_unshift($links,$settings_link);
	return $links;
}
function skt_nurc_admin_page() {
	$hook_suffix = add_options_page("Skt NURCaptcha", "Skt NURCaptcha", 'manage_options', "skt_nurcaptcha", "skt_nurc_admin");
	add_action( "admin_print_scripts-".$hook_suffix, 'skt_nurc_admin_init' );
}

function skt_nurc_admin_init() {
    wp_register_script( 'sktNURCScript', plugins_url('/js/skt-nurc-functions.js', __FILE__), array('jquery') );
	wp_enqueue_script('sktNURCScript');
}
function skt_nurc_admin() {
	include('skt-nurc-admin.php');
}
function skt_nurc_keys_alert() {
	add_action('admin_notices','skt_nurc_setup_alert');
}
/**
 * gets a URL where the user can sign up for reCAPTCHA. 
 */
function nurc_recaptcha_get_signup_url () {
	return "https://www.google.com/recaptcha/admin#createsite";
}

/*
* BuddyPress functions
*/
function skt_nurc_bp_include_hook() {
	define('SKTNURC_BP_ACTIVE',true);
	add_action( 'bp_signup_validate', 'skt_nurc_bp_signup_validate' ); 
		// located @ bp-members/bp-members-screens.php line 146 :: BP v 1.9.1
	add_action( 'bp_signup_profile_fields', 'skt_nurc_bp_signup_profile_fields' ); 
		// located @ bp_themes/bp_default/registration/register.php line 194 :: BP v 1.9.1
	add_action( 'wp_enqueue_scripts', 'skt_nurc_bp_register_script' );
}
function skt_nurc_bp_register_script () {
	$lang = '';
	if(get_site_option('sktnurc_recaptcha_language')=="") update_site_option('sktnurc_recaptcha_language','xx');
	if(get_site_option('sktnurc_recaptcha_language')!="xx") $lang .= "?hl=".get_site_option('sktnurc_recaptcha_language');
	if (bp_is_register_page()) {
		wp_enqueue_script( 'NurcBPregisterDisplay', "https://www.google.com/recaptcha/api.js".$lang );
	}
}
function skt_nurc_bp_signup_validate() {
    global $bp;
	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	if ( $http_post ) { // if we have a response, let's check it
		$nurc_result = new nurc_ReCaptchaResponse();	
		$privtkey = get_site_option('sktnurc_privtkey');
		$user_ip_address =  $_SERVER['REMOTE_ADDR'];
		$response = (isset($_POST['g-recaptcha-response'])) ? $_POST['g-recaptcha-response']: '';
		$nurc_result = nurc_recaptcha_check_answer($privtkey, $user_ip_address, $response );
		if (!$nurc_result->is_valid) {
			$log_res = nurc_log_attempt('reCAPTCHA'); // log attemptive
			$temp = $nurc_result->error;
			$bp->signup->errors['skt_nurc_error'] = $temp;
		}	
	}	
	return;
}
function skt_nurc_bp_signup_profile_fields() {
	echo '<div class="register-section" >';
    global $bp; ?>
    <label><?php _e('Fill the Captcha below', 'skt-nurcaptcha') ?></label>
    <?php
	if (!empty($bp->signup->errors)){
		if($temp = $bp->signup->errors['skt_nurc_error'])
			nurCaptchaMU_extra_output($temp);
	}
	nurc_recaptcha_challenge(NULL,false);
	echo '</div>';
}
/*
* WPMU - Multisite functions
*/
function skt_nurc_MU_signup_enqueue(){
	wp_enqueue_script( 'NurcBPregisterDisplay', "https://www.google.com/recaptcha/api.js" );
}
function nurCaptchaMU_preprocess() {
	if ((get_site_option('sktnurc_publkey')=='')||(get_site_option('sktnurc_privtkey')=='')) {
		die('<p class="error" style="font-weight:300"><strong>Security issue detected:</strong> reCAPTCHA configuration incomplete.<br /> Sorry! Signup will not be allowed until this problem is fixed. <br />Please try again later.</p>');
	}
}
/****
* WPMU
* Alert in WPMU - if reCAPTCHA is not yet enabled by registering the free keys at the Settings Page 
* 
****/
function skt_nurc_setup_alert() {
	
	?><div id="setup_alert" class="updated"><p><strong><?php 
	_e('Skt NURCaptcha Warning', 'skt-nurcaptcha' );
	?></strong><br /><?php
	_e('You must register your reCAPTCHA keys to have Skt NURCaptcha protection enabled.', 'skt-nurcaptcha' );
	if (get_admin_page_title() != 'Skt NURCaptcha') {
		echo '<br />'.__('Go to', 'skt-nurcaptcha')." <a href='options-general.php?page=skt_nurcaptcha'>".__('Skt NURCaptcha Settings', 'skt-nurcaptcha')."</a> ". __( 'and save your keys to the appropriate fields', 'skt-nurcaptcha' );
	} else {
		echo '<br />'. __( 'Be sure your keys are saved to the appropriate fields down here', 'skt-nurcaptcha' );
	}
	?></strong></p></div><?php
}

/****
* WPMU
* Error box in WPMU - if reCAPTCHA is not correctly filled or if spam signature check is positive 
* 
****/
function nurCaptchaMU_extra($errors = array()) {
	nurc_recaptcha_challenge();
	if($temp = $errors->get_error_message('skt_nurc_error')) 
			nurCaptchaMU_extra_output($temp);
}
/****
* WPMU
* Error box output - Multisite (WPMU) *** 
* 
****/
function nurCaptchaMU_extra_output($error_msg = '') {
	echo '<div class="error" style="font-weight:300"><strong>';
	echo __('reCaptcha ERROR', 'skt-nurcaptcha') .'</strong>:<br /> ';
	echo $error_msg . '</div>';	
}
/****
* WPMU
* Main routine - Multisite (WPMU) *** 
* 
****/
function skt_nurc_validate_captcha($result) { 
		/*  
			we start by checking if this function has been called by function validate_blog_signup() 
		 	-> located @ wp-signup.php line 454 :: WP v 3.6
		 	if call is from this function, it is a second check - NURCaptcha is not needed
		*/
		$callerfunc = skt_nurc_getCallingFunctionName(true);
		$pos = strpos($callerfunc, "validate_blog_signup");
		
		if($pos !== false) {
			return $result;
		} // this is a second check on username & email - so skip NURCaptcha
	// check if is there a BuddyPress installation active. If so, skip this routine.
	if(defined('SKTNURC_BP_ACTIVE')) return $result; 
	// now it's all OK! 
	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	if ( $http_post ) { // if we have a response, let's check it
		$nurc_result = new nurc_ReCaptchaResponse();	
		$privtkey = get_site_option('sktnurc_privtkey');
		$user_ip_address =  $_SERVER['REMOTE_ADDR'];
		$nurc_result = nurc_recaptcha_check_answer($privtkey, $user_ip_address, $_POST['g-recaptcha-response'] );
		if (!$nurc_result->is_valid) {
			$log_res = nurc_log_attempt('reCAPTCHA');
			$temp = $nurc_result->error;
			extract($result);
			if ( !is_wp_error($errors) )
				$errors = new WP_Error();
			$errors->add('skt_nurc_error', $temp);
			$result = array('user_name' => $user_name, 'orig_username' => $orig_username, 'user_email' => $user_email, 'errors' => $errors);
		}
	}	
return $result;
}

/****
*
* Main routine - non-multisite *** 
* This code overrides entirely the 'register' case on main switch @ wp_login.php 
* we fetch the 'login_form_register' hook 
* You may experience some problems if you install another plugin that needs to customize those lines.
*
****/
function skt_nurCaptcha() {

	$http_post = ('POST' == $_SERVER['REQUEST_METHOD']);
	
	if ( is_multisite() ) {
		/**
		 * Filters the Multisite sign up URL.
		 *
		 * @since 3.0.0
		 *
		 * @param string $sign_up_url The sign up URL.
		 */
		wp_redirect( apply_filters( 'wp_signup_location', network_site_url( 'wp-signup.php' ) ) );
		exit;
	}

	if ( !get_option('users_can_register') ) {
		wp_redirect( site_url('wp-login.php?registration=disabled') );
		exit();
	}
		// Plugin is disabled if one or both reCaptcha keys are missing: 
	if ((get_site_option('sktnurc_publkey')=='')||(get_site_option('sktnurc_privtkey')=='')) {return false;}
	 
    $result = new nurc_ReCaptchaResponse(); // sets $result as a class variable
	$result->is_valid = true;
	$user_login = '';
	$user_email = '';
	$errors = NULL;
	if ( $http_post ) { // if we have a response, let's check it
		$user_login = isset( $_POST['user_login'] ) ? $_POST['user_login'] : '';
		$user_email = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';
		if ($user_login ==''){
			$result->is_valid = false;
			$result->error = __("username missing", 'skt-nurcaptcha'); 
		}
		if ($user_email==''){
			$result->is_valid = false;
			$result->error = __("email missing", 'skt-nurcaptcha'); 
		}
		if ($result->is_valid) {
			$privtkey = get_site_option('sktnurc_privtkey');
			$user_ip_address =  $_SERVER['REMOTE_ADDR'];
            $result = nurc_recaptcha_check_answer($privtkey, $user_ip_address, $_POST['g-recaptcha-response'] );
		}

        // hook for extra checks on username and user_email, if needed
		if ($result->is_valid) {
			do_action('sktnurc_before_register_new_user', $result, $user_login, $user_email);
		}
		  
		if ($result->is_valid) { // captcha and botscout passed, so let's try to register the new user...
			if ( !function_exists('sktnurc_register_new_user') ) { 
				$errors = register_new_user($user_login, $user_email);
			}else{
				// if you want to customize registration, create a function for that with this name:
				$errors = sktnurc_register_new_user($user_login, $user_email);				
			}
			if ( !is_wp_error($errors) ) {
				$redirect_to = !empty( $_POST['redirect_to'] ) ? $_POST['redirect_to'] : 'wp-login.php?checkemail=registered';
				wp_safe_redirect( $redirect_to );
				exit(); // end of all procedures - job done!
			} 
		}

	}
	$registration_redirect = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
	/**
	 * Filter the registration redirect URL.
	 *
	 * @since 3.0.0
	 *
	 * @param string $registration_redirect The redirect destination URL.
	 */
	$redirect_to = apply_filters( 'registration_redirect', $registration_redirect );
	login_header(__('Registration Form'), '<p class="message register">' . __('Register For This Site') . '</p>', $errors);
	
	if (get_site_option('sktnurc_theme')!="clean"){$form_width ='320';}else{$form_width ='448';}
	
	if ((!$result->is_valid)and($result->error != '')) {
		
		$log_res = nurc_log_attempt('reCAPTCHA'); // register attemptive in log file
		echo '<div id="login_error"><strong>reCaptcha ERROR</strong>';
		echo ': '.sprintf( __("There is a problem with your response: %s", 'skt-nurcaptcha'),$result->error);
		echo '<br></div>';
	}

	?> 
<form id="nurc_form" action="<?php echo esc_url( site_url('wp-login.php?action=register', 'login_post') ); ?>"  method="post" style="width:300px;" novalidate>
	<p>
        <label for="user_login"><?php _e('Username'); ?><?php nurc_username_help(); ?>
        <input type="text" name="user_login" id="user_login" class="input" value="<?php echo esc_attr(wp_unslash($user_login)); ?>" size="20" />
        </label>
    </p>
	<p>
    	<label for="user_email"><?php _e('Email'); ?><?php nurc_email_help(); ?>
        <input type="email" name="user_email" id="user_email" class="input" value="<?php echo esc_attr(wp_unslash($user_email)); ?>" size="25" />
        </label>
    </p>

	<?php 
	nurc_recaptcha_challenge(); 
	/**
	 * Fires following the 'Email' field in the user registration form.
	 *
	 * @since 2.1.0
	 */
	do_action('register_form'); 
	?>
    
	<p id="reg_passmail"><?php _e( 'Registration confirmation will be emailed to you.' ); ?></p>
	<br class="clear" />
	<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>" />
	<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php 
	if (get_site_option('sktnurc_regbutton')==""){
		esc_attr_e('Register', 'skt-nurcaptcha'); 
	} else {
		echo get_site_option('sktnurc_regbutton');
	}
	?>" /></p></form>

<p id="nav">
<a href="<?php echo esc_url( wp_login_url() ); ?>"><?php _e( 'Log in' ); ?></a> |
<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php _e( 'Lost your password?' ); ?></a>
</p>
<?php
login_footer('user_login');
exit;

} 
/*****
*
* End main
*
**/

function nurc_username_help() {
	if (get_site_option('sktnurc_usrhlp_opt')=='true') return;
	?>
    <span id="username-help-toggle" style="cursor:pointer;float:right">&nbsp;(<strong> ? </strong>)</span>
    <div id="username-help" style="position:relative;display:none;">
    	<p class="message register" style="float:left; font-weight:normal;">
    		<?php 
			echo sktnurc_username_help_text();
			?>
        </p>
    </div>
    <?php 
}
function nurc_email_help() {
	if (get_site_option('sktnurc_emlhlp_opt')=='true') return;
	?>
    <span id="email-help-toggle" style="cursor:pointer;float:right">&nbsp;(<strong> ? </strong>)</span>
    <div id="email-help" style="position:relative;display:none;">
    	<p class="message register" style="float:left">
    		<?php 
			echo sktnurc_email_help_text();
			?>
        </p>
    </div>
    <?php 
}
function nurc_reCaptcha_help() {
	if (get_site_option('sktnurc_rechlp_opt')=='true') return;
	if ( is_multisite() ) return;
	if (defined('SKTNURC_BP_ACTIVE')) return;
	?>
    <span id="recaptcha-help-toggle" style="cursor:pointer;float:right">&nbsp;(<strong> ? </strong>)</span>
    <div id="recaptcha-help" style="position:relative;display:none;">
    	<p class="message register" style="float:left">
    		<?php 
			echo sktnurc_reCaptcha_help_text();
			?>
        </p>
    </div>
    <div style="clear:both"></div>
    <?php 
}
function nurc_make_path() {
		$nurc_pathinfo = pathinfo(realpath(__FILE__)); // get array of directory realpath on server 
		$npath = $nurc_pathinfo['dirname']."/"; // prepare realpath to base plugin directory
		return $npath;
}

/************ get help text *****
 *
 * Next three methods get help text that is displayed at the register form
 * They can be customized via Admin Panel
 * You need not change these strings 
 *
 *************/
function sktnurc_username_help_text(){
	$output = stripslashes(get_site_option('sktnurc_username_help'));
	if ($output == ""){
		$output = __('Use only non-accented alphanumeric characters plus these: _ [underscore], [space], . [dot], - [hyphen], * [asterisk], and @ [at]', 'skt-nurcaptcha'); 
	}
	return $output;
}
function sktnurc_email_help_text(){
	$output = stripslashes(get_site_option('sktnurc_email_help'));
	if ($output == ""){
		$output = __('Use a functional email address, as your temporary password will be sent to that email', 'skt-nurcaptcha'); 
	}
	return $output;
}
function sktnurc_reCaptcha_help_text(){
    $output = stripslashes(get_site_option('sktnurc_v2_reCaptcha_help'));
    if ($output == ""){
        $output = __("To get registered, just click on the box below to confirm you're not a spam robot. If after that you're prompted with another challenge, just transcribe the words, numbers and signs you see in the image to the small text field over it, no matter how absurd they look like, just to make clear you are a human being trying to register to this site. We welcome you, but we must keep out all spambots.", 'skt-nurcaptcha'); 
    }
    return $output;
}

/************ for your custom code *****
 *
 * This function is used to display the reCAPTCHA challenge
 * You may call it from anywhere, including other plugins or theme pages
 * To check the results, you may use function nurcResponse() below 
 *
 *************/
function nurcRecaptcha(){
	nurc_recaptcha_challenge(false,false);
}
// *************
function nurc_recaptcha_challenge($use_help = true, $use_label = true) {
		if ($use_label) { ?>
		<p><label><?php _e('Fill the Captcha below', 'skt-nurcaptcha') ?></label><?php if($use_help) nurc_reCaptcha_help(); ?></p>
		<?php }
		if (get_site_option('sktnurc_data_theme') == '') {
			update_site_option('sktnurc_data_theme','light');
		}
		if (get_site_option('sktnurc_data_type') == '') {
			update_site_option('sktnurc_data_type','image');
		}
		?>
      	<div class="g-recaptcha" 
      		data-sitekey="<?php echo get_site_option('sktnurc_publkey'); ?>"
            data-theme="<?php echo get_site_option('sktnurc_data_theme'); ?>"
      		data-type="<?php echo get_site_option('sktnurc_data_type'); ?>"
            style="padding-bottom:12px;"
            ></div>
        <?php 
		
}

/************ for your custom code *****
 * function nurcResponse()
 *
 * This function is used to get the results of a reCAPTCHA challenge posted
 * you may call it from another plugin or custom code on your template.
 * just place a code like this on the landing page to where the form data 
 * is sent after being posted:
 *		if ('POST' == $_SERVER['REQUEST_METHOD']){
 *         $result = nurcResponse();
 *         if ($result->is_valid){
 *             // answer is correct - so let's do something else...
 *         }else{
 *             // answer is incorrect - show error message and block the way out...
 *         }
 *		}
 ************/
function nurcResponse() {
	$check = new nurc_ReCaptchaResponse();
		$check = nurc_recaptcha_check_answer(get_option('sktnurc_privtkey'), 
											$_SERVER['REMOTE_ADDR'], 
											$_POST['g-recaptcha-response'] );
	return $check;
}

/**
 * Writes log into db table
 */
function nurc_log_attempt($processID = '', $user = NULL) {
		global $wpdb;
		$table_name = $wpdb->prefix . "sktnurclog";
		
		if (defined('SKTNURC_BP_ACTIVE')) {
			$ue = (isset($_POST['signup_email']))? $_POST['signup_email']: '';
			$ul = (isset($_POST['signup_username']))? $_POST['signup_username']: '';
		}else{
			$ue = (isset($_POST['user_email']))? $_POST['user_email']: ''; 
			if ( is_multisite() ) {		
				$ul = (isset($_POST['user_name']))? $_POST['user_name']: '';
			}else{
				$ul = (isset($_POST['user_login']))? $_POST['user_login']: '';
			}
		}
		if ($ue == '') {$ue = '  ...  ';}
		if ($ul == '') {$ul = '  ...  ';}
		$logtime = current_time("mysql",0);
		if ($user != '') {
			$ue = $user->user_email;
			$ul = $user->user_login; 
			}
		//
		// ****  Insert data into database table
		$rows_affected = $wpdb->insert( $table_name, array( 'time' => $logtime, 'username' => $ul, 'email' => $ue, 'ip' => $_SERVER['REMOTE_ADDR'], 'procid' => $processID ) );
		// ***
		return $rows_affected;
}


/**
 * Submits an HTTP POST to a reCAPTCHA server
 * @param string $host
 * @param string $path
 * @param array $data
 * @param int port
 * @return array response
 */
function nurc_recaptcha_http_post($host, $path, $data, $port = 80) {

        $req = http_build_query($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
                return array(false, "false \n".__('Could not open socket - server communication failed - try again later.', 'skt-nurcaptcha'));
        }

        fwrite($fs, $http_request);

        while ( !feof($fs) )
                $response .= fgets($fs, 1160); // One TCP-IP packet
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
}

/**
 * A nurc_ReCaptchaResponse is returned from nurc_recaptcha_check_answer()
 */
class nurc_ReCaptchaResponse {
        var $is_valid;
        var $error;
}


/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $challenge
  * @param string $response
  * @param array $extra_params an array of extra variables to post to the server
  * @return nurc_ReCaptchaResponse
  */
function nurc_recaptcha_check_answer ($privkey, $remoteip, $challenge, $response = NULL, $extra_params = array(), $add_count = true){

	$recaptcha_response = new nurc_ReCaptchaResponse();
		$response_string = $challenge; // the third variable will be the response string, with the new version 
		$query_url = "https://www.google.com/recaptcha/api/siteverify?secret=$privkey&response=$response_string&remoteip=$remoteip";
		$json_data = skt_nurc_get_page($query_url);
		$obj = json_decode($json_data);
		$recaptcha_response->is_valid = false;
		if (trim($obj->{"success"})==true){
			$recaptcha_response->is_valid = true;
		}else{
			if(is_array($obj->{"error-codes"})){ // this value may be an array
				$recaptcha_response->error = implode(", ", $obj->{"error-codes"}); // so lets turn it into a string
			}else{
				$recaptcha_response->error = $obj->{"error-codes"};
			}
		}
	return $recaptcha_response;
}



/**** 
*
* ***  Log db table installing and updating  *** 
* 
****/
function skt_nurc_install ($log_db_version) {
	global $wpdb;
	$table_name = $wpdb->prefix . "sktnurclog"; 
	$sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  email tinytext NOT NULL,
	  username tinytext NOT NULL,
	  ip tinytext NOT NULL,
	  procid tinytext NOT NULL,
	  UNIQUE KEY id (id)
	);";
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	update_site_option( "sktnurclog_db_version", $log_db_version );
}
function skt_nurc_update_db_check() {
    global $sktnurclog_db_version;
    if (get_site_option('sktnurclog_db_version') != $sktnurclog_db_version) {
        skt_nurc_install($sktnurclog_db_version);
    }
	delete_site_option('sktnurc_count');
}
add_action('plugins_loaded', 'skt_nurc_update_db_check');

/**** 
*
* ***  Log db table managing  *** 
* 
****/
function skt_nurc_listlog($limit = 20, $offset = 0) {
	global $wpdb;
	$table_name = $wpdb->prefix . "sktnurclog"; 
	$result = $wpdb->get_results("SELECT * FROM ". $table_name ." ORDER BY id DESC LIMIT ". $offset . ", " . $limit .";");
	return $result;
}
function skt_nurc_countlog() {
	global $wpdb;
	$table_name = $wpdb->prefix . "sktnurclog"; 
	$result = $wpdb->get_var("SELECT COUNT(*) FROM ". $table_name .";");
	return $result;
}
function nurc_clear_log_file() {
	global $wpdb;
	$target = skt_nurc_countlog();
	$table_name = $wpdb->prefix . "sktnurclog"; 
	$result = $wpdb->query("TRUNCATE TABLE ". $table_name .";");
	if($result === false){
		return $result;
	}else{
		return __('Log table successfully deleted.', 'skt-nurcaptcha');
	}
}
/**
* Send a GET request using cURL
* @param string $url to request
* @param array $get values to send
* @param array $options for cURL
* @return string
*/
function skt_nurc_get_page($url, array $get = null, array $options = array()){
    if($get !== null) $url = $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get);
    $protocol = ($_SERVER['HTTPS'])? 'https':'http';
    $referer= $protocol.'://'.$_SERVER['HTTP_HOST'];
    $defaults = array(
        CURLOPT_URL => $url,
		CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_REFERER => $referer
    );
    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch)) $result ='';
    curl_close($ch);
    return $result;
}
/***************
* This method derived from one published by Manish Zope, as a comment at:
* http://stackoverflow.com/questions/190421/caller-function-in-php-5/12813039#12813039
****************/
function skt_nurc_getCallingFunctionName($complete=false)
    {
        $trace=debug_backtrace();
        if($complete)
        {
            $str = '';
            foreach($trace as $caller)
            {
                $str .= " - {$caller['function']}";
                if (isset($caller['class']))
                    $str .= " Class {$caller['class']}";
            }
        }
        else
        {
            $caller=$trace[2];
            $str = "Called by {$caller['function']}";
            if (isset($caller['class']))
                $str .= " Class {$caller['class']}";
        }
        return $str;
    }
function skt_nurc_pages_checkbox() {
	$custom = get_site_option('sktnurc_custom_page_list');
	if (is_array($custom)){
		$i = count($custom);
	}else{
		$i = 0;
	}
	$html = "<div id =\"sktnurc_pages_checkbox\" style=\"display:none;\">";
	$skt_pages = skt_nurc_get_pages_array();
	foreach ($skt_pages as $ID => $title){
		$html .= "<input type=\"checkbox\" name=\"sktnurc_custom_page_list[]\"  value=\"$ID\"";
		if ($i>0){
			for ($r=0;$r<$i;$r++){
				if ($custom[$r] == $ID) {
					$html .= " checked ";
					continue;	
				}
			}
		}
		$html .= ">$title<br />";
	}
	$html .= "</div>";
	return $html;
}
function skt_nurc_get_pages_array(){
	$args = array(
		'sort_order' => 'ASC',
		'sort_column' => 'post_title',
		'post_type' => 'page',
		'post_status' => 'publish'
	); 
	$pages = get_pages($args); 

	$skt_pages = array(); // lists all pages.
	foreach ($pages as $pages_list){
		$skt_pages[$pages_list->ID] = $pages_list->post_title;
	}
	//$skt_pages=array(0=> __("Choose a page", 'skt-nurcaptcha'))+$skt_pages;
	return $skt_pages;
}