<?php
/**
 * The widget of the plugin.
 *
 * @link       https://wbcomdesigns.com/
 * @since      1.0.0
 *
 * @package    Bp_Stats
 * @subpackage Bp_Stats/public
 */

/** Register_bp_stats_activities_chart_widget */
function register_ld_dashboard_earning_chart_widget() {
	register_widget( 'ld_dashboard_earning_chart_widget' );
}
add_action( 'widgets_init', 'register_ld_dashboard_earning_chart_widget' );

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Bp_Stats
 * @subpackage Bp_Stats/public
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Ld_Dashboard_Earning_Chart_Widget extends WP_Widget {

	/** Constructor */
	public function __construct() {
		parent::__construct(
		// widget ID.
			'ld_dashboard_earning_chart_widget',
			// widget name.
			__( 'Learndash Dashboard Earning Chart Widget', 'ld-dashboard' ),
			// widget description.
			array( 'description' => __( 'Learndash Dashboard Earning Chart Widget', 'ld-dashboard' ) )
		);
	}

	/** Widget.
	 *
	 * @param args     $args Arguments.
	 * @param instance $instance Instance.
	 */
	public function widget( $args, $instance ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$title      = ( isset( $instance['title'] ) ) ? $instance['title'] : '';
		$title      = apply_filters( 'widget_title', $title );
		$title      = ( '' !== $title ) ? $title : esc_html__( 'Instructor Earning', 'ld-dashboard' );
		$currency   = ( version_compare( LEARNDASH_VERSION, '4.1.0', '<' ) ) ? learndash_30_get_currency_symbol() : learndash_get_currency_symbol();
		$user_data  = get_userdata( get_current_user_id() );
		$user_roles = $user_data->roles;
		if ( ! in_array( 'subscriber', $user_roles ) ) {
			echo ( isset( $args['before widget'] ) ) ? wp_kses_post( $args['before widget'] ) : '';
			?>
			<section class="widget widget_block">
				<div class="ldd-dashboard-head-wrapper">
					<h3 class="ldd-dashboard-title"><?php echo esc_html( $title ); ?></h3>
					<div class="ldd-dashboard-earning-filter-wrapper">
						<ul class="ldd-dashboard-earning-filters-list">
							<li class="ldd-dashboard-earning-filters-list-single filter-selected" data-filter="year"><?php echo esc_html__( 'Year', 'ld-dashboard' ); ?></li>
							<li class="ldd-dashboard-earning-filters-list-single" data-filter="l_month"><?php echo esc_html__( 'Last Month', 'ld-dashboard' ); ?></li>
							<li class="ldd-dashboard-earning-filters-list-single" data-filter="month"><?php echo esc_html__( 'This Month', 'ld-dashboard' ); ?></li>
							<li class="ldd-dashboard-earning-filters-list-single" data-filter="week"><?php echo esc_html__( 'Last 7 Days', 'ld-dashboard' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="ldd-dashboard-earning-amount-wrapper">
					<div class="ldd-dashboard-earning-amount-content">
						<span class="ldd-dashboard-earning-currency"><?php echo wp_kses_post( $currency ); ?></span>
						<span class="ldd-dashboard-earning-amount"></span>
					</div>
				</div>
				<div class="ldd-dashboard-earning-chart-wrapper"></div>
			</section>
			<?php
			echo ( isset( $args['after_widget'] ) ) ? wp_kses_post( $args['after_widget'] ) : '';
		} else {
			echo 'You dont have required permissions.';
		}
	}

	/** Form.
	 *
	 * @param instance $instance Instance.
	 */
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'ld-dashboard' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}

	/** Update.
	 *
	 * @param new_instance $new_instance New Instance.
	 * @param old_instance $old_instance Old Instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}
