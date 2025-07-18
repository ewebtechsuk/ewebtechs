<?php
/**
 * Provide a admin area view for instructors setting.
 *
 * This file is used to markup the course fields setting aspects of the plugin.
 *
 * @link       https://wbcomdesigns.com/plugins
 * @since      1.0.0
 *
 * @package    Ld_Dashboard
 * @subpackage Ld_Dashboard/admin/partials
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'Ld_Dashboard_Instructor_Settings' ) ) {
	/**
	 * Fired during plugin activation.
	 *
	 * This class defines all code necessary to create wp list table.
	 *
	 * @since      1.0.0
	 * @package    Bp_Stats
	 * @subpackage Bp_Stats/admin
	 * @author     Wbcom Designs <admin@wbcomdesigns.com>
	 */
	class Ld_Dashboard_Instructor_Settings extends WP_List_Table {

		/**
		 * Prepare data to display.
		 */
		public function prepare_items() {
			$columns  = $this->get_columns();
			$hidden   = $this->get_hidden_columns();
			$sortable = $this->get_sortable_columns();
			$views    = $this->views();
			$data     = $this->table_data();
			// usort( $data, array( &$this, 'sort_data' ) );.

			$per_page     = 20;
			$current_page = isset( $_REQUEST['paged'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['paged'] ) ) : 1;
			$total_items  = count( $data );

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);

			$data = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items           = $data;
		}

		/**
		 * Get all views
		 */
		public function views() {
			$filter = ( filter_input( INPUT_GET, 'filter' ) !== null ) ? filter_input( INPUT_GET, 'filter' ) : '';
			?>
			<ul class='subsubsub'>
				<li class='ld-dashboard-table-view-single <?php echo ( '' === $filter ) ? 'ld-dashboard-instructor-active-view' : ''; ?>'><a href='<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=ld-dashboard-settings&tab=ld-dashboard-instructor-settings'><?php echo esc_html__( 'All', 'ld-dashboard' ); ?></a></li>
				<li class='ld-dashboard-table-view-single <?php echo ( 'approved' === $filter ) ? 'ld-dashboard-instructor-active-view' : ''; ?>'><a href='<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=ld-dashboard-settings&tab=ld-dashboard-instructor-settings&filter=approved'><?php echo esc_html__( 'Approved', 'ld-dashboard' ); ?></a></li>
				<li class='ld-dashboard-table-view-single <?php echo ( 'pending' === $filter ) ? 'ld-dashboard-instructor-active-view' : ''; ?>'><a href='<?php echo esc_url( site_url() ); ?>/wp-admin/admin.php?page=ld-dashboard-settings&tab=ld-dashboard-instructor-settings&filter=pending'><?php echo esc_html__( 'Pending', 'ld-dashboard' ); ?></a></li>
			</ul>
			<?php
		}

		/**
		 * Get all columns.
		 */
		public function get_columns() {
			$columns = array(
				'user_name'  => 'Username',
				'name'       => 'Name',
				'user_email' => 'User Email',
				'role'       => 'Role',
				'status'     => 'Status',
				'commission' => 'Commission',
			);
			return $columns;
		}

		/**
		 * Get hidden columns.
		 */
		public function get_hidden_columns() {
			return array();
		}

		/**
		 * Get sortable columns.
		 */
		public function get_sortable_columns() {
			return array( 'id' => array( 'id', true ) );
		}

		/**
		 * Get table data.
		 */
		private function table_data() {
			global $wpdb, $wp_roles;

			$data                       = array();
			$function_obj               = Ld_Dashboard_Functions::instance();
			$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
			$settings                   = $ld_dashboard_settings_data['monetization_settings'];
			$args                       = array();
			$args['orderby']            = 'ID';
			$args['order']              = 'ASC';
			$args['role__in']           = array( 'ld_instructor', 'ld_instructor_pending' );

			$all_users        = get_users( $args );
			$registered_roles = $wp_roles->roles;
			$all_roles        = array();
			if ( is_array( $registered_roles ) ) {
				foreach ( $registered_roles as $slug => $value ) {
					$all_roles[ $slug ] = $value['name'];
				}
			}

			foreach ( $all_users as $us ) {
				$roles = '';

				$user = get_userdata( $us->ID );

				foreach ( $user->roles as $i => $val ) {
					$roles .= ( isset( $all_roles[ $val ] ) ) ? $all_roles[ $val ] : $val;
					if ( $i < count( $user->roles ) - 1 ) {
						$roles .= ', ';
					}
				}
				$commission_percent = get_user_meta( $us->ID, 'instructor-course-commission', true );
				$commission_percent = ( $commission_percent && '' !== $commission_percent && $commission_percent > 0 ) ? $commission_percent : 0;
				$actions            = '<div class="ld-dashboard-instructor-container"><div class="ld-dashboard-instructor-username">' . $user->data->user_login . '</div>';
				$status_label       = 'Approved';
				$status_class       = 'ld-dashboard-instructor-status ld-dashboard-instructor-status-approved';
				if ( in_array( 'ld_instructor_pending', $user->roles ) && ! in_array( 'administrator', $user->roles ) ) {
					$status_label = 'Pending';
					$status_class = 'ld-dashboard-instructor-status ld-dashboard-instructor-status-pending';
					$actions     .= '<div class="ld-dashboard-action-container" data-id="' . $us->ID . '"><span class="ld-dashboard-action-single --approve">' . esc_html__( 'Approve', 'ld-dashboard' ) . '</span><span class="ld-dashboard-action-single --reject">' . esc_html__( 'Reject', 'ld-dashboard' ) . '</span></div>';
				} elseif ( in_array( 'administrator', $user->roles ) ) {
					continue;
				} elseif ( in_array( 'ld_instructor', $user->roles ) ) {
					$actions .= '<div class="ld-dashboard-action-container" data-id="' . $us->ID . '"><span class="ld-dashboard-action-single --remove">' . esc_html__( 'Remove instructor', 'ld-dashboard' ) . '</span></div>';
				}
				if ( isset( $_GET['filter'] ) ) {
					$filter = sanitize_text_field( wp_unslash( $_GET['filter'] ) );
					if ( ucfirst( $filter ) != $status_label ) {
						continue;
					}
				}
				$commission_btn = '<div class="ld-dashboard-instructor-commission-container">';
				if ( ld_if_commission_enabled() && 'Approved' === $status_label ) {
					if ( (int) $commission_percent > 0 ) {
						$commission_btn .= '<input type="number" class="ld-dashboard-commission-value" min="0" max="100" value="' . $commission_percent . '"><button class="ld-dashboard-set-instructor-commission" data-user="' . $us->ID . '">' . esc_html__( 'Set %', 'ld-dashboard' ) . '</button>';
					} else {
						$commission_btn .= '<button class="ld-dashboard-set-instructor-commission-btn" data-user="' . $us->ID . '">' . esc_html__( 'Add', 'ld-dashboard' ) . '</button>';
					}
				}
				$commission_btn .= '</div>';
				$actions        .= '</div>';
				$status          = '<div class="' . $status_class . '">' . $status_label . '</div>';
				$data[]          = array(
					'user_name'  => $actions,
					'name'       => $user->data->display_name,
					'user_email' => $user->data->user_email,
					'role'       => $roles,
					'status'     => $status,
					'commission' => $commission_btn,
				);
			}
			return $data;
		}

		/**
		 * Get default column data.
		 *
		 * @param item        $item Item.
		 * @param column_name $column_name Column name.
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'user_name':
				case 'name':
				case 'user_email':
				case 'role':
				case 'status':
				case 'commission':
					return $item[ $column_name ];

				default:
					return print_r( $item, true );
			}
		}

		/**
		 * Get default column data.
		 *
		 * @param a $a a.
		 * @param b $b b.
		 */
		private function sort_data( $a, $b ) {
			$orderby = ( ! empty( $_GET['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'id';
			$order   = ( ! empty( $_GET['order'] ) ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'asc';
			$result  = strcmp( $a[ $orderby ], $b[ $orderby ] );
			return ( 'asc' === $order ) ? $result : -$result;
		}

	}
}

$instructor_nonce           = wp_create_nonce( 'add-new-instructor' );
$is_add_new_instructor_page = false;
if ( isset( $_GET['action'] ) && isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'add-new-instructor' ) ) {
	$is_add_new_instructor_page = true;
}
?>
<div class="wbcom-tab-content">
<div class="ld-dashboard-wrapper-admin">
	<div class="ld-dashboard-wrapper-section">
	<div class="ld-dashboard-instructor-settings ld-dashboard-settings">
		<div class="wbcom-admin-title-section title-tutorial-section-wrap">
				<?php if ( $is_add_new_instructor_page ) : ?>
					<h3 class="ld-dashboard-current-tab"><?php echo esc_html__( 'Add New Instructor', 'ld-dashboard' ); ?></h3>
					<?php else : ?>
					<h3 class="ld-dashboard-current-tab"><?php echo esc_html__( 'All Instructors', 'ld-dashboard' ); ?></h3>
					<a href="https://docs.wbcomdesigns.com/docs/learndash-dashboard/ls-instructors/instructor-listing/" class="ld-tutorial-btn" target="_blank"><?php esc_html_e( 'View Tutorial', 'ld-dashboard' ); ?></a>
				</div>
				<div class="ld-dashboard-add-instructor-btn-wrapper">
					<a href="<?php echo esc_url( site_url() ) . '/wp-admin/admin.php?page=ld-dashboard-settings&tab=ld-dashboard-instructor-settings&action=add-new&nonce=' . esc_attr( $instructor_nonce ); ?>" class="ld-dashboard-add-instructor-btn">
						<?php echo esc_html__( 'Add New Instructor', 'ld-dashboard' ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>
		<div class="ld-dashboard-content-container">
			<div class="wbcom-admin-option-wrap ld-dashboard-content">
				<?php
				if ( $is_add_new_instructor_page ) :
					?>
					<div class="ld-dashboard-add-new-instructor-wrapper">
						<div class="ld-dashboard-add-instructor-message-container"></div>
						<form id="ldd-add-new-instructor-form" method="POST">
							<div class="wbcom-settings-section-wrap">
							<table class="ldd-add-new-instructor-form-table">
								<tr>
									<td class="ldd-new-instructor-field">
										<label><?php echo esc_html__( 'First Name', 'ld-dashboard' ); ?></label>
										<input type="text" name="first_name" />
									</td>
									<td class="ldd-new-instructor-field">
										<label><?php echo esc_html__( 'Last Name', 'ld-dashboard' ); ?></label>
										<input type="text" name="last_name" />
									</td>
								</tr>
								<tr>
									<td class="ldd-new-instructor-field">
										<label><?php echo esc_html__( 'Username', 'ld-dashboard' ); ?></label>
										<input type="text" name="username" />
									</td>
									<td class="ldd-new-instructor-field">
										<label><?php echo esc_html__( 'Email', 'ld-dashboard' ); ?></label>
										<input type="email" name="email" />
									</td>
								</tr>
								<tr>
									<td class="ldd-new-instructor-field">
										<label><?php echo esc_html__( 'Password', 'ld-dashboard' ); ?></label>
										<input type="password" class="ldd-new-instructor-pass" name="pass" />
									</td>
									<td class="ldd-new-instructor-field">
										<label><?php echo esc_html__( 'Confirm Password', 'ld-dashboard' ); ?></label>
										<input type="password" class="ldd-new-instructor-pass-cnf" name="confirm_pass" />
									</td>
								</tr>
								<tr>
									<td class="ldd-new-instructor-field instructor-biographical">
										<label><?php echo esc_html__( 'Biographical Info', 'ld-dashboard' ); ?></label>
										<textarea name="bio"></textarea>
									</td>
								</tr>
								<tr>
									<td class="ldd-new-instructor-field" style="margin:0"><button class="ldd-add-new-instructor-submit-btn"><?php echo esc_html__( 'Add Instructor', 'ld-dashboard' ); ?></button></td>
								</tr>
							</table>
							</div>
						</form>
					</div>
					<?php
				else :
					$obj = new Ld_Dashboard_Instructor_Settings();
					$obj->prepare_items();
					$obj->display();
				endif;
				?>
			</div>
		</div>
	</div>
</div>
</div>
