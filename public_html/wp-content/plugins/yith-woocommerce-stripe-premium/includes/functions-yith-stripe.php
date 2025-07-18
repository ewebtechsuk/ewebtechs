<?php
/**
 * Function file
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Functions
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

if ( ! function_exists( 'yith_wcstripe_return_10' ) ) {
	/**
	 * Just returns 10
	 *
	 * @return int 10
	 */
	function yith_wcstripe_return_10() {
		return 10;
	}
}

if ( ! function_exists( 'yith_wcstripe_locate_template' ) ) {
	/**
	 * Locate template for Stripe plugin
	 *
	 * @param string $filename Template name (with or without extension).
	 * @param string $section  Subdirectory where to search.
	 *
	 * @return string Found template
	 */
	function yith_wcstripe_locate_template( $filename, $section = '' ) {
		$ext = preg_match( '/^.*\.[^\.]+$/', $filename ) ? '' : '.php';

		$template_name = $section . '/' . $filename . $ext;
		$template_path = WC()->template_path() . 'yith-wcstripe/';
		$default_path  = YITH_WCSTRIPE_DIR . 'templates/';

		return wc_locate_template( $template_name, $template_path, $default_path );
	}
}

if ( ! function_exists( 'yith_wcstripe_get_template' ) ) {
	/**
	 * Get template for Stripe plugin
	 *
	 * @param string $filename Template name (with or without extension).
	 * @param array  $args     Array of params to use in the template.
	 * @param string $section  Subdirectory where to search.
	 */
	function yith_wcstripe_get_template( $filename, $args = array(), $section = '' ) {
		$ext = preg_match( '/^.*\.[^\.]+$/', $filename ) ? '' : '.php';

		$template_name = $section . '/' . $filename . $ext;
		$template_path = WC()->template_path() . 'yith-wcstripe/';
		$default_path  = YITH_WCSTRIPE_DIR . 'templates/';

		wc_get_template( $template_name, $args, $template_path, $default_path );
	}
}

if ( ! function_exists( 'yith_wcstripe_get_cart_hash' ) ) {
	/**
	 * Retrieves cart hash, using WC method when available, or providing an approximated version for older WC versions
	 *
	 * @return string Cart hash
	 */
	function yith_wcstripe_get_cart_hash() {
		$cart = WC()->cart;

		if ( ! $cart ) {
			return '';
		}

		if ( method_exists( $cart, 'get_cart_hash' ) ) {
			return $cart->get_cart_hash();
		} else {
			$cart_contents = $cart->get_cart_contents();

			return $cart_contents ? md5( wp_json_encode( $cart_contents ) . $cart->get_total( 'edit' ) ) : '';
		}
	}
}

if ( ! function_exists( 'yith_wcstripe_get_shipping_counties' ) ) {
	/**
	 * Returns a list of valid countries for shipping, to be used in API calls
	 *
	 * @return array Array of country codes, in ISO format
	 */
	function yith_wcstripe_get_shipping_counties() {
		$shipping_countries = array_keys( WC()->countries->get_shipping_countries() );
		$allowed_countries  = array(
			'AC',
			'AD',
			'AE',
			'AF',
			'AG',
			'AI',
			'AL',
			'AM',
			'AO',
			'AQ',
			'AR',
			'AT',
			'AU',
			'AW',
			'AX',
			'AZ',
			'BA',
			'BB',
			'BD',
			'BE',
			'BF',
			'BG',
			'BH',
			'BI',
			'BJ',
			'BL',
			'BM',
			'BN',
			'BO',
			'BQ',
			'BR',
			'BS',
			'BT',
			'BV',
			'BW',
			'BY',
			'BZ',
			'CA',
			'CD',
			'CF',
			'CG',
			'CH',
			'CI',
			'CK',
			'CL',
			'CM',
			'CN',
			'CO',
			'CR',
			'CV',
			'CW',
			'CY',
			'CZ',
			'DE',
			'DJ',
			'DK',
			'DM',
			'DO',
			'DZ',
			'EC',
			'EE',
			'EG',
			'EH',
			'ER',
			'ES',
			'ET',
			'FI',
			'FJ',
			'FK',
			'FO',
			'FR',
			'GA',
			'GB',
			'GD',
			'GE',
			'GF',
			'GG',
			'GH',
			'GI',
			'GL',
			'GM',
			'GN',
			'GP',
			'GQ',
			'GR',
			'GS',
			'GT',
			'GU',
			'GW',
			'GY',
			'HK',
			'HN',
			'HR',
			'HT',
			'HU',
			'ID',
			'IE',
			'IL',
			'IM',
			'IN',
			'IO',
			'IQ',
			'IS',
			'IT',
			'JE',
			'JM',
			'JO',
			'JP',
			'KE',
			'KG',
			'KH',
			'KI',
			'KM',
			'KN',
			'KR',
			'KW',
			'KY',
			'KZ',
			'LA',
			'LB',
			'LC',
			'LI',
			'LK',
			'LR',
			'LS',
			'LT',
			'LU',
			'LV',
			'LY',
			'MA',
			'MC',
			'MD',
			'ME',
			'MF',
			'MG',
			'MK',
			'ML',
			'MM',
			'MN',
			'MO',
			'MQ',
			'MR',
			'MS',
			'MT',
			'MU',
			'MV',
			'MW',
			'MX',
			'MY',
			'MZ',
			'NA',
			'NC',
			'NE',
			'NG',
			'NI',
			'NL',
			'NO',
			'NP',
			'NR',
			'NU',
			'NZ',
			'OM',
			'PA',
			'PE',
			'PF',
			'PG',
			'PH',
			'PK',
			'PL',
			'PM',
			'PN',
			'PR',
			'PS',
			'PT',
			'PY',
			'QA',
			'RE',
			'RO',
			'RS',
			'RU',
			'RW',
			'SA',
			'SB',
			'SC',
			'SE',
			'SG',
			'SH',
			'SI',
			'SJ',
			'SK',
			'SL',
			'SM',
			'SN',
			'SO',
			'SR',
			'SS',
			'ST',
			'SV',
			'SX',
			'SZ',
			'TA',
			'TC',
			'TD',
			'TF',
			'TG',
			'TH',
			'TJ',
			'TK',
			'TL',
			'TM',
			'TN',
			'TO',
			'TR',
			'TT',
			'TV',
			'TW',
			'TZ',
			'UA',
			'UG',
			'US',
			'UY',
			'UZ',
			'VA',
			'VC',
			'VE',
			'VG',
			'VN',
			'VU',
			'WF',
			'WS',
			'XK',
			'YE',
			'YT',
			'ZA',
			'ZM',
			'ZW',
			'ZZ',

		);

		return array_values( array_intersect( $shipping_countries, $allowed_countries ) );
	}
}

if ( ! function_exists( 'yith_wcstripe_legacy_filters' ) ) {
	/**
	 * Hooks back function created for old filters, to the new ones
	 *
	 * @since 2.2.0
	 */
	function yith_wcstripe_legacy_filters() {
		static $executed;

		if ( $executed ) {
			return;
		}

		$legacy_filters = array(
			'yith-wcstripe-error-messages'   => array(
				'replacement' => 'yith_wcstripe_error_messages',
				'version'     => '2.2.0',
			),
			'yith-wcstripe-decline-messages' => array(
				'replacement' => 'yith_wcstripe_decline_messages',
				'version'     => '2.2.0',
			),
		);

		foreach ( $legacy_filters as $old_hook => $details ) {
			$new_hook = $details['replacement'];
			$since    = $details['version'];

			add_filter(
				$new_hook,
				function () use ( $old_hook, $new_hook, $since ) {
					$new_callback_args = func_get_args();
					$return_value      = $new_callback_args[0];

					if ( has_filter( $old_hook ) ) {
						wc_deprecated_hook( $old_hook, $since, $new_hook );
						$return_value = apply_filters_ref_array( $old_hook, $new_callback_args );
					}

					return $return_value;
				},
				0,
				2
			);
		}

		$executed = true;
	}
}

if ( ! function_exists( 'yith_stripe_get_view' ) ) {
	/**
	 * Get the view
	 *
	 * @param string $view View name.
	 * @param array  $args Parameters to include in the view.
	 */
	function yith_stripe_get_view( $view, $args = array() ) {
		$view_path = trailingslashit( YITH_WCSTRIPE_VIEWS ) . $view;

		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		if ( file_exists( $view_path ) ) {
			include $view_path;
		}
	}
}
