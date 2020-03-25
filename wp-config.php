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
define( 'DB_NAME', 'vapourle_mok' );

/** MySQL database username */
define( 'DB_USER', 'vapourle_mok' );

/** MySQL database password */
define( 'DB_PASSWORD', 'emntnsN}_iKb' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'L.xY#b<R)wnC+x9W?2(7j,b81U@A]:{Vb-pAs!w?2JAA/fb#(tP]qrPnzA@[#[_^' );
define( 'SECURE_AUTH_KEY',  'v^,us@BED!e(9.Wx$f:*hB4oX4#/!/Lm]yE$CMfmirZ*FgM~ZneTUZsm|b1e#@i;' );
define( 'LOGGED_IN_KEY',    'nMn9)vB^``+A6)^|QOcWD.~Dq8F2HZnQC7W0}%V)8ekKQg#l #Oyw27O<uq_0k#R' );
define( 'NONCE_KEY',        '#C igJ#G+[Oo^Y%ar*M1LWI<8*BJL4}+P--bQyz3I5f&7<nss`ywaC +}=fShL:,' );
define( 'AUTH_SALT',        'fv!jm@!&.430I|7K-(&!(,9V]I(_$Tslw]{P h5V3oAU4?0<Q=z&_<!wYA.SH8)^' );
define( 'SECURE_AUTH_SALT', ']sUqcQ/!!FO|ne#J3PA{,G7EDA-T3@4{x|VDLHg|7hi8[VFaU%KLg,^tO&fs0op)' );
define( 'LOGGED_IN_SALT',   'GB[_,!(a^TbV:$ZT1vF#XCY<1DRzSy8$tc6k4)z7~^F_Ig$%RAy&@#WQJIp*CrVY' );
define( 'NONCE_SALT',       '/TFeR5K2v[3r3Of+7:mO!Wu0S:Xc?:k3z.oA%|l1]t#J?$7_z7Co3%6oPa?2Jv_4' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
