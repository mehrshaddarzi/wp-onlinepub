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
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );

		//Set Admin Notice and Custom Redirect and Custom Js/css for Per Page
		foreach ( self::$pages as $page_slug ) {
			add_action( 'admin_notices', array( $this, 'admin_notice_' . $page_slug ) );
			add_action( 'admin_init', array( $this, 'wlt_redirect_' . $page_slug ) );
			add_action( 'admin_head', array( $this, 'wlt_script_' . $page_slug ) );
			add_action( 'wlt_top_content', array( $this, 'wlt_top_' . $page_slug ) );
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
		return add_query_arg( $args, admin_url( 'admin.php?page=' . $page ) );
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

		return false;
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
			wp_enqueue_style( 'jQuery-confirm', WP_Online_Pub::$plugin_url . '/asset/admin/css/jquery-confirm.min.css', true, '3.3.0' );
			wp_enqueue_script( 'jQuery-confirm', WP_Online_Pub::$plugin_url . '/asset/admin/js/jquery-confirm.min.js', array( 'jquery' ), '3.3.0', true );

			//Load init Script
			wp_enqueue_style( 'wp-online-style', WP_Online_Pub::$plugin_url . '/asset/admin/css/style.css', true, WP_Online_Pub::$plugin_version );
			wp_enqueue_script( 'wp-online-js', WP_Online_Pub::$plugin_url . '/asset/admin/js/script.js', array( 'jquery' ), WP_Online_Pub::$plugin_version, true );
			wp_localize_script( 'wp-online-js', 'wp_options_js', array(
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
					Admin_Ui::wp_admin_notice( __( "آیتم های انتخابی با موفقیت حذف گردید", 'wp-statistics-actions' ), "success" );
					break;

				//Change status
				case "change-status":
					Admin_Ui::wp_admin_notice( __( "تغییر وضعیت سفارش با موفقیت انجام شد", 'wp-statistics-actions' ), "success" );
					break;

			}
		}
	}

	//Custom Script css/Js
	public function wlt_script_order() {
		if ( self::in_page( 'order' ) ) {
			echo '<style>table.widefat th.column-title {width: 260px;}</style>';
		}
	}

	//Top content Wp List Table
	public function wlt_top_order() {
		if ( self::in_page( 'order' ) and isset( $_GET['top'] ) ) {

			//Top Content for Status
			if ( $_GET['top'] == "change-status" ) {
				?>
                <div class="wlt-top-content"><h2>تغییر وضعیت سفارش</h2><form action="" method="post">
                <table class="form-table">
                    <tbody>
                    <tr class="user-role-wrap">
                        <th><label for="role">تغییر وضعیت به</label></th>
                        <td>
                            <select name="new-status">
								<?php
								for ( $i = 1; $i <= 9; $i ++ ) {
									echo '<option value="' . $i . '"' . selected( $_GET['status'], $i, true ) . '>' . Helper::show_status( $i ) . '</option>';
								}
								?>
                            </select>
                        </td>
                    </tr>
                    <tr class="user-role-wrap">
                        <th><label for="role">اطلاع رسانی شود به کاربر ؟</label></th>
                        <td>
                            <select name="is-notification">
                                <option value="yes">آری</option>
                                <option value="no">خیر</option>
                            </select>
                        </td>
                    </tr>
                    <input type="hidden" name="order_id" value="<?php echo $_GET['order_id']; ?>">
                    </tbody>
                </table>
				<?php
				submit_button( "تغییر وضعیت" );
				echo '</form></div>';

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
	global $wpdb;

if ( ! isset( $_GET['method'] ) ) {

	//Show Wp List Table
	Admin_Ui::wp_list_table( $this->factor_obj, "format-aside", get_admin_page_title(), array( 'link' => self::admin_link( "factor", array( "method" => "add" ) ), 'name' => 'ایجاد فاکتور' ), true );
} else {

if ( $_GET['method'] == "add" ) {
	?>
    <div class="wrap wps_actions"><h1 class="wp-heading-inline">
    <span class="dashicons dashicons-format-aside"></span> ایجاد فاکتور </h1>
    <form action="<?php echo add_query_arg( array( 'page' => 'factor' ), admin_url( "admin.php" ) ); ?>" method="post">
    <table class="form-table">
        <tbody>
        <tr class="user-role-wrap">
            <th><label for="role">فاکتور متعلق به سفارش</label></th>
            <td>
                <select name="order_id">
					<?php
					$query = $wpdb->get_results( "SELECT * FROM `z_order` WHERE `status` < 8 ORDER BY `id` DESC", ARRAY_A );
					foreach ( $query as $row ) {
						echo '<option value="' . $row['id'] . '">' . $row['id'] . '# - ' . Helper::get_user_full_name( $row['user_id'] ) . ' - ' . $row['title'] . '</option>';
					}
					?>
                </select>
            </td>
        </tr>

		<?php
		for ( $x = 1; $x <= 5; $x ++ ) {
			$v = '';
			if ( $x == 1 ) {
				$entry = Gravity_Form::get_entry( $row['entry_id'] );
				$v     = $entry[78];
			}
			?>
            <tr class="user-role-wrap">
                <th><label for="role">آیتم #<?php echo $x; ?></label></th>
                <td>
                    <input type="text" class="regular-text" name="item[]" value="<?php echo $v; ?>" <?php if ( $x == 1 ) {
						echo 'required="required"';
					} ?>>

                    &nbsp;
                    &nbsp;
                    &nbsp;
                    مبلغ به <?php echo Helper::currency(); ?>
                    <input type="text" class="regular-small only-numeric" name="price[]" value="" style="text-align: left; direction: ltr;" <?php if ( $x == 1 ) {
	                    echo 'required="required"';
                    } ?>>

                </td>
            </tr>
			<?php
		}
		?>

        <tr class="user-role-wrap">
            <th><label for="role">نوع فاکتور</label></th>
            <td>
                <select name="type">
                    <option value="1"><?php echo Helper::get_type_factor( 1 ); ?></option>
                    <option value="2"><?php echo Helper::get_type_factor( 2 ); ?></option>
                </select>
            </td>
        </tr>

        <tr class="user-role-wrap">
            <th><label for="role">تغییر وضعیت این سفارش به</label></th>
            <td>
                <select name="new-status-order">
					<?php
					for ( $i = 1; $i <= 9; $i ++ ) {
							echo '<option value="' . $i . '">' . Helper::show_status( $i ) . '</option>';
					}
					?>
                </select>
            </td>
        </tr>

        <tr class="user-role-wrap">
            <th><label for="role">اطلاع رسانی شود به کاربر ؟</label></th>
            <td>
                <select name="is-notification">
                    <option value="yes">آری</option>
                    <option value="no">خیر</option>
                </select>
            </td>
        </tr>

        <input type="hidden" name="content-action" value="add-factor">
        </tbody>
    </table>
	<?php
	submit_button( "ثبت" );
	echo '</form>
    </div>';

}


}
}

	//Admin Notice
	public function admin_notice_factor() {
		if ( self::in_page( 'factor' ) and isset( $_GET['alert'] ) ) {
			switch ( $_GET['alert'] ) {

				//Delete Alert
				case "delete":
					Admin_Ui::wp_admin_notice( __( "آیتم های انتخابی با موفقیت حذف گردید", 'wp-statistics-actions' ), "success" );
					break;

				//Change status
				case "change-status":
					Admin_Ui::wp_admin_notice( __( "تغییر وضعیت فاکتور با موفقیت انجام شد", 'wp-statistics-actions' ), "success" );
					break;

			}
		}
	}

	//Custom Script css/Js
	public function wlt_script_factor() {
		if ( self::in_page( 'factor' ) ) {
			echo '<style>table.widefat th.column-factor_id {width: 260px;}</style>';
		}
	}

	//Top content Wp List Table
	public function wlt_top_factor() {
		if ( self::in_page( 'factor' ) and isset( $_GET['top'] ) ) {

			//Top Content for Status
			if ( $_GET['top'] == "change-payment-status" ) {
				?>
                <div class="wlt-top-content"><h2>تغییر وضعیت فاکتور</h2>
                <form action="" method="post">
                <table class="form-table">
                    <tbody>
                    <tr class="user-role-wrap">
                        <th><label for="role">تغییر وضعیت به</label></th>
                        <td>
                            <select name="new-status-factor">
								<?php
								for ( $i = 1; $i <= 2; $i ++ ) {
									echo '<option value="' . $i . '"' . selected( $_GET['status'], $i, true ) . '>' . Helper::get_status_factor( $i ) . '</option>';
								}
								?>
                            </select>
                        </td>
                    </tr>

                    <tr class="user-role-wrap">
                        <th><label for="role">تغییر وضعیت این سفارش به</label></th>
                        <td>
                            <select name="new-status-order">
								<?php
								for ( $i = 1; $i <= 9; $i ++ ) {
									echo '<option value="' . $i . '"' . selected( $_GET['order_status'], $i, true ) . '>' . Helper::show_status( $i ) . '</option>';
								}
								?>
                            </select>
                        </td>
                    </tr>

                    <tr class="user-role-wrap">
                        <th><label for="role">اطلاع رسانی شود به کاربر ؟</label></th>
                        <td>
                            <select name="is-notification">
                                <option value="yes">آری</option>
                                <option value="no">خیر</option>
                            </select>
                        </td>
                    </tr>
                    <input type="hidden" name="order_id" value="<?php echo $_GET['order_id']; ?>">
                    <input type="hidden" name="factor_id" value="<?php echo $_GET['factor_id']; ?>">
                    </tbody>
                </table>
				<?php
				submit_button( "تغییر وضعیت" );
				echo '</form></div>';

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

	//Custom Script css/Js
	public function wlt_script_payment() {
		if ( self::in_page( 'payment' ) ) {

		}
	}

	//Top content Wp List Table
	public function wlt_top_payment() {
		if ( self::in_page( 'factor' ) and isset( $_GET['top'] ) ) {

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

	//Custom Script css/Js
	public function wlt_script_ticket() {
		if ( self::in_page( 'ticket' ) ) {

		}
	}

	//Top content Wp List Table
	public function wlt_top_ticket() {
		if ( self::in_page( 'ticket' ) and isset( $_GET['top'] ) ) {

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