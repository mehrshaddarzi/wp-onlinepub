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

use WP_OnlinePub\WP_Mail;

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
	 * Used for regular plugin work.
	 *
	 * @wp-hook plugins_loaded
	 * @return  void
	 */
	public function plugin_setup() {

		//Set Variable
		$this->plugin_url  = plugins_url( '', __FILE__ );
		$this->plugin_path = plugin_dir_path( __FILE__ );

		//Set Text Domain
		$this->load_language( 'wp-onlinepub' );

		//Load Composer
		include_once dirname( __FILE__ ) . '/vendor/autoload.php';


		//Test Service
		if ( isset( $_GET['test'] ) ) {
		self::send_mail("mehrshad198@gmail.com", "ثبت نام شما", "<p>ثبت نام شما با موفقیت انجام شد</p><p>مر سی تشر</p>");
		exit;
		}


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
			'body' => array(
				'username' => 'c.mhm.graphic',
				'password' => '60441',
				'from'     => '10001000002424',
				'to'       => $to,
				'text'     => $text,
			)
		) );
		if ( ! is_wp_error( $request ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Send Email
	 *
	 * @param $to
	 * @param $subject
	 * @param $content
	 * @return bool
	 */
	public static function send_mail( $to, $subject, $content ) {

		//Email Template
		$email_template = wp_normalize_path( dirname( __FILE__ ) . '/template/email.php' );

		//Email from
		$from_name  = 'نشرآنلاین';
		$from_email = get_bloginfo( 'admin_email' );

		//Template Arg
		$template_arg = array(
			'title'      => $subject,
			'logo'       => 'http://order.onlinepub.ir/wp-content/themes/OPUB%20-System/OnlinePUB-Logo.png',
			'content'    => $content,
			'site_url'   => home_url(),
			'site_title' => 'نشر آنلاین',
		);

		//Send Email
		try {
			WP_Mail::init()->from( '' . $from_name . ' <' . $from_email . '>' )->to( $to )->subject( $subject )->template( $email_template, $template_arg )->send();
			return true;
		} catch ( Exception $e ) {
			return false;
		}

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
}