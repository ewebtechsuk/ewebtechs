<?php
/**
 * LD Dashboard Invite Students Content
 *
 * This file is used to markup the report
 *
 * @link       https://wbcomdesigns.com/
 * @since      5.9.9
 *
 * @package    Custom_Learndash
 * @subpackage Custom_Learndash/public/partials
 */

global $wpdb;
$user_id      = get_current_user_id();
$report_tab   = 'invite-students';
$cours_ids    = ldd_get_user_courses_list( get_current_user_id(), true, true );
$site_name 	  = get_bloginfo('name');
if ( isset($_GET['sub_tab']) ) {
	$report_title = __( 'Sent Invites', 'ld-dashboard' );
	$sub_title 	  = esc_html__( 'You have sent invitations to the following students.', 'ld-dashboard' );
} else {
	$report_title = __( 'Invite New Students', 'ld-dashboard' );
	$sub_title 	  = sprintf( esc_html__( 'Invite students to join %s by following these steps:', 'ld-dashboard' ), $site_name );
}


$function_obj 	= Ld_Dashboard_Functions::instance();
$dashboard_page = $function_obj->ld_dashboard_get_url( 'dashboard' );
$setting_data 	= $function_obj->ld_dashboard_settings_data();
$invite_user  	= $setting_data['invite_user'];


$message_text = sprintf( esc_html__( 'You have been invited by %s to join the %s community.', 'ld-dashboard' ), '[%INVITER_NAME%]', $site_name ); /* Do not translate the strings embedded in %% ... %% ! */

$footer_message_text = apply_filters( 'ld_dashboard_accept_invite_footer_message', esc_html__( 'To accept this invitation, please visit [%ACCEPT_URL%]', 'ld-dashboard' ) );
$footer_message_text .= '

';
$footer_message_text .= apply_filters( 'ld_dashboard_opt_out_footer_message', esc_html__( 'To opt out of future invitations to this site, please visit [%OPTOUT_URL%]', 'ld-dashboard' ) );
		

$invitation_subject	   		= isset($invite_user['invitation_subject']) ? $invite_user['invitation_subject'] : sprintf( esc_html__( 'An invitation to join the %s community.', 'ld-dashboard' ), $site_name );
$invitation_message	   		= isset($invite_user['invitation_message']) ? $invite_user['invitation_message'] : $message_text;
$footer_invitation_message	= isset($invite_user['footer_invitation_message']) ? $invite_user['footer_invitation_message'] : $footer_message_text;
$max_invites				= isset($invite_user['max_invites']) ? $invite_user['max_invites'] : 5;
$subject_is_customizable	= isset($invite_user['subject_is_customizable']) ? $invite_user['subject_is_customizable'] : '';
$message_is_customizable	= isset($invite_user['message_is_customizable']) ? $invite_user['message_is_customizable'] : '';
$date_format				= get_option( 'date_format' );
$time_format				= get_option( 'time_format' );
?>
<div class="wbcom-front-end-course-dashboard-my-courses-content">
	<div class="custom-learndash-list custom-learndash-my-courses-list">
			<div class="ld-dashboard-course-content instructor-courses-list"> 
				<?php if ( array_intersect( wp_get_current_user()->roles, array_keys( ld_dashboard_get_dashboard_user_roles() ) ) ) : ?>
					<div class="ld-dashboard-inline-links">
						<ul class="ld-dashboard-inline-links-ul">
							<li class="<?php echo (isset($_GET['tab']) && !isset($_GET['sub_tab']))? 'course-nav-active':'';?>">
								<a href="<?php echo $dashboard_page . '?tab=invite-students';?>">
									<?php esc_html_e( 'Invite New Students', 'ld-dashboard');?>
								</a>
							</li>
							<li class="<?php echo (isset($_GET['tab']) && isset($_GET['sub_tab']))? 'course-nav-active':'';?>">
								<a href="<?php echo $dashboard_page . '?tab=invite-students&sub_tab=sent-invites';?>">
									<?php esc_html_e( 'Sent Invites', 'ld-dashboard');?>
								</a>
							</li>
						</ul>
					</div>
				<?php endif;?>
				<div class="ld-dashboard-invite-students ld-dashboard-section-head-title">
					<h3><?php echo $report_title; ?></h3>
					<div class="ld-dashboard-content-inner">
						<p class="description"><?php echo $sub_title;?></p>
					</div>
				</div>
				<div class="my-courses ld-dashboard-invite-content-wrap ld-dashboard-content-inner ld-dashboard-tab-content-wrapper">
					<?php do_action( 'ld_dashboard_before_invite_students' ); ?>
					<?php
						if ( array_intersect( wp_get_current_user()->roles, array_keys( ld_dashboard_get_dashboard_user_roles() ) ) ) { ?>
						<?php if( isset($_GET['sub_tab'])):
							$table_name 		= $wpdb->prefix . 'ld_dashboard_invite_user';
							$where_search 		= "Where user_id={$user_id}";
							$total_query        = "SELECT count(*) as count FROM $table_name {$where_search}";
							$total              = $wpdb->get_var( $total_query );
							$items_per_page     = 20;
							$page               = ( isset( $_GET['cpage'] ) ) ? abs( (int) $_GET['cpage'] ) : 1;
							$offset             = ( $page * $items_per_page ) - $items_per_page;						
							$_invite_users      = $wpdb->get_results( "SELECT * FROM $table_name {$where_search} ORDER BY id DESC LIMIT {$offset}, {$items_per_page}" );
							$total_page         = ceil( $total / $items_per_page );						
						?>
						<div class="ld-dashboard-sent-invites-details">
							<?php if ( $total_page > 1 ) { ?>
								<div class="ld-dashboard-pagination">
									<div class="ld-dashboard--pagination pagination-bottom">
										<?php									
										echo paginate_links(
											array(
												'base' 		=> add_query_arg( 'cpage', '%#%' ),
												'format' 	=> '',
												'current' 	=> $page,
												'total' 	=> $total_page,
												'prev_text' => __( '&larr;', 'ld-dashboard' ),
												'next_text' => __( '&rarr;', 'ld-dashboard' ),
											)
										);
										?>
									</div>						
								</div>
							<?php } ?>
							
							<table class="invite-anyone-sent-invites zebra">
								<thead>
									<tr>									
										<th scope="col" class="col-email">
											<?php _e( 'Invited email address', 'ld-dashboard' ) ?>
										</th>
										<th scope="col" class="col-status">
											<?php _e( 'User Status', 'ld-dashboard' ) ?>
										</th>
										<th scope="col" class="col-group-invitations">
											<?php _e( 'Course invitations', 'ld-dashboard' ) ?>
										</th>
										<th scope="col" class="col-date-invited">
											<?php _e( 'Sent', 'ld-dashboard' ) ?>
										</th>
										<th scope="col" class="col-date-joined">
											<?php _e( 'Accepted', 'ld-dashboard' ) ?>
										</th>
									</tr>
								</thead>

								

								<tbody>
									<?php
									if ( ! empty( $_invite_users ) ) :
										foreach ( $_invite_users as $key => $invite ) :
											$courses = json_decode($invite->courses, true);
											if ( !empty( $courses ) ) {
												$course_names = '<ul>';
												foreach( $courses as $course ) {											
													$course_names .= '<li>' . get_the_title( $course ) . '</li>';
												}
												$course_names .= '</ul>';
											} else {
												$course_names = '-';
											}
											?>
										<tr class=""<?php if( $invite->invite_accepted == 'yes' ){ ?> class="accepted" <?php } ?>>
											<td data-title="<?php _e( 'Invited email address', 'ld-dashboard' ) ?>" class="col-email"><?php echo esc_html( $invite->invited_email ) ?></td>
											<td data-title="<?php _e( 'User Status', 'ld-dashboard' ) ?>" class="col-email-status"><?php echo esc_html( ($invite->invited_email_status == 'true')? 'Existing': 'Non Existing' ); ?></td>
											<td data-title="<?php _e( 'Course invitations', 'ld-dashboard' ) ?>" class="col-group-invitations"><?php echo $course_names ?></td>
											<td data-title="<?php _e( 'Sent', 'ld-dashboard' ) ?>" class="col-date-invited"><?php echo date_i18n( $date_format, strtotime( $invite->created ) );?></td>
											<td data-title="<?php _e( 'Accepted', 'ld-dashboard' ) ?>" class="date-joined col-date-joined"><?php echo ($invite->invite_accepted_date != '0000-00-00 00:00:00')? date_i18n( $date_format, strtotime( $invite->invite_accepted_date ) ): '-';?></td>
										</tr>
										<?php 
											endforeach;
										else: ?>
										<tr>
											<td colspan="5" class="no-invite-accepted-email" ><?php _e( "You haven't sent any email invitations yet.", 'ld-dashboard' ) ?></td>
										</tr>
									<?php endif;?>
								</tbody>
							</table>
							
							<?php if ( $total_page > 1 ) { ?>
								<div class="ld-dashboard-pagination">						
									<div class="ld-dashboard--pagination pagination-bottom">
										<?php									
										echo paginate_links(
											array(
												'base' 		=> add_query_arg( 'cpage', '%#%' ),
												'format' 	=> '',
												'current' 	=> $page,
												'total' 	=> $total_page,
												'prev_text' => __( '&larr;', 'ld-dashboard' ),
												'next_text' => __( '&rarr;', 'ld-dashboard' ),
											)
										);
										?>
									</div>						
								</div>
							<?php } ?>
							</div>
						<?php else: ?>
						
							<form method="post" action="">
								<ol id="ld-dashboard-invite-students-steps">
									<li>
										<div class="manual-email">
											<label for="ld-dashboard-email-addresses">
												<?php _e( 'Enter email addresses below, one per line.', 'ld-dashboard' ) ?>
											</label>
											<p class="description"><?php printf( __( 'You can invite a maximum of %s people at a time.', 'ld-dashboard' ), $max_invites ) ?></p>										
											<textarea name="ld-dashboard[invite_students][email_addresses]" class="ld-dashboard-email-addresses" id="ld-dashboard-email-addresses"></textarea>
										</div>

									</li>
									
									<li>
										<?php if ( $subject_is_customizable == '1' ) : ?>
											<label for="ld-dashboard-email-subject"><?php _e( '(optional) Customize the subject line of the invitation email.', 'ld-dashboard' ) ?></label>
											<textarea name="ld-dashboard[invite_students][email_subject]" id="ld-dashboard-email-subject" rows="15" cols="10" ><?php echo esc_html($invitation_subject); ?></textarea>
										<?php else : ?>
											<strong><?php _e( 'Subject:', 'ld-dashboard' ) ?></strong> <?php echo esc_html( $invitation_subject ) ?>

											<input type="hidden" id="ld-dashboard-customised-subject" name="ld-dashboard[invite_students][email_subject]" value="<?php echo esc_attr($invitation_subject) ?>" />
										<?php endif; ?>
									</li>
									
									<li>
										<?php if ( $message_is_customizable == '1' ) : ?>
											<label for="ld-dashboard-email-message"><?php _e( '(optional) Customize the text of the invitation.', 'ld-dashboard' ) ?></label>
											<p class="description"><?php _e( 'The message will also contain a email footer containing links to accept the invitation or opt out of further email invitations from this site.', 'ld-dashboard' ) ?></p>
												<textarea name="ld-dashboard[invite_students][email_message]" id="ld-dashboard-email-message" cols="40" rows="10"><?php echo esc_textarea($invitation_message ) ?></textarea>
										<?php else : ?>
											<label for="ld-dashboard-email-message"><?php _e( 'Message:', 'ld-dashboard' ) ?></label>
												<textarea name="ld-dashboard[invite_students][email_message]" id="ld-dashboard-email-message" disabled="disabled"><?php echo esc_textarea( $invitation_message) ?></textarea>
										<?php endif; ?>

									</li>
									
									<?php if ( !empty($cours_ids) ) : ?>
										
										<li>
											<fieldset>
												<legend><?php esc_html_e( '(optional) Select some courses. Invitees will receive invitations to these courses when they join the site.', 'ld-dashboard' ); ?></legend>
												<ul id="ld-dashboard-group-list">
													<?php foreach($cours_ids as $course):?>												
														<li>
															<input type="checkbox" name="ld-dashboard[invite_students][invite_courses][]" id="ld-dashboard-courses-<?php echo esc_attr($course ); ?>" value="<?php echo esc_attr( $course ); ?>"  />

															<label for="ld-dashboard-courses-<?php echo esc_attr( $course ) ?>" class="ld-dashboard-group-name"> <span><?php echo get_the_title($course) ?></span></label>

														</li>
													<?php endforeach; ?>

												</ul>
											</fieldset>
										</li>								

									<?php endif; ?>
									
									<?php do_action( 'ld_dashboard_invite_student_additional_fields' ); ?>
								</ol>
								
								<div class="submit">
									<?php wp_nonce_field( 'invite_students_send_by_email', 'ld-dashboard-send-by-email-nonce' ); ?>
									
									<input type="submit" name="ld-dashboard-invite-student-submit" id="ld-dashboard-invite-student-submit" value="<?php _e( 'Send Invites', 'ld-dashboard' ) ?> " />
								</div>
							</form>
							
						<?php endif;?>
					<?php
						} else {
							echo __( 'You must be a admin, group leader of instrutor to access this page.', 'ld-dashboard' );
							return;
						}
						?>
					
					<?php do_action( 'ld_dashboard_after_invite_student' ); ?>
				</div>
			</div>
	</div>
</div>
