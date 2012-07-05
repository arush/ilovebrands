<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'Yei2:128:FY2');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'yA<sN4sQ~f&Vmrsk(PXFM3)lG<f4QZ2.L6GE4v@np `qi|pRMjlZ!%gZp1>5Ve4>');
define('SECURE_AUTH_KEY',  'Wsl5(+9rTj.nJ*Ae8G: WjVe<,0+b_VvUsY[uA;AlP]i/d <gllAj3PrJ_ rGS,i');
define('LOGGED_IN_KEY',    'B|/as|uaqw8Qe2zbE5pzJi;g8[?,<-J<sxSiX}ltIZiraS_bydDOTE_pmMc~A/Yl');
define('NONCE_KEY',        'IHcH%.J+.f4Iw7!9~n^g21;ac3oOsGtzV.Iwreoz|m?|- 8HGj4->u~WYT<vpzX4');
define('AUTH_SALT',        'G0n!L<fV>lYvU4<%r%v)uUfYU>msKy@p^RII+;kCj4qe^jYF?*lNj9n4:]i|g ME');
define('SECURE_AUTH_SALT', '#wbO!n1+(IaZJUkNMX,n#q/+5WS5:[+Xf.-R9S-NxiArm8`*!P-6=JWQ?Zw6.16&');
define('LOGGED_IN_SALT',   'hGL5Z7oFC=U;38>hz~>)-MRUjG|mH,?-XkO*c!A%I*D$%.`|dG_ApSj5C6Y/-ewJ');
define('NONCE_SALT',       '#]^+W.Fv#5{K6:kVh:+ G9XM1K]0g#;^aJnn;^4F-|ZgXL-C=YxIDj5#na3I!c?#');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */
define('WP_HOME','http://www.ilovebrandsoutlet.com/blog');
define('WP_SITEURL','http://www.ilovebrandsoutlet.com/blog');
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

