<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Blacklist table class
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Classes
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'YITH_Stripe_Blacklist_Table' ) ) {
	/**
	 * Blacklist's records table
	 *
	 * @since 1.1.3
	 */
	class YITH_Stripe_Blacklist_Table extends WP_List_Table {

		/**
		 * Single instance of the class
		 *
		 * @var   \YITH_Stripe_Blacklist_Table
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_Stripe_Blacklist_Table
		 * @since  1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Months Dropdown value
		 *
		 * @var array
		 * @since 1.1.3
		 */
		protected $months_dropdown = array();

		/**
		 * Construct method
		 *
		 * @since 1.1.3
		 */
		public function __construct() {

			// Set parent defaults.
			parent::__construct(
				array(
					'singular' => 'ban', // singular name of the listed records.
					'plural'   => 'bans', // plural name of the listed records.
					'ajax'     => false, // does this table support ajax?.
				)
			);

			// Months dropdown.
			$this->months_dropdown = $this->months_dropdown_results();
		}

		/**
		 * Returns columns available in table
		 *
		 * @return array Array of columns of the table
		 * @since 1.1.3
		 */
		public function get_columns() {
			$columns = array(
				'cb'           => '<input type="checkbox" />',
				'user'         => __( 'User', 'yith-woocommerce-stripe' ),
				'ip'           => __( 'IP Address', 'yith-woocommerce-stripe' ),
				'order'        => __( 'Order', 'yith-woocommerce-stripe' ),
				'date'         => __( 'Date', 'yith-woocommerce-stripe' ),
				'ban_status'   => __( 'Status', 'yith-woocommerce-stripe' ),
				'user_actions' => '',
			);

			return $columns;
		}

		/**
		 * Sets bulk actions for table
		 *
		 * @return array Array of available actions
		 * @since 1.1.3
		 */
		public function get_bulk_actions() {
			$actions = array(
				'ban'   => __( 'Ban', 'yith-woocommerce-stripe' ),
				'unban' => __( 'Unban', 'yith-woocommerce-stripe' ),
			);

			return $actions;
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @since  1.1.3
		 * @access protected
		 */
		protected function get_views() {
			global $wpdb;

			$views        = array(
				'all'      => __( 'All', 'yith-woocommerce-stripe' ),
				'banned'   => __( 'Banned', 'yith-woocommerce-stripe' ),
				'unbanned' => __( 'Active', 'yith-woocommerce-stripe' ),
			);
			$current_view = $this->get_current_view();

			foreach ( $views as $id => $view ) {
				$href   = esc_url( add_query_arg( 'status', $id ) );
				$class  = $id === $current_view ? 'current' : '';
				$filter = "0', '1";
				if ( 'banned' === $id ) {
					$filter = '0';
				} elseif ( 'unbanned' === $id ) {
					$filter = '1';
				}
				$count        = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->yith_wc_stripe_blacklist WHERE unbanned IN ( %s )", $filter ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$views[ $id ] = sprintf( "<a href='%s' class='%s'>%s <span class='count'>(%d)</span></a>", $href, $class, $view, $count );
			}

			return $views;
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @return string The view name
		 * @since  1.1.2
		 */
		public function get_current_view() {
			return ! empty( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Prepare items for table
		 *
		 * @return void
		 * @since 1.1.3
		 */
		public function prepare_items() {

			// sets pagination arguments.
			$per_page     = $this->get_items_per_page( 'edit_bans_per_page' );
			$current_page = absint( $this->get_pagenum() );

			// blacklist args.
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			$q = array(
				'status'  => $this->get_current_view(),
				'paged'   => $current_page,
				'number'  => $per_page,
				'm'       => isset( $_REQUEST['m'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['m'] ) ) : false,
				's'       => isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '',
				'orderby' => 'ban_date',
				'order'   => 'DESC',
			);
			// phpcs:enable WordPress.Security.NonceVerification.Recommended

			global $wpdb;

			// First let's clear some variables.
			$where   = '';
			$join    = '';
			$limits  = '';
			$groupby = '';
			$orderby = '';

			// query parts initializating.
			$pieces = array( 'where', 'groupby', 'join', 'orderby', 'limits' );

			// The "m" parameter is meant for months but accepts datetimes of varying specificity.
			if ( $q['m'] ) {
				$q['m'] = absint( preg_replace( '|[^0-9]|', '', $q['m'] ) );

				$where .= ' AND YEAR(b.ban_date)=' . substr( $q['m'], 0, 4 );
				if ( strlen( $q['m'] ) > 5 ) {
					$where .= ' AND MONTH(b.ban_date)=' . substr( $q['m'], 4, 2 );
				}
				if ( strlen( $q['m'] ) > 7 ) {
					$where .= ' AND DAYOFMONTH(b.ban_date)=' . substr( $q['m'], 6, 2 );
				}
				if ( strlen( $q['m'] ) > 9 ) {
					$where .= ' AND HOUR(b.ban_date)=' . substr( $q['m'], 8, 2 );
				}
				if ( strlen( $q['m'] ) > 11 ) {
					$where .= ' AND MINUTE(b.ban_date)=' . substr( $q['m'], 10, 2 );
				}
				if ( strlen( $q['m'] ) > 13 ) {
					$where .= ' AND SECOND(b.ban_date)=' . substr( $q['m'], 12, 2 );
				}
			}

			// View.
			if ( 'banned' === $q['status'] ) {
				$where .= ' AND unbanned = 0';
			} elseif ( 'unbanned' === $q['status'] ) {
				$where .= ' AND unbanned = 1';
			}

			// Search.
			if ( $q['s'] ) {
				// user.
				$join  .= " JOIN $wpdb->users u ON u.ID = b.user_id";
				$join  .= " JOIN $wpdb->usermeta um ON um.user_id = b.user_id";
				$join  .= " JOIN $wpdb->usermeta um2 ON um2.user_id = b.user_id";
				$join  .= " JOIN $wpdb->usermeta um3 ON um3.user_id = b.user_id";
				$where .= " AND um.meta_key = 'first_name'";
				$where .= " AND um2.meta_key = 'last_name'";

				// order.
				if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
					$join .= false === strpos( $join, "{$wpdb->prefix}wc_orders o" ) ? " JOIN {$wpdb->prefix}wc_orders o ON o.id = b.order_id" : '';
				} else {
					$join .= false === strpos( $join, "$wpdb->posts o" ) ? " JOIN $wpdb->posts o ON o.ID = b.order_id" : '';
				}

				if ( yith_plugin_fw_is_wc_custom_orders_table_usage_enabled() ) {
					$prepare_order_search = $wpdb->prepare( 'o.id = %s', $q['s'] );
				} else {
					$prepare_order_search = $wpdb->prepare( 'o.ID = %s', $q['s'] );
				}

				$s = array(
					// search by username.
					$wpdb->prepare( 'u.user_login LIKE %s', "%{$q['s']}%" ),
					$wpdb->prepare( 'u.user_nicename LIKE %s', "%{$q['s']}%" ),
					$wpdb->prepare( 'u.user_email LIKE %s', "%{$q['s']}%" ),
					$wpdb->prepare( 'um.meta_value LIKE %s', "%{$q['s']}%" ),
					$wpdb->prepare( 'um2.meta_value LIKE %s', "%{$q['s']}%" ),
					// search by ip address.
					$wpdb->prepare( 'b.ip = %s', $q['s'] ),
					// search by order.
					$prepare_order_search,
				);

				$where .= ' AND ( ' . implode( ' OR ', $s ) . ' )';
			}

			// Paging.
			if ( ! empty( $q['paged'] ) && ! empty( $q['number'] ) ) {
				$page = absint( $q['paged'] );
				if ( ! $page ) {
					$page = 1;
				}

				if ( empty( $q['offset'] ) ) {
					$pgstrt = absint( ( $page - 1 ) * $q['number'] ) . ', ';
				} else { // we're ignoring $page and using 'offset'.
					$q['offset'] = absint( $q['offset'] );
					$pgstrt      = $q['offset'] . ', ';
				}
				$limits = 'LIMIT ' . $pgstrt . $q['number'];
			}

			// Order.
			if ( ! empty( $q['paged'] ) && ! empty( $q['number'] ) ) {
				$orderby = "ORDER BY {$q['orderby']} {$q['order']}";
			}

			$clauses = compact( $pieces );

			$where   = isset( $clauses['where'] ) ? $clauses['where'] : '';
			$join    = isset( $clauses['join'] ) ? $clauses['join'] : '';
			$groupby = isset( $clauses['groupby'] ) ? $clauses['groupby'] : '';
			$orderby = isset( $clauses['orderby'] ) ? $clauses['orderby'] : '';
			$limits  = isset( $clauses['limits'] ) ? $clauses['limits'] : '';

			// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$bans        = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM $wpdb->yith_wc_stripe_blacklist b $join WHERE 1=1 $where $groupby $orderby $limits" );
			$total_items = $wpdb->get_var( 'SELECT FOUND_ROWS()' );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			// sets columns headers.
			$columns               = $this->get_columns();
			$hidden                = array();
			$sortable              = $this->get_sortable_columns();
			$this->_column_headers = array( $columns, $hidden, $sortable );

			$this->process_bulk_action();

			$items = array();

			foreach ( $bans as $ban ) {
				$items[ $ban->ID ] = $ban;
			}

			// retrieve data for table.
			$this->items = $items;

			// sets pagination args.
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => ceil( $total_items / $per_page ),
				)
			);
		}

		/**
		 * Process the bulk actions of the blacklist table.
		 *
		 * @return void
		 */
		public function process_bulk_action() {
			if ( empty( $_GET['bans'] ) || empty( $_GET['action'] ) || empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-' . $this->_args['plural'] ) ) {
				return;
			}

			$bulk_ids = isset( $_GET['bans'] ) ? array_map( 'intval', (array) $_GET['bans'] ) : false;
			$action   = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : false;

			if ( ! ! $bulk_ids ) {
				$ids = $bulk_ids;
			}

			$this->change_banned_status( $ids, $action );
		}


		/**
		 * Change the banned status of the upcoming IDs.
		 *
		 * @param  array  $ids    Array of selected IDs (bulk) or array with only 1 element (individual actions).
		 * @param  string $action The action to be done.
		 * @return void
		 */
		public function change_banned_status( $ids, $action ) {
			global $wpdb;

			$status = 'ban' === $action ? 0 : 1;
			$ids    = implode( ', ', $ids );
			$args   = array( $status );

			$res = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}yith_wc_stripe_blacklist SET unbanned = %d WHERE ID IN ({$ids})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$status
				)
			);

			wp_safe_redirect( remove_query_arg( array( 'action', 'id', 'bans', '_wpnonce' ) ) );
			exit();
		}

		/**
		 * Print the columns information
		 *
		 * @param stdClass $rec         Current record.
		 * @param string   $column_name Column being output.
		 *
		 * @return string
		 * @since 1.1.3
		 */
		public function column_default( $rec, $column_name ) {
			switch ( $column_name ) {
				case 'ban_status':
					$display = 1 === intval( $rec->unbanned ) ? __( 'Active', 'yith-woocommerce-stripe' ) : __( 'Banned', 'yith-woocommerce-stripe' );
					$class   = 1 === intval( $rec->unbanned ) ? 'unbanned' : 'cancelled';

					return "<span class='status $class'>$display</span>";

				case 'user':
					if ( empty( $rec->user_id ) ) {
						return __( 'Unknown', 'yith-woocommerce-stripe' );
					}

					$user_info = get_user_by( 'id', $rec->user_id );

					if ( ! empty( $user_info ) ) {
						$current_user_can = current_user_can( 'edit_users' ) || get_current_user_id() === $user_info->ID;

						$username = $current_user_can ? '<a href="user-edit.php?user_id=' . absint( $user_info->ID ) . '">' : '';

						if ( $user_info->first_name || $user_info->last_name ) {
							$username .= esc_html( ucfirst( $user_info->first_name ) . ' ' . ucfirst( $user_info->last_name ) );
						} else {
							$username .= esc_html( ucfirst( $user_info->display_name ) );
						}

						if ( $current_user_can ) {
							$username .= '</a>';
						}

						$user = sprintf( '<a href="user-edit.php?user_id=%d">%s</a> - <a href="mailto:%3$s">%3$s</a>', $user_info->ID, $username, $user_info->user_email );
					} else {
						$username = __( 'Guest', 'woocommerce' );

						$user = sprintf( '<i>%s</i>', $username );
					}

					return sprintf( '<div class="tips" data-tip="%s">%s</div>', $rec->ua, $user );

				case 'order':
					$order = wc_get_order( $rec->order_id );

					if ( empty( $order ) ) {
						return null;
					}

					$order_number = '<strong>#' . esc_attr( $order->get_order_number() ) . '</strong>';
					$order_uri    = '<a href="' . admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ) . '">' . $order_number . '</a>';

					return $order_uri;

				case 'ip':
					return sprintf( '<a href="http://whois.domaintools.com/%1$s" target="_blank">%1$s</a>', $rec->ip );

				case 'date':
					$date   = $rec->ban_date;
					$t_time = date_i18n( __( 'Y/m/d g:i:s A' ), mysql2date( 'U', $date ) );
					$m_time = $date;
					$time   = mysql2date( 'G', $date );

					$time_diff = time() - $time;

					if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
						// translators: Time since record creation, in a human-friendly format 4 (4 hours / 2 days).
						$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
					} else {
						$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
					}

					return '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';

				case 'user_actions':
					$action = 0 === (int) $rec->unbanned ? 'unban' : 'ban';
					$title  = 0 === (int) $rec->unbanned ? __( 'Unban', 'yith-woocommerce-stripe' ) : __( 'Ban', 'yith-woocommerce-stripe' );
					$url    = wp_nonce_url(
						add_query_arg(
							array(
								'page'   => isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
								'tab'    => isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended
								'action' => $action,
								'id'     => $rec->ID,
							),
							admin_url( 'admin.php' )
						),
						'update_blacklist_status'
					);

					yith_plugin_fw_get_component(
						array(
							'type'   => 'action-button',
							'action' => $action,
							'title'  => $title,
							'icon'   => 'user-off',
							'url'    => $url,
						)
					);

					break;
			}

			return null;
		}

		/**
		 * Prints column cb
		 *
		 * @param stdClass $rec Item to use to print CB record.
		 *
		 * @return string
		 * @since 1.1.3
		 */
		public function column_cb( $rec ) {
			return sprintf(
				'<input type="checkbox" name="%1$s[]" value="%2$s" />',
				$this->_args['plural'], // Let's simply repurpose the table's plural label.
				$rec->ID // The value of the checkbox should be the record's id.
			);
		}

		/**
		 * Display the search box.
		 *
		 * @param string $text     The search button text.
		 * @param string $input_id The search input id.
		 *
		 * @since  3.1.0
		 * @access public
		 */
		public function add_search_box( $text, $input_id ) {
			parent::search_box( $text, $input_id );
		}

		/**
		 * Message to be displayed when there are no items
		 *
		 * @since  3.1.0
		 * @access public
		 */
		public function no_items() {
			esc_html_e( 'No bans found.', 'yith-woocommerce-stripe' );
		}

		/**
		 * Extra controls to be displayed between bulk actions and pagination
		 *
		 * @param string $which Whether we're in top or bottom tablenav.
		 *
		 * @since 1.1.3
		 */
		protected function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
				?>
				<div class="alignleft actions">
				<?php

				$this->months_dropdown( 'bans' );
				submit_button( __( 'Filter' ), 'button', 'filter_action', false, array( 'id' => 'ban-query-submit' ) );

				?>
				</div>
				<?php
			}
		}

		/**
		 * Month Dropdown filter
		 *
		 * @return array
		 * @since 1.1.3
		 */
		public function months_dropdown_results() {
			global $wpdb;

			$current_view = $this->get_current_view();
			$where        = 'WHERE 1=1 ';

			$months = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				"SELECT DISTINCT YEAR( ban_date ) AS year, MONTH( ban_date ) AS month
				FROM $wpdb->yith_wc_stripe_blacklist
				ORDER BY ban_date DESC
			"
			);

			if ( empty( $months ) ) {
				$months           = array();
				$months[0]        = new stdClass();
				$months[0]->year  = gmdate( 'Y' );
				$months[0]->month = gmdate( 'n' );
			}

			return $months;
		}

		/**
		 * Displays a dropdown for filtering items in the list table by month.
		 *
		 * @global WP_Locale $wp_locale WordPress date and time locale object.
		 *
		 * @param string $post_type Not in use.
		 */
		protected function months_dropdown( $post_type = '' ) {
			global $wp_locale;

			$months      = $this->months_dropdown;
			$month_count = count( $months );

			if ( ! $month_count || ( 1 === $month_count && 0 === $months[0]->month ) ) {
				return;
			}

			$m = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<label for="filter-by-date" class="screen-reader-text"><?php esc_html_e( 'Filter by date', 'yith-woocommerce-stripe' ); ?></label>
			<select name="m" id="filter-by-date">
				<option <?php selected( $m, 0 ); ?> value="0"><?php esc_html_e( 'All dates', 'yith-woocommerce-stripe' ); ?></option>
				<?php
				foreach ( $months as $arc_row ) {
					if ( 0 === (int) $arc_row->year ) {
						continue;
					}

					$month = zeroise( $arc_row->month, 2 );
					$year  = $arc_row->year;

					printf(
						"<option %s value='%s'>%s</option>\n",
						selected( $m, $year . $month, false ),
						esc_attr( $arc_row->year . $month ),
						// translators: 1: Month name, 2: 4-digit year.
						sprintf( esc_html__( '%1$s %2$d' ), esc_html( $wp_locale->get_month( $month ) ), esc_html( $year ) )
					);
				}
				?>
			</select>
			<?php
		}

		/**
		 * Check if there are IPs blocked to show in the table
		 */
		public function is_empty_table() {
			return 0 === count( $this->items );
		}
	}
}

