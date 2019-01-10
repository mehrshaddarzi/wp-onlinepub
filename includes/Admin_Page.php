<?php

namespace WP_OnlinePub;

use WP_Online_Pub;
use WP_OnlinePub\WP_List_Table\Factor as wlt_factor;
use WP_OnlinePub\WP_List_Table\Order as wlt_order;
use WP_OnlinePub\WP_List_Table\Payment as wlt_payment;
use WP_OnlinePub\WP_List_Table\Ticket as wlt_ticket;

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
	 * List Pages Slug in This Plugin
	 */
	public static $pages;


	/**
	 * Admin_Page constructor.
	 */
	public function __construct() {

		//Set Variable
		self::$admin_page_slug = 'order';
		self::$pages           = array( "order", "factor", "payment", "ticket" );

		//Add Admin Menu Wordpress
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		//Set Screen Option
		add_filter( 'set-screen-option', array( $this, 'set_screen' ), 10, 3 );

		//Add Script to Admin Wordpress
		//add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );

		//Set Admin Notice and Custom Redirect for Per Page
		foreach ( self::$pages as $page_slug ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_' . $page_slug ) );
			add_action( 'admin_init', array( $this, 'wlt_redirect_' . $page_slug ) );
		}

		//Remove All Notice Another Plugin
		add_action( 'admin_print_scripts', array( $this, 'prevent_admin_notices_plugins' ) );

	}

	/**
	 * Admin Link
	 *
	 * @param $page
	 * @param array $args
	 * @return string
	 */
	public static function admin_link( $page, $args = array() ) {
		return add_query_arg( $args, admin_url( '?page=' . $page ) );
	}

	/**
	 * If in Page in Admin
	 *
	 * @param $page_slug
	 * @return bool
	 */
	public static function in_page( $page_slug ) {
		global $pagenow;
		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and $_GET['page'] == $page_slug ) {
			return true;
		}
	}

	/**
	 * Prevent and Disable all admin Notice
	 */
	public function prevent_admin_notices_plugins() {
		global $wp_filter, $pagenow;

		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and in_array( $_GET['page'], self::$pages ) and ! isset( $_GET['alert'] ) ) {
			if ( isset( $wp_filter['user_admin_notices'] ) ) {
				unset( $wp_filter['user_admin_notices'] );
			}
			if ( isset( $wp_filter['admin_notices'] ) ) {
				unset( $wp_filter['admin_notices'] );
			}
			if ( isset( $wp_filter['all_admin_notices'] ) ) {
				unset( $wp_filter['all_admin_notices'] );
			}
		}
	}

	/**
	 * Load assets file in admin
	 */
	public function admin_assets() {
		global $pagenow;

		//List Allow This Script
		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and in_array( $_GET['page'], self::$pages ) ) {

			//Load Jquery Confirm
			wp_enqueue_style( 'jQuery-confirm', WP_Online_Pub::$plugin_url . 'assets/admin/css/jquery-confirm.min.css', true, '3.3.0' );
			wp_enqueue_script( 'jQuery-confirm', WP_Online_Pub::$plugin_url . 'assets/admin/js/jquery-confirm.min.js', array( 'jquery' ), '3.3.0', true );

			//Load init Script
			wp_enqueue_style( 'wp-online-pub', plugin_dir_url( __DIR__ ) . 'assets/admin/css/style.css', true, WP_Online_Pub::$plugin_version );
			wp_enqueue_script( 'wp-online-pub', plugin_dir_url( __DIR__ ) . 'assets/admin/js/script.js', array( 'jquery' ), WP_Online_Pub::$plugin_version, true );
			wp_localize_script( 'wp-online-pub', 'wp_options_js', array(
				'ajax'        => admin_url( "admin-ajax.php" ),
				'is_rtl'      => ( is_rtl() ? 1 : 0 ),
				'loading_img' => admin_url( "/images/spinner.gif" ),
			) );
		}

	}

	/**
	 * Screen Option
	 *
	 * @param $status
	 * @param $option
	 * @param $value
	 * @return mixed
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
		$this->order_obj = new wlt_order();
		$this->order_obj->prepare_items();

	}

	//Order Admin Page
	public function order() {
		if ( ! isset( $_GET['method'] ) ) {

			//Show Wp List Table
			Admin_Ui::wp_list_table( $this->order_obj, "cart", get_admin_page_title(), array(), true );
		} else {

		}
	}

	//Admin Notice
	public function admin_notice_order() {
		if ( self::in_page( 'order' ) and isset( $_GET['alert'] ) ) {
			switch ( $_GET['alert'] ) {

				//Delete Alert
				case "delete":
					//Admin_Ui::wp_admin_notice( __( "Selected item has been Deleted.", 'wp-statistics-actions' ), "success" );
					break;

			}
		}
	}

	//Redirect Process
	public function wlt_redirect_order() {
		//Current Page Slug
		$page_slug = 'order';
		if ( self::in_page( $page_slug ) and ! isset( $_GET['method'] ) ) {

			//Redirect For $_POST Form Performance
			foreach ( array( "s", "user" ) as $post ) {
				if ( isset( $_POST[ $post ] ) and ! empty( $_POST[ $post ] ) ) {
					$args = array( 'page' => $page_slug, $post => $_POST[ $post ] );
					if ( isset( $_GET['filter'] ) ) {
						$args['filter'] = $_GET['filter'];
					}
					wp_redirect( add_query_arg( $args, admin_url( "admin.php" ) ) );
					exit;
				}
			}

			//Remove Admin Notice From Pagination
			if ( isset( $_GET['alert'] ) and isset( $_GET['paged'] ) ) {
				wp_redirect( remove_query_arg( array( 'alert' ) ) );
				exit;
			}

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
		$this->factor_obj = new wlt_factor();
		$this->factor_obj->prepare_items();

	}

	//Factor Admin Page
	public function factor() {
		if ( ! isset( $_GET['method'] ) ) {

			//Show Wp List Table
			Admin_Ui::wp_list_table( $this->factor_obj, "format-aside", get_admin_page_title(), array( 'link' => self::admin_link( "factor", array( "method" => "add" ) ), 'name' => 'ایجاد فاکتور' ), true );
		} else {

		}
	}

	//Admin Notice
	public function admin_notice_factor() {
		if ( self::in_page( 'factor' ) and isset( $_GET['alert'] ) ) {
			switch ( $_GET['alert'] ) {

				//Delete Alert
				case "delete":
					//Admin_Ui::wp_admin_notice( __( "Selected item has been Deleted.", 'wp-statistics-actions' ), "success" );
					break;

			}
		}
	}

	//Redirect Process
	public function wlt_redirect_factor() {
		//Current Page Slug
		$page_slug = 'factor';
		if ( self::in_page( $page_slug ) and ! isset( $_GET['method'] ) ) {

			//Redirect For $_POST Form Performance
			foreach ( array( "s", "user" ) as $post ) {
				if ( isset( $_POST[ $post ] ) and ! empty( $_POST[ $post ] ) ) {
					$args = array( 'page' => $page_slug, $post => $_POST[ $post ] );
					if ( isset( $_GET['filter'] ) ) {
						$args['filter'] = $_GET['filter'];
					}
					wp_redirect( add_query_arg( $args, admin_url( "admin.php" ) ) );
					exit;
				}
			}

			//Remove Admin Notice From Pagination
			if ( isset( $_GET['alert'] ) and isset( $_GET['paged'] ) ) {
				wp_redirect( remove_query_arg( array( 'alert' ) ) );
				exit;
			}

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
		$this->payment_obj = new wlt_payment();
		$this->payment_obj->prepare_items();

	}

	//Payment Admin Page
	public function payment() {
		if ( ! isset( $_GET['method'] ) ) {

			//Show Wp List Table
			Admin_Ui::wp_list_table( $this->payment_obj, "slides", get_admin_page_title(), array(), false );
		} else {

		}
	}

	//Admin Notice
	public function admin_notice_payment() {
		if ( self::in_page( 'payment' ) and isset( $_GET['alert'] ) ) {
			switch ( $_GET['alert'] ) {

				//Delete Alert
				case "delete":
					//Admin_Ui::wp_admin_notice( __( "Selected item has been Deleted.", 'wp-statistics-actions' ), "success" );
					break;

			}
		}
	}

	//Redirect Process
	public function wlt_redirect_payment() {
		//Current Page Slug
		$page_slug = 'payment';
		if ( self::in_page( $page_slug ) and ! isset( $_GET['method'] ) ) {

			//Redirect For $_POST Form Performance
			foreach ( array( "s", "user" ) as $post ) {
				if ( isset( $_POST[ $post ] ) and ! empty( $_POST[ $post ] ) ) {
					$args = array( 'page' => $page_slug, $post => $_POST[ $post ] );
					if ( isset( $_GET['filter'] ) ) {
						$args['filter'] = $_GET['filter'];
					}
					wp_redirect( add_query_arg( $args, admin_url( "admin.php" ) ) );
					exit;
				}
			}

			//Remove Admin Notice From Pagination
			if ( isset( $_GET['alert'] ) and isset( $_GET['paged'] ) ) {
				wp_redirect( remove_query_arg( array( 'alert' ) ) );
				exit;
			}

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
		$this->ticket_obj = new wlt_ticket();
		$this->ticket_obj->prepare_items();

	}

	//Ticket Admin Page
	public function ticket() {
		if ( ! isset( $_GET['method'] ) ) {

			//Show Wp List Table
			Admin_Ui::wp_list_table( $this->ticket_obj, "testimonial", get_admin_page_title(), array(), true );
		} else {

		}
	}

	//Admin Notice
	public function admin_notice_ticket() {
		if ( self::in_page( 'ticket' ) and isset( $_GET['alert'] ) ) {
			switch ( $_GET['alert'] ) {

				//Delete Alert
				case "delete":
					//Admin_Ui::wp_admin_notice( __( "Selected item has been Deleted.", 'wp-statistics-actions' ), "success" );
					break;

			}
		}
	}

	//Redirect Process
	public function wlt_redirect_ticket() {
		//Current Page Slug
		$page_slug = 'ticket';
		if ( self::in_page( $page_slug ) and ! isset( $_GET['method'] ) ) {

			//Redirect For $_POST Form Performance
			foreach ( array( "s", "user" ) as $post ) {
				if ( isset( $_POST[ $post ] ) and ! empty( $_POST[ $post ] ) ) {
					$args = array( 'page' => $page_slug, $post => $_POST[ $post ] );
					if ( isset( $_GET['filter'] ) ) {
						$args['filter'] = $_GET['filter'];
					}
					wp_redirect( add_query_arg( $args, admin_url( "admin.php" ) ) );
					exit;
				}
			}

			//Remove Admin Notice From Pagination
			if ( isset( $_GET['alert'] ) and isset( $_GET['paged'] ) ) {
				wp_redirect( remove_query_arg( array( 'alert' ) ) );
				exit;
			}

		}
	}


}