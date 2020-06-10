<div class="ab-sub-wrapper">
	<div class="ab-submenu">
		<form name="login-form" style="display:none;" id="sidebar-login-form" class="standard-form form" action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post">
			<label><?php esc_html_e( 'Username', 'commons-in-a-box' ); ?><br /><input type="text" name="log" id="dropdown-user-login" class="input form-control" value="" /></label><br />
			<label><?php esc_html_e( 'Password', 'commons-in-a-box' ); ?><br /><input class="form-control" type="password" name="pwd" id="dropdown-user-pass" class="input" value="" /></label>
			<p class="forgetmenot checkbox"><label><input name="rememberme" type="checkbox" id="dropdown-rememberme" value="forever" /> <?php esc_html_e( 'Keep Me Logged In', 'commons-in-a-box' ); ?></label></p>
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( bp_get_requested_url() ); ?>" />
			<input type="submit" name="wp-submit" id="dropdown-wp-submit" class="btn btn-primary sidebar-wp-submit" value="<?php esc_html_e( 'Log In', 'commons-in-a-box' ); ?>" tabindex="0" />
			<span class="exit"><a href="<?php echo wp_lostpassword_url(); ?>" class="lost-pw"><?php esc_html_e( 'Forgot Password?', 'commons-in-a-box' ); ?></a></span>
		</form>
	</div>
</div>

