<?php 
namespace ArcStone\AMO;
AMODiv::$css_id = 'amo-login_form-wrapper';
echo AMODiv::get_header();

$website_url = get_option( 'wpamo_website_url' );

$after_login_url = ( isset( $atts['after_login_url'] ) ) ?  site_url( esc_url( $atts['after_login_url'] )) : '';
$after_logout_url = ( isset( $atts['after_logout_url'] ) ) ? site_url( esc_url( $atts['after_logout_url'] ) ) : site_url('/');


if ( !is_user_logged_in() ) : ?>
<div id="amo-login_error" style="display:none"></div>
<form action="<?php echo site_url( '/wpamo-login' ); ?>" method="post" id="amo-login_form">
	<input type="hidden" name="action" value="amo_login">
	<input type="hidden" name="destination" value="<?php echo $after_login_url ?>" />
	<div class="amo-form-group">
		<label for="wp-amo_username">Username</label>
		<input type="text" name="username" value="" id="wp-amo_username" required="required" />
	</div>
	<div class="amo-form-group">
		<label for="wp-amo_password">Password</label>
		<input type="password" name="password" value="" id="wp-amo_username" required="required" />
	</div>
	<button type="submit" name="submit" class="btn btn-primary btn-amo">Login</button>
</form>
<a href="<?php echo esc_url( $website_url . '/site_forgot_password.cfm', 'http' )?>" class="amo-password_reset">Reset Password</a>
<?php else: ?>
	<p>You are already logged in. <a href="<?php echo wp_logout_url( $after_logout_url ) ?>">Logout</a>.</p>
<?php endif; ?>

<?php echo AMODiv::get_footer(); ?>