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
define( 'DB_NAME', 'u753768407_I2bjh' );
/** Database username */
define( 'DB_USER', 'u753768407_pCbb6' );
/** Database password */
define( 'DB_PASSWORD', '9J2EJ7ezcx' );
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
define( 'AUTH_KEY',          '*Ab4=Hh;]@=XzN3B>&nS12+Csc)qh=Wdo})ncV[)N8{Mc1DS;}?+)|fewGti(`R6' );
define( 'SECURE_AUTH_KEY',   'RgO8jEP:!o, /<Uv{ouG]$XA,+Gf9-}EMt@%gk(-&6u ih!`wT.N~v|*jk>cr{NP' );
define( 'LOGGED_IN_KEY',     '=+}A*1[jHPWNn`5!Mm+-eVcaVhL7s3Wkejc>wR&N%_);nByx}f:Q7wv|QCb(bWjZ' );
define( 'NONCE_KEY',         '?hJrer>FjH[F:X(@n`bk35V$-BaIcL}3<N()%Cy9>!)K>ObEi~mGzjJL}A7!`#Vo' );
define( 'AUTH_SALT',         'z&bnECH(TzLd9/<Jp$/R<77w}!}%%av>HLu=rES9<$T|ZG5Sa70EBVSc/&E%YAds' );
define( 'SECURE_AUTH_SALT',  ';L?o.jOJ5z>OC+CA?TcEJi/Smx#z*`h*Mu{tepYpY.F-9$]3n)N9Q[7N5uc,Z.NX' );
define( 'LOGGED_IN_SALT',    'ttCi{?aDKqCY&pD4fl+ZYO&nqaLubzD6!=7ow[Tkx,_%@.-D-[QYf)]+5B*6()KA' );
define( 'NONCE_SALT',        'Q=F*Jx3&gHK,dvDqaE$|[&#DVf4Roh_eB}adI}l.Y!]Nd5La0O9Lcd/lDH~F$y`9' );
define( 'WP_CACHE_KEY_SALT', 'z?u4|Ks+i_p4GqRk8]o9%yFBbb~YisP|X1~t=S_1o!$n#7lmT3B|;]01k&Ra,<2-' );
define( 'FLUENTMAIL_SMTP_USERNAME', 'info@ewebtechs.com' );
define( 'FLUENTMAIL_SMTP_PASSWORD', 'Utembeeds875@' );
/**#@-*/
/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
define('DISALLOW_FILE_EDIT',true);
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
define( 'COOKIEHASH', '241d6142b2d1f036f32282333c84d05a' );
define( 'WP_AUTO_UPDATE_CORE', false );
define( 'SURECART_ENCRYPTION_KEY', '=+}A*1[jHPWNn`5!Mm+-eVcaVhL7s3Wkejc>wR&N%_);nByx}f:Q7wv|QCb(bWjZ' );
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
