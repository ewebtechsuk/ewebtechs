<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $ld_instructor_error_msgs, $current_user;

if ( is_multisite() ) {
	$registration_setting = get_site_option( 'users_can_register' );
} else {
	$registration_setting = get_option( 'users_can_register' );
}
$is_registration_enabled = ( $registration_setting && 1 == $registration_setting ) ? true : false;

if ( ! is_user_logged_in() && $is_registration_enabled ) : ?>
	<div class="ld-dashboard-main-wrapper">
		<?php
		if ( ! empty( $ld_instructor_error_msgs ) && $ld_instructor_error_msgs != '' ) {
			echo '<div class="ld-dashboard-instructor-error">';
			echo $ld_instructor_error_msgs[0];
			echo '</div>';
		}
		?>
		<form id="ld-instructor-reg-form" action="" method="post" >
			<div class="ld-dashboard-form-row">
				<div class="ld-dashboard-form-col-6">
					<div class="ld-dashboard-form-group">
						<label><?php esc_html_e( 'First Name', 'ld-dashboard' ); ?></label>
						<input type="text" id="ld_dashboard_first_name" name="first_name" value="" placeholder="<?php esc_html_e( 'First Name', 'ld-dashboard' ); ?>">
						<p class="ld_dashboard_first_name ld-dashboard-error"><?php esc_html_e( 'Enter your first name', 'ld-dashboard' ); ?></p>
					</div>
				</div>
				<div class="ld-dashboard-form-col-6">
					<div class="ld-dashboard-form-group">
						<label><?php esc_html_e( 'Last Name', 'ld-dashboard' ); ?></label>
						<input type="text" id="ld_dashboard_last_name" name="last_name" value="" placeholder="<?php esc_html_e( 'Last Name', 'ld-dashboard' ); ?>">
						<p  class="ld-dashboard-error ld_dashboard_last_name"><?php esc_html_e( 'Enter your last name', 'ld-dashboard' ); ?></p>
					</div>
				</div>
			</div>

			<div class="ld-dashboard-form-row">
				<div class="ld-dashboard-form-col-6">
					<div class="ld-dashboard-form-group">
						<label><?php esc_html_e( 'User Name', 'ld-dashboard' ); ?></label>
						<input type="text" id="ld_dashboard_username" name="user_login" class="ld-dashboard_user_name" value="" placeholder="<?php esc_html_e( 'User Name', 'ld-dashboard' ); ?>">
						<p  class="ld-dashboard-error ld_dashboard_username"><?php esc_html_e( 'Enter your username', 'ld-dashboard' ); ?></p>
					</div>
				</div>

				<div class="ld-dashboard-form-col-6">
					<div class="ld-dashboard-form-group">
						<label><?php esc_html_e( 'E-Mail', 'ld-dashboard' ); ?></label>
						<input type="text" id="ld_dashboard_email" name="email" value="" placeholder="<?php esc_html_e( 'E-Mail', 'ld-dashboard' ); ?>">
						<p class="ld-dashboard-error ld_dashboard_email"><?php esc_html_e( 'Enter your email address', 'ld-dashboard' ); ?></p>
						<p class="ld-dashboard-error ld_dashboard_email_wrong"><?php esc_html_e( 'Enter your corrent email address', 'ld-dashboard' ); ?></p>
					</div>
				</div>
			</div>

			<div class="ld-dashboard-form-row">
				<div class="ld-dashboard-form-col-6">
					<div class="ld-dashboard-form-group">
						<label><?php esc_html_e( 'Password', 'ld-dashboard' ); ?></label>
						<input type="password" id="ld_dashboard_password" name="password" value="" placeholder="<?php esc_html_e( 'Password', 'ld-dashboard' ); ?>">
						<p  class="ld-dashboard-error ld_dashboard_password"><?php esc_html_e( 'Enter your password', 'ld-dashboard' ); ?></p>
					</div>
				</div>

				<div class="ld-dashboard-form-col-6">
					<div class="ld-dashboard-form-group">
						<label><?php esc_html_e( 'Password confirmation', 'ld-dashboard' ); ?></label>
						<input type="password" id="ld_dashboard_password_confirmation"  name="password_confirmation" value="" placeholder="<?php esc_html_e( 'Password Confirmation', 'ld-dashboard' ); ?>">
						<p class="ld-dashboard-error ld_dashboard_password_confirmation"><?php esc_html_e( 'Enter your confirmation password', 'ld-dashboard' ); ?></p>
						<p class="ld-dashboard-error ld_dashboard_password_confirmation_wrong"><?php esc_html_e( 'Password doesn\'t match', 'ld-dashboard' ); ?></p>
					</div>
				</div>
			</div>

			<div class="ld-dashboard-form-row">
				<div class="ld-dashboard-form-col-12">
					<div class="ld-dashboard-form-group ld-dashboard-reg-form-btn-wrap">
						<button type="submit" name="ld_dashboard_register_instructor_btn" value="register" class="ld-dashboard-button"><?php esc_html_e( 'Register as instructor', 'ld-dashboard' ); ?></button>
					</div>
				</div>
			</div>
			<?php wp_nonce_field( 'ld_instructor_reg_action', 'ld_instructor_reg_action' ); ?>
			<input type="hidden" value="ld_dashboard_instructor_registration" name="ld_instructor_reg_action"/>
		</form>	
	</div>
	<?php
endif;
if ( ! is_user_logged_in() && ! $is_registration_enabled ) :
	?>
	<div class="ld-dashboard-no-access-page status-instructor-wrap ld-dashboard-alert-warning ld-dashboard-instructor-alert">
		<div class="ld-dashboard-no-page-access-image-container">
			<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/Access-Denied.png'; ?>" alt="<?php echo esc_attr__( 'Access Denied', 'ld-dashboard' ); ?>">
		</div>
		<h2><?php esc_html_e( 'Oooh! Access Denied', 'ld-dashboard' ); ?></h2>
		<p>
			<?php
			echo esc_html__( 'Sorry, Only registered users can apply for the instructor. Please login or signup to apply for instructor capabilities.', 'ld-dashboard' );
			?>
		</p>
		<div class="ld-dashboard-home-btn-container">
			<a href="<?php echo esc_url( site_url() ); ?>" class="ld-dashboard-home-btn"><?php echo esc_html__( 'Go To Home', 'ld-dashboard' ); ?></a>
		</div>
	</div>
	<?php
endif;
if ( is_user_logged_in() && ( in_array( 'ld_instructor', (array) $current_user->roles ) || in_array( 'ld_instructor_pending', (array) $current_user->roles ) ) ) :
	?>
	<div class="status-instructor-wrap ld-dashboard-alert-warning ld-dashboard-instructor-alert">
		<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'public/img/instructor.png'; ?>">
		<h2><?php esc_html_e( 'You are instructor', 'ld-dashboard' ); ?></h2>
		<p>
			<?php
			echo sprintf( __( 'Registered at : %1$s %2$s', 'ld-dashboard' ), date_i18n( get_option( 'date_format' ), strtotime( $current_user->user_registered ) ), date_i18n( get_option( 'time_format' ), strtotime( $current_user->user_registered ) ) );
			?>
		</p>
		<div class="status-instructor-alert">
			<strong class="pending">
			<?php
			if ( in_array( 'ld_instructor_pending', (array) $current_user->roles ) ) {
				$status = esc_html__( 'Pending', 'ld-dashboard' );
			} else {
				$status = esc_html__( 'Approved', 'ld-dashboard' );
			}
			echo sprintf( __( 'Status : %s', 'ld-dashboard' ), $status );
			?>
			</strong>
		</div>
	</div>
<?php elseif ( is_user_logged_in() && ! in_array( 'administrator', (array) $current_user->roles ) ) : ?>
	<form method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( 'ld_dashboarda_apply_instructor_action', 'ld_dashboarda_apply_instructor_nonce_field' ); ?>
		<input type="hidden" value="ld_apply_instructor" name="ld_dashboard_action"/>
		<div class="ld-dashboard-form-row">
			<div class="ld-dashboard-form-col-12">
				<div class="ld-dashboard-form-group">
					<button type="submit" name="lld_register_instructor_btn" value="apply">
						<?php esc_html_e( 'Apply to become an instructor', 'ld-dashboard' ); ?>
					</button>
				</div>
			</div>
		</div>
	</form>
<?php elseif ( is_user_logged_in() && in_array( 'administrator', (array) $current_user->roles ) ) : ?>
	<div class="ld-dashboard-form-row">
		<div class="ld-dashboard-form-col-12">
			<div class="ld-dashboard-form-group"><?php echo esc_html__( 'You already have all the capabilities of an instructor.', 'ld-dashboard' ); ?></div>
		</div>
	</div>
<?php endif; ?>
