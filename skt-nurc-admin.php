<?php
/*
// ============================================================
    if(get_site_option('sktnurc_stopforumspam_active')!==false)
        delete_site_option( 'sktnurc_stopforumspam_active' );
    if(get_site_option('sktnurc_recaptcha_version')!==false)
        delete_site_option( 'sktnurc_recaptcha_version' );
    if(get_site_option('sktnurc_botscout_active')!==false)
        delete_site_option( 'sktnurc_botscout_active' );
    if(get_site_option('sktnurc_botscoutkey')!==false)
        delete_site_option( 'sktnurc_botscoutkey' );
    if(get_site_option('sktnurc_botscoutTestMode')!==false)
        delete_site_option( 'sktnurc_botscoutTestMode' );
// ===========================================================
*/
if(get_site_option('sktnurc_recaptcha_language')=="") 
		update_site_option('sktnurc_recaptcha_language','xx');
	if(get_site_option('sktnurc_data_theme')=="")
		update_site_option('sktnurc_data_theme', "light");
	if(get_site_option('sktnurc_data_type')=="")
		update_site_option('sktnurc_data_type', "image");
	if(get_site_option('sktnurc_login_recaptcha')=="")
		update_site_option('sktnurc_login_recaptcha', "false");



	if(isset($_POST['sktnurc_hidden'])) { // == 'Y'
		//Form data sent
		$sktnurc_pubkey = $_POST['sktnurc_publkey'];
		update_site_option('sktnurc_publkey', $sktnurc_pubkey);

		$sktnurc_privkey = $_POST['sktnurc_privtkey'];	
		update_site_option('sktnurc_privtkey', $sktnurc_privkey);
		
		// *****  update register form's help messages:	
		update_site_option('sktnurc_username_help', $_POST['sktnurc_username_help']);
		update_site_option('sktnurc_email_help', $_POST['sktnurc_email_help']);
		update_site_option('sktnurc_v2_reCaptcha_help', $_POST['sktnurc_v2_reCaptcha_help']);
		if(isset($_POST['sktnurc_usrhlp_opt']) && ($_POST['sktnurc_usrhlp_opt'] == 'true')){
			update_site_option('sktnurc_usrhlp_opt', 'true');
		}else{
			update_site_option('sktnurc_usrhlp_opt', 'false');
		}

		if(isset($_POST['sktnurc_emlhlp_opt']) && ($_POST['sktnurc_emlhlp_opt'] == 'true')){
			update_site_option('sktnurc_emlhlp_opt', 'true');
		}else{
			update_site_option('sktnurc_emlhlp_opt', 'false');
		}

		if(isset($_POST['sktnurc_rechlp_opt']) && ($_POST['sktnurc_rechlp_opt'] == 'true')){
			update_site_option('sktnurc_rechlp_opt', 'true');
		}else{
			update_site_option('sktnurc_rechlp_opt', 'false');
		}

		// *****
		
		update_site_option('sktnurc_username_help', $_POST['sktnurc_username_help']);
		
		$sktnurc_logpage_limit = absint($_POST['sktnurc_logpage_limit']);
		if ($sktnurc_logpage_limit < 5) $sktnurc_logpage_limit = 5;
		update_site_option('sktnurc_logpage_limit', $sktnurc_logpage_limit);
		
		update_site_option('sktnurc_regbutton', $_POST['sktnurc_regbutton']);
		
        if (isset($_POST['sktnurc_custom_page_list']) && (is_array($_POST['sktnurc_custom_page_list']))){
            update_site_option('sktnurc_custom_page_list', $_POST['sktnurc_custom_page_list']);
        }else{
            update_site_option('sktnurc_custom_page_list', NULL);
        }
        update_site_option('sktnurc_data_type', $_POST['sktnurc_data_type']);
        update_site_option('sktnurc_data_theme', $_POST['sktnurc_data_theme']);
        update_site_option('sktnurc_login_recaptcha', $_POST['sktnurc_login_recaptcha']);
        update_site_option('sktnurc_recaptcha_language', $_POST['sktnurc_recaptcha_language']);
	

		if ($_POST['log_clear']!= 'no') {
			$clear_log_file = nurc_clear_log_file();
			if ($clear_log_file !== false) {
			?>
				<div class="updated"><p><strong><?php echo $clear_log_file; ?></strong></p></div>
			<?php
			}else{
				echo '<div class="error"><p><strong>';
				_e('An Error occurred: Log File deletion was not possible.', 'skt-nurcaptcha' );
				echo '</strong></p></div>';
			}
		}
		?>
			<div class="updated"><p><strong><?php _e('Options saved.', 'skt-nurcaptcha' ); ?></strong></p></div>
		<?php
		if ($botscoutkey_verified == false) {
			settings_errors( 'botscoutkey' );
			}
	} else {
		//Normal page display
		$sktnurc_pubkey = get_site_option('sktnurc_publkey');
		$sktnurc_privkey = get_site_option('sktnurc_privtkey');
		$sktnurc_botscoutkey = get_site_option('sktnurc_botscoutkey');
	}


?>
<div class="wrap">
	<?php 	
		$nurc_version = nurc_get_version();
		$nurc_icon = plugin_dir_url(dirname(__FILE__).'/skt-nurcaptcha.php')."img/icon.svg";
		echo "<div style=\"margin-left:24px;\"><h2><img src=\"$nurc_icon\" height=\"42\" width=\"42\" style=\"margin-right:4px;\" >" . __( 'Skt NURCaptcha Settings', 'skt-nurcaptcha' ) . "</h2></div>"; 
	?>
	
	<div style="width:680px;padding:12px 0 12px 24px">
		<form name="sktnurc_form" method="post" action="<?php echo admin_url( "options-general.php?page=".$_GET["page"] ) ?>">
				<input type="hidden" name="sktnurc_hidden" value="Y" />
				<input id="log_clear" type="hidden" name="log_clear" value="no" />
				<input id="confirm_dialog" type="hidden" name="confirm_dialog" value="<?php _e( 'This option cannot be undone. Do you really want to erase all Log Data and restart Log File?', 'skt-nurcaptcha' ) ?>" />
		<?php echo __('Version: ', 'skt-nurcaptcha') . $nurc_version; ?>
		<p style="padding: .5em; background-color: #666666; color: #fff;position:relative">Skt NURCaptcha 
		<?php   $total = skt_nurc_countlog();
			if ($total) {
			
			echo __( 'has blocked', 'skt-nurcaptcha' ) . " <strong> ".$total."</strong> ". __( 'suspect register attempts on this site.', 'skt-nurcaptcha' ). "<span id='log_button' class=\"log_button button-primary\" style=\"position:absolute;cursor:pointer;color:#fff;font-weight:bold;background:#4086aa;text-decoration:none;top:2px;right:12px\">&nbsp;".__('Toggle Log', 'skt-nurcaptcha')."&nbsp;</span><span id='no_log_button' class=\"log_button button-primary\" style=\"display:none;position:absolute;cursor:pointer;color:#fff;font-weight:bold;background:#4086aa;text-decoration:none;top:2px;right:12px\">&nbsp;".__('Toggle Log', 'skt-nurcaptcha')."&nbsp;</span>";
			} ?>
        </p><br />
		<?php    echo  __( 'NURCaptcha stands for <strong>New User Register Captcha</strong>.', 'skt-nurcaptcha' ) . "<br />"; ?>
		<?php    echo  __( 'It uses Google\'s reCaptcha tools to protect your site against spammer bots, ', 'skt-nurcaptcha' ) ; ?>
		<?php    echo  __( 'adding security to the WP Register Form. You can learn more', 'skt-nurcaptcha' ) ; ?>
		<?php    echo  '<a href="https://www.google.com/recaptcha/intro/index.html#advanced-security" target="_blank"> '.__('here','skt-nurcaptcha').' </a>'.__('about reCAPTCHA Security','skt-nurcaptcha').'<br />'; ?>
		<?php    echo "<br />"; ?>
		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'skt-nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page! ','skt-nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
        <div style="clear:both"></div>
		<?php
		if(!isset( $_GET['pagenum'] )){$display_none = "display:none";}else{$display_none = "";}
		echo "<div id='log_entries' style='width:800px;position:relative;".$display_none."'>"; 
		echo "<span id='unlink_log_button' class=\"button-primary\" style=\"position:absolute;cursor:pointer;color:#fff;font-weight:bold;background:#4086aa;text-decoration:none;top:2px;right:82px;".$display_none ."\">&nbsp;".__('Delete Log File', 'skt-nurcaptcha')."&nbsp;</span><span id='link_log_button' class=\"button-primary\" style=\"display:none;position:absolute;cursor:pointer;color:#fff;font-weight:bold;background:#ff2000;text-decoration:none;top:2px;right:132px\">&nbsp;".__('Cancel Delete Log File', 'skt-nurcaptcha')."&nbsp;</span> ";

		$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1; // if pagenum absent, assume value 1.
		$limit = get_site_option('sktnurc_logpage_limit',20);
		$num_of_pages = ceil( $total / $limit );
		if($pagenum > $num_of_pages) $pagenum = $num_of_pages;
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'pagenum', '%#%' ),
			'format' => '',
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'total' => $num_of_pages,
			'current' => $pagenum
		) );
		
		echo "<h3>". __('Last Blocked Attemptives','skt-nurcaptcha')." (".$total.")</h3>";
		if ($num_of_pages > 1) 
			echo "<strong>". __('Page','skt-nurcaptcha'). " ".$pagenum." ".__('of','skt-nurcaptcha')." ".$num_of_pages. "</strong> -> ";
		echo " (";
		echo " <input type=\"text\" id=\"sktnurc_logpage_limit\" name=\"sktnurc_logpage_limit\" value=\""; 
		echo $limit ."\" size=\"4\"> ".__("occurrences per page",'skt-nurcaptcha').")";
		
		$offset = ( $pagenum - 1 ) * $limit;
		if ($attemptives = skt_nurc_listlog($limit, $offset)){
			if ( $page_links ) {
				echo '<div class="tablenav" style="margin-right:70px"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div><div style="clear:both"></div>';
			}else{
				echo '<br /><br />';
			}
			//abrir form de eliminação de registros
			foreach($attemptives as $attemptive){
				// incluir checkbox com value=$attemptive->id;
				echo $attemptive->time;
				echo " &raquo;&ensp; email: &lt;<strong>".$attemptive->email."</strong>&gt;";
				echo " &rarr; name: <strong>".$attemptive->username."</strong> ";
				echo " &rarr; IP: ".$attemptive->ip." ";
				echo "&ensp;&raquo;&ensp; [".$attemptive->procid."]";
				echo "<br />";
			}
			// fechar form de eliminação de registros com botão "Apagar os registros selecionados"
			if ( $page_links ) {
				echo '<div class="tablenav" style="margin-right:70px"><div class="tablenav-pages" style="margin: 1em 0">' . $page_links . '</div></div>';
			}

		}else{
			echo __("No data to show", 'skt-nurcaptcha');
		}
		echo '<br /><br />';
	    echo "</div>"; // end of <div id="log_entries">
		?>
        <div style="clear:both"></div>
		<?php    echo "<br />"; ?>
		<?php    echo "<p style=\"padding: .5em; background-color: #666666; color: #fff;\">" . __( 'To enable this plugin\'s functionality, please enter your reCAPTCHA keys here:', 'skt-nurcaptcha' ) . "</p><br />"; // grey section title bar
		
		 	echo "<small>[". __( 'You can sign up for reCaptcha <strong>free</strong> keys here: ', 'skt-nurcaptcha' );
			echo '<a href="'.nurc_recaptcha_get_signup_url().'" target="_blank"><strong>reCAPTCHA API Signup Page</strong></a>]</small>'; ?>
	</div>	
	
	<div style="width:680px;padding:12px 0 12px 24px">
				<p><?php _e("reCaptcha Site Key: ", 'skt-nurcaptcha' ); ?><input type="text" id="sktnurc_publkey" name="sktnurc_publkey" value="<?php echo $sktnurc_pubkey; ?>" size="46"></p>
				<p><?php _e("reCaptcha Secret Key: ", 'skt-nurcaptcha' ); ?><input type="text" id="sktnurc_privtkey" name="sktnurc_privtkey" value="<?php echo $sktnurc_privkey; ?>" size="46"></p>
		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'skt-nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page!','skt-nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
	</div>
	
	<div style="position:relative;width:680px;padding:12px 0 12px 24px">
		<p style="padding: .5em; background-color: #666666; color: #fff;"><?php echo __( 'Style your reCaptcha:', 'skt-nurcaptcha' ) ?></p>
		<div style="float:left;width:600px;padding-left:24px;margin:12px 0 12px 0">

            <div id="sktth" style="position:relative;">
                <span><strong><?php  _e('reCAPTCHA theme:', 'skt-nurcaptcha'); ?></strong></span><br />
                &nbsp;&nbsp;&nbsp;&nbsp;<select id="sktnurc_theme" name="sktnurc_data_theme">
                    <?php
                    $plugin_img_path = array();
                    $rc_themes = array('light' => 'Light (default)', 'dark' => 'Dark');
                    foreach( $rc_themes as $k => $v ) {
                        $selected = ( $k == get_site_option('sktnurc_data_theme') ) ? 'selected="selected"' : '';
                        echo "<option value='$k' $selected>$v</option>";
                        $plugin_img_path[$k] = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
                        $plugin_img_path[$k] .= 'img/'.$k.'.png';
                    }
                    $def_img = ( "" == get_site_option('sktnurc_data_theme') ) ? 'light' : get_site_option('sktnurc_data_theme');
                    ?>
                </select><br />
                <!-- reCAPTCHA images --> 
                <div class="captcha-img" style="float:left;width:460px; margin:-42px 0 110px 0; padding:0 0 12px 0">
                    <?php
                    foreach ($plugin_img_path as $k => $v) {
                    ?>
                    <div id="sktnurc-display-<?php 
                        echo $k; 
                        ?>" style="position:absolute;margin-left:232px<?php 
                        if ($k != $def_img) { echo ';display:none';} ?>">
                        <img src="<?php echo $plugin_img_path[$k]; ?>" title="<?php 
                        _e('This is the look of your captcha','skt-nurcaptcha'); ?>" />
                    </div>
                    <?php
                    }
                    ?>
                </div><!-- end of reCAPTCHA images -->
                 
            </div><!-- end of reCAPTCHA theme block -->

        </div><!-- end of wrapper - theme block -->
		<div style="clear:both;border-bottom:dotted #ccc 1px;"></div> <!-- separator -->

        <div style="float:left;width:600px;padding-left:24px;margin:12px 0 12px 0">
            <div style="padding:4px 0 4px 0;">
                    <span><strong><?php _e('reCAPTCHA language:', 'skt-nurcaptcha') ?></strong></span><br /><br />
					<span><?php _e("If you want to force the widget to render in a specific language, use the selector below. Auto-detects the user's language if unspecified.", 'skt-nurcaptcha') ?></span><br /><br />
					<?php  echo skt_get_lang_selector(get_site_option('sktnurc_recaptcha_language')) ?>
            </div>
        </div>
		<div style="clear:both"></div>

		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'skt-nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page!','skt-nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
		<div style="clear:both;border-bottom:dotted #ccc 1px;"></div> <!-- separator -->
        <div style="float:left;width:600px;padding-left:24px;margin:12px 0 12px 0">
            <div style="padding:4px 0 4px 0;">
                    
                    <span><strong><?php _e('reCAPTCHA type:', 'skt-nurcaptcha') ?></strong></span><br /><br />
                    <span><?php _e('Once and a while, when needed, reCAPTCHA will present a challenge for the user to solve. By default, this challenge is an image with words or numbers. You can change the challenge to audio type, if you think this is a better fit to your site.', 'skt-nurcaptcha') ?></span><br /><br />
                <p><input type="radio" id="rec_type_image" value="image" name="sktnurc_data_type" <?php 
                        if(get_site_option('sktnurc_data_type')=="image") echo 'checked';
                        ?> /> <?php _e('Use image challenge, when needed','skt-nurcaptcha'); ?><br />
                <input type="radio" id="rec_type_audio" value="audio" name="sktnurc_data_type" <?php 
                        if(get_site_option('sktnurc_data_type')=="audio") echo 'checked';
                        ?> /> <?php _e('Use audio challenge, only','skt-nurcaptcha'); ?><br /></p>
                    
			</div>
        </div>

		<div style="clear:both;border-bottom:dotted #ccc 1px;"></div> <!-- separator -->
        <div style="float:left;width:600px;padding-left:24px;margin:12px 0 12px 0">
            <div style="padding:4px 0 4px 0;">
                    
                    <span><strong><?php _e('enable reCAPTCHA on LOGIN form:', 'skt-nurcaptcha') ?></strong></span><br /><br />
                    <span><?php _e('This feature adds extra security to your site, by inserting a reCAPTCHA at the end of the login form. Thus, even if a bot comes to guess the right username and password, this extra reCAPTCHA may provide additional strenght to block the invasion.', 'skt-nurcaptcha') ?></span><br /><br />
                <p><input type="radio" id="rec_at_login" value="true" name="sktnurc_login_recaptcha" <?php 
                        if(get_site_option('sktnurc_login_recaptcha')=="true") echo 'checked';
                        ?> /> <?php _e('Use login reCAPTCHA, for extra protection.','skt-nurcaptcha'); ?><br />
                <input type="radio" id="rec_not_at_login" value="false" name="sktnurc_login_recaptcha" <?php 
                        if(get_site_option('sktnurc_login_recaptcha')=="false") echo 'checked';
                        ?> /> <?php _e('No, thanks.','skt-nurcaptcha'); ?><br /></p>
                    
			</div>
        </div>
		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'skt-nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page!','skt-nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
		<div style="clear:both;border-bottom:dotted #ccc 1px;"></div> <!-- separator -->
        <div style="float:left;width:600px;padding-left:24px;margin:12px 0 12px 0">
            <div style="padding:4px 0 4px 0;">
                    
                    <span><strong><?php _e('enable reCAPTCHA on selected front-end pages:', 'skt-nurcaptcha') ?></strong></span><br /><br />
                    <span><?php _e('If you have login or register forms located on front-end pages in your site, you must select them at the selector below, so the reCAPTCHA script can be correctly loaded to these pages - ready to be called to action.', 'skt-nurcaptcha') ?></span><br />
                    <span><?php _e('Get some more information on how to set up front pages to display the captcha by reading this article:', 'skt-nurcaptcha') ?></span> <a href="https://skt-nurcaptcha.sanskritforum.org/2015/08/25/recaptcha-anywhere-in-your-theme/" target="_blank">How to implement a reCAPTCHA anywhere in your theme</a>
                    <br /><br />
                <p>
                <span id='custom_pages_button' class="button-primary" style="cursor:pointer;color:#fff;font-weight:bold;background:#4086aa;text-decoration:none;"><?php echo  __('Toggle Selector', 'skt-nurcaptcha'); ?></span>
                <br /></p>
                <div id="debug_output"></div>
        		<div style="clear:both"></div>
                <?php echo skt_nurc_pages_checkbox(); ?>
                <br />
                  
			</div>
        </div>

        <div style="clear:both;border-bottom:dotted #ccc 1px;"></div> <!-- separator -->
		<div style="float:left;width:180px;padding-left:24px;margin:12px 0 12px 0;">
				<p style="position:relative">
				<?php _e("Customize text to appear in Submit Button (register form): ", 'skt-nurcaptcha' ); ?>
                <input type="text" id="sktnurc_regbutton" name="sktnurc_regbutton" 
                value="<?php echo get_site_option('sktnurc_regbutton'); ?>" size="26"><br />
				<?php echo "[ default: <strong><span id='sktnurc_regbutton_text' >". __('Register', 'skt-nurcaptcha' )."</span></strong> ]"; ?>

    			</p>
        </div>
		<div class="regbutton-mock-up" style="margin-left:232px;position:absolute;width:312px;padding:24px 0 12px 0">
				<input style="float:left;margin-right:12px; border:1px solid #fff" type="submit" size="auto" class="button-primary" id="sktnurc-mockup-wp-submit" value="<?php 
					if (get_site_option('sktnurc_regbutton')==""){
						_e("Register", 'skt-nurcaptcha'); 
					} else {
						echo get_site_option('sktnurc_regbutton');
					}
				?>" />
		</div>
		<div style="clear:both"></div>

		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'skt-nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page!','skt-nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
	</div>

<?php

/********************* Customize register form help messages */
			
?>
	<div style="position:relative;width:680px;padding:8px 0 12px 24px">
		<p style="padding: .6em; background-color: #666; color: #fff;">
			<?php echo __( 'Customize register form\'s help messages:', 'skt-nurcaptcha' ) ?>
		</p><br />
    <span>
    <?php _e('Use these fields to change the default help messages that Skt NURCaptcha adds to the register form. To return to default values, simply delete all content from that field and click on the save button.','skt-nurcaptcha'); ?> 
    </span><br /><br />
<?php
	$userHelp = sktnurc_username_help_text();
	$emailHelp = sktnurc_email_help_text();
	$recHelp = sktnurc_reCaptcha_help_text();
			
?>
<strong><?php _e( 'Username:', 'skt-nurcaptcha' ) ?></strong><br />
<textarea id="sktnurc_username_help" name="sktnurc_username_help" cols="90" rows="5">
<?php echo $userHelp ?>
</textarea>
<input class="sktSpam_check" type="checkbox" name="sktnurc_usrhlp_opt" id="sktnurc_usrhlp_opt" value="true" 
	<?php if (get_site_option('sktnurc_usrhlp_opt')=='true') { ?>checked<?php } ?> /> 
	<?php _e('(Hide this help)','skt-nurcaptcha'); ?>
<br /><br />
<strong><?php _e( 'Email:', 'skt-nurcaptcha' ) ?></strong><br />
<textarea id="sktnurc_email_help" name="sktnurc_email_help" cols="90" rows="5">
<?php echo $emailHelp ?>
</textarea>
<input class="sktSpam_check" type="checkbox" name="sktnurc_emlhlp_opt" id="sktnurc_emlhlp_opt" value="true" 
	<?php if (get_site_option('sktnurc_emlhlp_opt')=='true') { ?>checked<?php } ?> /> 
	<?php _e('(Hide this help)','skt-nurcaptcha'); ?>
<br /><br />
    <strong><?php _e( 'reCAPTCHA:', 'skt-nurcaptcha' ) ?></strong><br />
<textarea id="sktnurc_reCaptcha_help" name="sktnurc_v2_reCaptcha_help" cols="90" rows="5">
<?php echo $recHelp ?>
</textarea>
<input class="sktSpam_check" type="checkbox" name="sktnurc_rechlp_opt" id="sktnurc_rechlp_opt" value="true" 
	<?php if (get_site_option('sktnurc_rechlp_opt')=='true') { ?>checked<?php } ?> /> 
	<?php _e('(Hide this help)','skt-nurcaptcha'); ?>
<br />


		<div style="clear:both"></div>

		<p class="submit" >
		<input style="float:right;margin-right:12px; border:1px solid #fff" type="submit" id="submit" class="button-primary" name="submit" value="<?php _e('Update Options', 'skt-nurcaptcha' ) ?>" />
		<span class="save-advert" style="display:none;color:#ff2200;float:right;margin-right:8px"><strong><?php _e('Remember to save your changes before leaving this page!','skt-nurcaptcha'); ?>&nbsp;&raquo;&nbsp;&raquo;&nbsp;&raquo;&nbsp;</strong></span>
		</p>
	</div>
	</form>

<?php

/********************* PayPal Donation Button */
			
?>
	<div style="position:relative;width:680px;padding:8px 0 12px 24px">
		<p style="padding: .6em; background-color: #666; color: #fff;">
				<?php echo __( 'Make me happy:', 'skt-nurcaptcha' ) ?>
				</p>
		<div style="margin-left:32px;padding-bottom:12px">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="hidden" name="hosted_button_id" value="SKNS7K7L5BFLL">
				<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
	</div>
	
</div>	<?php /* end of div.wrap */ ?>

<?php

function nurc_get_version() {
		$npath = nurc_make_path();
		$npath .= 'skt-nurcaptcha.php';
		$lines = file($npath);
		$l = count($lines);
		$version = 'wrong version';
		$i=0;
		while ($i < $l):
			if (trim($lines[$i])=='') {
				$i++;
				continue;
			}
			$r = strpos($lines[$i],'Version:');
			if ($r != false) {
				$version = trim(substr($lines[$i],$r+8)); 
				break;
			}
			$i++;
		endwhile;
		return $version;
		
}


/*
* builds array witth a few country names
*/
function skt_nurc_lang_array()
{
return array(
'xx' => 'Auto-detect user language',
'ar' => 'Arabic', 
'bg' => 'Bulgarian', 
'ca' => 'Catalan', 
'zh-CN' => 'Chinese (Simplified)', 
'zh-TW' => 'Chinese (Traditional)', 
'hr' => 'Croatian', 
'cs' => 'Czech', 
'da' => 'Danish', 
'nl' => 'Dutch',
'en-GB' => 'English (UK)',
'en' => 'English (US)', 
'fil' => 'Filipino', 
'fi' => 'Finnish',
'fr' => 'French',
'fr-CA' => 'French (Canadian)',
'de' => 'German',
'de-AT' => 'German (Austria)',
'de-CH' => 'German (Switzerland)',
'el' => 'Greek',
'iw' => 'Hebrew',
'hi' => 'Hindi',
'hu' => 'Hungarian',
'id' => 'Indonesian',
'it' => 'Italian',
'ja' => 'Japanese', 
'ko' => 'Korean', 
'lv' => 'Latvian',
'lt' => 'Lithuanian',
'no' => 'Norwegian',
'fa' => 'Persian',
'pl' => 'Polish',
'pt' => 'Portuguese',
'pt-BR' => 'Portuguese (Brazil)',
'pt-PT' => 'Portuguese (Portugal)', 
'ro' => 'Romanian', 
'ru' => 'Russian',
'sr' => 'Serbian',
'sk' => 'Slovak',
'sl' => 'Slovenian',
'es' => 'Spanish',
'es-419' => 'Spanish (Latin America)',
'sv' => 'Swedish',
'th' => 'Thai', 
'tr' => 'Turkish', 
'uk' => 'Ukrainian',
'vi' => 'Vietnamese'  
);	
}
//
/* 
************** produz select com os estados brasileiros  *************
*/
function skt_get_lang_selector($selected = 'xx') {
	$uf_array = skt_nurc_lang_array();
	$html = '<select id="sktnurc_lang" name="sktnurc_recaptcha_language">';
	foreach($uf_array as $lg => $lang_name)
	{
		if ($lg==$selected){
			$s = 'selected="selected"';
		}else{
			$s = '';
		}
		$html .= "<option value=\"$lg\" $s >$lang_name ";
		if($lg != 'xx') $html .= "[$lg]";
		$html .= "</option>";
	}	
	
	$html .= '</select>';
	return $html;
}
//
