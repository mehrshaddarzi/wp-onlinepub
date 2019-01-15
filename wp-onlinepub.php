<?php
/**
 * Plugin Name: Online pub
 * Description: A Plugin For Shopping System in OnlinePub.Ir
 * Plugin URI:  https://realwp.net
 * Version:     2.0.2
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
	public static $plugin_url = '';

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public static $plugin_path;

	/**
	 * Path to this plugin's directory.
	 *
	 * @type string
	 */
	public static $plugin_version;

	/**
	 * Plugin Option Store
	 */
	public static $option;

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

		//Get plugin Data information
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( __FILE__ );

		//Get Option
		self::$option = get_option( 'wp_online_pub_opt' );

		//Get Plugin Version
		self::$plugin_version = $plugin_data['Version'];

		//Set Variable
		self::$plugin_url  = plugins_url( '', __FILE__ );
		self::$plugin_path = plugin_dir_path( __FILE__ );

		//Set Text Domain
		$this->load_language( 'wp-onlinepub' );

		//Load Composer
		include_once dirname( __FILE__ ) . '/vendor/autoload.php';

		//set plugin option
		new \WP_OnlinePub\Gravity_Form();
		new \WP_OnlinePub\Ticket();
		new \WP_OnlinePub\Admin_Setting_Api();
		new \WP_OnlinePub\Admin_Page();
		new \WP_OnlinePub\Front();
		new \WP_OnlinePub\Payment();
		new \WP_OnlinePub\Ajax();

		//Test Service
		if ( isset( $_GET['test'] ) ) {
			//self::send_mail('admin', 'عنوان ایمیل','matn email test');
			//exit;
		}
	}

	/**
	 * Send SMS
	 *
	 * @param $to
	 * @param $text
	 * @param string $template
	 * @param array $args
	 * @return bool
	 */
	public static function send_sms( $to, $text, $template = '', $args = array() ) {

		//Brand Name
		$brand = "نشرآنلاین";
		$brand .= "\n";
		$brand .= "OnlinePub.ir";

		//Sms To Admin
		if ( $to == "admin" ) {
			//$to = '09101566463';
			$opt = get_option( 'wp_online_pub_opt' );
			$to  = $opt['modir_mobile'];
		}

		//Template Sms
		switch ( $template ) {
			case "send_to_admin_at_new_order":
				$text = 'یک سفارش جدید به شناسه ';
				$text .= $args['order_id'];
				$text .= ' به نام ';
				$text .= $args['user_name'];
				$text .= ' در سایت ثبت شده است';
				break;

			case "send_to_user_at_new_order":
				$text = 'کاربر گرامی سفارش شما به شناسه ';
				$text .= $args['order_id'];
				$text .= '  با موفقت ثبت و برای بررسی اولیه ارسال شده است';
				$text .= "\n" . $brand;
				break;

			case "send_to_user_at_change_status":
				$text = 'کاربر گرامی , وضعیت سفارش شما به شناسه ';
				$text .= $args['order_id'];
				$text .= ' به "';
				$text .= $args['new_status'];
				$text .= '"';
				$text .= ' تغییر داده شد.';
				$text .= "\n" . $brand;
				break;

			case "send_to_user_at_create_factor":
				$text = 'کاربر گرامی , یک ';
				if ( $args['factor_type'] == 1 ) {
					$text .= 'پیش فاکتور ';
				} else {
					$text .= 'فاکتور ';
				}
				//$text .= "به مبلغ ";
				//$text .= number_format( $args['factor_price'] ) . ' ' . \WP_OnlinePub\Helper::currency() . ' ';
				$text .= "به شناسه ";
				$text .= number_format( $args['factor_id'] );
				$text .= 'برای سفارش به شناسه ';
				$text .= $args['order_id'];
				$text .= ' ایجاد شده است.لطفا نسبت به پرداخت آن اقدام نمایید.';
				$text .= "\n" . $brand;
				break;

			case "send_to_user_at_edit_factor":
				$text = 'کاربر گرامی';
				$text .= " مبلغ فاکتور به شناسه ";
				$text .= $args['order_id'];
				$text .= " به ";
				$text .= number_format( $args['factor_price'] ) . ' ' . \WP_OnlinePub\Helper::currency() . ' ';
				$text .= ' تغییر پیدا کرد .';
				$text .= "\n" . $brand;
				break;

			case "send_to_user_at_create_ticket":
				$text = 'کاربر گرامی';
				$text .= " یک تیکت جدید برای سفارش با شناسه ";
				$text .= $args['order_id'];
				$text .= " در سامانه نشر آنلاین ایجاد شده است.لطفا مشاهده کنید و به آن پاسخ دهید ";
				$text .= "\n" . $brand;
				break;

			case "send_to_user_at_reply_ticket":
				$text = 'کاربر گرامی';
				$text .= " یک پاسخ جدید توسط کارشناس برای سفارش با شناسه ";
				$text .= $args['order_id'];
				$text .= " در سامانه نشر آنلاین ارسال شده است.لطفا مشاهده کنید و به آن پاسخ دهید ";
				$text .= "\n" . $brand;
				break;

			case "send_to_admin_at_ticket_from_user":
				$text = 'مدیر گرامی ، کاربر با نام ';
				$text .= $args['user_name'];
				$text .= " برای سفارش با شناسه ";
				$text .= $args['order_id'];
				$text .= " یک تیکت ارسال کرده است. ";
				$text .= "\n" . $brand;
				break;

			case "send_to_admin_at_new_fish":
				$text = 'مدیر گرامی ، کاربر با نام ';
				$text .= $args['user_name'];
				$text .= " برای فاکتور با شناسه ";
				$text .= $args['factor_id'];
				$text .= " یک فیش بانکی اضافه کرد. ";
				$text .= "\n" . $brand;
				break;

			case "send_to_user_at_pay_online_factor":
				$text = 'کاربر گرامی';
				$text .= " فاکتور با شناسه ";
				$text .= $args['factor_id'];
				$text .= " و مبلغ ";
				$text .= $args['factor_price'] . ' ' . \WP_OnlinePub\Helper::currency();
				$text .= " با موفقیت پرداخت و تایید شد .";
				$text .= "\n" . $brand;
				break;

			case "send_to_admin_at_new_online_pay":
				$text = 'مدیر گرامی ، کاربر با نام ';
				$text .= $args['user_name'];
				$text .= " برای فاکتور با شناسه ";
				$text .= $args['factor_id'];
				$text .= " یک پرداخت آنلاین موفق به مبلغ ";
				$text .= $args['factor_price'] . ' ' . \WP_OnlinePub\Helper::currency();
				$text .= " انجام داده است.  ";
				$text .= "\n" . $brand;
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

		//Set To Admin
		if ( $to == "admin" ) {
			$opt = get_option( 'wp_online_pub_opt' );
			//$to = 'opub.ir@gmail.com';
			$to = $opt['modir_email'];
		}

		//Email from
		$from_name  = 'نشرآنلاین';
		$from_email = get_bloginfo( 'admin_email' );

		//Template Arg
		$template_arg = array(
			'title'      => $subject,
			'logo'       => plugins_url( '', __FILE__ ) . '/template/email.jpg',
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