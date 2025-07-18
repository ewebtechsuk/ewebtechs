<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com/plugins
 * @since      1.0.0
 *
 * @package    Ld_Dashboard
 * @subpackage Ld_Dashboard/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ld_Dashboard
 * @subpackage Ld_Dashboard/admin
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Ld_Dashboard_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * All tabs of settings page.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      array    $plugin_settings_tabs  The tabs of plugin's admin settings.
	 */
	private $plugin_settings_tabs;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 'wp-color-picker' );
		if ( ! wp_style_is( 'font-awesome', 'enqueued' ) ) {
			wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
		}
		wp_enqueue_style( $this->plugin_name . '-selectize-css', plugin_dir_url( __FILE__ ) . 'css/selectize.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'ld-dashboard-zoom', plugin_dir_url( __FILE__ ) . 'css/ld-dashboard-admin-zoom.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'jquery-ui-datepicker-style', plugin_dir_url( __FILE__ ) . 'css/jquery.ui.datepicker.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/ld-dashboard-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Ld_dashboard_register_acf_groups
	 *
	 * @return void
	 */
	public function ld_dashboard_register_acf_groups() {
		$register_acf_certificate_group  = new Ld_Dashboard_Register_Announcement_Group_Fields();
		$register_acf_certificate_group  = new Ld_Dashboard_Register_Certificate_Group_Fields();
		$register_acf_question_group     = new Ld_Dashboard_Register_Question_Group_Fields();
		$register_acf_quiz_group         = new Ld_Dashboard_Register_Quiz_Group_Fields();
		$register_acf_lesson_group       = new Ld_Dashboard_Register_Lesson_Group_Fields();
		$register_acf_topic_group        = new Ld_Dashboard_Register_Topic_Group_Fields();
		$register_acf_course_group       = new Ld_Dashboard_Register_Course_Group_Fields();
		$register_acf_share_course_group = new Ld_Dashboard_Register_Share_Course_Field();
		$register_acf_group_fields       = new Ld_Dashboard_Register_Group_Fields();
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_script( $this->plugin_name . '-1a', plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.min.js', array( 'jquery' ) );
		wp_enqueue_style( $this->plugin_name . '-2a', plugin_dir_url( __FILE__ ) . 'css/jquery.dataTables.min.css' );

		// wp_enqueue_script( 'plugin-jquery-ui-js', plugin_dir_url( __FILE__ ) . 'js/jquery-ui.js' );
		// wp_enqueue_style( 'plugin-jquery-ui-css', plugin_dir_url( __FILE__ ) . '/css/jquery-ui.css', array(), $this->version, 'all' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( $this->plugin_name . '-sweetalert', plugin_dir_url( __FILE__ ) . 'js/sweetalert.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name . '-selectize-js', plugin_dir_url( __FILE__ ) . 'js/selectize.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'js/ld-dashboard-admin.js', array( 'jquery', 'wp-i18n', 'jquery-ui-datepicker', 'jquery-ui-accordion' ), $this->version, false );
		wp_set_script_translations( $this->plugin_name . '-admin', 'ld-dashboard', LD_DASHBOARD_PLUGIN_DIR . 'languages/' );
		wp_localize_script(
			$this->plugin_name . '-admin',
			'ld_dashboard_obj',
			array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'field_ajax_nonce' => wp_create_nonce( 'ajax-nonce' ),
				'ajax_nonce'       => wp_create_nonce( 'ld_dashboard_ajax_security' ),
			)
		);
		wp_enqueue_media();

	}

	/**
	 * Hide all notices from the setting page.
	 *
	 * @return void
	 */
	public function wbcom_hide_all_admin_notices_from_setting_page() {
		$wbcom_pages_array  = array( 'wbcomplugins', 'wbcom-plugins-page', 'wbcom-support-page', 'ld-dashboard-settings', 'wbcom-license-page' );
		$wbcom_setting_page = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : '';

		if ( in_array( $wbcom_setting_page, $wbcom_pages_array, true ) ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	}

	/*
	 * Add Instructor user Role.
	 */
	public function ld_dashboard_add_instructor_role() {
		global $wp_roles;
		if ( $GLOBALS['wp_roles']->is_role( 'ld_instructor' ) !== false ) {
			$instructor = get_role( 'ld_instructor' );
			if ( $instructor->has_cap( 'read_group' ) ) {
				$wp_roles->remove_cap( 'ld_instructor', 'read_group' );
			}
			if ( $instructor->has_cap( 'edit_groups' ) ) {
				$wp_roles->remove_cap( 'ld_instructor', 'edit_groups' );
			}
		}

		if ( $GLOBALS['wp_roles']->is_role( 'ld_instructor' ) === false ) {

			$instructor_caps = array(
				'wpProQuiz_show'               => true,
				'wpProQuiz_add_quiz'           => true,
				'wpProQuiz_edit_quiz'          => true,
				'wpProQuiz_delete_quiz'        => true,
				'wpProQuiz_show_statistics'    => true,
				'wpProQuiz_change_settings'    => true,
				'read_course'                  => true,
				'publish_courses'              => true,
				'edit_courses'                 => true,
				'edit_others_courses'          => true,
				'edit_private_courses'         => true,
				'delete_courses'               => true,
				'edit_course'                  => true,
				'delete_course'                => true,
				'edit_published_courses'       => true,
				'delete_published_courses'     => true,
				'edit_assignment'              => true,
				'edit_assignments'             => true,
				'publish_assignments'          => true,
				'read_assignment'              => true,
				'delete_assignment'            => true,
				'edit_published_assignments'   => true,
				'delete_published_assignments' => true,
				'propanel_widgets'             => true,
				'read'                         => true,
				'edit_others_assignments'      => true,
				'instructor_reports'           => true,
				'instructor_page'              => true,
				'manage_categories'            => true,
				'wpProQuiz_toplist_edit'       => true,
				'upload_files'                 => true,
				'edit_essays'                  => true,
				'edit_others_essays'           => true,
				'publish_essays'               => true,
				'read_essays'                  => true,
				'edit_private_essays'          => true,
				'delete_essays'                => true,
				'edit_published_essays'        => true,
				'delete_others_essays'         => true,
				'delete_published_essays'      => true,
				'edit_posts'                   => true,
				'edit_post'                    => true,
				'publish_posts'                => true,
				'edit_published_posts'         => true,
				'delete_posts'                 => true,
				'delete_published_posts'       => true,
				'delete_product'               => true,
				'delete_products'              => true,
				'delete_published_products'    => true,
				'edit_product'                 => true,
				'edit_products'                => true,
				'edit_published_products'      => true,
				'publish_products'             => true,
				'read_product'                 => true,
				'assign_product_terms'         => true,
				'unfiltered_html'              => true,
			);

			$instructor_caps_woo = array(
				'delete_product'            => true,
				'delete_products'           => true,
				'delete_published_products' => true,
				'edit_product'              => true,
				'edit_products'             => true,
				'edit_published_products'   => true,
				'publish_products'          => true,
				'read_product'              => true,
				'assign_product_terms'      => true,
			);

			$ld_instructor_caps = array_merge( $instructor_caps_woo, $instructor_caps );
			$wp_roles->add_role( 'ld_instructor', 'Instructor', $ld_instructor_caps );
		} else {
			$role = get_role( 'ld_instructor' );
			// Add a new capability.
			$role->add_cap( 'edit_others_courses', true );
			$role->add_cap( 'edit_private_courses', true );
			$role->add_cap( 'unfiltered_html', true );
		}

		if ( $GLOBALS['wp_roles']->is_role( 'ld_instructor_pending' ) === false ) {
			$wp_roles->add_role( 'ld_instructor_pending', 'Instructor Pending', array() );
		}

		$contributor = get_role( 'contributor' );
		$subscriber  = get_role( 'subscriber' );

		if ( ! empty( $contributor ) ) {
			$contributor->add_cap( 'upload_files' );

		}

		if ( ! empty( $subscriber ) ) {
			$subscriber->add_cap( 'upload_files' );
		}

		$group_leader = get_role( 'group_leader' );
		if ( ! empty( $group_leader ) ) {
			$group_leader->add_cap( 'upload_files' );
		}
	}

	public function ld_dashboard_set_zoom_co_hosts( $old_value, $value, $option ) {
		$zoom             = new Zoom_Api();
		$add_hosts        = array();
		$remove_hosts     = array();
		$existing_users   = array();
		$registered_users = $zoom->get_all_users();
		if ( is_object( $registered_users ) && property_exists( $registered_users, 'users' ) ) {
			foreach ( $registered_users->users as $usr ) {
				$userdata = get_user_by( 'email', $usr->email );
				if ( is_object( $userdata ) ) {
					$existing_users[] = $userdata->ID;
				}
			}
		}
		$old_co_hosts = ( isset( $old_value['zoom-co-hosts'] ) && ! empty( $old_value['zoom-co-hosts'] ) ) ? $old_value['zoom-co-hosts'] : array();
		$new_co_hosts = ( isset( $value['zoom-co-hosts'] ) && ! empty( $value['zoom-co-hosts'] ) ) ? $value['zoom-co-hosts'] : array();
		$add_hosts    = array_diff( $new_co_hosts, $old_co_hosts );
		$remove_hosts = array_diff( $old_co_hosts, $new_co_hosts );
		if ( ! isset( $value['zoom-co-hosts'] ) ) {
			$remove_hosts = $old_co_hosts;
		}
		if ( ! empty( $add_hosts ) ) {
			foreach ( $add_hosts as $add_host ) {
				if ( ! in_array( $add_host, $existing_users ) ) {
					$zoom->create_user( $add_host );
				}
			}
		}
		if ( ! empty( $remove_hosts ) ) {
			foreach ( $remove_hosts as $remove_host ) {
				if ( in_array( $remove_host, $existing_users ) ) {
					$zoom->delete_user( $remove_host );
				}
			}
		}
	}

	/**
	 * Ld_dashboard_nav_menus
	 */
	public function ld_dashboard_nav_menus() {
		register_nav_menu( 'ld-dashboard-profile-menu', esc_html__( 'LearnDash Dashboard Profile Menu', 'ld-dashboard' ) );
	}

	/**
	 * Add submenu for LearnDash Dashboard setting.
	 */
	public function ld_dashboard_menu_page() {

		if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {
			add_menu_page( esc_html__( 'WB Plugins', 'ld-dashboard' ), esc_html__( 'WB Plugins', 'ld-dashboard' ), 'manage_options', 'wbcomplugins', array( $this, 'ld_dashboard_settings_page' ), 'dashicons-lightbulb', 59 );
			add_submenu_page( 'wbcomplugins', esc_html__( 'General', 'ld-dashboard' ), esc_html__( 'General', 'ld-dashboard' ), 'manage_options', 'wbcomplugins' );
		}
		add_submenu_page( 'wbcomplugins', esc_html__( 'LD Dashboard', 'ld-dashboard' ), esc_html__( 'LD Dashboard', 'ld-dashboard' ), 'manage_options', 'ld-dashboard-settings', array( $this, 'ld_dashboard_settings_page' ) );
	}

	/**
	 * Actions performed to create tabs on the sub menu page.
	 *
	 * @since  1.0.0
	 * @author Wbcom Designs
	 * @access public
	 */
	public function ld_dashboard_plugin_settings_tabs() {
		$current = ( filter_input( INPUT_GET, 'tab' ) !== null ) ? filter_input( INPUT_GET, 'tab' ) : 'ld-dashboard-welcome';

		$tab_html = '<div class="wbcom-tabs-section"><div class="nav-tab-wrapper"><div class="wb-responsive-menu"><span>' . esc_html__( 'Menu', 'ld-dashboard' ) . '</span><input class="wb-toggle-btn" type="checkbox" id="wb-toggle-btn"><label class="wb-toggle-icon" for="wb-toggle-btn"><span class="wb-icon-bars"></span></label></div><ul>';
		foreach ( $this->plugin_settings_tabs as $ldd_tab => $tab_name ) {
			$class     = ( $ldd_tab === $current ) ? 'nav-tab-active' : '';
			$page      = 'ld-dashboard-settings';
			$tab_html .= '<li><a id="' . $ldd_tab . '" class="nav-tab ' . $class . '" href="admin.php?page=' . $page . '&tab=' . $ldd_tab . '">' . $tab_name . '</a></li>';
		}
		$tab_html .= '</div></ul></div>';
		echo $tab_html;
	}

	public function ld_dashboard_get_custom_preset_fields_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$type                       = ( isset( $_POST['type'] ) ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$function_obj               = Ld_Dashboard_Functions::instance();
		$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
		if ( 'default' === $type ) {
			$settings = $ld_dashboard_settings_data['default_design_options'];
		} elseif ( 'custom' === $type ) {
			$settings = $ld_dashboard_settings_data['design_options'];
		}
		$fields = array(
			'color'       => array(
				'label' => esc_html__( 'Primary Color', 'ld-dashboard' ),
				'desc'  => esc_html__( 'Choose a primary color.', 'ld-dashboard' ),
			),
			'hover_color' => array(
				'label' => esc_html__( 'Primary Hover Color', 'ld-dashboard' ),
				'desc'  => esc_html__( 'Choose a primary hover color.', 'ld-dashboard' ),
			),
			'text_color'  => array(
				'label' => esc_html__( 'Text Color', 'ld-dashboard' ),
				'desc'  => esc_html__( 'Choose a text color for your website.', 'ld-dashboard' ),
			),
			'background'  => array(
				'label' => esc_html__( 'Background', 'ld-dashboard' ),
				'desc'  => esc_html__( 'Choose a background color for your website.', 'ld-dashboard' ),
			),
			'border'      => array(
				'label' => esc_html__( 'Border', 'ld-dashboard' ),
				'desc'  => esc_html__( 'Choose a border color for your website.', 'ld-dashboard' ),
			),
		);
		ob_start();
		foreach ( $fields as $menu_key => $field ) :
			?>
		<div class="wbcom-settings-section-wrap">
			<div class="wbcom-settings-section-options-heading" scope="row">
				<label><?php echo esc_html( $field['label'] ); ?></label>
				<p class="description">
					<?php echo esc_html( $field['desc'] ); ?>
				</p>
			</div>
			<div class="wbcom-settings-section-options ld-dashboard-color-options-value ld-dashboard-grid-content">
				<div class="ld-dashboard-color-lavel-title">
					<label class="ld-dashboard-setting-switch">
						<input type="text" class="ld-dashboard-setting ld-dashboard-menu-tab-checkbox ld-dashboard-design-color-picker ld-dashboard-color" data-id="<?php echo esc_attr( $menu_key ); ?>" name="ld_dashboard_design_settings[<?php echo esc_attr( $menu_key ); ?>]" value="<?php echo ( isset( $settings[ $menu_key ] ) ) ? $settings[ $menu_key ] : ''; ?>" data-id="<?php echo esc_attr( $menu_key ); ?>" />
					</label>					
				</div>
			</div>
		</div>
			<?php
			endforeach;
		echo ob_get_clean();
		exit();
	}

	public function ld_dashboard_get_instructors_listing_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$instructors                = get_users( 'role=ld_instructor' );
		$function_obj               = Ld_Dashboard_Functions::instance();
		$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
		$settings                   = $ld_dashboard_settings_data['zoom_meeting_settings'];
		ob_start();
		if ( ! empty( $instructors ) ) {
			?>
			<select class="ld-dashboard-co-host-instructors" name="ld_dashboard_zoom_meeting_settings[zoom-co-hosts][]" multiple>
			<?php
			foreach ( $instructors as $instructor ) {
				?>
				<option value="<?php echo esc_attr( $instructor->ID ); ?>" <?php echo ( isset( $settings['zoom-co-hosts'] ) && in_array( $instructor->ID, $settings['zoom-co-hosts'] ) ) ? 'selected' : ''; ?>><?php echo esc_html( $instructor->data->display_name ); ?></option>
				<?php
			}
			?>
			</select>
			<?php
		}
		echo ob_get_clean();
		exit;
	}

	public function ld_dashboard_add_new_instructor_user_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$form_raw_data = ( isset( $_POST['form_data'] ) ) ? wp_unslash( $_POST['form_data'] ) : array();
		$form_data     = array();
		foreach ( $form_raw_data as $value ) {
			$form_data[ $value['name'] ] = $value['value'];
		}
		$msg = '';
		if ( false !== email_exists( $form_data['email'] ) ) {
			$msg .= 'Email';
		}
		if ( false !== username_exists( $form_data['username'] ) ) {
			$msg .= ( '' != $msg ) ? ', ' : '';
			$msg .= 'Username';
		}
		$msg .= ( '' != $msg ) ? ' already exists!' : '';
		if ( false === email_exists( $form_data['email'] ) && false === username_exists( $form_data['username'] ) ) {
			$username = strtolower( $form_data['username'] );
			$username = str_replace( ' ', '_', $username );
			$userdata = array(
				'user_pass'    => $form_data['pass'],
				'user_login'   => $username,
				'user_email'   => $form_data['email'],
				'display_name' => $form_data['first_name'] . ' ' . $form_data['last_name'],
				'first_name'   => $form_data['first_name'],
				'last_name'    => $form_data['last_name'],
				'description'  => $form_data['bio'],
				'role'         => 'ld_instructor',
			);
			$user_id  = wp_insert_user( $userdata );
		}
		echo esc_html( $msg );
		exit();
	}

	public function ld_dashboard_add_meeting_custom_column( $columns ) {
		unset( $columns['comments'] );
		return array_merge(
			$columns,
			array(
				'actions' => esc_html__( 'Actions', 'ld-dashboard' ),
			)
		);
	}

	public function ld_dashboard_add_meeting_custom_column_content( $column_key, $post_id ) {
		if ( 'actions' === $column_key ) {
			$meeting_id = get_post_meta( $post_id, 'zoom_meeting_id', true );
			$start_url  = get_post_meta( $post_id, 'zoom_meeting_start_url', true );
			if ( ld_dashboard_can_user_start_meeting( $post_id ) ) {
				?>
			<div class="ld-dashboard-meetings-actions-wrapper">
				<a href="<?php echo esc_url( $start_url ); ?>" class="ld-dashboard-meeting-action ld-dashboard-meeting-start-btn" target="_blank"><?php echo esc_html__( 'Start', 'ld-dashboard' ); ?></a>
				<a href="#" class="ld-dashboard-meeting-action  ld-delete-meeting-action" data-post="<?php echo esc_attr( $post_id ); ?>" data-meeting="<?php echo esc_attr( $meeting_id ); ?>"><img src="" /><?php echo esc_html__( 'Delete', 'ld-dashboard' ); ?></a>
			</div>
				<?php
			}
		}
	}

	// Add custom column to Withdrawals post table.
	public function ld_dashboard_add_withdrawal_custom_column( $columns ) {

		return array_merge(
			$columns,
			array(
				'amount' => esc_html__( 'Amount', 'ld-dashboard' ),
				'method' => esc_html__( 'Withdraw Method', 'ld-dashboard' ),
				'status' => esc_html__( 'Status', 'ld-dashboard' ),
			)
		);
	}

	// Add custom column content to Withdrawals post table.
	public function ld_dashboard_add_withdrawal_custom_column_content( $column_key, $post_id ) {
		$labels = array(
			'bank_transfer'        => esc_html__( 'Bank Transfer', 'ld-dashboard' ),
			'e_check'              => esc_html__( 'E-Check', 'ld-dashboard' ),
			'paypal'               => esc_html__( 'Paypal', 'ld-dashboard' ),
			'ldd_account_name'     => esc_html__( 'Account Name', 'ld-dashboard' ),
			'ldd_account_number'   => esc_html__( 'Account Number', 'ld-dashboard' ),
			'ldd_bank_name'        => esc_html__( 'Bank Name', 'ld-dashboard' ),
			'ldd_iban'             => esc_html__( 'IBAN', 'ld-dashboard' ),
			'ldd_bic_swift'        => esc_html__( 'BIC / SWIFT', 'ld-dashboard' ),
			'ldd_physical_address' => esc_html__( 'Physical Addesss', 'ld-dashboard' ),
			'ldd_paypal_email'     => esc_html__( 'Paypal Email', 'ld-dashboard' ),
		);
		if ( 'amount' === $column_key ) {
			$amount   = get_post_meta( $post_id, 'withdrawal_amount', true );
			$currency = ( version_compare( LEARNDASH_VERSION, '4.1.0', '<' ) ) ? learndash_30_get_currency_symbol() : learndash_get_currency_symbol();
			echo wp_kses_post( $currency ) . ' ' . esc_html( $amount );
		}
		if ( 'method' === $column_key ) {
			$method          = get_post_meta( $post_id, 'withdrawal_method', true );
			$withdrawal_data = get_post_meta( $post_id, 'withdrawal_data', true );
			$method_label    = ( isset( $labels[ $method ] ) ) ? esc_html( $labels[ $method ] ) : '';
			echo '<div class="ld-dashboard-withdrawal-data-column"><table>';
			if ( is_array( $withdrawal_data ) ) {
				foreach ( $withdrawal_data as $field => $value ) {
					if ( 'ldd_withdraw_method' === $field ) {
						echo '<tr><td colspan="2" class="ld-dashboard-withdrawal-data-column-title">' . $labels[ $value ] . '</td></tr>';
					} else {
						echo '<tr><td class="ld-dashboard-withdrawal-data-column-title">' . $labels[ $field ] . '</td><td class="ld-dashboard-withdrawal-data-column-value">' . $value . '</td></tr>';
					}
				}
			}
			echo '</table></div>';
		}
		if ( 'status' === $column_key ) {
			$approved = get_post_meta( $post_id, 'withdrawal_status', true );
			if ( 1 == $approved ) {
				echo '<span class="ld-dashboard-withdrawal-status-single ld-dashboard-withdrawal-approved" style="color:green;">';
				esc_html_e( 'Approved', 'ld-dashboard' );
				echo '</span>';
			} elseif ( 0 == $approved ) {
				echo '<span class="ld-dashboard-withdrawal-status-single ld-dashboard-withdrawal-pending" style="color:yellow;">';
				esc_html_e( 'Pending', 'ld-dashboard' );
				echo '</span>';
			} elseif ( 2 == $approved ) {
				echo '<span class="ld-dashboard-withdrawal-status-single ld-dashboard-withdrawal-rejected" style="color:red;">';
				esc_html_e( 'Rejected', 'ld-dashboard' );
				echo '</span>';
			}
		}
	}

	public function ld_dashboard_modify_withrawal_post_row_actions( $actions, $post ) {
		if ( 'withdrawals' === $post->post_type ) {
			$approved = get_post_meta( $post->ID, 'withdrawal_status', true );
			if ( 0 == $approved ) {
				$actions = array(
					'approved' => sprintf(
						'<a href="#" class="ld-dashboard-process-withrawal-request ld-dashboard-approve-withrawal-request" data-id="' . $post->ID . '">%1$s</a>',
						esc_html__( 'Approve', 'ld-dashboard' )
					),
					'reject'   => sprintf(
						'<a href="#" class="ld-dashboard-process-withrawal-request ld-dashboard-reject-withrawal-request" data-id="' . $post->ID . '">%1$s</a>',
						esc_html__( 'Reject', 'ld-dashboard' )
					),
				);
			} else {
				$actions = array();
			}
		}
		return $actions;
	}

	public function ld_dashboard_process_withrawal_request_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$action       = ( isset( $_POST['type'] ) ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$request_id   = ( isset( $_POST['post_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0;
		$request_data = get_post( $request_id );
		$status       = 2;
		if ( 'approve' === $action ) {
			/*
			$instructor_wallet_balance = get_user_meta( $request_data->post_author, 'instructor_wallet_balance', true );
			$amount                    = get_post_meta( $request_id, 'withdrawal_amount', true );
			$remaining_balance         = $instructor_wallet_balance - $amount;
			update_user_meta( $request_data->post_author, 'instructor_wallet_balance', $remaining_balance );
			*/
			$status = 1;
		}
		/**
		 * 1 : approve
		 * 2 : reject
		 */
		update_post_meta( $request_id, 'withdrawal_status', $status );
		do_action( 'ld_dashboard_after_withdrawal_request_process', $request_id, $status );
		exit();
	}

	/**
	 * Ld_dashboard_set_instructor_role_callback
	 *
	 * @return void
	 */
	public function ld_dashboard_set_instructor_role_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$action   = ( isset( $_POST['type'] ) ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
		$user_id  = ( isset( $_POST['user_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : 0;
		$response = '';
		if ( '' !== $action && 0 != $user_id ) {
			$user    = new WP_User( $user_id );
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			if ( 'approve' === $action ) {
				$user->remove_role( 'ld_instructor_pending' );
				$user->add_role( 'ld_instructor' );
				$response = 'Approved';
				$subject  = __( 'Instructor Application Approved', 'ld-dashboard' );
				$message  = __( 'Hi there, ', 'ld-dashboard' );
				$message .= '<br>';
				$message .= '<br>';
				$message .= __( 'Congratulations!, Your request to become an instructor has been approved. Keep publishing courses.', 'ld-dashboard' );

				wp_mail( $user->user_email, $subject, $message, $headers );
			} elseif ( 'reject' === $action ) {
				if ( in_array( 'ld_instructor_pending', $user->roles ) ) {
					$user->remove_role( 'ld_instructor_pending' );
				}
				if ( in_array( 'ld_instructor', $user->roles ) ) {
					$user->remove_role( 'ld_instructor' );
				}
				if ( ! in_array( 'subscriber', $user->roles ) ) {
					$user->add_role( 'subscriber' );
				}
				$response = 'Rejected';

				$subject  = __( 'Instructor Application Rejected', 'ld-dashboard' );
				$message  = __( 'Hi there, ', 'ld-dashboard' );
				$message .= '<br>';
				$message .= '<br>';
				$message .= __( 'Sorry, Your request to become an instructor has been rejected. Please try after some time.', 'ld-dashboard' );
				wp_mail( $user->user_email, $subject, $message, $headers );
			} elseif ( 'remove' === $action ) {
				if ( in_array( 'ld_instructor', $user->roles ) ) {
					$user->remove_role( 'ld_instructor' );
					$response = 'removed';
				}
			}
		}
		echo esc_html( $response );
		exit();
	}

	public function ld_dashboard_set_instructor_commission_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$commission = ( isset( $_POST['commission'] ) ) ? sanitize_text_field( wp_unslash( $_POST['commission'] ) ) : 0;
		$user_id    = ( isset( $_POST['user_id'] ) ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : 0;
		if ( $user_id > 0 ) {
			update_user_meta( $user_id, 'instructor-course-commission', $commission );
		}
		wp_die();
	}

	public function set_frontend_form_fields_callback() {
		if ( isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ajax-nonce' ) ) {
			exit();
		}
		$group = ( isset( $_POST['group'] ) ) ? sanitize_text_field( wp_unslash( $_POST['group'] ) ) : '';
		if ( '' !== $group ) {
			ob_start();
			include 'partials/ld-dashboard-course-fields-setting.php';
			$response = ob_get_clean();
			echo $response;
		}
		wp_die();
	}

	public function ld_dashboard_welcome_settings() {
		include 'partials/ld-dashboard-welcome-page.php';
	}

	/**
	 * Get general settings html.
	 *
	 * @since  1.0.0
	 * @author Wbcom Designs
	 * @access public
	 */
	public function ld_dashboard_general_settings() {
		include 'partials/ld-dashboard-general-settings.php';
	}

	public function ld_dashboard_design_settings() {
		include 'partials/ld-dashboard-design-settings.php';
	}

	public function ld_dashboard_integration() {
		include 'partials/ld-dashboard-integration.php';
	}
	public function ld_dashboard_welcome_screen() {
		include 'partials/ld-dashboard-welcome-screen.php';
	}
	public function ld_dashboard_feed_settings() {
		include 'partials/ld-dashboard-feed-settings.php';
	}
	public function ld_dashboard_tiles_options() {
		include 'partials/ld-dashboard-tiles-options.php';
	}
	public function ld_dashboard_menu_options() {
		include 'partials/ld-dashboard-menu-options.php';
	}
	public function ld_dashboard_page_mapping() {
		include 'partials/ld-dashboard-page-mapping.php';
	}

	public function ld_dashboard_set_frontend_form_fields() {
		include 'partials/ld-dashboard-frontend-fields-setting.php';
	}

	public function ld_dashboard_manage_instructors() {
		include 'partials/ld-dashboard-instructors-setting.php';
	}

	public function ld_dashboard_manage_monetization() {
		include 'partials/ld-dashboard-monetization-setting.php';
	}

	public function ld_dashboard_commission_report_settings() {
		include 'partials/ld-dashboard-commission-report.php';
	}

	public function ld_dashboard_zoom_meeting_settings() {
		include 'partials/ld-dashboard-zoom-meeting-setting.php';
	}

	public function ld_dashboard_support() {
		include 'partials/ld-dashboard-support.php';
	}
	
	public function ld_dashboard_time_tracking() {
		include 'partials/ld-dashboard-time-tracking.php';
	}
	public function ld_dashboard_invite_user() {
		include 'partials/ld-dashboard-invite-user.php';
	}
	public function ld_dashboard_email_logs() {
		include 'partials/ld-dashboard-email-logs.php';
	}

	public function ld_ajax_update_instructor_commission() {
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'ld_ajax_update_instructor_commission' ) {
			check_ajax_referer( 'ld_dashboard_ajax_security', 'ajax_nonce' );

			$instructor_id         = $_POST['instructor_id'];
			$instructor_commission = (int) $_POST['instructor_commission'];

			update_user_meta( $instructor_id, 'instructor-course-commission', $instructor_commission );
		}
	}

	public function ld_ajax_generate_instructor_data() {
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'ld_ajax_generate_instructor_data' ) {
			check_ajax_referer( 'ld_dashboard_ajax_security', 'ajax_nonce' );

			global $wpdb;

			$instructor_id = $_POST['instructor_id'];

			$query                = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'ld_dashboard_instructor_commission_logs WHERE user_id = %d order by ID DESC', $instructor_id );
			$course_purchase_data = $wpdb->get_results( $query, ARRAY_A );
			$tr_html              = '';
			$tfoot_html           = '';
			if ( is_array( $course_purchase_data ) ) {
				$count                    = 0;
				$instructor_total_earning = 0;
				foreach ( $course_purchase_data as $key => $value ) {
					if ( $count % 2 == 0 ) {
						$class = 'even';
					} else {
						$class = 'odd';
					}
					$instructor_total_earning += $value['commission'];

					$fees_type = $value['fees_type'];
					$fee_typpe = '';
					if ( $fees_type == 'percent' ) {
						$fee_typpe = '%';
					}

					$course_pricing = learndash_get_course_price( $value['course_id'] );
					$tr_html       .= '<tr class="' . $class . '" role="row">';
					$tr_html       .= '<td>' . $value['id'] . '</td>';
					$tr_html       .= '<td>' . $value['created'] . '</td>';
					$tr_html       .= '<td><a href="' . get_the_permalink( $value['course_id'] ) . '">' . get_the_title( $value['course_id'] ) . '</a></td>';
					$tr_html       .= '<td>' . $course_pricing['price'] . '</td>';
					$tr_html       .= '<td>' . $value['commission'] . '</td>';
					$tr_html       .= '<td>' . $value['commission_rate'] . '</td>';
					$tr_html       .= '<td>' . $value['fees_amount'] . $fee_typpe . '</td>';
					$tr_html       .= '<td>' . $value['source_type'] . '</td>';
					$tr_html       .= '<td>#' . $value['reference'] . '</td>';
					$tr_html       .= '</tr>';
					$count++;
				}
				$instructor_paid_earning   = ld_dashboard_instrictor_paid_earnings( $instructor_id );
				$instructor_unpaid_earning = $instructor_total_earning - $instructor_paid_earning;

				$tfoot_html .= '<tr>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '<td>' . esc_html( 'Paid Earning', 'ld-dashboard' ) . '</td>';
				$tfoot_html .= '<td>' . $instructor_paid_earning . '</td>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '</tr>';
				$tfoot_html .= '<tr>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '<td>' . esc_html( 'Unpaid Earning', 'ld-dashboard' ) . '</td>';
				$tfoot_html .= '<td>' . $instructor_unpaid_earning . '</td>';
				/*
				$tfoot_html .= '<td colspan="2">
				<a class="instructor-pay-amount" href="#" data-instructor-id="' . $instructor_id . '" data-unpaid-amt="' . $instructor_unpaid_earning . '" data-paid-amt="' . $instructor_paid_earning . '" data-total-earning="' . $instructor_total_earning . '">' . esc_html( 'Pay', 'ld-dashboard' ) . '</a>

				<span class="ld-dashboard-export"><a class="ld-dashboard-export-instructor-commission ld-dashboard-btn" href="' . admin_url( 'admin.php?page=ld-dashboard-settings&tab=ld-dashboard-commission-report&ld-export=instructor-commission&instructor-id=' . $instructor_id . '&export-format=csv' ) . '" target="Blank">' . __( 'Export CSV', 'ld-dashboard' ) . '</a></span>


				</td>';*/

				$tfoot_html .= '<td colspan="2">
				<span class="ld-dashboard-export"><a class="ld-dashboard-export-instructor-commission ld-dashboard-btn" href="' . admin_url( 'admin.php?page=ld-dashboard-settings&tab=ld-dashboard-commission-report&ld-export=instructor-commission&instructor-id=' . $instructor_id . '&export-format=csv' ) . '" target="Blank">' . __( 'Export CSV', 'ld-dashboard' ) . '</a></span>
				
				
				</td>';

				$tfoot_html .= '<td></td>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '<td></td>';
				$tfoot_html .= '</tr>';
			} else {
				$tr_html .= '<tr>';
				$tr_html .= '<td></td>';
				$tr_html .= '<td></td>';
				$tr_html .= '<td>' . sprintf( esc_html__( 'No %s has been purchased yet', 'ld-dashboard' ), esc_html( LearnDash_Custom_Label::get_label( 'courses' ) ) ) . '</td>';
				$tr_html .= '<td></td>';
				$tr_html .= '<td></td>';
				$tr_html .= '<td></td>';
				$tr_html .= '<td></td>';
				$tr_html .= '</tr>';
			}
			$response_array = array(
				'tr_html'    => $tr_html,
				'tfoot_html' => $tfoot_html,
			);
			echo json_encode( $response_array );
			exit();
		}
	}

	public function ld_ajax_pay_instructor_amount() {
		if ( isset( $_POST['action'] ) && 'ld_ajax_pay_instructor_amount' === $_POST['action'] ) {
			check_ajax_referer( 'ld_dashboard_ajax_security', 'ajax_nonce' );

			$instructor_id = wp_unslash( $_POST['instructor_id'] );
			$paid_earning  = wp_unslash( $_POST['paid_earning'] );
			$paying_amount = wp_unslash( $_POST['paying_amount'] );
			$instructor_id = wp_unslash( $_POST['instructor_id'] );

			$instructor_paid_earning = (int) $paid_earning + (int) $paying_amount;
			update_user_meta( $instructor_id, 'instructor_paid_earning', $instructor_paid_earning );
			exit();
		}
	}

	/**
	 * Register all settings.
	 *
	 * @since  1.0.0
	 * @author Wbcom Designs
	 * @access public
	 */
	public function ld_dashboard_register_admin_setting() {
		$function_obj               = Ld_Dashboard_Functions::instance();
		$ld_dashboard_settings_data = $function_obj->ld_dashboard_settings_data();
		$settings                   = $ld_dashboard_settings_data['general_settings'];
		$enable_zoom                = ( isset( $settings['enable-zoom'] ) && 1 == $settings['enable-zoom'] ) ? true : false;
		$this->plugin_settings_tabs['ld-dashboard-welcome'] = esc_html__( 'Welcome', 'ld-dashboard' );
		$this->plugin_settings_tabs['ld-dashboard-general'] = esc_html__( 'General', 'ld-dashboard' );
		$this->plugin_settings_tabs['ld-dashboard-design']  = esc_html__( 'Design', 'ld-dashboard' );
		if ( isset( $settings['statistics-tiles'] ) && $settings['statistics-tiles'] == 1 ) {
			$this->plugin_settings_tabs['ld-dashboard-tiles-options'] = esc_html__( 'Dashboard Tiles', 'ld-dashboard' );
		}
		$this->plugin_settings_tabs['ld-dashboard-menu-options']  = esc_html__( 'Dashboard Menu', 'ld-dashboard' );
		$this->plugin_settings_tabs['ld-dashboard-feed-settings'] = esc_html__( 'Student Activity', 'ld-dashboard' );

		$this->plugin_settings_tabs['ld-dashboard-frontend-field-settings'] = esc_html__( 'Fields Restrictions', 'ld-dashboard' );

		$this->plugin_settings_tabs['ld-dashboard-instructor-settings'] = esc_html__( 'Instructors', 'ld-dashboard' );
		if ( $enable_zoom ) {
			$this->plugin_settings_tabs['ld-dashboard-zoom-setting'] = esc_html__( 'Zoom Meetings', 'ld-dashboard' );
			register_setting( 'ld_dashboard_zoom_meeting_settings', 'ld_dashboard_zoom_meeting_settings' );
			add_settings_section( 'ld-dashboard-zoom-setting', ' ', array( $this, 'ld_dashboard_zoom_meeting_settings' ), 'ld-dashboard-zoom-setting' );
		}
		if ( ld_if_commission_enabled() ) {
			$this->plugin_settings_tabs['ld-dashboard-monetization-settings'] = esc_html__( 'Monetization', 'ld-dashboard' );
			$this->plugin_settings_tabs['ld-dashboard-commission-report']     = esc_html__( 'Commission Report', 'ld-dashboard' );
		}

		$this->plugin_settings_tabs['time_tracking'] 	= esc_html__( 'Time Tracking', 'ld-dashboard' );
		$this->plugin_settings_tabs['invite_user'] 		= esc_html__( 'Invite User', 'ld-dashboard' );
		$this->plugin_settings_tabs['email_logs'] 		= esc_html__( 'Email Logs', 'ld-dashboard' );
		$this->plugin_settings_tabs['support']    		= esc_html__( 'Support', 'ld-dashboard' );

		register_setting( 'ld_dashboard_welcome_settings', 'ld_dashboard_welcome_settings' );
		register_setting( 'ld_dashboard_general_settings', 'ld_dashboard_general_settings' );
		register_setting( 'ld_dashboard_design_settings', 'ld_dashboard_design_settings' );
		register_setting( 'ld_dashboard_tiles_options', 'ld_dashboard_tiles_options' );
		register_setting( 'ld_dashboard_menu_options', 'ld_dashboard_menu_options' );

		register_setting( 'ld_dashboard_welcome_screen', 'ld_dashboard_welcome_screen' );

		register_setting( 'ld_dashboard_feed_settings', 'ld_dashboard_feed_settings' );
		register_setting( 'ld_dashboard_page_mapping', 'ld_dashboard_page_mapping' );
		register_setting( 'ld_dashboard_zoom_meeting_settings', 'ld_dashboard_zoom_meeting_settings' );
		// Acf form settings.
		register_setting( 'ld_dashboard_course_form_settings', 'ld_dashboard_course_form_settings' );
		register_setting( 'ld_dashboard_lesson_form_settings', 'ld_dashboard_lesson_form_settings' );
		register_setting( 'ld_dashboard_topic_form_settings', 'ld_dashboard_topic_form_settings' );
		register_setting( 'ld_dashboard_quiz_form_settings', 'ld_dashboard_quiz_form_settings' );
		register_setting( 'ld_dashboard_question_form_settings', 'ld_dashboard_question_form_settings' );
		register_setting( 'ld_dashboard_manage_monetization', 'ld_dashboard_manage_monetization' );
		register_setting( 'ld_dashboard_comm_report_settings', 'ld_dashboard_comm_report_settings' );
		register_setting( 'ld_dashboard_invite_user_settings', 'ld_dashboard_invite_user_settings' );
		register_setting( 'ld_dashboard_time_tracking_settings', 'ld_dashboard_time_tracking_settings' );

		add_settings_section( 'ld-dashboard-welcome', ' ', array( $this, 'ld_dashboard_welcome_settings' ), 'ld-dashboard-welcome' );
		add_settings_section( 'ld-dashboard-general', ' ', array( $this, 'ld_dashboard_general_settings' ), 'ld-dashboard-general' );
		add_settings_section( 'ld-dashboard-design', ' ', array( $this, 'ld_dashboard_design_settings' ), 'ld-dashboard-design' );
		add_settings_section( 'ld-dashboard-tiles-options', ' ', array( $this, 'ld_dashboard_tiles_options' ), 'ld-dashboard-tiles-options' );
		add_settings_section( 'ld-dashboard-menu-options', ' ', array( $this, 'ld_dashboard_menu_options' ), 'ld-dashboard-menu-options' );

		add_settings_section( 'ld-dashboard-welcome-screen', ' ', array( $this, 'ld_dashboard_welcome_screen' ), 'ld-dashboard-welcome-screen' );
		add_settings_section( 'ld-dashboard-feed-settings', ' ', array( $this, 'ld_dashboard_feed_settings' ), 'ld-dashboard-feed-settings' );
		add_settings_section( 'ld-dashboard-page-mapping', ' ', array( $this, 'ld_dashboard_page_mapping' ), 'ld-dashboard-page-mapping' );		
		

		// Acf form setting section.
		add_settings_section( 'ld-dashboard-frontend-field-settings', ' ', array( $this, 'ld_dashboard_set_frontend_form_fields' ), 'ld-dashboard-frontend-field-settings' );

		add_settings_section( 'ld-dashboard-instructor-settings', ' ', array( $this, 'ld_dashboard_manage_instructors' ), 'ld-dashboard-instructor-settings' );

		add_settings_section( 'ld-dashboard-monetization-settings', ' ', array( $this, 'ld_dashboard_manage_monetization' ), 'ld-dashboard-monetization-settings' );

		// Earning reports.
		add_settings_section( 'ld-dashboard-commission-report', ' ', array( $this, 'ld_dashboard_commission_report_settings' ), 'ld-dashboard-commission-report' );
		
		add_settings_section( 'time_tracking', ' ', array( $this, 'ld_dashboard_time_tracking' ), 'time_tracking' );
		add_settings_section( 'invite_user', ' ', array( $this, 'ld_dashboard_invite_user' ), 'invite_user' );
		
		add_settings_section( 'email_logs', ' ', array( $this, 'ld_dashboard_email_logs' ), 'email_logs' );

		add_settings_section( 'support', ' ', array( $this, 'ld_dashboard_support' ), 'support' );
	}

	public function ld_dashboard_meetings_meta_box() {
		add_meta_box(
			'zoom-meeting-fields',
			__( 'Zoom Details', 'ld-dashboard' ),
			array( $this, 'ld_dashboard_zoom_meeting_fields' ),
			'zoom_meet',
			'normal',
			'high'
		);
	}

	public function ld_dashboard_zoom_meeting_fields() {
		include 'partials/ld-dashboard-zoom-meeting-fields.php';
	}

	/**
	 * Display Learndash Instructor Dashboard settings.
	 *
	 * @since  1.0.0
	 * @author Wbcom Designs
	 * @access public
	 */
	public function ld_dashboard_settings_page() {
		$current = ( filter_input( INPUT_GET, 'tab' ) !== null ) ? filter_input( INPUT_GET, 'tab' ) : 'ld-dashboard-welcome';
		?>
		<div class="wrap">
			<div class="wbcom-bb-plugins-offer-wrapper">
				<div id="wb_admin_logo">
					<a href="https://wbcomdesigns.com/downloads/buddypress-community-bundle/" target="_blank">
						<img src="<?php echo esc_url( LD_DASHBOARD_PLUGIN_URL ) . 'admin/wbcom/assets/imgs/wbcom-offer-notice.png'; ?>">
					</a>
				</div>
			</div>
			<div class="wbcom-wrap">
				<div class="ld-dashboard-admin-header">
					<div class="wbcom_admin_header-wrapper">
						<div id="wb_admin_plugin_name">
							<?php esc_html_e( 'LearnDash Dashboard', 'ld-dashboard' ); ?>
							<span><?php printf( esc_html__( 'Version %s', 'ld-dashboard' ), esc_html( LD_DASHBOARD_VERSION ) ); ?></span>
						</div>
						<?php echo do_shortcode( '[wbcom_admin_setting_header]' ); ?>
					</div>
				</div>
				<div class="wbcom-admin-settings-page">
					<?php
					$this->ld_dashboard_plugin_settings_tabs();
					settings_fields( $current );
					do_settings_sections( $current );
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Display form field with list of authors and instructor.
	 *
	 * @param array $query_args
	 */
	public function ld_dashboard_dropdown_users_args( $query_args ) {
		$allowed_post_type = array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'sfwd-essays', 'sfwd-question', 'sfwd-certificates', 'product' );
		if ( in_array( get_post_type(), $allowed_post_type ) ) {
			unset( $query_args['who'] );
			$query_args['role__in'] = array( 'Administrator', 'ld_instructor' );
		}
		return $query_args;
	}

	/**
	 * Set posts query clauses
	 *
	 * @param array $clauses
	 */

	public function ld_dahsboard_admin_posts_clauses( $clauses ) {
		global $current_user, $wpdb;

		if ( ! is_admin() ) {
			return $clauses;
		}

		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'sfwd-courses' ) {
			$clauses['join'] .= "INNER JOIN {$wpdb->prefix}postmeta pm6 ON ( {$wpdb->prefix}posts.ID = pm6.post_id )";

			if ( isset( $_GET['post_status'] ) && $_GET['post_status'] != '' ) {
					$post_status_where = "{$wpdb->prefix}posts.post_status = '" . $_GET['post_status'] . "'";
			} else {
				$post_status_where = "{$wpdb->prefix}posts.post_status = 'publish' OR {$wpdb->prefix}posts.post_status = 'graded' OR {$wpdb->prefix}posts.post_status = 'not_graded' OR {$wpdb->prefix}posts.post_status = 'future' OR {$wpdb->prefix}posts.post_status = 'draft' OR {$wpdb->prefix}posts.post_status = 'pending' OR {$wpdb->prefix}posts.post_author = {$current_user->ID} AND {$wpdb->prefix}posts.post_status = 'private'";
			}
			$clauses['where'] .= " OR ( ({$post_status_where}) AND pm6.meta_key = '_ld_instructor_ids' AND pm6.meta_value REGEXP '.*;s:[0-9]+:\"{$current_user->ID}\"*' )";

			$clauses['groupby'] .= " {$wpdb->prefix}posts.ID";

		}

		if ( isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], array( 'sfwd-lessons', 'sfwd-quiz', 'sfwd-essays', 'sfwd-topic', 'sfwd-assignment' ) ) ) {
			$clauses['where'] = str_replace( "AND {$wpdb->prefix}posts.post_author IN ({$current_user->ID})", '', $clauses['where'] );

			$get_courses_sql = "select ID from {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta pm6 ON ( {$wpdb->prefix}posts.ID = pm6.post_id ) where ( post_author={$current_user->ID} OR ( pm6.meta_key = '_ld_instructor_ids' AND pm6.meta_value REGEXP '.*;s:[0-9]+:\"{$current_user->ID}\"*' ) ) AND post_type='sfwd-courses' Group By {$wpdb->prefix}posts.ID";

			$cousres = $wpdb->get_results( $get_courses_sql );
			if ( empty( $cousres ) ) {
				$cousres[] = (object) array( 'ID' => 0 );
			}

			if ( ! empty( $cousres ) ) {
				$course_ids = array();
				foreach ( $cousres as $course ) {
					$course_ids[] = $course->ID;
				}
				$course_ids = implode( "','", $course_ids );
				if ( isset( $_GET['post_status'] ) && $_GET['post_status'] != '' ) {
					$post_status_where = "{$wpdb->prefix}posts.post_status = '" . $_GET['post_status'] . "'";
				} else {
					$post_status_where = "{$wpdb->prefix}posts.post_status = 'publish' OR {$wpdb->prefix}posts.post_status = 'graded' OR {$wpdb->prefix}posts.post_status = 'not_graded' OR {$wpdb->prefix}posts.post_status = 'future' OR {$wpdb->prefix}posts.post_status = 'draft' OR {$wpdb->prefix}posts.post_status = 'pending' OR {$wpdb->prefix}posts.post_author = {$current_user->ID} AND {$wpdb->prefix}posts.post_status = 'private'";
				}

				$clauses['join'] .= "INNER JOIN {$wpdb->prefix}postmeta pm6 ON ( {$wpdb->prefix}posts.ID = pm6.post_id )";
				if ( $_GET['post_type'] == 'sfwd-assignment' ) {
					$clauses['where']  = str_replace( "OR {$wpdb->prefix}posts.post_author = {$current_user->ID}", '', $clauses['where'] );
					$clauses['where']  = str_replace( "AND {$wpdb->prefix}posts.post_author IN ({$current_user->ID})", '', $clauses['where'] );
					$clauses['where'] .= " AND ( pm6.meta_key = 'course_id' AND pm6.meta_value IN ('{$course_ids}') )";
				} else {
					$clauses['where'] .= " AND {$wpdb->prefix}posts.post_author = {$current_user->ID} OR ( pm6.meta_key = 'course_id' AND pm6.meta_value IN ('{$course_ids}') AND {$wpdb->prefix}posts.post_type = '" . $_GET['post_type'] . "' AND ({$post_status_where}) )";
				}

				$clauses['groupby'] = " {$wpdb->prefix}posts.ID";

			}
		}

		remove_filter( 'posts_clauses', array( $this, 'ld_dahsboard_admin_posts_clauses' ), 99 );

		return $clauses;
	}

	/**
	 * Display Instructor role related couse, lesson, topics and etc
	 *
	 * @param array $query
	 */
	public function ld_dahsboard_admin_pre_get_posts( $query ) {
		global $current_user, $wpdb;

		if ( is_admin() && in_array( 'ld_instructor', (array) $current_user->roles ) ) {

			if ( isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], array( 'sfwd-courses', 'sfwd-question', 'sfwd-certificates', 'product' ) ) ) {
				$query->set( 'author', $current_user->ID );
				add_filter( 'posts_clauses', array( $this, 'ld_dahsboard_admin_posts_clauses' ), 99 );
			} else {

				if ( isset( $_GET['post_type'] ) && in_array( $_GET['post_type'], array( 'sfwd-lessons', 'sfwd-essays', 'sfwd-quiz', 'sfwd-topic', 'sfwd-assignment' ) ) ) {

					add_filter( 'posts_clauses', array( $this, 'ld_dahsboard_admin_posts_clauses' ), 99 );
					$_REQUEST['all_posts'] = 1;
					/*
					$get_courses_sql = "select ID from {$wpdb->prefix}posts where post_author={$current_user->ID} AND post_type='sfwd-courses'";
					$cousres = $wpdb->get_results( $get_courses_sql );
					if ( !empty($cousres) ) {
						$course_ids = array();
						foreach ($cousres as $course ) {
							$course_ids[] = $course->ID;
						}

						$query->set('meta_query', array(
												array(
													'key'     => 'course_id',
													'value'   => $course_ids,
													'compare' => 'IN'
												)
											)
										);

					} else {
						$query->set( 'author', $current_user->ID );
					}
					*/
				}
			}

			$allowed_post_type = array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'sfwd-essays', 'sfwd-question', 'sfwd-certificates', 'product' );

			foreach ( $allowed_post_type as $post_type ) {
				// add_filter('views_edit-' . $post_type, array( $this, 'ld_dashboard_fix_lms_post_type_counts' ) );
			}
		}
	}

	/*
	* Count Custom post type for instructor user rol
	*
	* @param array $views
	*/
	public function ld_dashboard_fix_lms_post_type_counts( $views ) {
		global $current_user, $wp_query, $wpdb;

		$allowed_post_type = array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'sfwd-essays', 'sfwd-question', 'sfwd-certificates', 'product' );

		if ( isset( $_REQUEST['post_type'] ) && $_REQUEST['post_type'] != '' ) {
			$posttype = $_REQUEST['post_type'];
		} else {
			$posttype = get_post_type();
		}

		if ( ! in_array( $posttype, $allowed_post_type ) ) {

			return $views;
		}

		unset( $views['mine'] );

		$types      = array(
			array( 'status' => null ),
			array( 'status' => 'publish' ),
			array( 'status' => 'draft' ),
			array( 'status' => 'pending' ),
			array( 'status' => 'trash' ),
		);
		$meta_query = array();
		if ( in_array( $posttype, array( 'sfwd-lessons', 'sfwd-topic' ) ) ) {

			$get_courses_sql = "select ID from {$wpdb->prefix}posts where post_author={$current_user->ID} AND post_type='sfwd-courses'";
			$cousres         = $wpdb->get_results( $get_courses_sql );
			if ( ! empty( $cousres ) ) {
				$course_ids = array();
				foreach ( $cousres as $course ) {
					$course_ids[] = $course->ID;
				}

				$meta_query['meta_query'] = array(
					'relation' => 'AND',
					array(
						'key'     => 'course_id',
						'value'   => $course_ids,
						'compare' => 'IN',
					),
				);

			}
		}

		foreach ( $types as $type ) {

			$query = array(
				'author'      => $current_user->ID,
				'post_type'   => $posttype,
				'post_status' => $type['status'],

			);

			if ( in_array( $posttype, array( 'sfwd-lessons', 'sfwd-topic' ) ) ) {
				unset( $query['author'] );
			}
			$query  = array_merge( $query, $meta_query );
			$result = new WP_Query( $query );

			if ( $type['status'] == null ) :

				$class = ( $wp_query->query_vars['post_status'] == null ) ? ' class="current"' : '';

				$views['all'] = sprintf(
					__( '<a href="%s"' . $class . '>All <span class="count">(%d)</span></a>', 'ld-dashboard' ),
					admin_url( 'edit.php?post_type=' . $posttype ),
					$result->found_posts
				);

			elseif ( $type['status'] == 'publish' ) :

				$class = ( $wp_query->query_vars['post_status'] == 'publish' ) ? ' class="current"' : '';

				$views['publish'] = sprintf(
					__( '<a href="%s"' . $class . '>Published <span class="count">(%d)</span></a>', 'ld-dashboard' ),
					admin_url( 'edit.php?post_status=publish&post_type=' . $posttype ),
					$result->found_posts
				);

			elseif ( $type['status'] == 'draft' ) :

				$class = ( $wp_query->query_vars['post_status'] == 'draft' ) ? ' class="current"' : '';

				$views['draft'] = sprintf(
					__( '<a href="%s"' . $class . '>Draft' . ( ( sizeof( $result->posts ) > 1 ) ? 's' : '' ) . ' <span class="count">(%d)</span></a>', 'ld-dashboard' ),
					admin_url( 'edit.php?post_status=draft&post_type=' . $posttype ),
					$result->found_posts
				);

			elseif ( $type['status'] == 'pending' ) :

				$class = ( $wp_query->query_vars['post_status'] == 'pending' ) ? ' class="current"' : '';

				$views['pending'] = sprintf(
					__( '<a href="%s"' . $class . '>Pending <span class="count">(%d)</span></a>', 'ld-dashboard' ),
					admin_url( 'edit.php?post_status=pending&post_type=' . $posttype ),
					$result->found_posts
				);

			elseif ( $type['status'] == 'trash' ) :

				$class = ( $wp_query->query_vars['post_status'] == 'trash' ) ? ' class="current"' : '';

				$views['trash'] = sprintf(
					__( '<a href="%s"' . $class . '>Trash <span class="count">(%d)</span></a>', 'ld-dashboard' ),
					admin_url( 'edit.php?post_status=trash&post_type=' . $posttype ),
					$result->found_posts
				);

			endif;

		}

		return $views;
	}

	/**
	 * Add admin commission meta box for course post type.
	 *
	 * @since  1.0.0
	 * @author Wbcom Designs
	 * @access public
	 */
	public function ld_dashboard_add_post_commission_meta_box() {

		add_meta_box( 'ld-dashboard-instructors', __( 'LD Instructors', 'ld-dashboard' ), array( $this, 'ld_dashboard_admin_instructors_metabox' ), 'sfwd-courses' );

	}

	/*
	* Assign Multi Instructor
	*
	*/
	public function ld_dashboard_admin_instructors_metabox( $post ) {
		global $post;
		$instructors_ids = get_post_meta( $post->ID, '_ld_instructor_ids', true );

		$args = array(
			'orderby'  => 'user_nicename',
			'role__in' => 'ld_instructor',
			'order'    => 'ASC',
			'fields'   => array( 'ID', 'display_name' ),
			'include'  => ( ! empty( $instructors_ids ) ) ? $instructors_ids : array( 0 ),
		);

		$instructors = get_users( $args );
		?>
		<div class="ld-instructors-metabox-wrap">
			<div class="ld-available-instructors">
				<?php
				if ( is_array( $instructors ) && count( $instructors ) ) {
					foreach ( $instructors as $instructor ) {
						?>
						<div id="added-instructor-id-<?php echo $instructor->ID; ?>" class="added-instructor-item added-instructor-item-<?php echo $instructor->ID; ?>" data-instructor-id="<?php echo $instructor->ID; ?>">
							<span class="instructor-icon">
								<?php echo get_avatar( $instructor->ID, 30 ); ?>
							</span>
							<span class="instructor-name"> <?php echo $instructor->display_name; ?> </span>
							<span class="instructor-control">
								<a href="javascript:;" class="ld-instructor-delete-btn"><i class="dashicons dashicons-no"></i></a>
							</span>
						</div>
						<?php
					}
				}
				?>
			</div>
			<div class="ld-add-instructor-button-wrap">
				<button type="button" class="ld-btn ld-add-instructor-btn bordered-btn"> <?php esc_html_e( 'Add More Instructors', 'ld-dashboard' ); ?> </button>
			</div>

			<div class="ld-modal-wrap ld-instructors-modal-wrap">
				<div class="ld-modal-content">
					<div class="modal-header">
						<div class="modal-title">
							<h1><?php esc_html_e( 'Add instructors', 'ld-dashboard' ); ?></h1>
						</div>
						<div class="lesson-modal-close-wrap">
							<a href="javascript:;" class="modal-close-btn"><i class="dashicons dashicons-no"></i></a>
						</div>
					</div>
					<div class="modal-content-body">

						<div class="search-bar">
							<input type="text" class="ld-instructor-modal-search-input" placeholder="<?php esc_html_e( 'Search instructors...', 'ld-dashboard' ); ?>">
						</div>
					</div>
					<div class="modal-container"></div>
					<div class="modal-footer has-padding">
						<button type="button" class="ld-btn add_instructor_to_course_btn"><?php esc_html_e( 'Add Instructors', 'ld-dashboard' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function ld_dashboard_load_instructors_modal() {
		global $wpdb;

		$post_id               = (int) sanitize_text_field( $_POST['post_id'] );
		$search_terms          = ( isset( $_POST['search_terms'] ) ) ? sanitize_text_field( wp_unslash( $_POST['search_terms'] ) ) : '';
		$saved_instructors_ids = get_post_meta( $post_id, '_ld_instructor_ids', true );
		$saved_instructors     = array();

		$args = array(
			'orderby'  => 'user_nicename',
			'role__in' => 'ld_instructor',
			'order'    => 'ASC',
			'fields'   => array( 'ID', 'display_name' ),
		);

		if ( is_array( $saved_instructors_ids ) && ! empty( $saved_instructors_ids ) ) {
			$args['exclude'] = $saved_instructors_ids;
		}
		if ( $search_terms != '' ) {
			$args['search']         = '*' . $search_terms . '*';
			$args['search_columns'] = array( 'user_login', 'user_nicename', 'display_name' );
		}

		$instructors = get_users( $args );
		$output      = '';
		if ( is_array( $instructors ) && count( $instructors ) ) {
			$instructor_output = '';
			foreach ( $instructors as $instructor ) {
				$instructor_output .= "<p><label><input type='radio' name='ld_instructor_ids[]' value='{$instructor->ID}' > {$instructor->display_name} </label></p>";
			}

			$output .= $instructor_output;

		} else {
			$output .= '<p>' . esc_html__( 'No instructors available or you have already added maximum instructors', 'ld-dashboard' ) . '</p>';
		}

		wp_send_json_success( array( 'output' => $output ) );
	}

	public function ld_dashboard_add_instructors_to_course() {
		$post_id        = (int) sanitize_text_field( $_POST['post_id'] );
		$instructor_ids = $_POST['ld_instructor_ids'];

		$_ld_instructor_ids = get_post_meta( $post_id, '_ld_instructor_ids', true );
		if ( is_array( $_ld_instructor_ids ) && count( $_ld_instructor_ids ) ) {
			foreach ( $_ld_instructor_ids as $instructor_id ) {
				$instructor_ids[] = $instructor_id;
			}
		}
		update_post_meta( $post_id, '_ld_instructor_ids', array_unique( $instructor_ids ) );
		$args = array(
			'orderby'  => 'user_nicename',
			'role__in' => 'ld_instructor',
			'order'    => 'ASC',
			'fields'   => array( 'ID', 'display_name' ),
			'include'  => $instructor_ids,
		);

		$saved_instructors = get_users( $args );

		$output = '';

		if ( ! empty( $saved_instructors ) ) {
			foreach ( $saved_instructors as $t ) {

				$output .= '<div id="added-instructor-id-' . $t->ID . '" class="added-instructor-item added-instructor-item-' . $t->ID . '" data-instructor-id="' . $t->ID . '">
                    <span class="instructor-icon">' . get_avatar( $t->ID, 30 ) . '</span>
                    <span class="instructor-name"> ' . $t->display_name . ' </span>
                    <span class="instructor-control">
                        <a href="javascript:;" class="ld-instructor-delete-btn"><i class="dashicons dashicons-no"></i></a>
                    </span>
                </div>';
			}
		}

		wp_send_json_success( array( 'output' => $output ) );
	}

	public function ld_dashboard_detach_instructor() {
		global $wpdb;

		$instructor_id      = (int) sanitize_text_field( $_POST['instructor_id'] );
		$post_id            = (int) sanitize_text_field( $_POST['post_id'] );
		$_ld_instructor_ids = get_post_meta( $post_id, '_ld_instructor_ids', true );
		if ( is_array( $_ld_instructor_ids ) && count( $_ld_instructor_ids ) ) {
			foreach ( $_ld_instructor_ids as $key => $inst_id ) {
				if ( $instructor_id == $inst_id ) {
					unset( $_ld_instructor_ids[ $key ] );
				}
			}
		}
		update_post_meta( $post_id, '_ld_instructor_ids', array_unique( $_ld_instructor_ids ) );

		wp_send_json_success();
	}

	/*
	* When Instructor User Role plugin share course then assign user into LD Instructor user role too.
	*
	*/
	public function ld_dashboard_save_course_instructor_meta( $course_id, $course ) {

		if ( isset( $_POST['shared_instructors'] ) ) {
			/*
			$_ld_instructor_ids = get_post_meta($course_id, '_ld_instructor_ids', true );
			if ( empty($_ld_instructor_ids)) {

			}
			*/
			$_ld_instructor_ids = array();
			if ( ! empty( $_POST['shared_instructors'] ) ) {
				foreach ( $_POST['shared_instructors'] as $user_id ) {
					$ld_user = new WP_User( $user_id );
					$ld_user->add_role( 'ld_instructor' );
				}
			} else {
				$_POST['shared_instructors'] = array();
			}

			$_ld_instructor_ids = array_merge( $_POST['shared_instructors'], $_ld_instructor_ids );
			update_post_meta( $course_id, '_ld_instructor_ids', array_unique( $_ld_instructor_ids ) );
		}
	}

	/*
	* Display essays post type tabs on admin side in quiz post type screen.
	*
	*/
	public function ld_dashboard_learndash_header_tab_menu( $header_data_tabs, $menu_tab_key, $screen_post_type ) {
		global $current_user, $pagenow;

		if ( in_array( 'ld_instructor', (array) $current_user->roles ) && ( $screen_post_type == 'sfwd-quiz' || $screen_post_type == 'sfwd-essays' ) && $pagenow == 'edit.php' ) {
			$find_essays = false;

			foreach ( $header_data_tabs as $tabs ) {
				if ( $tabs['id'] == 'edit-sfwd-essays' ) {
					$find_essays = true;
					break;
				}
			}
			if ( ! $find_essays ) {
				$header_data_tabs[] = array(
					'link'       => admin_url( 'edit.php?post_type=sfwd-essays' ),
					'name'       => 'Submitted Essays',
					'id'         => 'edit-sfwd-essays',
					'isExternal' => 'true',
					'actions'    => array(),
					'metaboxes'  => array(),
				);
			}
		}

		return $header_data_tabs;
	}

	/**
	 * Reset dashboard settings to it's default state
	 *
	 * @return void
	 */
	public function ld_dashboard_reset_settings() {
		if ( isset( $_POST['security'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'ajax-nonce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'You are not permited to this action', 'ld-dashboard' ) );
		}

		if ( isset( $_POST['setting'] ) && ! empty( $_POST['setting'] ) ) {
			$setting          = sanitize_text_field( wp_unslash( $_POST['setting'] ) );
			$default_settings = get_option( $setting . '_default' );
			$option           = update_option( $setting, $default_settings );

			if ( $option ) {
				wp_send_json_success( __( 'Settings Rest', 'ld-dashboard' ) );
			} else {
				wp_send_json_error( __( 'Settings Not Rest', 'ld-dashboard' ) );
			}
		}
	}


	public function ld_dashboard_course_publish( $new, $old, $post ) {
		if ( ( 'publish' === $new ) && ( 'pending' === $old ) && ( 'sfwd-courses' === $post->post_type ) ) {
				update_post_meta( $post->ID, 'ldd_course_status', $post->post_status );
		}
	}

}
