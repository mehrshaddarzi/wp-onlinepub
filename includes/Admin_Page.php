<?php

namespace WP_OnlinePub;


use WP_OnlinePub\WP_List_Table\Factor;
use WP_OnlinePub\WP_List_Table\Order;
use WP_OnlinePub\WP_List_Table\Payment;
use WP_OnlinePub\WP_List_Table\Ticket;

class Admin_Page {

	/**
	 * Admin Page slug
	 */
	public static $admin_page_slug;

	/**
	 * List OF Variable For WP_List_Table
	 */
	public $order_obj, $factor_obj, $payment_obj, $ticket_obj;


	/**
	 * Admin_Page constructor.
	 */
	public function __construct() {

		//Set Variable
		self::$admin_page_slug = 'online_pub';

		//Add Admin Menu Wordpress
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		//Set Screen Option
		add_filter( 'set-screen-option', array( $this, 'set_screen' ), 10, 3 );


	}

	/**
	 * Screen Option
	 */
	public static function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Set Admin Menu
	 */
	public function admin_menu() {

		add_menu_page( 'فروشگاه', 'فروشگاه', 'manage_options', self::$admin_page_slug, array( $this, 'order' ), 'dashicons-cart', 2 );
		$order   = add_submenu_page( self::$admin_page_slug, __( 'سفارشات', '' ), __( 'سفارشات', '' ), 'manage_options', self::$admin_page_slug, array( $this, 'order' ) );
		$factor  = add_submenu_page( self::$admin_page_slug, __( 'فاکتورها', '' ), __( 'فاکتورها', '' ), 'manage_options', 'factor', array( $this, 'factor' ) );
		$payment = add_submenu_page( self::$admin_page_slug, __( 'پرداخت ها', '' ), __( 'پرداخت ها', '' ), 'manage_options', 'payment', array( $this, 'payment' ) );
		$ticket  = add_submenu_page( self::$admin_page_slug, __( 'تیکت ها', '' ), __( 'تیکت ها', '' ), 'manage_options', 'ticket', array( $this, 'ticket' ) );

		//Set Load Action For WP_List_Table
		add_action( "load-$order", array( $this, 'screen_option_order' ) );
		add_action( "load-$factor", array( $this, 'screen_option_factor' ) );
		add_action( "load-$payment", array( $this, 'screen_option_payment' ) );
		add_action( "load-$ticket", array( $this, 'screen_option_ticket' ) );
	}



	/**=============================================================================== ORDER
	 * = Order WP_LIST_TABLE
	 * ================================================================================= */

	//Screen Option
	public function screen_option_order() {

		//Set Screen Option
		$option = 'per_page';
		$args   = array( 'label' => __( "تعداد نمایش در صفحه", '' ), 'default' => 10, 'option' => 'order_per_page' ); //options is user Meta
		add_screen_option( $option, $args );

		//Load WP_List_Table
		$this->order_obj = new Order();
		$this->order_obj->prepare_items();

	}

	//Order Admin Page
	public function order() {
		if ( ! isset( $_GET['method'] ) ) {

			//Show Wp List Table
			Admin_Ui::wp_list_table($this->order_obj, "cart", get_admin_page_title(), array(), false);
		} else {

		}
	}

	/**=============================================================================== Factor
	 * = Factor WP_LIST_TABLE
	 * ================================================================================= */

	//Screen Option
	public function screen_option_factor() {

		//Set Screen Option
		$option = 'per_page';
		$args   = array( 'label' => __( "تعداد نمایش در صفحه", '' ), 'default' => 10, 'option' => 'factor_per_page' ); //options is user Meta
		add_screen_option( $option, $args );

		//Load WP_List_Table
		$this->factor_obj = new Factor();
		$this->factor_obj->prepare_items();

	}

	//Factor Admin Page
	public function factor() {
		if ( ! isset( $_GET['method'] ) ) {

			//Show Wp List Table
			Admin_Ui::wp_list_table($this->factor_obj, "format-aside", get_admin_page_title(), array('link' => 'http//', 'name' => 'ایجاد فاکتور'), true);
		} else {

		}
	}

	/**=============================================================================== Payment
	 * = Payment WP_LIST_TABLE
	 * ================================================================================= */

	//Screen Option
	public function screen_option_payment() {

		//Set Screen Option
		$option = 'per_page';
		$args   = array( 'label' => __( "تعداد نمایش در صفحه", '' ), 'default' => 10, 'option' => 'payment_per_page' ); //options is user Meta
		add_screen_option( $option, $args );

		//Load WP_List_Table
		$this->payment_obj = new Payment();
		$this->payment_obj->prepare_items();

	}

	//Payment Admin Page
	public function payment() {
		if ( ! isset( $_GET['method'] ) ) {

			//Show Wp List Table
			Admin_Ui::wp_list_table($this->payment_obj, "slides", get_admin_page_title(), array(), false);
		} else {

		}
	}


	/**=============================================================================== Ticket
	 * = Ticket WP_LIST_TABLE
	 * ================================================================================= */

	//Screen Option
	public function screen_option_ticket() {

		//Set Screen Option
		$option = 'per_page';
		$args   = array( 'label' => __( "تعداد نمایش در صفحه", '' ), 'default' => 10, 'option' => 'ticket_per_page' ); //options is user Meta
		add_screen_option( $option, $args );

		//Load WP_List_Table
		$this->ticket_obj = new Ticket();
		$this->ticket_obj->prepare_items();

	}

	//Ticket Admin Page
	public function ticket() {
		if ( ! isset( $_GET['method'] ) ) {

			//Show Wp List Table
			Admin_Ui::wp_list_table($this->ticket_obj, "testimonial", get_admin_page_title(), array(), true);
		} else {

		}
	}


}