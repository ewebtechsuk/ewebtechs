<?php
$current_user_id    = get_current_user_id();
$user_data          = get_userdata( $current_user_id );
$user_name          = $user_data->user_nicename;
$user_email         = $user_data->user_email;
$user_firstname     = get_user_meta( $user_id, 'first_name', true );
$user_lastname      = get_user_meta( $user_id, 'last_name', true );
$user_description   = get_user_meta( $user_id, 'description', true );
$user_registered    = gmdate( 'F j, Y H:i:s', strtotime( $user_data->user_registered ) );
$user_phone_number  = get_the_author_meta( 'phone_number', $user_data->ID );
$dashboard_page_url = Ld_Dashboard_Functions::instance()->ld_dashboard_get_url( 'dashboard' );
?>
<div class="ld-dashboard-my-profile">
	<div class="ld-dashboard-section-head-title">
		<h3 class="ld-dashboard-nav-title"><?php esc_html_e( 'My Profile', 'ld-dashboard' ); ?></h3>
		<div class="ld-dashboard-header-button ld-dashboard-add-new-button-container">
			<a class="ld-dashboard-add-course ld-dashboard-add-new-button ld-dashboard-btn-bg" href="<?php echo esc_url( $dashboard_page_url ); ?>?tab=settings">
			<span class="ld-icons ld-icon-edit-box-line edit_square"></span> <?php esc_html_e( 'Edit Profile', 'ld-dashboard' ); ?></a>
		</div>
	</div>
	<table class="table_content_ld">
	<tr class="registeration-date">
		<td><?php echo esc_html__( 'Registration Date', 'ld-dashboard' ); ?></td>
		<td><?php echo esc_html( $user_registered ); ?></td>
	</tr>
	<tr class="first-name">
		<td><?php echo esc_html__( 'First Name', 'ld-dashboard' ); ?></td>
		<td>
		<?php
		if ( $user_firstname != '' ) {
			echo esc_html( $user_firstname );
		} else {
			echo esc_html( '_________' );
		}
		?>
		</td>
	</tr>
	<tr class="last-name">
		<td><?php echo esc_html__( 'Last Name', 'ld-dashboard' ); ?></td>
		<td>
			<?php
			if ( $user_lastname != '' ) {
				echo esc_html( $user_lastname );
			} else {
				echo esc_html( '_________' );
			}
			?>
		</td>
	</tr>
	<tr class="user-name">
		<td><?php echo esc_html__( 'User Name', 'ld-dashboard' ); ?></td>
		<td><?php echo esc_html( $user_name ); ?></td>
	</tr>
	<tr class="email">
		<td><?php echo esc_html__( 'Email', 'ld-dashboard' ); ?></td>
		<td><?php echo esc_html( $user_email ); ?></td>
	</tr>
	<tr class="phone-number">
		<td><?php echo esc_html__( 'Phone Number', 'ld-dashboard' ); ?></td>
		<td>
			<?php
			if ( $user_phone_number != '' ) {
				echo esc_html( $user_phone_number );
			} else {
				echo esc_html( '__________' );
			}
			?>
		</td>
	</tr>
	<tr class="bio">
		<td><?php echo esc_html__( 'Bio', 'ld-dashboard' ); ?></td>
		<td>
		<?php
		if ( $user_description != '' ) {
			echo esc_html( $user_description );
		} else {
			echo esc_html( '__________' );
		}
		?>
		</td>
	</tr>
	</table>
</div>

