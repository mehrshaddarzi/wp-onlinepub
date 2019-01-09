<?php
/**
 * Plugin Name: Online pub
 * Description: A Plugin For Shopping System in OnlinePub.Ir
 * Plugin URI:  https://realwp.net
 * Version:     1.0
 * Author:      Mehrshad darzi
 * Author URI:  https://realwp.net
 * License:     MIT
 * Text Domain: wp-onlinepub
 * Domain Path: /languages
 */

add_action( 'plugins_loaded', array( WP_Online_Pub::get_instance(), 'plugin_setup' ) );

class WP_Online_Pub {

	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = null;

	/**
	 * URL to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_url = '';

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public $plugin_path = '';

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @since   2012.09.13
	 * @return  object of this class
	 */
	public static function get_instance() {
		null === self::$instance and self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Send SMS
	 *
	 * @param $to
	 * @param $text
	 * @param string $template
	 * @return bool
	 */
	public static function send_sms( $to, $text, $template = '' ) {

		//Brand Name
		$brand = "نشرآنلاین";
		$brand .= "\n";
		$brand .= "OnlinePub.ir";

		//Template Sms
		switch ( $template ) {
			case "red":
				echo "Your favorite color is red!";
				break;
			default:
				$text = $text . "\n" . $brand;
		}

		//Send Sms
		$url     = 'http://login.niazpardaz.ir/SMSInOutBox/SendSms';
		$request = wp_remote_get( $url, array(
			'username' => 'c.mhm.graphic',
			'password' => '60441',
			'from'     => '10001000002424',
			'to'       => $to,
			'text'     => $text,
		) );
		if ( ! is_wp_error( $request ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook plugins_loaded
	 * @return  void
	 */
	public function plugin_setup() {
		$this->plugin_url  = plugins_url( '/', __FILE__ );
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->load_language( 'psr4-wordpress-plugin' );
		spl_autoload_register( array( $this, 'autoload' ) );


		self::send_sms( "09358510091", "ثبت نا شما با موفقیت انجام شد" );


	}

	/**
	 * Constructor. Intentionally left empty and public.
	 *
	 * @see plugin_setup()
	 */
	public function __construct() {
	}

	/**
	 * Loads translation file.
	 *
	 * Accessible to other classes to load different language files (admin and
	 * front-end for example).
	 *
	 * @wp-hook init
	 * @param   string $domain
	 * @return  void
	 */
	public function load_language( $domain ) {
		load_plugin_textdomain( $domain, false, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * @param $class
	 *
	 */
	public function autoload( $class ) {
		$class = str_replace( '\\', DIRECTORY_SEPARATOR, $class );

		if ( ! class_exists( $class ) ) {
			$class_full_path = $this->plugin_path . 'includes/' . $class . '.php';

			if ( file_exists( $class_full_path ) ) {
				require $class_full_path;
			}
		}
	}
}