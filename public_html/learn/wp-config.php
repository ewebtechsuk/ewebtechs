<?php
define( 'WP_CACHE', true );

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'u753768407_FuQcY' );

/** Database username */
define( 'DB_USER', 'u753768407_E7jq9' );

/** Database password */
define( 'DB_PASSWORD', 'MDtcMBXgsd' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '{Wj9>6Ex!i[iEEN?(Ove}WWUtK,?AhpAY<nM|p~wV}/&r#YqzEzCC`Of61;<UvrN' );
define( 'SECURE_AUTH_KEY',   '>B!v./7#g;!R6Bg*8+H^fXh4*N`GA?n!Cf-GwHE,N/HA.2<c+?;Gmu4(4xMs}7LT' );
define( 'LOGGED_IN_KEY',     'RzA}z*;J$zX;*qqNHn;Vk  6?g8VIR{ZCx b6kA}Ph3fwXk%|Aj6Bb2GBkUE14|B' );
define( 'NONCE_KEY',         'E`IYnKBp?eedT!?&6@`=wx,:-h.t69{{7G6k8Kgsu:=9,ON5O;@Y>BFVoM(K3{[q' );
define( 'AUTH_SALT',         '4{A_/bJ;,U03EbH<Kbx]jb&a@>; A:n#v)M3s]Bl#>FzWj<a@U-{X}/]xcQIDqs~' );
define( 'SECURE_AUTH_SALT',  'TLB%9|ih3jpTF~/YUx.0+3SZZa/$oR1-&cFb&,s;,*%8)bV,WXx-k{?Bb_I/c5U&' );
define( 'LOGGED_IN_SALT',    ',z%@(XmTEW&q 7pyI,c$3s;?=O46q(}*1kB`S=un,]vkLx^&cPE gTL /g*.n}`T' );
define( 'NONCE_SALT',        'xjYo+4e[>P@b{pKg_Bf]]7NJI~{,$>SQaoRc*A!|QS&]9Pup|o-dc}5x<r g-ARr' );
define( 'WP_CACHE_KEY_SALT', '{ef]:E]Lk2&}uEA6I_L;yCm_</,giFT<@~cp:#V<f+:oe@<0*TzUyAvskWinOuOB' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'FS_METHOD', 'direct' );
define( 'COOKIEHASH', '80b86575e4b27e4c38e5cf045004de3b' );
define( 'WP_AUTO_UPDATE_CORE', 'minor' );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
