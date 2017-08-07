<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'amuse');

/** MySQL database username */
define('DB_USER', 'kaihaan');

/** MySQL database password */
define('DB_PASSWORD', 'sh1r1n!2J@m');

/** MySQL hostname */
define('DB_HOST', 'localhost:3306');

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
define('AUTH_KEY',         'f04d9c2619c6274ca9f3638bf5392c166a3231fe41aaf7761def6ef42cbae7a1');
define('SECURE_AUTH_KEY',  '64564681685598bc63238257a0009fdb54c626b85a9473d57c9e2b9ea041285f');
define('LOGGED_IN_KEY',    '8b75695f67fa7c4a20f729c5940fdfd493d05429f38b1d5144df64b131ea3304');
define('NONCE_KEY',        'de8666a18f22280dfa9e067c8ffaafb4fa9125793b2ccfc71e1de2ebd98b221b');
define('AUTH_SALT',        '92f415495204edf822d47b4a723f7ba6051825cf8fd6d8c8b25b98a489c1a289');
define('SECURE_AUTH_SALT', '856de79183135621a665144c4c3e1d419503cdaeb221501922d04d0d829cb05c');
define('LOGGED_IN_SALT',   '33e5fbbaa4484f4b09745e903a0291b8772bf6030aa7ff684acc195931aaa39b');
define('NONCE_SALT',       'bd7e81f360055ed35294e53487811ba874d44ad1f17a3e04ce6787ee1e947481');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */
/**
 * The WP_SITEURL and WP_HOME options are configured to access from any hostname or IP address.
 * If you want to access only from an specific domain, you can modify them. For example:
 *  define('WP_HOME','http://example.com');
 *  define('WP_SITEURL','http://example.com');
 *
*/

define('WP_SITEURL', 'http://' . $_SERVER['HTTP_HOST']);
define('WP_HOME', 'http://' . $_SERVER['HTTP_HOST']);


/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

define('WP_TEMP_DIR', dirname(__FILE__) . '/wp-content/tmp');



//  Disable pingback.ping xmlrpc method to prevent Wordpress from participating in DDoS attacks
//  More info at: https://docs.bitnami.com/?page=apps&name=wordpress&section=how-to-re-enable-the-xml-rpc-pingback-feature

// remove x-pingback HTTP header
add_filter('wp_headers', function($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});
// disable pingbacks
add_filter( 'xmlrpc_methods', function( $methods ) {
        unset( $methods['pingback.ping'] );
        return $methods;
});
add_filter( 'auto_update_translation', '__return_false' );
