<?php
/**
 * List of options for Blacklist tab.
 *
 * @author YITH <plugins@yithemes.com>
 * @package YITH\Stripe\Options
 * @version 1.0.0
 */

if ( ! defined( 'YITH_WCSTRIPE' ) ) {
	exit;
} // Exit if accessed directly

return array(
	'blacklist' => array(
		'blacklist' => array(
			'type'         => 'custom_tab',
			'action'       => 'yith_wcstripe_blacklist_tab',
			'hide_sidebar' => true,
		),
	),
);
